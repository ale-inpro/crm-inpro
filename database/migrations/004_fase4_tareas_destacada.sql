USE crm_inpro;

-- Tareas: columna destacada (estrella) para marcar favoritos
ALTER TABLE tareas
  ADD COLUMN destacada TINYINT(1) NOT NULL DEFAULT 0 AFTER estado;

-- Índice para ordenar destacadas primero
ALTER TABLE tareas ADD INDEX idx_tareas_destacada (destacada);
