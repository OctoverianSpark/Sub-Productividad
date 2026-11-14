<?php if (!empty($errores)) { ?>
    <?php include_once "../includes/templates/error-modal.php" ?>
<?php } ?>

<?php echo csrfField() ?>

<?php
    // Siempre se muestra al menos una fila (vacia) para poder agregar empleados
    $filasRelacion = $relaciones ?: ["" => $tipoDefecto];
?>

<main class="settings-clientes-from">
    <div class="container-input inputs-clientes">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" value="<?php echo s($cliente->nombre) ?>" name="clientes[nombre]" placeholder="Nombre" required>
    </div>
    <div class="container-input inputs-clientes">
        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" value="<?php echo s($cliente->apellido) ?>" name="clientes[apellido]" placeholder="Apellido" required>
    </div>

    <div class="container-input inputs-clientes relaciones-cliente">
        <label>Empleados asignados y tipo de servicio</label>
        <p class="relaciones-ayuda">El tipo define la tarifa que se cobra por las horas de cada empleado con este cliente. Si una hora no tiene relacion registrada se cobra como <strong><?php echo s(mb_strtoupper($tipoDefecto)) ?></strong>.</p>

        <table class="tabla-relaciones" data-tabla="relaciones">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; foreach ($filasRelacion as $empleadoId => $tipoRelacion) { ?>
                    <tr>
                        <td>
                            <select name="relaciones[<?php echo $i ?>][empleado_id]">
                                <option value="">-- Sin empleado --</option>
                                <?php foreach ($empleados as $empleado) { ?>
                                    <option value="<?php echo (int) $empleado->id ?>" <?php echo ((int) $empleadoId === (int) $empleado->id) ? "selected" : "" ?>><?php echo s(mb_strtoupper($empleado->nombreCompleto())) ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <select name="relaciones[<?php echo $i ?>][tipo]">
                                <?php foreach ($tipos as $tipo) { ?>
                                    <option value="<?php echo s($tipo) ?>" <?php echo (strtolower((string) $tipoRelacion) === $tipo) ? "selected" : "" ?>><?php echo s(mb_strtoupper($tipo)) ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><button type="button" class="boton-rojo-inline js-quitar-relacion">Quitar</button></td>
                    </tr>
                <?php $i++; } ?>
            </tbody>
        </table>
        <button type="button" class="boton-fucsia-inline js-agregar-relacion">+ Agregar empleado</button>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.querySelector('table[data-tabla="relaciones"] tbody');
    if (!tbody) return;

    // Agregar: clona la primera fila sin empleado seleccionado y con un indice nuevo
    document.querySelector(".js-agregar-relacion").addEventListener("click", () => {
        const nueva = tbody.querySelector("tr").cloneNode(true);
        const indice = Date.now();
        nueva.querySelectorAll("select").forEach((select) => {
            select.name = select.name.replace(/\[\d+\]/, `[${indice}]`);
            if (select.name.endsWith("[empleado_id]")) select.value = "";
            else select.value = <?php echo json_encode($tipoDefecto) ?>;
        });
        tbody.appendChild(nueva);
    });

    // Quitar: si es la ultima fila solo se vacia
    tbody.addEventListener("click", (e) => {
        if (!e.target.classList.contains("js-quitar-relacion")) return;
        const fila = e.target.closest("tr");
        if (tbody.querySelectorAll("tr").length > 1) fila.remove();
        else fila.querySelector("select[name$='[empleado_id]']").value = "";
    });
});
</script>
