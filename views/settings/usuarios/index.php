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
                            <a href="/settings/usuarios/actualizar?id=<?php echo $usuario->id ?>"><i class="bi bi-pencil-fill" title="Actualizar Informacion"></i></a>
                            <a href="/settings/usuarios/eliminar?id=<?php echo $usuario->id ?>" title="Eliminar"><i class="bi bi-trash-fill"></i></a>

                        </div>
                    </td>
                    <td><?php echo strtoupper($usuario->user )?></td>
                    <td><?php echo strtoupper($usuario->email) ?></td>
                    <td><?php echo strtoupper($usuario->mode )?> MODE</td>
                    
                </tr>
                
            
            <?php } ?>
        </tbody>


    </table>

</main>