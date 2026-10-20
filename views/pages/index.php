<main class="main-title">

    <h1 class="title">BIENVENIDO <?php echo s($_SESSION["name"] ?? "") ?></h1>

    <?php if (puede("ver_logs")) { ?>
        <h2>Registros de Eventos</h2>
        <table class="tabla-logs">
            <thead>
                <th>Fecha</th>
                <th>Titulo</th>
                <th>Descripcion</th>
            </thead>
            <tbody>
                <?php foreach ($logs as $log) { ?>
                    <tr>
                        <td><?php echo s($log->fecha) ?></td>
                        <td><?php echo s(mb_strtoupper($log->titulo)) ?></td>
                        <td><?php echo s(mb_strtoupper($log->contenido)) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Paginacion -->
        <?php include "../includes/templates/pagination.php"; ?>

    <?php } ?>
</main>
