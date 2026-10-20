
<form method="get" class="form-search">
            <div class="container-input-search">
                <label for="column">BUSCAR POR: </label>
                <select name="column" id="column">
                    <?php foreach($columnas as $columna){ ?>
                        <option value="<?php echo s($columna) ?>" <?php echo (($_GET["column"] ?? "") === $columna) ? "selected" : "" ?>><?php echo s(strtoupper(str_replace("_", " ", $columna))) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="container-input-search">
                <input type="text" name="param" value="<?php echo s($_GET["param"] ?? "") ?>">
            </div>
            <button type="submit" class="boton-morado-inline">Buscar <i class='bx bx-search bx-tada' ></i></button>
</form>
