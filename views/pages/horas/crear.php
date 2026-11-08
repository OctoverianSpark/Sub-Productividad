<?php
    // Valor de un campo de la entrada (escapado)
    $v = fn(array $e, string $k, $defecto = "") => s((string) ($e[$k] ?? $defecto));
    $marcado = fn(array $e, string $k) => (($e[$k] ?? "") === "si") ? "checked" : "";
    $camposHoras = [
        "diurnas_ordinarias" => "DIURNAS ORDINARIAS",
        "nocturnas_ordinarias" => "NOCTURNAS ORDINARIAS",
        "diurnas_extras" => "DIURNAS EXTRAS",
        "nocturnas_extras" => "NOCTURNAS EXTRAS",
    ];
?>
<h1 class="title">Ingresar Horas Extras</h1>

<form method="post" class="form-times" novalidate
    data-inicio-nocturno="<?php echo s($jornada["inicioNocturno"]) ?>"
    data-fin-nocturno="<?php echo s($jornada["finNocturno"]) ?>"
    data-horas-dia="<?php echo s($jornada["horasDia"]) ?>"
    data-max-horas="<?php echo s($jornada["maxHoras"]) ?>"
    data-nuevo="<?php echo empty($errores) ? "1" : "0" ?>">
    <?php echo csrfField() ?>

    <div class="container-bg">
        <picture class="logo-1" loading="lazy">
            <source srcset="/build/img/collectsLogo.webp" type="image/webp">
            <img src="/build/img/collectsLogo.png" type="image/png" alt="">
        </picture>
        <picture class="logo-2" loading="lazy">
            <source srcset="/build/img/fullTimeLogo.webp" type="image/webp">
            <img src="/build/img/fullTimeLogo.png" type="image/png" alt="">
        </picture>
        <picture class="logo-3" loading="lazy">
            <source srcset="/build/img/partTimeLogo.webp" type="image/webp">
            <img src="/build/img/partTimeLogo.png" type="image/png" alt="">
        </picture>
        <picture class="logo-4" loading="lazy">
            <source srcset="/build/img/paymentsLogo.webp" type="image/webp">
            <img src="/build/img/paymentsLogo.png" type="image/png" alt="">
        </picture>
        <picture class="logo-5" loading="lazy">
            <source srcset="/build/img/reportsLogo.webp" type="image/webp">
            <img src="/build/img/reportsLogo.png" type="image/png" alt="">
        </picture>
    </div>

    <?php if (!empty($errores)) { ?>
        <?php include_once "../includes/templates/error-modal.php" ?>
    <?php } ?>

    <div class="index-page dragable">
        <div class="add-remove">
            <button type="button" class="page-add" title="Agregar entrada (copia fechas y cliente de la ultima)"><i class="bi bi-plus-circle-fill"></i></button>
            <button type="button" class="page-remove" title="Quitar la ultima entrada"><i class="bi bi-dash-circle-fill"></i></button>
        </div>
        <div class="pages">
            <?php foreach ($entradas as $i => $e) { ?>
                <a href="#entrada-<?php echo $i + 1 ?>" id="pagina-<?php echo $i + 1 ?>" class="page-number"><?php echo $i + 1 ?></a>
            <?php } ?>
        </div>
        <button type="button" id="button-charge" class="boton-morado-inline">Cargar Horas</button>
    </div>

    <div class="container-times">
        <?php foreach ($entradas as $i => $e) { $n = $i + 1; $name = "horas[$i]"; ?>
        <div class="container-page" id="entrada-<?php echo $n ?>">
            <h3 class="subtitle">Entrada <?php echo $n ?></h3>

            <fieldset class="container-inputs">
                <legend>INFORMACION PRINCIPAL</legend>
                <div class="container-input input-horas">
                    <label for="empleado-<?php echo $n ?>">EMPLEADO</label>
                    <select name="<?php echo $name ?>[empleado]" id="empleado-<?php echo $n ?>" data-campo="empleado" class="js-select2" required>
                        <option value="">-- Selecciona un empleado --</option>
                        <?php foreach ($empleados as $empleado) { $nombre = $empleado->nombreCompleto(); ?>
                            <option value="<?php echo s($nombre) ?>" <?php echo mb_strtolower($nombre) === mb_strtolower((string) ($e["empleado"] ?? "")) ? "selected" : "" ?>><?php echo s(mb_strtoupper($nombre)) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="container-input input-horas">
                    <label for="cliente-<?php echo $n ?>">CLIENTE</label>
                    <select name="<?php echo $name ?>[cliente]" id="cliente-<?php echo $n ?>" data-campo="cliente" class="js-select2" required>
                        <option value="administrativo">HORAS ADMINISTRATIVAS</option>
                        <?php foreach ($clientes as $cliente) { $nombre = $cliente->nombreCompleto(); ?>
                            <option value="<?php echo s($nombre) ?>" <?php echo mb_strtolower($nombre) === mb_strtolower((string) ($e["cliente"] ?? "")) ? "selected" : "" ?>><?php echo s(mb_strtoupper($nombre)) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </fieldset>

            <fieldset class="container-inputs">
                <legend>INFORMACION DE JORNADA</legend>
                <div class="container-input">
                    <label for="festivo-<?php echo $n ?>">FESTIVO</label>
                    <input type="checkbox" name="<?php echo $name ?>[festivo]" id="festivo-<?php echo $n ?>" data-campo="festivo" value="si" <?php echo $marcado($e, "festivo") ?>>
                    <span class="festivo-aviso" aria-live="polite"></span>
                </div>
                <div class="container-input input-horas">
                    <label for="inicio-<?php echo $n ?>">INICIO DE JORNADA</label>
                    <input type="datetime-local" name="<?php echo $name ?>[inicio]" id="inicio-<?php echo $n ?>" data-campo="inicio" value="<?php echo $v($e, "inicio") ?>" required>
                </div>
                <div class="container-input input-horas">
                    <span class="label-radio">HORAS DE ALMUERZO</span>
                    <div class="radio">
                        <?php foreach (["0", "1", "2"] as $alm) { ?>
                            <div class="radio-input">
                                <label for="almuerzo-<?php echo $n ?>-<?php echo $alm ?>"><?php echo $alm ?></label>
                                <input type="radio" name="<?php echo $name ?>[almuerzo]" value="<?php echo $alm ?>" id="almuerzo-<?php echo $n ?>-<?php echo $alm ?>" data-campo="almuerzo" <?php echo ((string) ($e["almuerzo"] ?? "2")) === $alm ? "checked" : "" ?>>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="container-input input-horas">
                    <label for="final-<?php echo $n ?>">FINAL DE JORNADA</label>
                    <input type="datetime-local" name="<?php echo $name ?>[final]" id="final-<?php echo $n ?>" data-campo="final" value="<?php echo $v($e, "final") ?>" required>
                </div>
            </fieldset>

            <fieldset class="container-inputs input-horas-ordinarias-extras">
                <legend>REGISTRO DE HORAS <small>(se calculan solas, puedes corregirlas)</small></legend>
                <?php foreach ($camposHoras as $campo => $texto) { ?>
                    <div class="container-input input-horas range-number-container">
                        <label for="<?php echo $campo ?>-<?php echo $n ?>"><?php echo $texto ?></label>
                        <input type="range" min="0" max="16" step="0.25" value="<?php echo $v($e, $campo, 0) ?>" aria-hidden="true" tabindex="-1">
                        <input type="number" name="<?php echo $name ?>[<?php echo $campo ?>]" id="<?php echo $campo ?>-<?php echo $n ?>" data-campo="<?php echo $campo ?>" value="<?php echo $v($e, $campo, 0) ?>" min="0" max="24" step="0.01" class="calcTime" required>
                    </div>
                <?php } ?>
                <p class="resumen-horas" aria-live="polite"></p>
            </fieldset>

            <div class="container-fieldsets">
                <fieldset class="container-inputs">
                    <legend>LOGISTICA</legend>
                    <div class="container-input">
                        <label for="cena-<?php echo $n ?>">CENA</label>
                        <input type="checkbox" name="<?php echo $name ?>[cena]" id="cena-<?php echo $n ?>" data-campo="cena" value="si" <?php echo $marcado($e, "cena") ?>>
                    </div>
                    <div class="container-input">
                        <label for="taxi-<?php echo $n ?>">TAXI</label>
                        <input type="checkbox" name="<?php echo $name ?>[taxi]" id="taxi-<?php echo $n ?>" data-campo="taxi" value="si" <?php echo $marcado($e, "taxi") ?>>
                    </div>
                </fieldset>

                <fieldset class="container-inputs">
                    <legend>COMENTARIOS</legend>
                    <textarea name="<?php echo $name ?>[comentarios]" id="comentarios-<?php echo $n ?>" data-campo="comentarios" maxlength="500"><?php echo $v($e, "comentarios") ?></textarea>
                </fieldset>
            </div>
        </div>
        <?php } ?>
    </div>

    <?php include_once "../includes/templates/confirm-modal.php" ?>
</form>
