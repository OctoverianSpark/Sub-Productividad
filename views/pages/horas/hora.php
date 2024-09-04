
<h1 class="title">GESTION DE HORA EXTRA</h1>

<div class="contenedor-tiempo-empleado">


    <div class="container-info">
        <h2 class="subtitle">EMPLEADO <span><?php echo $hora->empleado ?></span></h2>
        <h2 class="subtitle">CLIENTE <span><?php echo $hora->cliente ?></span></h2>
        <h2 class="subtitle">INICIO DE JORNADA <span><?php echo $hora->inicio?></span></h2>
        <h2 class="subtitle">HORAS DE ALMUERZO <span><?php echo $hora->almuerzo ?></span></h2>
        <h2 class="subtitle">INICIO DE JORNADA <span><?php echo $hora->final ?></span></h2>
    </div>

    <div class="container-updates">
        <label for="actualizar" class="label-title">Quieres modificar informacion de las horas?</label>
        <div class="container-input">
            <label for="actualizar">ACTUALIZAR?</label>
            <input type="checkbox" id="actualizar">
        </div>
        <form method="post" class="form-actualizaciones" >

            <fieldset class="container-inputs input-horas-ordinarias-extras" disabled>
            <legend>ACTUALIZAR DATOS</legend>
                <div class="container-input input-horas">
                    <label for="empleado">EMPLEADO</label>
                    <select name="horas[empleado]" id="empleado">
                        <?php foreach($empleados as $empleado){ ?>
                            <option value="<?php echo $empleado->nombre . " " . $empleado->apellido ?>"<?php echo ($empleado->nombre . " " . $empleado->apellido == strtolower($hora->empleado)) ? "selected" : "" ?> ><?php echo strtoupper($empleado->nombre . " " . $empleado->apellido) ?></option>    
                        <?php }?>
                    </select>
                </div>
                <div class="container-input input-horas">
                    <label for="cliente">CLIENTE</label>
                    <select name="horas[cliente]" id="cliente">
                        <?php foreach($clientes as $cliente){ ?>
                            <option value="<?php echo $cliente->nombre . " " . $cliente->apellido ?>" <?php echo ($cliente->nombre . " " . $cliente->apellido == $hora->cliente) ? "selected" : "" ?> ><?php echo strtoupper($cliente->nombre . " " . $cliente->apellido) ?> </option>    
                        <?php }?>
                    </select>
                </div>
                <div class="container-input input-horas">
                    <label for="diurnas_ordinarias">DIURNAS ORDINARIAS</label>
                    <input type="range" name="horas[diurnas_ordinarias]" id="diurnas_ordinarias" value="<?php echo $hora->diurnas_ordinarias ?>" max="10" step="0.1">
                    <input type="number" name="horas[diurnas_ordinarias]" id="diurnas_ordinarias_value" value="<?php echo $hora->diurnas_ordinarias ?>" class="calcTime">
                </div>

                <div class="container-input input-horas">
                    <label for="nocturnas_ordinarias">NOCTURNAS ORDINARIAS</label>
                    <input type="range" name="horas[nocturnas_ordinarias]" id="nocturnas_ordinarias" value="<?php echo $hora->nocturnas_ordinarias ?>" max="10" step="0.1">
                    <input type="number"name="horas[nocturnas_ordinarias]"  id="nocturnas_ordinarias_value" value="<?php echo $hora->nocturnas_ordinarias ?>" class="calcTime">
                </div>
                <div class="container-input input-horas">
                    <label for="diurnas_extras">DIURNAS EXTRAS</label>
                    <input type="range" name="horas[diurnas_extras]" id="diurnas_extras" value="<?php echo $hora->diurnas_extras ?>" max="10" step="0.2">
                    <input type="number" name="horas[diurnas_extras]" id="diurnas_extras_value" value="<?php echo $hora->diurnas_extras ?>" class="calcTime">
                </div>

                <div class="container-input input-horas">
                    <label for="nocturnas_extras">NOCTURNAS EXTRAS</label>
                    <input type="range" name="horas[nocturnas_extras]" id="nocturnas_extras" value="<?php echo $hora->nocturnas_extras ?>" max="10" step="0.2">
                    <input type="number" name="horas[nocturnas_extras]" id="nocturnas_extras_value" value="<?php echo $hora->nocturnas_extras ?>" class="calcTime">
                </div>

                <div class="container-input">
                    <label for="comentarios">COMENTARIOS DE LA ACTUALIZACION</label>
                    <textarea name="horas[comentarios]" id="comentarios"><?php echo $hora->comentarios ?></textarea>
                </div>

                <input type="submit" value="Actualizar" class="boton-morado-inline">
            </fieldset>

            

        </form>

    </div>
</div>
