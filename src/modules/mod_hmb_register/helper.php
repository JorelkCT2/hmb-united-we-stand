<?php
/**
 * Heavy Metal Brothers — Registration Module Helper
 * com_ajax: index.php?option=com_ajax&module=hmb_register&method=register&format=json
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Uri\Uri;

class ModHmbRegisterHelper
{
    // ────────────────────────────────────────────────────────────────────────
    //  REGISTRO
    // ────────────────────────────────────────────────────────────────────────
    public static function registerAjax(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // ── CSRF ──────────────────────────────────────────────────────────
        \Joomla\CMS\Session\Session::checkToken()
            or jexit(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'));

        // ── Recoger datos del formulario ───────────────────────────────────
        $firstname       = trim($_POST['firstname']        ?? '');
        $surname         = trim($_POST['surname']          ?? '');
        $username        = trim($_POST['username']         ?? '');
        $email           = trim($_POST['email']            ?? '');
        $email2          = trim($_POST['email2']           ?? '');
        $password        = $_POST['password']              ?? '';
        $password2       = $_POST['password2']             ?? '';
        $recaptchaToken  = trim($_POST['g-recaptcha-response'] ?? '');
        // Metal Quiz opt-in: 1 si marcado, 0 si desmarcado
        $quizOptIn            = isset($_POST['metal_quiz_invitations']) ? 1 : 0;
        // Notificaciones generales
        $acceptNotifications  = isset($_POST['accept_notifications'])  ? 1 : 0;

        $fullName = trim($firstname . ' ' . $surname);
        if (empty($fullName)) $fullName = $username;

        // ── reCAPTCHA v3 ───────────────────────────────────────────────────
        $modDb  = Factory::getDbo();
        $modQ   = $modDb->getQuery(true)
            ->select('params')
            ->from($modDb->qn('#__modules'))
            ->where($modDb->qn('module')    . ' = ' . $modDb->q('mod_hmb_register'))
            ->where($modDb->qn('published') . ' = 1');
        $paramsJson      = $modDb->setQuery($modQ)->loadResult();
        $moduleParams    = new \Joomla\Registry\Registry($paramsJson ?: '{}');
        $captchaEnabled  = (bool) $moduleParams->get('enable_recaptcha', false);
        $recaptchaSecret = trim((string) $moduleParams->get('recaptcha_secret_key', ''));

        if ($captchaEnabled && !empty($recaptchaSecret)) {
            if ($recaptchaToken === '') {
                return ['success' => false, 'message' => 'No se pudo verificar que eres una persona. Recarga la página e inténtalo de nuevo.'];
            }
            $ctx = stream_context_create(['http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret'   => $recaptchaSecret,
                    'response' => $recaptchaToken,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ]),
                'timeout' => 10,
            ]]);
            $result      = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
            $captchaResp = $result ? json_decode($result, true) : [];
            $score       = (float) ($captchaResp['score'] ?? 0);
            if (empty($captchaResp['success']) || $score < 0.3) {
                return ['success' => false, 'message' => 'Actividad sospechosa detectada. Inténtalo de nuevo.'];
            }
        }

        // ── Validación ─────────────────────────────────────────────────────
        $errors = [];
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Correo electrónico inválido.';
        }
        if (!empty($email2) && $email !== $email2) {
            $errors[] = 'Los correos electrónicos no coinciden.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $password2) {
            $errors[] = 'Las contraseñas no coinciden.';
        }
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors)];
        }

        try {
            $db = Factory::getDbo();

            // ── Duplicados ─────────────────────────────────────────────────
            $q = $db->getQuery(true)->select('COUNT(*)')->from('#__users')
                ->where('email = ' . $db->q($email));
            if ((int) $db->setQuery($q)->loadResult() > 0) {
                return ['success' => false, 'message' => 'No se pudo completar el registro con esos datos. Si ya tienes cuenta, inicia sesión o recupera tu contraseña.'];
            }

            if (empty($username)) {
                $username = self::generateUsername($email, $db);
            } else {
                $q = $db->getQuery(true)->select('COUNT(*)')->from('#__users')
                    ->where('username = ' . $db->q($username));
                if ((int) $db->setQuery($q)->loadResult() > 0) {
                    return ['success' => false, 'message' => 'No se pudo completar el registro con esos datos. Si ya tienes cuenta, inicia sesión o recupera tu contraseña.'];
                }
            }

            // ── Crear usuario ──────────────────────────────────────────────
            $now = Factory::getDate()->toSql();

            // Params iniciales: Metal Quiz opt-in + notificaciones generales
            $userParams = json_encode([
                'metal_quiz_invitations' => $quizOptIn,
                'accept_notifications'   => $acceptNotifications,
            ]);

            $userTable = new \Joomla\CMS\Table\User(Factory::getDbo());
            $userTable->bind([
                'name'         => $fullName,
                'username'     => $username,
                'email'        => $email,
                'password'     => UserHelper::hashPassword($password),
                'block'        => 0,
                'sendEmail'    => 0,
                'registerDate' => $now,
                'activation'   => '',
                'params'       => $userParams,
            ]);

            if (!$userTable->check()) {
                return ['success' => false, 'message' => $userTable->getError()];
            }
            if (!$userTable->store()) {
                return ['success' => false, 'message' => $userTable->getError()];
            }

            $userId = (int) $userTable->id;
            if (!$userId) {
                return ['success' => false, 'message' => 'Error al crear el usuario.'];
            }

            // Grupo Registered (2)
            $groupMap = (object) ['user_id' => $userId, 'group_id' => 2];
            $db->insertObject('#__user_usergroup_map', $groupMap);

            // ── Avatar opcional ────────────────────────────────────────────
            if (!empty($_FILES['avatar']['name'])) {
                $rel = self::guardarAvatar($_FILES['avatar'], $userId);
                if ($rel !== null) {
                    $ap = new \Joomla\Registry\Registry(json_decode($userParams, true));
                    $ap->set('avatar', $rel);
                    $db->setQuery(
                        $db->getQuery(true)
                            ->update($db->qn('#__users'))
                            ->set($db->qn('params') . ' = ' . $db->q((string) $ap))
                            ->where($db->qn('id') . ' = ' . (int) $userId)
                    )->execute();
                }
            }

            // ── Auto-login vía sesión ──────────────────────────────────────
            $loggedUser = \Joomla\CMS\User\User::getInstance($userId);
            $app->getSession()->set('user', $loggedUser);

            return [
                'success'  => true,
                'message'  => '¡Bienvenido a la Hermandad, ' . htmlspecialchars($fullName) . '!',
                'redirect' => self::redirectSeguro($input->getString('redirect', '')),
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    //  GOOGLE OAUTH
    // ────────────────────────────────────────────────────────────────────────
    public static function googleAuthAjax(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $credential = $input->post->getString('credential', '');
        $clientId   = $input->post->getString('client_id', '');

        if (empty($credential) || empty($clientId)) {
            return ['success' => false, 'message' => 'Credenciales de Google incompletas.'];
        }

        try {
            $context  = stream_context_create(['http' => ['timeout' => 10]]);
            $response = @file_get_contents(
                'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential),
                false, $context
            );

            if (!$response) {
                return ['success' => false, 'message' => 'No se pudieron verificar las credenciales de Google.'];
            }

            $payload = json_decode($response, true);

            if (empty($payload['aud']) || $payload['aud'] !== $clientId) {
                return ['success' => false, 'message' => 'Token de Google inválido.'];
            }

            $email = $payload['email'] ?? '';
            $name  = $payload['name']  ?? '';

            if (empty($email)) {
                return ['success' => false, 'message' => 'No se pudo obtener el email de Google.'];
            }

            $db = Factory::getDbo();
            $q  = $db->getQuery(true)->select('id')->from('#__users')
                ->where('email = ' . $db->q($email));
            $existingId = $db->setQuery($q)->loadResult();

            if ($existingId) {
                // Usuario ya existe → login
                $user = \Joomla\CMS\User\User::getInstance((int) $existingId);
                $app->getSession()->set('user', $user);
                return [
                    'success'  => true,
                    'message'  => '¡Bienvenido de nuevo, ' . htmlspecialchars($name) . '!',
                    'redirect' => self::redirectSeguro($input->getString('redirect', '')),
                    'action'   => 'login',
                ];
            }

            // Usuario nuevo vía Google: opt-in Metal Quiz = 1 por defecto
            $username = self::generateUsername($email, $db);
            $now      = Factory::getDate()->toSql();

            $userTable = new \Joomla\CMS\Table\User(Factory::getDbo());
            $userTable->bind([
                'name'         => $name ?: $username,
                'username'     => $username,
                'email'        => $email,
                'password'     => UserHelper::hashPassword(UserHelper::genRandomPassword(24)),
                'block'        => 0,
                'sendEmail'    => 0,
                'registerDate' => $now,
                'activation'   => '',
                'params'       => json_encode(['metal_quiz_invitations' => 1]),
            ]);

            if (!$userTable->check() || !$userTable->store()) {
                return ['success' => false, 'message' => 'No se pudo crear la cuenta desde Google.'];
            }

            $userId   = (int) $userTable->id;
            $groupMap = (object) ['user_id' => $userId, 'group_id' => 2];
            $db->insertObject('#__user_usergroup_map', $groupMap);

            $user = \Joomla\CMS\User\User::getInstance($userId);
            $app->getSession()->set('user', $user);

            return [
                'success'  => true,
                'message'  => '¡Bienvenido a la Hermandad, ' . htmlspecialchars($name) . '!',
                'redirect' => self::redirectSeguro($input->getString('redirect', '')),
                'action'   => 'register',
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ────────────────────────────────────────────────────────────────────────
    /** Motivo del ultimo fallo de guardarAvatar(), en espanol y listo para mostrar. */
    public static $errorAvatar = '';

    /**
     * Guarda un avatar subido. Valida el contenido real (no el tipo/extension que manda el cliente),
     * acepta fotos grandes (hasta 12 MB) y las reduce a 512 px, corrigiendo la orientacion EXIF del movil.
     * Devuelve la ruta relativa, o null (con el motivo en self::$errorAvatar).
     */
    public static function guardarAvatar(array $file, int $userId): ?string
    {
        self::$errorAvatar = '';
        $max   = 12 * 1024 * 1024;
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error !== UPLOAD_ERR_OK) {
            if ($error === UPLOAD_ERR_NO_FILE) {
                self::$errorAvatar = 'No has elegido ninguna imagen.';
            } elseif ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                self::$errorAvatar = 'La foto pesa demasiado (máx. 12 MB).';
            } else {
                self::$errorAvatar = 'La subida falló (código ' . (int) $error . '). Inténtalo de nuevo.';
            }

            return null;
        }

        if (($file['size'] ?? 0) > $max) {
            self::$errorAvatar = 'La foto pesa demasiado (máx. 12 MB).';

            return null;
        }

        if (!is_uploaded_file($file['tmp_name'] ?? '')) {
            self::$errorAvatar = 'Archivo no válido.';

            return null;
        }

        $info = @getimagesize($file['tmp_name']);
        $exts = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];

        if (!$info || !isset($exts[$info[2]])) {
            self::$errorAvatar = 'Formato no admitido. Usa JPG, PNG, GIF o WEBP (las fotos HEIC del iPhone hay que convertirlas o hacerlas en formato "más compatible").';

            return null;
        }

        $tipo = $info[2];
        $dir  = JPATH_ROOT . '/images/hmb_avatars/';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $nombre  = 'user_' . $userId . '.' . $exts[$tipo];
        $destino = $dir . $nombre;
        $guardado = false;

        // Reduccion con GD (no se toca el GIF para no romper animaciones pequenas).
        if ($tipo !== IMAGETYPE_GIF && function_exists('imagecreatefromstring')
            && (max($info[0], $info[1]) > 512 || ($file['size'] ?? 0) > 400 * 1024)) {
            $src = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));

            if ($src) {
                if ($tipo === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                    $exif = @exif_read_data($file['tmp_name']);
                    $giro = [3 => 180, 6 => -90, 8 => 90][(int) ($exif['Orientation'] ?? 1)] ?? 0;

                    if ($giro) {
                        $rot = @imagerotate($src, $giro, 0);

                        if ($rot) {
                            $src = $rot;
                        }
                    }
                }

                $w   = imagesx($src);
                $h   = imagesy($src);
                $esc = min(1, 512 / max($w, $h));
                $nw  = max(1, (int) round($w * $esc));
                $nh  = max(1, (int) round($h * $esc));
                $dst = imagecreatetruecolor($nw, $nh);

                if ($tipo === IMAGETYPE_PNG || $tipo === IMAGETYPE_WEBP) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
                }

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

                foreach (glob($dir . 'user_' . $userId . '.*') ?: [] as $viejo) {
                    @unlink($viejo);
                }

                if ($tipo === IMAGETYPE_JPEG) {
                    $guardado = @imagejpeg($dst, $destino, 88);
                } elseif ($tipo === IMAGETYPE_PNG) {
                    $guardado = @imagepng($dst, $destino, 6);
                } else {
                    $guardado = function_exists('imagewebp') && @imagewebp($dst, $destino, 88);
                }
            }
        }

        if (!$guardado) {
            if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
                self::$errorAvatar = 'No se pudo reducir la foto (pesa más de 2 MB). Prueba con una más pequeña.';

                return null;
            }

            foreach (glob($dir . 'user_' . $userId . '.*') ?: [] as $viejo) {
                @unlink($viejo);
            }

            if (!move_uploaded_file($file['tmp_name'], $destino)) {
                self::$errorAvatar = 'No se pudo guardar la imagen en el servidor.';

                return null;
            }
        }

        @chmod($destino, 0644);

        return 'images/hmb_avatars/' . $nombre;
    }

    /** Solo se permite volver a una URL del propio sitio; cualquier otra cosa cae a la portada. */
    /**
     * Destino tras iniciar sesion: la misma pagina en la que esta la persona.
     * Si esta en las paginas de registro/acceso (o vuelve de cerrar sesion) va a la portada.
     * Se usa el HOST ACTUAL para no perder la sesion al saltar entre www y sin www.
     */
    public static function destinoLogin(): string
    {
        $uri  = Uri::getInstance();
        $raiz = rtrim($uri->toString(['scheme', 'host', 'port']), '/') . '/';

        if (preg_match('#/(registro|component/users|login|logout)(/|$)#i', (string) $uri->getPath())
            || $uri->getVar('hmbbye') !== null || $uri->getVar('rdr') !== null) {
            return $raiz;
        }

        return $uri->toString(['scheme', 'host', 'port', 'path', 'query']);
    }

    public static function redirectSeguro(string $url): string
    {
        $raiz = Uri::root();
        $url  = trim($url);

        if ($url === '') {
            return $raiz;
        }

        $host = parse_url($raiz, PHP_URL_HOST);
        $dest = parse_url($url, PHP_URL_HOST);
        $base = preg_replace('/^www\./i', '', (string) $host);

        if ($dest === null) {
            // ruta relativa: valida salvo "//host" o esquemas raros
            return (strpos($url, '//') === 0 || preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) ? $raiz : $raiz . ltrim($url, '/');
        }

        return (preg_replace('/^www\./i', '', (string) $dest) === $base && preg_match('#^https?://#i', $url)) ? $url : $raiz;
    }

    private static function generateUsername(string $email, $db): string
    {
        $base     = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]);
        $base     = substr($base ?: 'user', 0, 20);
        $username = $base;
        $i        = 1;
        while (true) {
            $q = $db->getQuery(true)->select('COUNT(*)')->from('#__users')
                ->where('username = ' . $db->q($username));
            if ((int) $db->setQuery($q)->loadResult() === 0) break;
            $username = $base . $i++;
        }
        return $username;
    }
}
