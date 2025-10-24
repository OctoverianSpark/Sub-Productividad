<?php if (!empty($errores)) { ?>
    <?php include_once "../includes/templates/error-modal.php" ?>
<?php } ?>

<main class="settings-clientes-from">
    <div class="container-input inputs-clientes">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" value="<?php echo $cliente->nombre ?>" name="clientes[nombre]" placeholder="Nombre">
    </div>
    <div class="container-input inputs-clientes">
        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" value="<?php echo $cliente->apellido ?>" name="clientes[apellido]" placeholder="Apellido">
    </div>
    <div class="container-input inputs-clientes">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="clientes[tipo]">
            <option value="full time" <?php echo ($cliente->tipo == "full time") ? "selected" : "" ?>>FULL TIME</option>
            <option value="part time" <?php echo ($cliente->tipo == "part time") ? "selected" : "" ?>>PART TIME</option>
        </select>
    </div>
</main>