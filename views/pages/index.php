<main class="main-title">

    
    <h1 class="title">BIENVENIDO <?php echo $_SESSION["name"] ?></h1>


    
    
    <?php if($_SESSION["mode"] == "ADMIN" || $_SESSION["mode"] == "GOD"){ ?>
        <h2>Registros de Eventos</h2>
        <table class="tabla-logs">
            <thead>
                <th>Fecha</th>
                <th>Titulo</th>
                <th>Descripcion</th>
            </thead>
            <tbody>



                <?php foreach($logs as $log){ ?>
                    
                    <tr>
                        <td><?php echo $log->fecha ?></td>
                        <td><?php echo strtoupper($log->titulo) ?></td>
                        <td><?php echo strtoupper($log->contenido) ?></td>
                    </tr>

                <?php } ?>


            </tbody>



        </table>
    <?php } ?>

</main>