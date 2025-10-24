<h1 class="title">Configuracion de Empleados</h1>

<?php if($mensaje){ ?>
    <?php include "../includes/templates/success-modal.php" ?>
<?php }?>


<main class="main-settings-empleados">
    
<a href="empleados/crear" class="boton-fucsia-inline">Ingresar Empleado/a</a>

<?php include "../includes/templates/searchForm.php" ?>
<a href="/export?table=empleados" class="boton-fucsia-inline">Exportar <i class='bx bxs-save bx-tada' ></i></a>

        <table class="tabla-empleados">
            <thead>
                <th>NOMBRE</th>
                <th>APELLIDO</th>
                <th>TIPO DE DOCUMENTO</th>
                <th>DOCUMENTO</th>
                <th>SEDE</th>
                <th>MODALIDAD</th>
                <th>CARGO</th>
                <th>BASE SALARIAL</th>
                <th>ACCIONES</th>
            </thead>
            <tbody>
                <?php if (!empty($empleados)): ?>
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
                                <a href="empleados/eliminar?id=<?php echo $empleado->id ?>" class="boton-rojo-inline">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">
                            No se encontraron empleados
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php include "../includes/templates/pagination.php";?>

</main><!-main-settings-empleados-!>