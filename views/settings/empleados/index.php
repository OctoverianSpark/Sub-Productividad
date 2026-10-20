<h1 class="title">Configuracion de Empleados</h1>

<main class="main-settings-empleados">

<a href="empleados/crear" class="boton-fucsia-inline">Ingresar Empleado/a</a>

<?php include "../includes/templates/searchForm.php" ?>
<?php if (puede("exportar_maestros")) { ?>
    <a href="/export?table=empleados" class="boton-fucsia-inline">Exportar <i class='bx bxs-save bx-tada' ></i></a>
<?php } ?>

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
                            <td><?php echo s(mb_strtoupper($empleado->nombre)) ?></td>
                            <td><?php echo s(mb_strtoupper($empleado->apellido)) ?></td>
                            <td><?php echo s(mb_strtoupper($empleado->tipo_documento)) ?></td>
                            <td><?php echo s(mb_strtoupper($empleado->documento)) ?></td>
                            <td><?php echo s(mb_strtoupper($empleado->sede)) ?></td>
                            <td><?php echo s(mb_strtoupper($empleado->modalidad)) ?></td>
                            <td><?php echo s(mb_strtoupper($empleado->cargo)) ?></td>
                            <td><?php echo s($empleado->salario) ?></td>
                            <td>
                                <a href="empleados/actualizar?id=<?php echo (int) $empleado->id ?>" class="boton-fucsia-inline">Modificar</a>
                                <form method="post" action="/settings/empleados/eliminar" style="display:inline" onsubmit="return confirm('¿Eliminar este empleado?')">
                                    <?php echo csrfField() ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $empleado->id ?>">
                                    <button type="submit" class="boton-rojo-inline">Eliminar</button>
                                </form>
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

</main>
