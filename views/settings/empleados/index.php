<h1 class="title">Configuracion de Empleados</h1>



<?php if($mensaje){ ?>

    <?php include "../includes/templates/success-modal.php" ?>

<?php }?>


<a href="empleados/crear" class="boton-fucsia-inline">Ingresar Empleado/a</a>


<main class="main-settings-empleados">


<?php include "../includes/templates/searchForm.php" ?>
<a href="/export?table=empleados" class="boton-fucsia-inline">Exportar <i class='bx bxs-save bx-tada' ></i></a>



        <table class="tabla-empleados">
            <thead>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Tipo de Documento</th>
                <th>Documento</th>
                <th>Sede</th>
                <th>Modalidad</th>
                <th>Cargo</th>
                <th>Salario</th>
                <th>Acciones</th>
            </thead>
            <tbody>
                <?php foreach($empleados as $empleado): ?>
                    <tr>
                        <td><?php echo strtoupper( s($empleado->nombre) )?></td>
                        <td><?php echo strtoupper( s($empleado->apellido) ) ?></td>
                        <td><?php echo strtoupper( s($empleado->tipo_documento) ) ?></td>
                        <td><?php echo strtoupper( s($empleado->documento) ) ?></td>
                        <td><?php echo strtoupper( s($empleado->sede) ) ?></td>
                        <td><?php echo strtoupper( s($empleado->modalidad) ) ?></td>
                        <td><?php echo strtoupper( s($empleado->cargo) )?></td>
                        <td><?php echo strtoupper( s($empleado->salario) ) ?></td>
                        <td>
                            <a href="empleados/actualizar?id=<?php echo $empleado->id ?>" class="boton-fucsia-inline">Modificar</a>
                            <a href="empleados/eliminar?id=<?php echo $empleado->id ?>" class="boton-rojo-inline">Eliminar<a>
                        </td>
                    </tr>
                <?php endforeach?>
            </tbody>
        </table>








</main><!-main-settings-empleados-!>