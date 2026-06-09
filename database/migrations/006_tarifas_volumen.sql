USE crm_inpro;

-- Tarifas
CREATE TABLE IF NOT EXISTS tarifas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    es_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Tramos por volumen de obras
CREATE TABLE IF NOT EXISTS tarifa_tramos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tarifa_id INT UNSIGNED NOT NULL,
    obras_desde INT UNSIGNED NOT NULL,
    obras_hasta INT UNSIGNED NULL COMMENT 'NULL = sin límite (∞)',
    precio_mes_eur DECIMAL(10,2) NOT NULL,
    stripe_price_id VARCHAR(120) NULL,
    orden INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_tarifa_tramos_tarifa (tarifa_id),
    CONSTRAINT fk_tarifa_tramos_tarifa FOREIGN KEY (tarifa_id) REFERENCES tarifas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Empresa colaboradora → tarifa
ALTER TABLE empresas_colaboradoras
    ADD COLUMN tarifa_id INT UNSIGNED NULL AFTER activa;

ALTER TABLE empresas_colaboradoras
    ADD CONSTRAINT fk_empresas_tarifa FOREIGN KEY (tarifa_id) REFERENCES tarifas(id) ON DELETE SET NULL;

-- Ventas: datos de volumen (producto_id queda nullable para histórico)
ALTER TABLE ventas
    MODIFY producto_id INT UNSIGNED NULL,
    ADD COLUMN num_obras INT UNSIGNED NULL AFTER producto_id,
    ADD COLUMN tarifa_tramo_id INT UNSIGNED NULL AFTER num_obras,
    ADD COLUMN precio_mes_eur DECIMAL(10,2) NULL AFTER tarifa_tramo_id,
    ADD COLUMN stripe_price_id VARCHAR(120) NULL AFTER precio_mes_eur;

ALTER TABLE ventas
    ADD CONSTRAINT fk_ventas_tarifa_tramo FOREIGN KEY (tarifa_tramo_id) REFERENCES tarifa_tramos(id) ON DELETE SET NULL;

-- Tarifa estándar + tramos (como tu captura)
INSERT INTO tarifas (nombre, activa, es_default) VALUES ('Tarifa estándar', 1, 1);
SET @tarifa_id = LAST_INSERT_ID();

INSERT INTO tarifa_tramos (tarifa_id, obras_desde, obras_hasta, precio_mes_eur, stripe_price_id, orden) VALUES
(@tarifa_id, 1,  5,  300.00, 'price_1TdPgaGhRm3Efx4yV5IYYI7P', 1),
(@tarifa_id, 6,  10, 285.00, 'price_1TdPgaGhRm3Efx4yYY3RE2gE', 2),
(@tarifa_id, 11, 15, 270.00, 'price_1TdPgbGhRm3Efx4yfMIN2kaA', 3),
(@tarifa_id, 16, NULL, 255.00, 'price_1TdPgaGhRm3Efx4yBaa4ZUQS', 4);