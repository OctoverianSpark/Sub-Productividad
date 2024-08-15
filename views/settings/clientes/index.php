<h1 class="title">Configuracion de los Clientes</h1>

<?php if($mensaje){ ?>

<?php include "../includes/templates/success-modal.php" ?>

<?php }?>

<a href="clientes/crear" class="boton-fucsia-inline">Ingresar Cliente</a>


<main class="main-settings-clientes">


<?php include "../includes/templates/searchForm.php" ?>

<div class="container-clientes">
    <?php foreach($clientes as $cliente){ ?>

        <div class="container-datos-cliente">
            <h2><?php echo ucwords($cliente->nombre . " " . $cliente->apellido) ?></h2>
            <p>TIPO</p>
            <p><?php echo strtoupper($cliente->tipo) ?></p>
            <div class="actions">
            <a href="clientes/actualizar?id=<?php echo $cliente->id ?>" class="boton-fucsia-inline">Actualizar</a>
            <a href="clientes/eliminar?id=<?php echo $cliente->id ?>" class="boton-rojo-inline">Eliminar</a>

            </div>
        </div>
        
    <?php }?>
</div>

</main><!-main-settings-clientes-!>