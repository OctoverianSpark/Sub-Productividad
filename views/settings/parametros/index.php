<?php
    $nomina = $p["nomina"];
    $col = $nomina["colombia_oficina"];
    $hog = $nomina["hogar"];
    $ven = $nomina["venezuela_oficina"];
    $cli = $p["clientes"];
    $permisosActuales = $p["roles"]["permisos"] ?? [];
    $sinAuditoria = $p["roles"]["cargan_sin_auditoria"] ?? [];
    $num = fn($v) => $v === "" ? "" : s(rtrim(rtrim(number_format((float) $v, 6, ".", ""), "0"), "."));
?>
<main class="main-parametros">

    <h1 class="title">Parametros del Sistema</h1>
    <p class="parametros-ayuda">Los cambios se aplican de inmediato a los calculos y permisos. Cada vez que guardas se crea un respaldo automatico de la version anterior.</p>

    <?php if (!empty($errores)) { ?>
        <?php include_once "../includes/templates/error-modal.php" ?>
    <?php } ?>

    <form method="post" class="form-parametros">
        <?php echo csrfField() ?>

        <!-- ================= NOMINA ================= -->
        <fieldset class="parametros-seccion">
            <legend>Pago a empleados</legend>

            <div class="parametros-grid">
                <div class="container-input">
                    <label for="horas_mes">Horas laborales al mes</label>
                    <input type="number" step="any" min="1" id="horas_mes" name="nomina[horas_mes]" value="<?php echo $num($nomina["horas_mes"]) ?>" required>
                    <small>Se usa para sacar el valor de la hora: salario / horas.</small>
                </div>
            </div>

            <h3>Colombia - Oficina</h3>
            <div class="parametros-grid">
                <div class="container-input">
                    <label for="col_moneda">Moneda</label>
                    <input type="text" id="col_moneda" name="nomina[colombia_oficina][moneda]" value="<?php echo s($col["moneda"]) ?>" maxlength="20" required>
                </div>
                <?php foreach ($recargos as $clave => $nombre) { ?>
                    <div class="container-input">
                        <label for="col_<?php echo s($clave) ?>">Recargo <?php echo s($nombre) ?> (%)</label>
                        <input type="number" step="any" min="0" id="col_<?php echo s($clave) ?>" name="nomina[colombia_oficina][<?php echo s($clave) ?>]" value="<?php echo $num(($col[$clave] ?? 0) * 100) ?>" required>
                    </div>
                <?php } ?>
            </div>

            <h3>Trabajo desde el hogar</h3>
            <div class="parametros-grid">
                <div class="container-input">
                    <label for="hog_moneda">Moneda</label>
                    <input type="text" id="hog_moneda" name="nomina[hogar][moneda]" value="<?php echo s($hog["moneda"]) ?>" maxlength="20" required>
                </div>
                <div class="container-input">
                    <label for="hog_valor">Valor por hora extra</label>
                    <input type="number" step="any" min="0" id="hog_valor" name="nomina[hogar][valor_hora]" value="<?php echo $num($hog["valor_hora"]) ?>" required>
                </div>
            </div>

            <h3>Venezuela - Oficina</h3>
            <div class="parametros-grid">
                <div class="container-input">
                    <label for="ven_moneda">Moneda</label>
                    <input type="text" id="ven_moneda" name="nomina[venezuela_oficina][moneda]" value="<?php echo s($ven["moneda"]) ?>" maxlength="20" required>
                </div>
            </div>
            <p class="parametros-ayuda-sm">Valor de la hora extra segun el salario. Para salarios intermedios se calcula proporcionalmente entre las dos filas mas cercanas.</p>
            <table class="tabla-parametros" data-tabla="venezuela">
                <thead>
                    <tr><th>Salario mensual</th><th>Valor hora extra</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach (($ven["tabla_valor_hora_extra"] ?: [["salario" => "", "valor_hora_extra" => ""]]) as $i => $fila) { ?>
                        <tr>
                            <td><input type="number" step="any" min="0" name="nomina[venezuela_oficina][tabla][<?php echo (int) $i ?>][salario]" value="<?php echo $num($fila["salario"]) ?>"></td>
                            <td><input type="number" step="any" min="0" name="nomina[venezuela_oficina][tabla][<?php echo (int) $i ?>][valor_hora_extra]" value="<?php echo $num($fila["valor_hora_extra"]) ?>"></td>
                            <td><button type="button" class="boton-rojo-inline js-quitar-fila">Quitar</button></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <button type="button" class="boton-fucsia-inline js-agregar-fila" data-tabla="venezuela">+ Agregar fila</button>
        </fieldset>

        <!-- ================= CLIENTES ================= -->
        <fieldset class="parametros-seccion">
            <legend>Cobro a clientes</legend>

            <div class="parametros-grid">
                <div class="container-input">
                    <label for="cli_moneda">Moneda</label>
                    <input type="text" id="cli_moneda" name="clientes[moneda]" value="<?php echo s($cli["moneda"]) ?>" maxlength="20" required>
                </div>
                <div class="container-input">
                    <label for="cli_cena">Logistica: cena</label>
                    <input type="number" step="any" min="0" id="cli_cena" name="clientes[logistica][cena]" value="<?php echo $num($cli["logistica"]["cena"] ?? 0) ?>" required>
                </div>
                <div class="container-input">
                    <label for="cli_taxi">Logistica: taxi</label>
                    <input type="number" step="any" min="0" id="cli_taxi" name="clientes[logistica][taxi]" value="<?php echo $num($cli["logistica"]["taxi"] ?? 0) ?>" required>
                </div>
            </div>

            <p class="parametros-ayuda-sm">Tarifa por hora segun el tipo de cliente. Un tipo nuevo aparece automaticamente al crear o editar un cliente.</p>
            <table class="tabla-parametros" data-tabla="tipos">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <?php foreach ($tarifasCliente as $texto) { ?><th><?php echo s($texto) ?></th><?php } ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; foreach (($cli["tarifas"] ?: ["" => []]) as $tipo => $tarifa) { ?>
                        <tr>
                            <td><input type="text" name="tipos[<?php echo $i ?>][nombre]" value="<?php echo s($tipo) ?>" maxlength="30" pattern="[a-z0-9_]+" title="Solo minusculas, numeros o _"></td>
                            <?php foreach ($tarifasCliente as $clave => $texto) { ?>
                                <td><input type="number" step="any" min="0" name="tipos[<?php echo $i ?>][<?php echo s($clave) ?>]" value="<?php echo $num($tarifa[$clave] ?? 0) ?>"></td>
                            <?php } ?>
                            <td><button type="button" class="boton-rojo-inline js-quitar-fila">Quitar</button></td>
                        </tr>
                    <?php $i++; } ?>
                </tbody>
            </table>
            <button type="button" class="boton-fucsia-inline js-agregar-fila" data-tabla="tipos">+ Agregar tipo</button>
        </fieldset>

        <!-- ================= PERMISOS ================= -->
        <fieldset class="parametros-seccion">
            <legend>Permisos por rol</legend>
            <p class="parametros-ayuda-sm">"Todos" da el permiso a cualquier usuario con acceso. GOD siempre conserva Configuracion, Usuarios y Parametros.</p>

            <div class="tabla-scroll">
                <table class="tabla-parametros tabla-permisos">
                    <thead>
                        <tr>
                            <th>Permiso</th>
                            <th>Todos</th>
                            <?php foreach ($roles as $rol) { ?><th><?php echo s(mb_strtoupper($rol)) ?></th><?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permisos as $permiso => $descripcion) { ?>
                            <?php
                                $asignados = $permisosActuales[$permiso] ?? [];
                                $paraTodos = in_array("*", $asignados, true);
                            ?>
                            <tr>
                                <td class="texto-permiso"><?php echo s($descripcion) ?></td>
                                <td><input type="checkbox" class="js-permiso-todos" name="permisos_todos[<?php echo s($permiso) ?>]" value="1" <?php echo $paraTodos ? "checked" : "" ?>></td>
                                <?php foreach ($roles as $rol) { ?>
                                    <td><input type="checkbox" name="permisos[<?php echo s($permiso) ?>][]" value="<?php echo s($rol) ?>" <?php echo (!$paraTodos && in_array($rol, $asignados, true)) ? "checked" : "" ?> <?php echo $paraTodos ? "disabled" : "" ?>></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td class="texto-permiso">Sus horas NO pasan por auditoria</td>
                            <td></td>
                            <?php foreach ($roles as $rol) { ?>
                                <td><input type="checkbox" name="sin_auditoria[]" value="<?php echo s($rol) ?>" <?php echo in_array($rol, $sinAuditoria, true) ? "checked" : "" ?>></td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <!-- ================= GENERAL ================= -->
        <fieldset class="parametros-seccion">
            <legend>General</legend>
            <div class="parametros-grid">
                <div class="container-input">
                    <label for="inactividad">Cerrar sesion tras inactividad (minutos)</label>
                    <input type="number" min="5" max="1440" id="inactividad" name="general[sesion_inactividad_minutos]" value="<?php echo (int) $p["app"]["sesion_inactividad_minutos"] ?>" required>
                </div>
                <div class="container-input">
                    <label for="pp_opciones">Opciones de registros por pagina</label>
                    <input type="text" id="pp_opciones" name="general[por_pagina_permitidos]" value="<?php echo s(implode(", ", $p["paginacion"]["por_pagina_permitidos"])) ?>" required>
                    <small>Separadas por coma, ej: 20, 50, 100</small>
                </div>
                <div class="container-input">
                    <label for="pp_defecto">Registros por pagina por defecto</label>
                    <input type="number" min="1" id="pp_defecto" name="general[por_pagina_defecto]" value="<?php echo (int) $p["paginacion"]["por_pagina_defecto"] ?>" required>
                </div>
            </div>
        </fieldset>

        <div class="parametros-acciones">
            <a href="/settings" class="boton-fucsia-inline">Cancelar</a>
            <button type="submit" class="boton-morado-block" onclick="return confirm('¿Guardar los parametros? Los nuevos valores se aplican de inmediato.')">Guardar parametros</button>
        </div>
    </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Agregar fila: clona la ultima fila de la tabla con los campos vacios y un indice nuevo
    document.querySelectorAll(".js-agregar-fila").forEach((boton) => {
        boton.addEventListener("click", () => {
            const tbody = document.querySelector(`table[data-tabla="${boton.dataset.tabla}"] tbody`);
            const modelo = tbody.querySelector("tr");
            if (!modelo) return;
            const indice = Date.now();
            const nueva = modelo.cloneNode(true);
            nueva.querySelectorAll("input").forEach((input) => {
                input.value = "";
                input.name = input.name.replace(/\[\d+\]/, `[${indice}]`);
            });
            tbody.appendChild(nueva);
        });
    });

    // Quitar fila (siempre queda al menos una)
    document.addEventListener("click", (e) => {
        if (!e.target.classList.contains("js-quitar-fila")) return;
        const tbody = e.target.closest("tbody");
        if (tbody.querySelectorAll("tr").length > 1) e.target.closest("tr").remove();
    });

    // "Todos" desactiva las casillas por rol de ese permiso
    document.querySelectorAll(".js-permiso-todos").forEach((todos) => {
        todos.addEventListener("change", () => {
            todos.closest("tr").querySelectorAll("input[name^='permisos[']").forEach((c) => c.disabled = todos.checked);
        });
    });
});
</script>
