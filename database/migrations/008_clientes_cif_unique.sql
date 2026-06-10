-- Índice único CIF (NULL permitido varias veces en MySQL)
-- Ejecutar solo si no hay CIF duplicados entre clientes activos:
-- SELECT cif_normalizado, COUNT(*) FROM clientes
-- WHERE deleted_at IS NULL AND cif_normalizado IS NOT NULL
-- GROUP BY cif_normalizado HAVING COUNT(*) > 1;

CREATE UNIQUE INDEX idx_clientes_cif_norm ON clientes (cif_normalizado);
