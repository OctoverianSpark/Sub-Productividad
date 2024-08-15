
<form method="get" class="form-search">
            <div class="container-input-search">
                <label for="column">BUSCAR POR: </label>
                <select name="column" id="column">
                    <?php foreach($columnas as $columna){ ?>
                        <option value="<?php echo $columna ?>"><?php echo strtoupper((str_contains($columna,"_"))? str_replace("_"," ",$columna): $columna) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="container-input-search">
                <input type="text" name="param">
            </div>
            <button type="submit" class="boton-morado-inline">Buscar <i class='bx bx-search bx-tada' ></i></button>
</form>