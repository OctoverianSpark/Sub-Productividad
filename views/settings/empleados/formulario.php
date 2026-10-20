<div class="form-empleados">
    <?php if (!empty($errores)) { ?>
        <?php include_once "../includes/templates/error-modal.php" ?>
    <?php } ?>

    <?php echo csrfField() ?>

    <?php
        // Opciones de los selects: valor => texto
        $tiposDocumento = ["ppt" => "PPT", "pasaporte" => "PASAPORTE", "cc" => "CEDULA COLOMBIANA", "cv" => "CEDULA VENEZOLANA", "ce" => "CEDULA EXTRANJERIA"];
        $sedes = ["venezuela" => "VENEZUELA", "colombia" => "COLOMBIA"];
        $modalidades = ["oficina" => "OFICINA", "hogar" => "HOGAR"];
        $opciones = function (array $lista, $actual) {
            foreach ($lista as $valor => $texto) {
                $sel = (strtolower((string) $actual) === $valor) ? "selected" : "";
                echo '<option value="' . s($valor) . '" ' . $sel . '>' . s($texto) . '</option>';
            }
        };
    ?>

    <div class="container-input input-empleados">
        <label for="name">NOMBRES</label>
        <input type="text" name="empleados[nombre]" id="name" value="<?php echo s(mb_strtoupper((string) $empleados->nombre)) ?>" placeholder="Nombres" required>
    </div>
    <div class="container-input input-empleados">
        <label for="lastName">APELLIDOS</label>
        <input type="text" name="empleados[apellido]" id="lastName" value="<?php echo s(mb_strtoupper((string) $empleados->apellido)) ?>" placeholder="Apellidos" required>
    </div>
    <div class="container-input input-empleados">
        <label for="docType">TIPO DE DOCUMENTO</label>
        <select name="empleados[tipo_documento]" id="docType">
            <?php $opciones($tiposDocumento, $empleados->tipo_documento) ?>
        </select>
    </div>
    <div class="container-input input-empleados">
        <label for="document">DOCUMENTO</label>
        <input type="text" name="empleados[documento]" id="document" value="<?php echo s($empleados->documento) ?>" placeholder="Número de documento" required>
    </div>
    <div class="container-input input-empleados">
        <label for="sede">SEDE</label>
        <select name="empleados[sede]" id="sede">
            <?php $opciones($sedes, $empleados->sede) ?>
        </select>
    </div>
    <div class="container-input input-empleados">
        <label for="modalidad">MODALIDAD DE TRABAJO</label>
        <select name="empleados[modalidad]" id="modalidad">
            <?php $opciones($modalidades, $empleados->modalidad) ?>
        </select>
    </div>
    <div class="container-input input-empleados">
        <label for="cargo">CARGO</label>
        <input type="text" id="cargo" name="empleados[cargo]" value="<?php echo s(mb_strtoupper((string) $empleados->cargo)) ?>" placeholder="Cargo del empleado">
    </div>
    <div class="container-input input-empleados">
        <label for="munny">BASE SALARIAL</label>
        <input type="number" step="0.01" min="0" name="empleados[salario]" id="munny" value="<?php echo s((float) $empleados->salario) ?>">
    </div>
</div>
