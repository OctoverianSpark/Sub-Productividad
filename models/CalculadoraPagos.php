<?php

namespace Models;

use DateTime;
use MVC\Config;

/**
 * Unico lugar donde se calculan montos de horas extras.
 * Las tarifas se leen de config/parametros.json ("nomina" y "clientes").
 */
class CalculadoraPagos
{
    // Domingo o marcado como festivo
    public static function esDominicalOFestivo(Time $time): bool
    {
        return (new DateTime($time->inicio))->format("w") === "0" || strtolower($time->festivo) === "si";
    }

    // =================
    // PAGO A EMPLEADOS
    // =================

    /**
     * @return array{diurnas_monto: float, nocturnas_monto: float, moneda: string}
     */
    public static function pagoEmpleado(Time $time, ?Empleados $empleado): array
    {
        $vacio = ["diurnas_monto" => 0.0, "nocturnas_monto" => 0.0, "moneda" => ""];

        if (!$empleado) {
            error_log("Empleado no encontrado para el calculo de pago: " . $time->empleado);
            return $vacio;
        }

        $modalidad = strtolower($empleado->modalidad);
        $sede = strtolower($empleado->sede);
        $dominical = self::esDominicalOFestivo($time);

        $do = (float) $time->diurnas_ordinarias;
        $no = (float) $time->nocturnas_ordinarias;
        $de = (float) $time->diurnas_extras;
        $ne = (float) $time->nocturnas_extras;

        if ($modalidad === "oficina" && $sede === "colombia") {
            $t = Config::get("nomina.colombia_oficina");
            $hora = self::salarioHora($empleado);

            $diurnaExtra = round($hora + $hora * $t["recargo_diurna_extra"], 2);
            $nocturnaExtra = round($hora + $hora * $t["recargo_nocturna_extra"], 2);
            $horaDominical = round($hora + $hora * $t["recargo_dominical"], 2);
            $diurnaExtraDominical = round($hora + $hora * $t["recargo_diurna_extra_dominical"], 2);
            $nocturnaExtraDominical = round($hora + $hora * $t["recargo_nocturna_extra_dominical"], 2);

            return [
                "diurnas_monto" => $dominical ? $do * $horaDominical + $de * $diurnaExtraDominical : $de * $diurnaExtra,
                "nocturnas_monto" => $dominical ? $no * $horaDominical + $ne * $nocturnaExtraDominical : $ne * $nocturnaExtra,
                "moneda" => $t["moneda"],
            ];
        }

        if ($modalidad === "hogar") {
            $t = Config::get("nomina.hogar");
            $valor = (float) $t["valor_hora"];

            return [
                "diurnas_monto" => $dominical ? ($do + $de) * $valor : $de * $valor,
                "nocturnas_monto" => $dominical ? ($no + $ne) * $valor : $ne * $valor,
                "moneda" => $t["moneda"],
            ];
        }

        if ($modalidad === "oficina" && $sede === "venezuela") {
            $t = Config::get("nomina.venezuela_oficina");
            $hora = self::salarioHora($empleado);
            $extra = round($hora * self::factorVenezuela((float) $empleado->salario), 2);

            return [
                "diurnas_monto" => $dominical ? $do * $hora + $de * $extra : $de * $extra,
                "nocturnas_monto" => $dominical ? $no * $hora + $ne * $extra : $ne * $extra,
                "moneda" => $t["moneda"],
            ];
        }

        error_log("Modalidad/sede sin tarifa configurada: {$modalidad}/{$sede} ({$time->empleado})");
        return $vacio;
    }

    private static function salarioHora(Empleados $empleado): float
    {
        return (float) $empleado->salario / (float) Config::get("nomina.horas_mes", 240);
    }

    /**
     * Factor sobre el salario por hora, interpolado linealmente entre los puntos de la tabla
     * "tabla_valor_hora_extra". En cada punto exacto el valor de la hora extra es el configurado.
     */
    private static function factorVenezuela(float $salario): float
    {
        $horasMes = (float) Config::get("nomina.horas_mes", 240);
        $tabla = Config::get("nomina.venezuela_oficina.tabla_valor_hora_extra", []);
        usort($tabla, fn($a, $b) => $a["salario"] <=> $b["salario"]);

        $puntos = array_map(fn($p) => [
            "salario" => (float) $p["salario"],
            "factor" => $p["valor_hora_extra"] / ($p["salario"] / $horasMes),
        ], $tabla);

        $primero = $puntos[0];
        $ultimo = $puntos[count($puntos) - 1];

        if ($salario <= $primero["salario"]) return $primero["factor"];
        if ($salario >= $ultimo["salario"]) return $ultimo["factor"];

        for ($i = 1; $i < count($puntos); $i++) {
            if ($salario < $puntos[$i]["salario"]) {
                [$a, $b] = [$puntos[$i - 1], $puntos[$i]];
                return $a["factor"] + ($b["factor"] - $a["factor"]) * ($salario - $a["salario"]) / ($b["salario"] - $a["salario"]);
            }
        }
        return $ultimo["factor"];
    }

    // =================
    // COBRO A CLIENTES
    // =================

    public static function resumenClienteVacio(string $cliente): array
    {
        return [
            "cliente" => $cliente,
            "diurnas" => 0,
            "diurnas_domingo" => 0,
            "diurnas_ordinarias_domingo" => 0,
            "nocturnas" => 0,
            "nocturnas_domingo" => 0,
            "nocturnas_ordinarias_domingo" => 0,
            "diurnas_monto" => 0,
            "nocturnas_monto" => 0,
            "logistica" => 0,
        ];
    }

    // Acumula una hora en el resumen del cliente segun su tipo (nuevo/viejo)
    public static function acumularCliente(array &$resumen, Time $time, string $tipoCliente): void
    {
        $tarifa = Config::get("clientes.tarifas." . strtolower($tipoCliente));
        if (!$tarifa) {
            error_log("Tipo de cliente sin tarifa configurada: $tipoCliente");
            $tarifa = ["diurna" => 0, "nocturna" => 0, "diurna_dominical" => 0, "nocturna_dominical" => 0];
        }
        $logistica = Config::get("clientes.logistica", []);

        $domingo = (new DateTime($time->inicio))->format("w") === "0";
        $de = (float) $time->diurnas_extras;
        $ne = (float) $time->nocturnas_extras;
        $do = (float) $time->diurnas_ordinarias;
        $no = (float) $time->nocturnas_ordinarias;

        if ($domingo) {
            $resumen["diurnas_domingo"] += $de;
            $resumen["diurnas_ordinarias_domingo"] += $do;
            $resumen["nocturnas_domingo"] += $ne;
            $resumen["nocturnas_ordinarias_domingo"] += $no;
            $resumen["diurnas_monto"] += ($de + $do) * $tarifa["diurna_dominical"];
            $resumen["nocturnas_monto"] += $ne * $tarifa["nocturna_dominical"];
        } else {
            $resumen["diurnas"] += $de;
            $resumen["nocturnas"] += $ne;
            $resumen["diurnas_monto"] += $de * $tarifa["diurna"];
            $resumen["nocturnas_monto"] += $ne * $tarifa["nocturna"];
        }

        if (strtolower($time->cena) === "si") $resumen["logistica"] += $logistica["cena"] ?? 0;
        if (strtolower($time->taxi) === "si") $resumen["logistica"] += $logistica["taxi"] ?? 0;
    }
}
