-- =====================================================================
-- El "tipo" (nuevo/viejo...) deja de ser del cliente y pasa a ser de la
-- relacion cliente-empleado. Ej: PAMELA -> VALERIA = nuevo, PAMELA -> JOSE = viejo.
--
-- Recomendado: usar database/migrar_cliente_empleado.php, que hace lo mismo con
-- simulacion previa, reporte de inconsistencias y respaldo de clientes.tipo.
-- Este .sql queda como alternativa manual. Ejecutar UNA sola vez, con respaldo previo.
-- =====================================================================

START TRANSACTION;

-- 1. Tabla de relaciones
CREATE TABLE IF NOT EXISTS cliente_empleado (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    empleado_id INT NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cliente_empleado (cliente_id, empleado_id),
    KEY idx_cliente_empleado_empleado (empleado_id),
    CONSTRAINT fk_cliente_empleado_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
    CONSTRAINT fk_cliente_empleado_empleado FOREIGN KEY (empleado_id) REFERENCES empleados (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Una relacion por cada pareja cliente-empleado que ya tiene horas cargadas,
--    heredando el tipo que hoy tiene el cliente.
--    (horas_extras guarda nombres completos, no IDs)
INSERT IGNORE INTO cliente_empleado (cliente_id, empleado_id, tipo)
SELECT DISTINCT c.id, e.id, LOWER(TRIM(c.tipo))
FROM horas_extras h
JOIN clientes c ON LOWER(TRIM(h.cliente)) = LOWER(TRIM(CONCAT(c.nombre, ' ', c.apellido)))
JOIN empleados e ON LOWER(TRIM(h.empleado)) = LOWER(TRIM(CONCAT(e.nombre, ' ', e.apellido)))
WHERE h.cliente <> 'administrativo'
  AND TRIM(COALESCE(c.tipo, '')) <> '';

COMMIT;

-- 3. Revisar el resultado antes de borrar la columna:
--    SELECT c.nombre, c.apellido, e.nombre, e.apellido, ce.tipo
--    FROM cliente_empleado ce JOIN clientes c ON c.id = ce.cliente_id JOIN empleados e ON e.id = ce.empleado_id;

-- 4. Quitar el tipo del cliente (DDL: no se puede deshacer con ROLLBACK)
ALTER TABLE clientes DROP COLUMN tipo;
