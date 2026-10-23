<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->set(
			InstallerScriptInterface::class,
			new class () implements InstallerScriptInterface {
				public function install(InstallerAdapter $adapter): bool
				{
					return true;
				}

				public function update(InstallerAdapter $adapter): bool
				{
					return true;
				}

				public function uninstall(InstallerAdapter $adapter): bool
				{
					return true;
				}

				public function preflight(string $type, InstallerAdapter $adapter): bool
				{
					return true;
				}

				public function postflight(string $type, InstallerAdapter $adapter): bool
				{
					if ($type === 'uninstall') {
						return true;
					}

					$db = Factory::getContainer()->get('DatabaseDriver');

					// Idempotente: garantiza las tablas tanto en instalación como en actualización.
					$tables = [
						"CREATE TABLE IF NOT EXISTS `#__hmb_fest_checkins` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`user_id` INT UNSIGNED NOT NULL,
							`festival_id` INT UNSIGNED NOT NULL,
							`festival_name` VARCHAR(255) NOT NULL DEFAULT '',
							`festival_end` DATE NULL DEFAULT NULL,
							`intent` VARCHAR(10) NOT NULL DEFAULT 'colegas',
							`adult_confirmed` TINYINT NOT NULL DEFAULT 0,
							`bio` VARCHAR(160) NOT NULL DEFAULT '',
							`visible` TINYINT NOT NULL DEFAULT 1,
							`created` DATETIME NOT NULL,
							`expires` DATETIME NOT NULL,
							PRIMARY KEY (`id`),
							UNIQUE KEY `uq_user_fest` (`user_id`, `festival_id`),
							KEY `idx_fest` (`festival_id`),
							KEY `idx_expires` (`expires`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
						"CREATE TABLE IF NOT EXISTS `#__hmb_fest_horns` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`user_from` INT UNSIGNED NOT NULL,
							`user_to` INT UNSIGNED NOT NULL,
							`festival_id` INT UNSIGNED NOT NULL,
							`created` DATETIME NOT NULL,
							PRIMARY KEY (`id`),
							UNIQUE KEY `uq_horn` (`user_from`, `user_to`, `festival_id`),
							KEY `idx_to` (`user_to`, `festival_id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
						"CREATE TABLE IF NOT EXISTS `#__hmb_fest_matches` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`user_a` INT UNSIGNED NOT NULL,
							`user_b` INT UNSIGNED NOT NULL,
							`festival_id` INT UNSIGNED NOT NULL,
							`festival_end` DATE NULL DEFAULT NULL,
							`created` DATETIME NOT NULL,
							PRIMARY KEY (`id`),
							UNIQUE KEY `uq_match` (`user_a`, `user_b`, `festival_id`),
							KEY `idx_a` (`user_a`),
							KEY `idx_b` (`user_b`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
						"CREATE TABLE IF NOT EXISTS `#__hmb_fest_messages` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`match_id` INT UNSIGNED NOT NULL,
							`user_from` INT UNSIGNED NOT NULL,
							`body` VARCHAR(1000) NOT NULL,
							`is_read` TINYINT NOT NULL DEFAULT 0,
							`created` DATETIME NOT NULL,
							PRIMARY KEY (`id`),
							KEY `idx_match` (`match_id`, `id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
						"CREATE TABLE IF NOT EXISTS `#__hmb_fest_blocks` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`user_id` INT UNSIGNED NOT NULL,
							`blocked_id` INT UNSIGNED NOT NULL,
							`created` DATETIME NOT NULL,
							PRIMARY KEY (`id`),
							UNIQUE KEY `uq_block` (`user_id`, `blocked_id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
						"CREATE TABLE IF NOT EXISTS `#__hmb_fest_reports` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`user_id` INT UNSIGNED NOT NULL,
							`reported_id` INT UNSIGNED NOT NULL,
							`festival_id` INT UNSIGNED NOT NULL DEFAULT 0,
							`reason` VARCHAR(500) NOT NULL DEFAULT '',
							`created` DATETIME NOT NULL,
							PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
					];

					foreach ($tables as $sql) {
						try {
							$db->setQuery($sql)->execute();
						} catch (\Throwable $e) {
							// No crítico: el SQL de instalación del manifiesto es el primer seguro.
						}
					}

					Factory::getApplication()->enqueueMessage(
						'HMB Festival listo. Configura la integración con MetalMap en Opciones y crea un ítem de menú "HMB Festival Mode".',
						'info'
					);

					return true;
				}
			}
		);
	}
};
