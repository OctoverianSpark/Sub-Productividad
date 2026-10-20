<h1 class="title">Configuracion de los Clientes</h1>

<main class="main-settings-clientes">

  <a href="clientes/crear" class="boton-fucsia-inline">Ingresar Cliente</a>

<?php include "../includes/templates/searchForm.php" ?>
<?php if (puede("exportar_maestros")) { ?>
    <a href="/export?table=clientes_info" class="boton-fucsia-inline">Exportar <i class='bx bxs-save bx-tada' ></i></a>
<?php } ?>


<div class="container-clientes">
    <?php foreach($clientes as $cliente){ ?>

        <div class="container-datos-cliente">
            <h2><?php echo s(ucwords($cliente->nombreCompleto())) ?></h2>
            <p>TIPO</p>
            <p><?php echo s(mb_strtoupper($cliente->tipo)) ?></p>
            <div class="actions">
            <a href="clientes/actualizar?id=<?php echo (int) $cliente->id ?>" class="boton-fucsia-inline">Actualizar</a>
            <form method="post" action="/settings/clientes/eliminar" style="display:inline" onsubmit="return confirm('¿Eliminar este cliente?')">
                <?php echo csrfField() ?>
                <input type="hidden" name="id" value="<?php echo (int) $cliente->id ?>">
                <button type="submit" class="boton-rojo-inline">Eliminar</button>
            </form>
            </div>
        </div>

    <?php }?>
</div>

</main>
