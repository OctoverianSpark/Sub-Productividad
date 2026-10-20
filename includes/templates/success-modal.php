
<?php $mensaje = mostrarNotificacion($_GET["resultado"] ?? null) ?>


<div class="modal modal-exito">

    <p class="alerta-error"><i class='bx bxs-message-square-error'></i><?php echo $mensaje /* texto fijo */ ?></p>
    <button type="button" class="close-button"><i class='bx bxs-x-circle' ></i></button>

</div>
