USE crm_inpro;

-- Visitas: estados programada / realizada / cancelada
ALTER TABLE visitas
  ADD COLUMN estado ENUM('programada','realizada','cancelada') NOT NULL DEFAULT 'realizada' AFTER tipo,
  ADD COLUMN recordatorio_enviado_at DATETIME NULL AFTER notas;

UPDATE visitas SET estado = 'realizada' WHERE estado IS NULL OR estado = '';

-- Tareas: vínculo con visita + hora para calendario
ALTER TABLE tareas
  ADD COLUMN visita_id INT UNSIGNED NULL AFTER cliente_id,
  ADD COLUMN tipo ENUM('general','visita') NOT NULL DEFAULT 'general' AFTER visita_id,
  ADD COLUMN fecha_hora DATETIME NULL AFTER fecha_vencimiento,
  ADD CONSTRAINT fk_tareas_visita FOREIGN KEY (visita_id) REFERENCES visitas(id) ON DELETE SET NULL;

-- Sincronizar fecha_hora con fecha_vencimiento en datos existentes
UPDATE tareas SET fecha_hora = CONCAT(fecha_vencimiento, ' 09:00:00') WHERE fecha_hora IS NULL;

-- Asegurar enum cancelada en tareas (si ya existe, ignorar error)
ALTER TABLE tareas MODIFY estado ENUM('pendiente','completada','cancelada') NOT NULL DEFAULT 'pendiente';

-- Log de emails enviados (historial)
CREATE TABLE IF NOT EXISTS actividad_emails (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id INT UNSIGNED NOT NULL,
  visita_id INT UNSIGNED NULL,
  usuario_id INT UNSIGNED NOT NULL,
  destinatario VARCHAR(150) NOT NULL,
  asunto VARCHAR(255) NOT NULL,
  enviado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_actividad_emails_cliente (cliente_id),
  CONSTRAINT fk_ae_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_ae_visita FOREIGN KEY (visita_id) REFERENCES visitas(id) ON DELETE SET NULL,
  CONSTRAINT fk_ae_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;