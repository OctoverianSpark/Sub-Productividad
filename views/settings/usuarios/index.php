<main class="main-settings">

    <a href="/settings/usuarios/crear" class="boton-morado-block">Registrar usuario</a>

    <table class="tabla-usuarios">

        <thead>
            <th>Acciones</th>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Permisos</th>
        </thead>
        <tbody>
            <?php foreach($usuarios as $usuario){ ?>

                <tr>
                    <td>
                        <div class="container-buttons">
                            <a href="/settings/usuarios/actualizar?id=<?php echo (int) $usuario->id ?>"><i class="bi bi-pencil-fill" title="Actualizar Informacion"></i></a>
                            <form method="post" action="/settings/usuarios/eliminar" style="display:inline" onsubmit="return confirm('¿Quitar el acceso a este usuario?')">
                                <?php echo csrfField() ?>
                                <input type="hidden" name="id" value="<?php echo (int) $usuario->id ?>">
                                <button type="submit" title="Eliminar" style="background:none;border:none;cursor:pointer;color:inherit"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        </div>
                    </td>
                    <td><?php echo s(mb_strtoupper((string) $usuario->user)) ?></td>
                    <td><?php echo s(mb_strtoupper((string) $usuario->email)) ?></td>
                    <td><?php echo s(mb_strtoupper((string) $usuario->mode)) ?> MODE</td>

                </tr>

            <?php } ?>
        </tbody>

    </table>

</main>
