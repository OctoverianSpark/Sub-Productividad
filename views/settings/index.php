<main class="main-settings">


    <h1 class="title">Configuraciones</h1>

    <?php $mensaje = mostrarNotificacion($_GET["resultado"])?>
        
    <?php if($mensaje){ ?>

    <?php include "../includes/templates/success-modal.php" ?>

    <?php }?>

    
    <p>Que ajuste haremos hoy?</p>

    <div class="container-actions">
                <div class="container-count-action">
                    <span>Empleados Registrados: <?php echo $empleados ?> </span>
                    <a href="settings/empleados" class="boton-morado-block">Empleados</a>
                </div>
                <div class="container-count-action">
                    <span>Clientes Registrados: <?php echo $clientes ?></span>
                    <a href="settings/clientes" class="boton-morado-block">Clientes</a>
                </div>
                <?php if(in_array($_SESSION["mode"],["GOD"])){ ?>
                    <div class="container-count-action">
                        <span>Usuarios con Acceso: <?php echo $usuarios ?></span>
                        <a href="settings/usuarios" class="boton-morado-block">Usuarios</a>
                    </div>
                <?php } ?>
    </div>



</main>