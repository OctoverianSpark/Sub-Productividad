<main class="main-settings-clientes">



    <h1 class="title">GESTION DE HORAS DEL CLIENTE <?php echo strtoupper($_GET["name"])  ?></h1>




    <form method="get" class="form-search">
        <h2 class="subtitle">FILTRO POR RANGO DE FECHAS</h2>

        <div class="container-inputs-search" id="date-search">
            <div class="container-input-search">
                <label for="date-search">DESDE</label>
                <input type="datetime-local" name="from" id="date-search-input" value="<?php echo $_GET["from"] ?>">
            </div>
            <div class="container-input-search">
                <label for="date-search">HASTA</label>
                <input type="datetime-local" name="to" id="date-search-input" value="<?php echo $_GET["to"] ?>">
            </div>

        </div>

        <div class="container-buttons">
            <button type="submit" class="boton-morado-inline">BUSCAR <i class='bx bx-search bx-tada'></i></button>

            <a href="/horas/ver?table=<?php echo $_GET["table"] ?>" class="boton-fucsia-inline">Borrar Filtro <i class='bx bxs-trash bx-tada'></i></a>

        </div>

    </form>


    <h2 class="subtitle">HISTORIAL DE HORAS</h2>


    <?php if (!empty($horas)) { ?>
        <div class="datos-cliente">


            <table class="tabla-historial">
                <thead>
                    <th>EMPLEADO</th>
                    <th>INICIO</th>
                    <th>HORAS DE ALMUERZO</th>
                    <th>FINAL</th>
                    <th>EXTRAS DIURNAS</th>
                    <th>EXTRAS NOCTURNAS</th>
                    <th>CENA</th>
                    <th>TAXI</th>
                </thead>
                <tbody>
                    <?php foreach ($horas as $hora) { ?>


                        <tr>
                            <td><?php echo strtoupper(s($hora->empleado)) ?></td>
                            <td><?php echo s($hora->inicio) ?></td>
                            <td><?php echo s($hora->almuerzo) ?></td>
                            <td><?php echo s($hora->final) ?></td>
                            <td><?php echo s($hora->diurnas_extras) ?></td>
                            <td><?php echo s($hora->nocturnas_extras) ?></td>
                            <td><?php echo strtoupper(s($hora->cena)) ?></td>
                            <td><?php echo strtoupper(s($hora->taxi)) ?></td>
                        </tr>


                    <?php } ?>
                </tbody>

            </table>

        </div>
    <?php } else { ?>
        <div class="container-message">
            <h1 class="error">NO HAY DATOS DE HORAS CON ESTE AGENTE</h1>
        </div>
    <?php } ?>
</main>