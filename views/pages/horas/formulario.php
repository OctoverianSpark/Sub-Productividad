
<?php if(!empty($errores)){ ?>
    <?php include_once "../includes/templates/error-modal.php" ?>
<?php } ?>

<fieldset class="container-inputs">
    <legend>INFORMACION PRINCIPAL</legend>
    <div class="container-input input-horas">
        <label for="empleado">EMPLEADO</label>
        <select name="horas[empleado]" id="empleado">
            <?php foreach($empleados as $empleado){ ?>
                <option value="<?php echo $empleado->nombre . " " . $empleado->apellido ?>"><?php echo strtoupper($empleado->nombre . " " . $empleado->apellido) ?></option>    
            <?php }?>
        </select>
    </div>
    <div class="container-input input-horas">
        <label for="cliente">CLIENTE</label>
        <select name="horas[cliente]" id="cliente">
            <option value="administrativo">HORAS ADMINISTRATIVAS</option>
            <?php foreach($clientes as $cliente){ ?>
                <option value="<?php echo $cliente->nombre . " " . $cliente->apellido ?>"><?php echo strtoupper($cliente->nombre . " " . $cliente->apellido) ?></option>    
            <?php }?>
        </select>
    </div>

</fieldset>

<fieldset class="container-inputs">
    <legend>INFORMACION DE JORNADA</legend>
    <div class="container-input input-horas">
        <label for="inicio">INICIO DE JORNADA</label>
        <input type="datetime-local" name="horas[inicio]" id="inicio" class="inicio" value="<?php echo $time->inicio?? date("Y-m-d") . "T08:00:00" ?>">
    </div>
    <div class="container-input input-horas">
        <label for="almuerzo">HORAS DE ALMUERZO</label>

        <div class="radio">
            <div class="radio-input">
                <label for="almuerzo-0">0</label>
                <input type="radio" name="horas[almuerzo]" value="0" id="almuerzo-0" <?php echo ($time->almuerzo == 0)? "checked":"" ?>>
            </div>

            <div class="radio-input">
                <label for="almuerzo-1">1</label>
                <input type="radio" name="horas[almuerzo]" value="1" id="almuerzo-1" <?php echo ($time->almuerzo == 1)? "checked":"" ?>>

            </div>
            <div class="radio-input">
                <label for="almuerzo-2">2</label>
                <input type="radio" name="horas[almuerzo]" value="2" id="almuerzo-2" <?php echo ($time->almuerzo == 2)? "checked":"" ?>>

            </div>

        </div>
    </div>
    <div class="container-input input-horas">
        <label for="final">FINAL DE JORNADA</label>
        <input type="datetime-local" name="horas[final]" id="final" class="final" value="<?php echo $time->final?? date("Y-m-d") . "T18:00:00" ?>">
    </div>



</fieldset>

<fieldset class="container-inputs input-horas-ordinarias-extras">
    <legend>REGISTRO DE HORAS</legend>
    <div class="container-input input-horas">
        <label for="diurnas_ordinarias">DIURNAS ORDINARIAS</label>
        <input type="range" name="horas[diurnas_ordinarias]" id="diurnas_ordinarias" value="0" max="10" step="0.1">
        <input type="number" name="horas[diurnas_ordinarias]" id="diurnas_ordinarias_value" value="0" class="calcTime">
    </div>

    <div class="container-input input-horas">
        <label for="nocturnas_ordinarias">NOCTURNAS ORDINARIAS</label>
        <input type="range" name="horas[nocturnas_ordinarias]" id="nocturnas_ordinarias" value="0" max="10" step="0.1">
        <input type="number"name="horas[nocturnas_ordinarias]"  id="nocturnas_ordinarias_value" value="0" class="calcTime">
    </div>
    <div class="container-input input-horas">
        <label for="diurnas_extras">DIURNAS EXTRAS</label>
        <input type="range" name="horas[diurnas_extras]" id="diurnas_extras" value="0" max="10" step="0.2">
        <input type="number" name="horas[diurnas_extras]" id="diurnas_extras_value" value="0" class="calcTime">
    </div>

    <div class="container-input input-horas">
        <label for="nocturnas_extras">NOCTURNAS EXTRAS</label>
        <input type="range" name="horas[nocturnas_extras]" id="nocturnas_extras" value="0" max="10" step="0.2">
        <input type="number" name="horas[nocturnas_extras]" id="nocturnas_extras_value" value="0" class="calcTime">
    </div>


</fieldset>



<div class="container-fieldsets">
<fieldset class="container-inputs">
    <legend>LOGISTICA</legend>

    <div class="container-input">
        <label for="cena">CENA</label>
        <input type="checkbox" name="horas[cena]" id="cena" value="si">
    </div>
    <div class="container-input">
        <label for="taxi">TAXI</label>
        <input type="checkbox" name="horas[taxi]" id="taxi" value="si">
    </div>




</fieldset>



<fieldset class="container-inputs">
    <legend>COMENTARIOS</legend>
    <textarea name="horas[comentarios]" id="comentarios"></textarea>
</fieldset>






</div>

