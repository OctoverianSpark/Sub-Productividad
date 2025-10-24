
<?php
    $auth = estalogueado();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub-Productividad</title>
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/build/css/app.css">
    
</head>
<body>
    

<?php $mensaje=mostrarNotificacion($_GET["resultado"])?>
<?php if($mensaje){ ?>
    <div class="modal modal-exito">
        <p><?php echo $mensaje ?></p>        
        <button class="close-button"><i class='bx bxs-x-circle' ></i></button>
    </div>
<?php } ?>
    

    <header>
    <?php if($auth){ ?>
            <div class="barra">
                    <a href="/" class="mainLogoHeader">
                        <h1>Sub<span>-</span><span>Productividad</span></h1>
                    </a>
                    <nav class="navegacion">
                        <ul>
                                <li><a href="/horas/registrar">Ingresar Horas</a></li>
                                <li><a href="/horas/ver" >Ver Horas</a></li>
                                <?php if(in_array($_SESSION["mode"],["GOD","ADMIN"])){ ?>
                                    <li><a href="/settings" >Configuracion</a></li>
                                <?php } ?>
                                <li><a href="/logout" >Cerrar Sesion</a></li>
                        </ul>
                    </nav>
            </div>
    <?php }else{ ?>
        <div class="barra center">
            <a href="" class="mainLogoHeader logo-center">
                <h1>Sub<span>-</span><span>Productividad</span></h1>
            </a>
        </div>
    <?php } ?>

    </header>
    <?php echo $contenido ?>


<!-- jQuery para Select2 -->    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="/build/js/bundle.min.js"></script>

<footer>
            <a href="/" class="mainLogoFooter">
                <h1>Sub <span>-</span> <span>Productividad</span></h1>
            </a>
            <p>ASISTENTE VIRTUAL S.A.S &copy;</p>
    </footer>

</body>
</html>