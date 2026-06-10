-- Normalización para unicidad email/teléfono en contactos
ALTER TABLE contactos
    ADD COLUMN email_normalizado VARCHAR(255) NULL AFTER email,
    ADD COLUMN telefono_normalizado VARCHAR(32) NULL AFTER telefono;

-- Rellenar normalizados existentes
UPDATE contactos
SET email_normalizado = LOWER(TRIM(email))
WHERE email IS NOT NULL AND TRIM(email) <> '';

UPDATE contactos
SET telefono_normalizado = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefono, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '')
WHERE telefono IS NOT NULL AND TRIM(telefono) <> '';

-- Clientes sin contacto principal: crear uno desde datos legacy del cliente
INSERT INTO contactos (
    cliente_id, nombre, cargo, email, telefono, email_normalizado, telefono_normalizado,
    es_principal, activo
)
SELECT
    c.id,
    COALESCE(NULLIF(TRIM(c.nombre_comercial), ''), c.razon_social, 'Contacto principal'),
    NULL,
    NULLIF(TRIM(c.email_principal), ''),
    NULLIF(TRIM(c.telefono_principal), ''),
    CASE WHEN c.email_principal IS NOT NULL AND TRIM(c.email_principal) <> ''
         THEN LOWER(TRIM(c.email_principal)) ELSE NULL END,
    CASE WHEN c.telefono_principal IS NOT NULL AND TRIM(c.telefono_principal) <> ''
         THEN REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.telefono_principal, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '')
         ELSE NULL END,
    1,
    1
FROM clientes c
WHERE c.deleted_at IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM contactos ct
      WHERE ct.cliente_id = c.id AND ct.es_principal = 1 AND ct.activo = 1
  )
  AND (
      (c.email_principal IS NOT NULL AND TRIM(c.email_principal) <> '')
      OR (c.telefono_principal IS NOT NULL AND TRIM(c.telefono_principal) <> '')
  );

-- Índices únicos (NULL permitido varias veces en MySQL)
CREATE UNIQUE INDEX idx_contactos_email_norm ON contactos (email_normalizado);
CREATE UNIQUE INDEX idx_contactos_tel_norm ON contactos (telefono_normalizado);