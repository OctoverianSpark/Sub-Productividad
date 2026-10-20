
<h1 class="title">GESTION DE HORA EXTRA</h1>

<?php if (!empty($errores)) { ?>
    <?php include_once "../includes/templates/error-modal.php" ?>
<?php } ?>

<div class="contenedor-tiempo-empleado">


    <div class="container-info">
        <h2 class="subtitle">EMPLEADO <span><?php echo s($hora->empleado) ?></span></h2>
        <h2 class="subtitle">CLIENTE <span><?php echo s($hora->cliente) ?></span></h2>
        <h2 class="subtitle">INICIO DE JORNADA <span><?php echo s($hora->inicio) ?></span></h2>
        <h2 class="subtitle">HORAS DE ALMUERZO <span><?php echo s($hora->almuerzo) ?></span></h2>
        <h2 class="subtitle">FINAL DE JORNADA <span><?php echo s($hora->final) ?></span></h2>
        <?php if (puede("eliminar_horas")) { ?>
            <form method="post" action="/horas/ver/hora/eliminar" onsubmit="return confirm('¿Eliminar este registro de horas?')">
                <?php echo csrfField() ?>
                <input type="hidden" name="id" value="<?php echo (int) $hora->id ?>">
                <button type="submit" class="boton-rojo-inline">Eliminar</button>
            </form>
        <?php } ?>
    </div>

    <div class="container-updates">
        <label for="actualizar" class="label-title">Quieres modificar informacion de las horas?</label>
        <div class="container-input">
            <label for="actualizar">ACTUALIZAR?</label>
            <input type="checkbox" id="actualizar">
        </div>
        <form method="post" class="form-actualizaciones" novalidate="novalidate">
            <?php echo csrfField() ?>

            <fieldset class="container-inputs input-horas-ordinarias-extras" disabled>
            <legend>ACTUALIZAR DATOS</legend>
                <div class="container-input input-horas">
                    <label for="empleado">EMPLEADO</label>
                    <select name="horas[empleado]" id="empleado">
                        <?php foreach($empleados as $empleado){ ?>
                            <?php $nombre = $empleado->nombreCompleto() ?>
                            <option value="<?php echo s($nombre) ?>" <?php echo (mb_strtolower($nombre) === mb_strtolower($hora->empleado)) ? "selected" : "" ?>><?php echo s(mb_strtoupper($nombre)) ?></option>
                        <?php }?>
                    </select>
                </div>
                <div class="container-input input-horas">
                    <label for="cliente">CLIENTE</label>
                    <select name="horas[cliente]" id="cliente">
                        <option value="administrativo" <?php echo ($hora->cliente == "administrativo") ? "selected" : "" ?> >HORAS ADMINISTRATIVAS</option>
                        <?php foreach($clientes as $cliente){ ?>
                            <?php $nombre = $cliente->nombreCompleto() ?>
                            <option value="<?php echo s($nombre) ?>" <?php echo (mb_strtolower($nombre) === mb_strtolower($hora->cliente)) ? "selected" : "" ?>><?php echo s(mb_strtoupper($nombre)) ?></option>
                        <?php }?>
                    </select>
                </div>
                  <div class="container-input">
                        <label for="festivo">FESTIVO</label>
                        <input type="checkbox" name="horas[festivo]" id="festivo" value="si" <?php echo (strtolower($hora->festivo) == "si") ? "checked" : "" ?>>
                    </div>
                <div class="container-input input-horas range-number-container">
                    <label for="diurnas_ordinarias">DIURNAS ORDINARIAS</label>
                    <input type="range" name="horas[diurnas_ordinarias]" id="diurnas_ordinarias" value="<?php echo s($hora->diurnas_ordinarias) ?>" max="10" step="0.1">
                    <input type="number" name="horas[diurnas_ordinarias]" id="diurnas_ordinarias_value" value="<?php echo s($hora->diurnas_ordinarias) ?>" class="calcTime">
                </div>

                <div class="container-input input-horas range-number-container">
                    <label for="nocturnas_ordinarias">NOCTURNAS ORDINARIAS</label>
                    <input type="range" name="horas[nocturnas_ordinarias]" id="nocturnas_ordinarias" value="<?php echo s($hora->nocturnas_ordinarias) ?>" max="10" step="0.1">
                    <input type="number" name="horas[nocturnas_ordinarias]" id="nocturnas_ordinarias_value" value="<?php echo s($hora->nocturnas_ordinarias) ?>" class="calcTime">
                </div>
                <div class="container-input input-horas range-number-container">
                    <label for="diurnas_extras">DIURNAS EXTRAS</label>
                    <input type="range" name="horas[diurnas_extras]" id="diurnas_extras" value="<?php echo s($hora->diurnas_extras) ?>" max="10" step="0.2">
                    <input type="number" name="horas[diurnas_extras]" id="diurnas_extras_value" value="<?php echo s($hora->diurnas_extras) ?>" class="calcTime">
                </div>

                <div class="container-input input-horas range-number-container">
                    <label for="nocturnas_extras">NOCTURNAS EXTRAS</label>
                    <input type="range" name="horas[nocturnas_extras]" id="nocturnas_extras" value="<?php echo s($hora->nocturnas_extras) ?>" max="10" step="0.2">
                    <input type="number" name="horas[nocturnas_extras]" id="nocturnas_extras_value" value="<?php echo s($hora->nocturnas_extras) ?>" class="calcTime">
                </div>
                <div class="container-input container-logistica">
                    <div class="container-input">
                        <label for="cena">CENA</label>
                        <input type="checkbox" name="horas[cena]" id="cena" value="si" <?php echo (strtolower($hora->cena) == "si") ? "checked" : "" ?>>
                    </div>
                    <div class="container-input">
                        <label for="taxi">TAXI</label>
                        <input type="checkbox" name="horas[taxi]" id="taxi" value="si" <?php echo (strtolower($hora->taxi) == "si") ? "checked" : "" ?>>
                    </div>
                </div>


                <div class="container-input">
                    <label for="comentarios">COMENTARIOS DE LA ACTUALIZACION</label>
                    <textarea name="horas[comentarios]" id="comentarios"><?php echo s($hora->comentarios) ?></textarea>
                </div>

                <input type="submit" value="Actualizar" class="boton-morado-inline">
            </fieldset>

        </form>

    </div>
</div>
