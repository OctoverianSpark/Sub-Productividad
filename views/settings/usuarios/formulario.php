<?php if (!empty($errores)) { ?>
    <?php include_once "../includes/templates/error-modal.php" ?>
<?php } ?>

<?php echo csrfField() ?>

<div class="user-data-from">

    <div class="container-input input-usuarios">
        <label for="domain-user">Usuario de Dominio</label>
        <input type="text" id="domain-user" name="user" value="<?php echo s($user->user) ?>" required>
    </div>

    <div class="container-input input-usuarios">
        <label for="mail-user">Usuario de Correo</label>
        <input type="email" id="mail-user" name="email" value="<?php echo s($user->email) ?>" required>
    </div>

    <div class="container-input input-usuarios">
        <label for="mode">Permisos</label>
        <select name="mode" id="mode">
            <?php foreach (\MVC\Config::get("roles.disponibles", []) as $rol => $descripcion) { ?>
                <option value="<?php echo s($rol) ?>" <?php echo (strtolower((string) $user->mode) === $rol) ? "selected" : "" ?>><?php echo s($descripcion) ?></option>
            <?php } ?>
        </select>
    </div>
</div>
