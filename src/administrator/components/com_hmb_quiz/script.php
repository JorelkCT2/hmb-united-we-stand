<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
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

					// Idempotent: ensures both tables exist on install AND upgrade.
					$db->setQuery(
						"CREATE TABLE IF NOT EXISTS `#__hmb_quiz_scores` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`player_name` VARCHAR(100) NOT NULL DEFAULT '',
							`user_id` INT NOT NULL DEFAULT 0,
							`score` INT NOT NULL DEFAULT 0,
							`rounds` INT NOT NULL DEFAULT 0,
							`correct` INT NOT NULL DEFAULT 0,
							`created` DATETIME NULL DEFAULT NULL,
							`ip` VARCHAR(45) NOT NULL DEFAULT '',
							PRIMARY KEY (`id`), KEY `idx_score` (`score`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
					)->execute();

					$db->setQuery(
						"CREATE TABLE IF NOT EXISTS `#__hmb_quiz_songs` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`title` VARCHAR(255) NOT NULL DEFAULT '',
							`band` VARCHAR(255) NOT NULL DEFAULT '',
							`genre` VARCHAR(100) NOT NULL DEFAULT '',
							`year` VARCHAR(10) NOT NULL DEFAULT '',
							`album` VARCHAR(255) NOT NULL DEFAULT '',
							`cover_url` VARCHAR(255) NOT NULL DEFAULT '',
							`origin` VARCHAR(255) NOT NULL DEFAULT '',
							`singer` VARCHAR(255) NOT NULL DEFAULT '',
							`youtube_id` VARCHAR(32) NOT NULL DEFAULT '',
							`difficulty` TINYINT NOT NULL DEFAULT 1,
							`start_time` INT NOT NULL DEFAULT 0,
							`clip_length` INT NOT NULL DEFAULT 0,
							`state` TINYINT NOT NULL DEFAULT 1,
							`ordering` INT NOT NULL DEFAULT 0,
							`created` DATETIME NULL DEFAULT NULL,
							PRIMARY KEY (`id`), KEY `idx_state` (`state`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
					)->execute();

					// Ensure newer columns exist on installs that predate them.
					foreach ([
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `difficulty` TINYINT NOT NULL DEFAULT 1",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `album` VARCHAR(255) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `origin` VARCHAR(255) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `cover_url` VARCHAR(255) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `singer` VARCHAR(255) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `content_type` VARCHAR(10) NOT NULL DEFAULT 'song'",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `movie` VARCHAR(255) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `movie_year` VARCHAR(10) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `director` VARCHAR(255) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `original_title` VARCHAR(255) NOT NULL DEFAULT ''",
						"ALTER TABLE `#__hmb_quiz_songs` ADD COLUMN IF NOT EXISTS `poster_url` VARCHAR(255) NOT NULL DEFAULT ''",
					] as $alter) {
						try {
							$db->setQuery($alter)->execute();
						} catch (\Throwable $e) {
							// Ignore if it already exists.
						}
					}

					// Normaliza rankings con rondas no estándar (p. ej. partidas que cayeron a
					// 19 por un fallo) al tramo más cercano (10/20/50), para que no salgan pestañas raras.
					try {
						$db->setQuery(
							"UPDATE `#__hmb_quiz_scores` SET `rounds` = CASE"
							. " WHEN `rounds` <= 15 THEN 10"
							. " WHEN `rounds` <= 35 THEN 20"
							. " ELSE 50 END"
							. " WHERE `rounds` > 0 AND `rounds` NOT IN (10, 20, 50)"
						)->execute();
					} catch (\Throwable $e) {
					}

					$db->setQuery(
						"CREATE TABLE IF NOT EXISTS `#__hmb_quiz_players` (
							`user_id` INT UNSIGNED NOT NULL,
							`player_name` VARCHAR(100) NOT NULL DEFAULT '',
							`games_played` INT NOT NULL DEFAULT 0,
							`total_correct` INT NOT NULL DEFAULT 0,
							`total_rounds` INT NOT NULL DEFAULT 0,
							`total_score` INT NOT NULL DEFAULT 0,
							`total_league` INT NOT NULL DEFAULT 0,
							`total_ms` BIGINT NOT NULL DEFAULT 0,
							`total_timed` INT NOT NULL DEFAULT 0,
							`best_score` INT NOT NULL DEFAULT 0,
							`updated` DATETIME NULL DEFAULT NULL,
							PRIMARY KEY (`user_id`), KEY `idx_league` (`total_league`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
					)->execute();

					$db->setQuery(
						"CREATE TABLE IF NOT EXISTS `#__metalquiz_presence` (
							`user_id` INT UNSIGNED NOT NULL,
							`last_seen` DATETIME NOT NULL,
							PRIMARY KEY (`user_id`), KEY `idx_last_seen` (`last_seen`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
					)->execute();

					$db->setQuery(
						"CREATE TABLE IF NOT EXISTS `#__metalquiz_matches` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
							`challenger_id` INT NOT NULL DEFAULT 0,
							`opponent_id` INT NOT NULL DEFAULT 0,
							`status` VARCHAR(20) NOT NULL DEFAULT 'pending',
							`rounds` INT NOT NULL DEFAULT 10,
							`genres` TEXT NULL,
							`seed` INT UNSIGNED NOT NULL DEFAULT 0,
							`rounds_data` MEDIUMTEXT NULL,
							`challenger_score` INT NOT NULL DEFAULT 0,
							`opponent_score` INT NOT NULL DEFAULT 0,
							`challenger_done` TINYINT NOT NULL DEFAULT 0,
							`opponent_done` TINYINT NOT NULL DEFAULT 0,
							`winner_id` INT NOT NULL DEFAULT 0,
							`created` DATETIME NULL DEFAULT NULL,
							`updated` DATETIME NULL DEFAULT NULL,
							PRIMARY KEY (`id`),
							KEY `idx_opp` (`opponent_id`, `status`),
							KEY `idx_chal` (`challenger_id`, `status`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
					)->execute();

					try {
						$db->setQuery("ALTER TABLE `#__metalquiz_matches` ADD COLUMN IF NOT EXISTS `abandoned_by` INT NOT NULL DEFAULT 0")->execute();
					} catch (\Throwable $e) {
					}

					// Índice de ronda POR JUGADOR en la propia fila del duelo (no en sesión:
					// la sesión la pisan las peticiones concurrentes y repetía canción).
					try {
						$db->setQuery("ALTER TABLE `#__metalquiz_matches` ADD COLUMN IF NOT EXISTS `challenger_idx` INT NOT NULL DEFAULT 0")->execute();
						$db->setQuery("ALTER TABLE `#__metalquiz_matches` ADD COLUMN IF NOT EXISTS `opponent_idx` INT NOT NULL DEFAULT 0")->execute();
					} catch (\Throwable $e) {
					}

					$db->setQuery(
						"CREATE TABLE IF NOT EXISTS `#__metalquiz_elo` (
							`user_id` INT UNSIGNED NOT NULL,
							`elo` INT NOT NULL DEFAULT 1000,
							`wins` INT NOT NULL DEFAULT 0,
							`losses` INT NOT NULL DEFAULT 0,
							`draws` INT NOT NULL DEFAULT 0,
							`played` INT NOT NULL DEFAULT 0,
							`updated` DATETIME NULL DEFAULT NULL,
							PRIMARY KEY (`user_id`), KEY `idx_elo` (`elo`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
					)->execute();

					// Seed the genre list so the dropdown works out of the box.
					try {
						$ext = $db->getQuery(true)
							->select($db->quoteName('params'))
							->from($db->quoteName('#__extensions'))
							->where($db->quoteName('element') . ' = ' . $db->quote('com_hmb_quiz'))
							->where($db->quoteName('type') . ' = ' . $db->quote('component'));
						$db->setQuery($ext);
						$reg = new \Joomla\Registry\Registry((string) $db->loadResult());

						if (trim((string) $reg->get('genres_list', '')) === '') {
							$reg->set('genres_list', implode("\n", [
								'Heavy Metal', 'Thrash Metal', 'Death Metal', 'Black Metal', 'Power Metal',
								'Doom Metal', 'Speed Metal', 'Hard Rock', 'Progressive Metal', 'Folk Metal',
								'Symphonic Metal', 'Metalcore', 'Groove Metal', 'Nu Metal', 'Glam Metal',
							]));
							$params = $reg->toString();
							$upd = $db->getQuery(true)
								->update($db->quoteName('#__extensions'))
								->set($db->quoteName('params') . ' = :p')
								->where($db->quoteName('element') . ' = ' . $db->quote('com_hmb_quiz'))
								->where($db->quoteName('type') . ' = ' . $db->quote('component'))
								->bind(':p', $params);
							$db->setQuery($upd)->execute();
						}
					} catch (\Throwable $e) {
						// Non-critical.
					}

					Factory::getApplication()->enqueueMessage(
						'HMB Quiz v1.2 listo. Ve a «Canciones del juego» para añadir tus vídeos y, si quieres, fijar el tramo de cada uno.',
						'info'
					);

					return true;
				}
			}
		);
	}
};
