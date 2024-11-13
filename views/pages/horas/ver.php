<h1 class="title">Visualizar Horas</h1>




<form class="form-search" method="get">

    <div class="container-input">

        <div class="radio">
            <div class="container-input-search">
                <label for="empleados">EMPLEADOS</label>
                <input type="radio" name="table"  id="radio-selector" value="empleados" <?php echo ($_GET["table"] ==="empleados" || !$_GET["table"])? "checked" : "" ?>>
            </div>
            <div class="container-input-search">
                <label for="clientes">CLIENTES</label>
                <input type="radio" name="table"  id="radio-selector" value="clientes" <?php echo ($_GET["table"] === "clientes")?"checked" : "" ?>>
            </div>
            <?php if(in_array($_SESSION["mode"] , ["GOD","AUDITER"])){ ?>
                <div class="container-input-search">
                    <label for="auditar">AUDITAR</label>
                    <input type="radio" name="table"  id="radio-selector" value="auditar" <?php echo ($_GET["table"] === "auditar")?"checked" : "" ?>>
                    
                </div>
            <?php } ?>
        </div>
    </div>


    <div class="container-inputs-search">
        <div class="container-input-search">
            <label for="searchBy">FILTRAR</label>
            <select id="searchBy" name="column">
                <option value="fecha">RANGO DE FECHAS</option>
                <option value="<?php echo ($_GET["table"] == "empleados" || !$_GET["table"])? "empleado" : "cliente"?>">NOMBRE</option>
            </select>
        </div>
    </div>

        <div class="container-inputs-search" id="date-search">
            <div class="container-input-search">
                <label for="date-search">DESDE</label>
                <input type="date" name="from" id="date-search-input" value="<?php echo $_GET["from"] ?>">
            </div>
            <div class="container-input-search">
                <label for="date-search">HASTA</label>
                <input type="date" name="to" id="date-search-input" value="<?php echo $_GET["to"] ?>">
            </div>

        </div>

        <div class="container-input-search" id="text-search" style="display:none">
            <label for="param">VALOR</label>
            <input type="text" name="param" id="param" disabled>
        </div>
        <button type="submit" class="boton-morado-inline">BUSCAR <i class='bx bx-search bx-tada' ></i></button>

    <?php if($_GET["from"] && $_GET["to"]){?>
        <div class="container-buttons">
                    
            <a href="/horas/ver?table=<?php echo $_GET["table"] ?>" class="boton-fucsia-inline">Borrar Filtro <i class='bx bxs-trash bx-tada' ></i></a>

            <a href="/export?table=<?php echo $_GET["table"] ?>&from=<?php echo $_GET["from"]?>&to=<?php echo $_GET["to"]?>" class="boton-fucsia-inline">Exportar <i class='bx bxs-save bx-tada'></i></a>
        </div>
    <?php } ?>

</form>





<?php if($_GET["table"] ==="empleados" || !$_GET["table"]){ ?>
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
            <?php foreach($horas as $hora){ ?>
                <tr>
                    <td><?php echo strtoupper(s($hora["inicio"]))?></td>
                    <td><?php echo strtoupper(s($hora["empleado"]))?></td>
                    <td><?php echo strtoupper(s($hora["cliente"]))?></td>
                    <td><?php echo strtoupper(s($hora["diurnas_ordinarias"]))?></td>
                    <td><?php echo strtoupper(s($hora["nocturnas_ordinarias"]))?></td>
                    <td><?php echo strtoupper(s($hora["diurnas_extras"]))?></td>
                    <td>$ <?php echo strtoupper(s($hora["diurnas_monto"])) . " " . $hora["moneda"]?></td>
                    <td><?php echo strtoupper(s($hora["nocturnas_extras"]))?></td>
                    <td><?php echo strtoupper(s($hora["nocturnas_monto"])) . " " . $hora["moneda"] ?></td>
                
                    <td>$ <?php echo floatval(s($hora["diurnas_monto"])) + floatval(s($hora["nocturnas_monto"]))  . " " . $hora["moneda"] ?></td>
                    <td>
                        <a href="/horas/ver/hora?id=<?php echo s($hora["id"]) ?>" class="boton-morado-inline">Gestionar</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else if($_GET["table"] === "clientes"){ ?>
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
            <?php foreach($horas as $hora){ ?>
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
        </tbody>
    </table>
<?php } else if($_GET["table"] === "auditar") {?>

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
            <?php foreach($horas as $hora){ ?>
                <tr>
                    <td><?php echo strtoupper(s($hora->inicio))?></td>
                    <td><?php echo strtoupper(s($hora->empleado))?></td>
                    <td><?php echo strtoupper(s($hora->cliente)) ?></td>
                    <td><?php echo strtoupper(s($hora->diurnas_ordinarias)) ?></td>
                    <td><?php echo strtoupper(s($hora->nocturnas_ordinarias)) ?></td>
                    <td><?php echo strtoupper(s($hora->diurnas_extras)) ?></td>
                    <td><?php echo strtoupper(s($hora->nocturnas_extras)) ?></td>
                    <td>
                        <a href="/horas/ver/hora?id=<?php echo s($hora->id) ?>" class="boton-morado-inline">Gestionar</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>


<?php } ?>
