<div class="container-input input-usuarios">
    <label for="domain-user">Usuario de Dominio</label>
    <input type="text" id="domain-user" name="user" value="<?php echo $user->user ?>">
</div>

<div class="container-input input-usuarios">
    <label for="mail-user">Usuario de Correo</label>
    <input type="mail" id="mail-user" name="email" value="<?php echo $user->email ?>">
</div>

<div class="container-input input-usuarios">
    <label for="mode-user">Permisos</label>
    <select name="mode" id="mode">
        <option value="GOD" <?php echo ($user->mode === "GOD") ? "selected" : "" ?>>GOD MODE (CONTROL ABSOLUTO DEL SISTEMA)</option>
        <option value="ADMIN" <?php echo ($user->mode === "ADMIN") ? "selected" : "" ?>>ADMIN MODE (CONTROL EN LAS CONFIGURACIONES BASICAS Y CARGA DE HORAS)</option>
        <option value="AUDITER" <?php echo ($user->mode === "AUDITER") ? "selected" : "" ?>>AUDITER MODE (CONTROL EN LAS CONFIGURACIONES BASICAS Y AUDITORIAS)</option>
        <option value="CHARGER" <?php echo ($user->mode === "CHARGER") ? "selected" : "" ?>>CHARGER MODE (CONTROL DE CARGA DE HORAS)</option>
        <option value="AUDITED" <?php echo ($user->mode === "AUDITED") ? "selected" : "" ?>>AUDITED MODE (CONTROL DE CARGA DE HORAS LIMITADO)</option>
    </select>
</div>