<?php
    $param = $_GET["param"] ?? "";
    $from = $_GET["from"] ?? "";
    $to = $_GET["to"] ?? "";
?>
<main class="main-settings-ver">

    <h1 class="title">Visualizar Horas</h1>

    <form class="form-search" method="get">

        <div class="container-input">
            <div class="radio">
                <div class="container-input-search">
                    <label for="empleados">EMPLEADOS</label>
                    <input type="radio" name="table" id="radio-selector" value="empleados" <?php echo ($table === "empleados") ? "checked" : "" ?>>
                </div>
                <div class="container-input-search">
                    <label for="clientes">CLIENTES</label>
                    <input type="radio" name="table" id="radio-selector" value="clientes" <?php echo ($table === "clientes") ? "checked" : "" ?>>
                </div>
                <?php if (puede("auditar")) { ?>
                    <div class="container-input-search">
                        <label for="auditar">AUDITAR</label>
                        <input type="radio" name="table" id="radio-selector" value="auditar" <?php echo ($table === "auditar") ? "checked" : "" ?>>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        $defaultColumn = ($table === "clientes") ? "cliente" : "empleado";
        $currentColumn = $_GET["column"] ?? $defaultColumn;
        ?>
        <input type="hidden" name="column" value="<?php echo s($currentColumn) ?>">
        <div class="container-inputs-search" id="date-search">
            <div class="container-input-search" id="text-search">
                <label for="param">Buscar por nombre</label>
                <input type="text" name="param" id="param" value="<?php echo s($param) ?>">
            </div>

            <?php if ($table !== "auditar") { ?>
            <div class="container-input-search">
                <label for="date-search">DESDE</label>
                <input type="date" name="from" id="date-search-input" value="<?php echo s($from) ?>">
            </div>
            <div class="container-input-search">
                <label for="date-search">HASTA</label>
                <input type="date" name="to" id="date-search-input" value="<?php echo s($to) ?>">
            </div>
            <?php } ?>
        </div>


        <!-- Mantener parámetros de paginación en el formulario de búsqueda -->
        <?php if (isset($_GET["per_page"])): ?>
            <input type="hidden" name="per_page" value="<?php echo (int) $_GET["per_page"] ?>">
        <?php endif; ?>

        <button type="submit" class="boton-morado-inline">BUSCAR <i class='bx bx-search bx-tada'></i></button>

        <?php if ($from && $to) { ?>
            <?php $exportQuery = http_build_query(["table" => $table, "column" => "empleado", "param" => $param, "from" => $from, "to" => $to]) ?>
            <div class="container-buttons">
                <a href="/horas/ver?<?php echo s(http_build_query(["table" => $table])) ?>" class="boton-fucsia-inline">Borrar Filtro <i class='bx bxs-trash bx-tada'></i></a>
                <a href="/export?<?php echo s($exportQuery) ?>" class="boton-fucsia-inline">Exportar <i class='bx bxs-save bx-tada'></i></a>
            </div>
        <?php } ?>

    </form>

    <?php if ($table === "empleados") { ?>

        <table class="tabla-empleados">
            <thead>
                <th>FECHA</th>
                <th>EMPLEADO</th>
                <th>CLIENTE</th>
                <th>DIURNAS ORDINARIAS</th>
                <th>NOCTURNAS ORDINARIAS</th>
                <th>DIURNAS EXTRAS</th>
                <th>MONTO DIURNAS</th>
                <th>NOCTURNAS EXTRAS</th>
                <th>MONTO NOCTURNAS</th>
                <th>TOTAL</th>
                <th>ACCIONES</th>
            </thead>

            <tbody>
                <?php if (!empty($horas)): ?>
                    <?php foreach ($horas as $hora) { ?>
                        <tr>
                            <td><?php echo s($hora["inicio"]) ?></td>
                            <td><?php echo s(mb_strtoupper($hora["empleado"])) ?></td>
                            <td><?php echo s(mb_strtoupper($hora["cliente"])) ?></td>
                            <td><?php echo s($hora["diurnas_ordinarias"]) ?></td>
                            <td><?php echo s($hora["nocturnas_ordinarias"]) ?></td>
                            <td><?php echo s($hora["diurnas_extras"]) ?></td>
                            <td>$ <?php echo s($hora["diurnas_monto"]) . " " . s($hora["moneda"]) ?></td>
                            <td><?php echo s($hora["nocturnas_extras"]) ?></td>
                            <td>$ <?php echo s($hora["nocturnas_monto"]) . " " . s($hora["moneda"]) ?></td>
                            <td>$ <?php echo s((float) $hora["diurnas_monto"] + (float) $hora["nocturnas_monto"]) . " " . s($hora["moneda"]) ?></td>
                            <td>
                                <a href="/horas/ver/hora?id=<?php echo (int) $hora["id"] ?>" class="boton-morado-inline">Gestionar</a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 20px;">
                            No se encontraron registros
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- Paginacion -->
        <?php include "../includes/templates/pagination.php"; ?>

    <?php } else if ($table === "clientes") { ?>
        <?php $moneda = s(mb_strtoupper(\MVC\Config::get("clientes.moneda", "Dolares"))) ?>
        <table class="tabla-clientes">
            <thead>
                <th>CLIENTE</th>
                <th>HORAS EXTRAS DIURNAS</th>
                <th>HORAS EXTRAS DIURNAS DOMINICALES</th>
                <th>HORAS DIURNAS ORDINARIAS DOMINICALES</th>
                <th>MONTO DIURNAS</th>
                <th>HORAS NOCTURNAS</th>
                <th>HORAS NOCTURNAS DOMINICALES</th>
                <th>MONTO NOCTURNAS</th>
                <th>MONTO LOGISTICA</th>
                <th>TOTAL</th>
                <th>ACCIONES</th>
            </thead>

            <tbody>
                <?php if (!empty($horas)): ?>
                    <?php foreach ($horas as $hora) { ?>
                        <tr>
                            <td><?php echo s(mb_strtoupper($hora["cliente"])) ?></td>
                            <td><?php echo s($hora["diurnas"]) ?></td>
                            <td><?php echo s($hora["diurnas_domingo"]) ?></td>
                            <td><?php echo s($hora["diurnas_ordinarias_domingo"]) ?></td>
                            <td><?php echo s($hora["diurnas_monto"]) ?> <?php echo $moneda ?></td>
                            <td><?php echo s($hora["nocturnas"]) ?></td>
                            <td><?php echo s($hora["nocturnas_domingo"]) ?></td>
                            <td><?php echo s($hora["nocturnas_monto"]) ?> <?php echo $moneda ?></td>
                            <td><?php echo s($hora["logistica"]) ?> <?php echo $moneda ?></td>
                            <td><?php echo s((float) $hora["diurnas_monto"] + (float) $hora["nocturnas_monto"] + (float) $hora["logistica"]) ?> <?php echo $moneda ?></td>
                            <td><a href="/horas/ver/cliente?<?php echo s(http_build_query(["name" => $hora["cliente"]])) ?>" class="boton-morado-inline">Gestionar</a></td>
                        </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 20px;">
                            No se encontraron registros
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


    <?php } else if ($table === "auditar") { ?>


        <table class="tabla-empleados">
            <thead>
                <th>FECHA</th>
                <th>EMPLEADO</th>
                <th>CLIENTE</th>
                <th>DIURNAS ORDINARIAS</th>
                <th>NOCTURNAS ORDINARIAS</th>
                <th>DIURNAS EXTRAS</th>
                <th>NOCTURNAS EXTRAS</th>
                <th>ACCIONES</th>
            </thead>

            <tbody>
                <?php if (!empty($horas)): ?>
                    <?php foreach ($horas as $hora) { ?>
                        <tr>
                            <td>
                                <?php echo s($hora->inicio) ?>
                                <form method="post" action="/aproove" style="display:inline">
                                    <?php echo csrfField() ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $hora->id ?>">
                                    <button type="submit" title="Aprobar Auditoria" style="background:none;border:none;cursor:pointer"><i style="font-size:18px" class="bi bi-check2-all"></i></button>
                                </form>
                            </td>
                            <td><?php echo s(mb_strtoupper($hora->empleado)) ?></td>
                            <td><?php echo s(mb_strtoupper($hora->cliente)) ?></td>
                            <td><?php echo s($hora->diurnas_ordinarias) ?></td>
                            <td><?php echo s($hora->nocturnas_ordinarias) ?></td>
                            <td><?php echo s($hora->diurnas_extras) ?></td>
                            <td><?php echo s($hora->nocturnas_extras) ?></td>
                            <td>
                                <a href="/horas/ver/hora?id=<?php echo (int) $hora->id ?>&table=auditar" class="boton-morado-inline">Gestionar</a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">
                            No se encontraron registros para auditar
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- Paginación -->
        <?php include "../includes/templates/pagination.php"; ?>
    <?php } ?>
</main>
