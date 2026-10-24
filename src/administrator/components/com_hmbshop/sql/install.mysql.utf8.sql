-- com_hmbshop: tienda de Heavy Metal Brothers (sustituye a Phoca Cart).
-- Modelo tomado de la tienda de Magones (categorias, productos, ejes, variantes,
-- envios, pedidos) sin socios ni vocalias, y con recargo por talla/valor y IVA.

CREATE TABLE IF NOT EXISTS `#__hmbshop_categorias` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `nombre` VARCHAR(190) NOT NULL,
  `alias` VARCHAR(190) NOT NULL DEFAULT '',
  `titulo_largo` VARCHAR(255) NOT NULL DEFAULT '',
  `descripcion` TEXT NULL,
  `imagen` VARCHAR(255) NULL DEFAULT NULL,
  `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_desc` VARCHAR(400) NOT NULL DEFAULT '',
  `published` TINYINT(1) NOT NULL DEFAULT 1,
  `ordering` INT NOT NULL DEFAULT 0,
  `created` DATETIME NOT NULL,
  `phoca_id` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hmbshop_productos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `nombre` VARCHAR(190) NOT NULL,
  `alias` VARCHAR(190) NOT NULL DEFAULT '',
  `titulo_largo` VARCHAR(255) NOT NULL DEFAULT '',
  `fabricante` VARCHAR(190) NOT NULL DEFAULT '',
  `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_desc` VARCHAR(400) NOT NULL DEFAULT '',
  `descripcion` TEXT NULL,
  `precio` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `iva_pct` DECIMAL(4,2) NOT NULL DEFAULT 21.00,
  `sku` VARCHAR(60) NULL DEFAULT NULL,
  `destacado` TINYINT(1) NOT NULL DEFAULT 0,
  `published` TINYINT(1) NOT NULL DEFAULT 1,
  `ordering` INT NOT NULL DEFAULT 0,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `phoca_id` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_alias` (`alias`),
  KEY `idx_published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Un producto puede estar en varias categorias (Phoca lo permitia).
CREATE TABLE IF NOT EXISTS `#__hmbshop_producto_categorias` (
  `producto_id` INT UNSIGNED NOT NULL,
  `categoria_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`producto_id`, `categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hmbshop_fotos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id` INT UNSIGNED NOT NULL,
  `fichero` VARCHAR(255) NOT NULL,
  `principal` TINYINT(1) NOT NULL DEFAULT 0,
  `ordering` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Ejes de variacion (Talla, Color, Tipo...) y sus valores, con recargo (3XL +3 EUR).
CREATE TABLE IF NOT EXISTS `#__hmbshop_ejes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id` INT UNSIGNED NOT NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `obligatorio` TINYINT(1) NOT NULL DEFAULT 1,
  `ordering` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hmbshop_eje_valores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `eje_id` INT UNSIGNED NOT NULL,
  `valor` VARCHAR(60) NOT NULL,
  `recargo` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `por_defecto` TINYINT(1) NOT NULL DEFAULT 0,
  `ordering` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_eje` (`eje_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Variante = combinacion concreta de valores, con su stock.
CREATE TABLE IF NOT EXISTS `#__hmbshop_variantes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id` INT UNSIGNED NOT NULL,
  `etiqueta` VARCHAR(190) NOT NULL DEFAULT '',
  `sku` VARCHAR(60) NULL DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `recargo` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `published` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hmbshop_variante_valores` (
  `variante_id` INT UNSIGNED NOT NULL,
  `eje_id` INT UNSIGNED NOT NULL,
  `valor_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`variante_id`, `eje_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hmbshop_envios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(190) NOT NULL,
  `descripcion` VARCHAR(255) NULL DEFAULT NULL,
  `coste` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `pide_direccion` TINYINT(1) NOT NULL DEFAULT 1,
  `published` TINYINT(1) NOT NULL DEFAULT 1,
  `ordering` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- metodo_pago: transferencia | contrareembolso | stripe | paypal
CREATE TABLE IF NOT EXISTS `#__hmbshop_pedidos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `email` VARCHAR(190) NOT NULL DEFAULT '',
  `nombre` VARCHAR(190) NOT NULL DEFAULT '',
  `telefono` VARCHAR(30) NULL DEFAULT NULL,
  `envio_id` INT UNSIGNED NULL DEFAULT NULL,
  `envio_nombre` VARCHAR(190) NULL DEFAULT NULL,
  `envio_coste` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `direccion` VARCHAR(255) NULL DEFAULT NULL,
  `ciudad` VARCHAR(100) NULL DEFAULT NULL,
  `cp` VARCHAR(12) NULL DEFAULT NULL,
  `importe_total` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `iva_total` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` VARCHAR(20) NOT NULL DEFAULT 'transferencia',
  `referencia_pago` VARCHAR(80) NULL DEFAULT NULL,
  `estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  `created` DATETIME NOT NULL,
  `actualizado` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_referencia` (`referencia_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hmbshop_pedido_lineas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_id` INT UNSIGNED NOT NULL,
  `variante_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `producto_nombre` VARCHAR(190) NOT NULL,
  `etiqueta_variante` VARCHAR(190) NOT NULL DEFAULT '',
  `cantidad` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `precio_unitario` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `iva_pct` DECIMAL(4,2) NOT NULL DEFAULT 21.00,
  PRIMARY KEY (`id`),
  KEY `idx_pedido` (`pedido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
