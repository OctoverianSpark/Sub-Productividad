<div class="form-empleados">
    <?php if (!empty($errores)) { ?>
        <?php include_once "../includes/templates/error-modal.php" ?>
    <?php } ?>


    <div class="container-input input-empleados">
        <label for="name">NOMBRES</label>
        <input type="text" name="empleados[nombre]" id="name" value="<?php echo strtoupper($empleados->nombre) ?>" placeholder="Nombres" required>
    </div>
    <div class="container-input input-empleados">
        <label for="lastName">APELLIDOS</label>
        <input type="text" name="empleados[apellido]" id="lastName" value="<?php echo strtoupper($empleados->apellido) ?>" placeholder="Apellidos" required>
    </div>
    <div class="container-input input-empleados">
        <label for="docType">TIPO DE DOCUMENTO</label>
        <select name="empleados[tipo_documento]" id="docType">
            <option <?php echo ($empleados->tipo_documento === "ppt") ? "selected" : "" ?> value="ppt">PPT</option>
            <option <?php echo ($empleados->tipo_documento === "pasaporte") ? "selected" : "" ?> value="pasaporte">PASAPORTE</option>
            <option <?php echo ($empleados->tipo_documento === "cc") ? "selected" : "" ?> value="cc">CEDULA COLOMBIANA</option>
            <option <?php echo ($empleados->tipo_documento === "cv") ? "selected" : "" ?> value="cv">CEDULA VENEZOLANA</option>
            <option <?php echo ($empleados->tipo_documento === "ce") ? "selected" : "" ?> value="ce">CEDULA EXTRANJERIA</option>
        </select>
    </div>
    <div class="container-input input-empleados">
        <label for="document">DOCUMENTO</label>
        <input type="text" name="empleados[documento]" id="document" value="<?php echo $empleados->documento ?>" placeholder="Número de documento" required>
    </div>
    <div class="container-input input-empleados">
        <label for="sede">SEDE</label>
        <select name="empleados[sede]" id="sede">
            <option <?php echo ($empleados->sede == "venezuela") ? "selected" : "" ?> value="venezuela">VENEZUELA</option>
            <option <?php echo ($empleados->sede == "colombia") ? "selected" : "" ?> value="colombia">COLOMBIA</option>
        </select>
    </div>
    <div class="container-input input-empleados">
        <label for="modalidad">MODALIDAD DE TRABAJO</label>
        <select name="empleados[modalidad]" id="modalidad">
            <option <?php echo ($empleados->modalidad == "oficina") ? "selected" : "" ?> value="oficina">OFICINA</option>
            <option <?php echo ($empleados->modalidad == "hogar") ? "selected" : "" ?> value="hogar">HOGAR</option>
        </select>
    </div>
    <div class="container-input input-empleados">
        <label for="cargo">CARGO</label>
        <input type="text" id="cargo" name="empleados[cargo]" value="<?php echo strtoupper($empleados->cargo) ?>" placeholder="Cargo del empleado">
    </div>
    <div class="container-input input-empleados">
        <label for="munny">BASE SALARIAL</label>
        <input type="number" name="empleados[salario]" id="munny" value=<?php echo floatval($empleados->salario) ?>>
    </div>
</div>