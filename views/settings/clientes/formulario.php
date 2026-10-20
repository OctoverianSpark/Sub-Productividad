<?php if (!empty($errores)) { ?>
    <?php include_once "../includes/templates/error-modal.php" ?>
<?php } ?>

<?php echo csrfField() ?>

<main class="settings-clientes-from">
    <div class="container-input inputs-clientes">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" value="<?php echo s($cliente->nombre) ?>" name="clientes[nombre]" placeholder="Nombre" required>
    </div>
    <div class="container-input inputs-clientes">
        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" value="<?php echo s($cliente->apellido) ?>" name="clientes[apellido]" placeholder="Apellido" required>
    </div>
    <div class="container-input inputs-clientes">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="clientes[tipo]">
            <?php foreach (array_keys(\MVC\Config::get("clientes.tarifas", [])) as $tipo) { ?>
                <option value="<?php echo s($tipo) ?>" <?php echo (strtolower((string) $cliente->tipo) === $tipo) ? "selected" : "" ?>><?php echo s(mb_strtoupper($tipo)) ?></option>
            <?php } ?>
        </select>
    </div>
</main>
