CREATE TABLE IF NOT EXISTS `#__hmb_temas` (
  `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(255)      NOT NULL DEFAULT '',
  `album`         VARCHAR(255)      NOT NULL DEFAULT '',
  `album_year`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `track_number`  TINYINT UNSIGNED  NOT NULL DEFAULT 0,
  `duration`      SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Segundos',
  `cover_image`   VARCHAR(500)      NOT NULL DEFAULT '',
  `audio_file`    VARCHAR(500)      NOT NULL DEFAULT '',
  `spotify_url`   VARCHAR(500)      NOT NULL DEFAULT '',
  `youtube_url`   VARCHAR(500)      NOT NULL DEFAULT '',
  `description`   TEXT,
  `published`     TINYINT(1)        NOT NULL DEFAULT 1,
  `ordering`      INT               NOT NULL DEFAULT 0,
  `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_album`     (`album`),
  KEY `idx_published` (`published`),
  KEY `idx_orden`     (`album_year`, `track_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla de discos ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `#__hmb_discos` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `grupo`        VARCHAR(255)     NOT NULL DEFAULT '',
  `titulo`       VARCHAR(255)     NOT NULL DEFAULT '',
  `productora`   VARCHAR(255)     NOT NULL DEFAULT '',
  `anno`         SMALLINT(4)      NOT NULL DEFAULT 0,
  `portada`      VARCHAR(500)     NOT NULL DEFAULT '',
  `audio_file`   VARCHAR(500)     NOT NULL DEFAULT '',
  `youtube_links` TEXT,
  `descripcion`  TEXT,
  `published`    TINYINT(1)       NOT NULL DEFAULT 1,
  `ordering`     INT              NOT NULL DEFAULT 0,
  `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`),
  KEY `idx_anno`      (`anno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
