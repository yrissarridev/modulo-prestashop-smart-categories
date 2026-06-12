-- SmartCategories module SQL
-- These tables are also created via installDb() in the module's install() method

CREATE TABLE IF NOT EXISTS `PREFIX_smartcategory_rules` (
    `id_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `id_category` INT(10) UNSIGNED NOT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `noindex` TINYINT(1) NOT NULL DEFAULT 0,
    `flag_text` VARCHAR(100) DEFAULT '',
    `flag_bg` VARCHAR(7) DEFAULT '#e84444',
    `flag_color` VARCHAR(7) DEFAULT '#ffffff',
    `listing_sel_type` VARCHAR(10) DEFAULT 'class',
    `listing_sel_value` VARCHAR(255) DEFAULT 'product-price-and-shipping',
    `listing_position` VARCHAR(10) DEFAULT 'prepend',
    `product_sel_type` VARCHAR(10) DEFAULT 'class',
    `product_sel_value` VARCHAR(255) DEFAULT 'product-prices',
    `product_position` VARCHAR(10) DEFAULT 'prepend',
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla que almacena el badge personalizado por producto (gestionado por las reglas)
CREATE TABLE IF NOT EXISTS `PREFIX_smartcategory_rule_products` (
    `id_rule` INT(10) UNSIGNED NOT NULL,
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_category` INT(10) UNSIGNED NOT NULL,
    `date_add` DATETIME NOT NULL,
    PRIMARY KEY (`id_rule`, `id_product`),
    KEY `id_category` (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla que almacena qué productos ha añadido el módulo por cada regla
-- Esto evita eliminar productos añadidos manualmente a la categoría

CREATE TABLE IF NOT EXISTS `PREFIX_smartcategory_badges` (
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_rule` INT(10) UNSIGNED NOT NULL,
    `badge_text` VARCHAR(100) NOT NULL,
    `badge_bg` VARCHAR(7) NOT NULL DEFAULT '#e84444',
    `badge_color` VARCHAR(7) NOT NULL DEFAULT '#ffffff',
    PRIMARY KEY (`id_product`, `id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migraciones para instalaciones existentes:
-- ALTER TABLE `PREFIX_smartcategory_rules` ADD COLUMN IF NOT EXISTS `flag_text` VARCHAR(100) DEFAULT '';
-- ALTER TABLE `PREFIX_smartcategory_rules` DROP COLUMN IF EXISTS `product_flag`;

CREATE TABLE IF NOT EXISTS `PREFIX_smartcategory_conditions` (
    `id_condition` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_rule` INT(10) UNSIGNED NOT NULL,
    `condition_type` VARCHAR(50) NOT NULL,
    `operator` VARCHAR(20) NOT NULL,
    `value` TEXT NOT NULL,
    `value2` TEXT DEFAULT NULL,
    `sort_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_condition`),
    KEY `id_rule` (`id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_smartcategory_logs` (
    `id_log` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_rule` INT(10) UNSIGNED NOT NULL,
    `products_added` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `products_removed` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `execution_time` FLOAT NOT NULL DEFAULT 0,
    `status` ENUM('success','error') NOT NULL DEFAULT 'success',
    `message` TEXT,
    `date_add` DATETIME NOT NULL,
    PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Migración v1.1: ampliar columnas value/value2 a TEXT ──────────────────
-- Ejecutar manualmente si ya tienes el módulo instalado:
-- ALTER TABLE `PREFIX_smartcategory_conditions` MODIFY `value` TEXT NOT NULL;
-- ALTER TABLE `PREFIX_smartcategory_conditions` MODIFY `value2` TEXT DEFAULT NULL;
