<main class="main-settings-ver">

    <h1 class="title">Visualizar Horas</h1>

    <form class="form-search" method="get">

        <div class="container-input">
            <div class="radio">
                <div class="container-input-search">
                    <label for="empleados">EMPLEADOS</label>
                    <input type="radio" name="table" id="radio-selector" value="empleados" <?php echo ($_GET["table"] === "empleados" || !$_GET["table"]) ? "checked" : "" ?>>
                </div>
                <div class="container-input-search">
                    <label for="clientes">CLIENTES</label>
                    <input type="radio" name="table" id="radio-selector" value="clientes" <?php echo ($_GET["table"] === "clientes") ? "checked" : "" ?>>
                </div>
                <?php if (in_array(strtoupper($_SESSION["mode"]), ["GOD", "AUDITER"])) { ?>
                    <div class="container-input-search">
                        <label for="au2ditar">AUDITAR</label>
                        <input type="radio" name="table" id="radio-selector" value="auditar" <?php echo ($_GET["table"] === "auditar") ? "checked" : "" ?>>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        $defaultColumn = (isset($_GET["table"]) && $_GET["table"] === "clientes") ? "cliente" : "empleado";
        $currentColumn = isset($_GET["column"]) ? htmlspecialchars($_GET["column"]) : $defaultColumn;
        ?>
        <input type="hidden" name="column" value="<?php echo $currentColumn; ?>">
        <div class="container-inputs-search" id="date-search">
            <div class="container-input-search" id="text-search">
                <label for="param">Buscar por nombre</label>
                <input type="text" name="param" id="param" value="<?php echo htmlspecialchars($_GET['param'] ?? '') ?>" <?php echo (isset($_GET["column"]) && $_GET["column"] === "fecha") ? 'disabled' : '' ?>>
            </div>

            <?php if ($_GET["table"] !== "auditar") { ?>
            <div class="container-input-search">
                <label for="date-search">DESDE</label>
                <input type="date" name="from" id="date-search-input" value="<?php echo $_GET["from"] ?>">
            </div>
            <div class="container-input-search">
                <label for="date-search">HASTA</label>
                <input type="date" name="to" id="date-search-input" value="<?php echo $_GET["to"] ?>">
            </div>
            <?php } ?> 
        </div>


        <!-- Mantener parámetros de paginación en el formulario de búsqueda -->
        <?php if (isset($_GET["per_page"])): ?>
            <input type="hidden" name="per_page" value="<?php echo $_GET["per_page"]; ?>">
        <?php endif; ?>

        <button type="submit" class="boton-morado-inline">BUSCAR <i class='bx bx-search bx-tada'></i></button>

        <?php if ($_GET["from"] && $_GET["to"]) { ?>
            <div class="container-buttons">
                <a href="/horas/ver?table=<?php echo $_GET["table"] ?>" class="boton-fucsia-inline">Borrar Filtro <i class='bx bxs-trash bx-tada'></i></a>
                <a href="/export?table=<?php echo $_GET["table"] ?>&from=<?php echo $_GET["from"] ?>&to=<?php echo $_GET["to"] ?>" class="boton-fucsia-inline">Exportar <i class='bx bxs-save bx-tada'></i></a>
            </div>
        <?php } ?>

    </form>

    <?php if ($_GET["table"] === "empleados" || !$_GET["table"]) { ?>

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
                            <td><?php echo strtoupper(s($hora["inicio"])) ?></td>
                            <td><?php echo strtoupper(s($hora["empleado"])) ?></td>
                            <td><?php echo strtoupper(s($hora["cliente"])) ?></td>
                            <td><?php echo strtoupper(s($hora["diurnas_ordinarias"])) ?></td>
                            <td><?php echo strtoupper(s($hora["nocturnas_ordinarias"])) ?></td>
                            <td><?php echo strtoupper(s($hora["diurnas_extras"])) ?></td>
                            <td>$ <?php echo strtoupper(s($hora["diurnas_monto"])) . " " . $hora["moneda"] ?></td>
                            <td><?php echo strtoupper(s($hora["nocturnas_extras"])) ?></td>
                            <td>$ <?php echo strtoupper(s($hora["nocturnas_monto"])) . " " . $hora["moneda"] ?></td>
                            <td>$ <?php echo floatval(s($hora["diurnas_monto"])) + floatval(s($hora["nocturnas_monto"]))  . " " . $hora["moneda"] ?></td>
                            <td>
                                <a href="/horas/ver/hora?id=<?php echo s($hora["id"]) ?>" class="boton-morado-inline">Gestionar</a>
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

    <?php } else if ($_GET["table"] === "clientes") { ?>
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
                            <td><?php echo strtoupper(s($hora["cliente"])) ?></td>
                            <td><?php echo s($hora["diurnas"]) ?></td>
                            <td><?php echo s($hora["diurnas_domingo"]) ?></td>
                            <td><?php echo s($hora["diurnas_ordinarias_domingo"]) ?></td>
                            <td><?php echo s($hora["diurnas_monto"]) ?> DOLARES</td>
                            <td><?php echo s($hora["nocturnas"]) ?></td>
                            <td><?php echo s($hora["nocturnas_domingo"]) ?></td>
                            <td><?php echo s($hora["nocturnas_monto"]) ?> DOLARES</td>
                            <td><?php echo s($hora["logistica"]) ?> DOLARES</td>
                            <td><?php echo floatval(s($hora["diurnas_monto"])) + floatval(s($hora["nocturnas_monto"])) + floatval(s($hora["logistica"])) ?> DOLARES</td>
                            <td><a href="/horas/ver/cliente?name=<?php echo $hora["cliente"] ?>" class="boton-morado-inline">Gestionar</a></td>
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


    <?php } else if ($_GET["table"] === "auditar") { ?>


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
                            <td><?php echo strtoupper(s($hora->inicio)) ?> <a title="Aprobar Auditoria" href="/aproove?id=<?php echo $hora->id ?>"><i style="font-size:18px" class="bi bi-check2-all"></i></a></td>
                            <td><?php echo strtoupper(s($hora->empleado)) ?></td>
                            <td><?php echo strtoupper(s($hora->cliente)) ?></td>
                            <td><?php echo strtoupper(s($hora->diurnas_ordinarias)) ?></td>
                            <td><?php echo strtoupper(s($hora->nocturnas_ordinarias)) ?></td>
                            <td><?php echo strtoupper(s($hora->diurnas_extras)) ?></td>
                            <td><?php echo strtoupper(s($hora->nocturnas_extras)) ?></td>
                            <td>
                                <a href="/horas/ver/hora?id=<?php echo s($hora->id) ?>&table=auditar" class="boton-morado-inline">Gestionar</a>
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