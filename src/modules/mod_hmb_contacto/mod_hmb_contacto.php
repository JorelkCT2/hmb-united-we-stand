<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$app   = Factory::getApplication();
$input = $app->getInput();
$root  = rtrim(Uri::root(), '/');

// Helper para extraer URL de imagen desde params de Joomla
function hmb_ct_img(string $root, $val): string {
    if (empty($val)) return '';
    if (is_object($val)) $val = $val->imagefile ?? '';
    if (is_string($val)) {
        $d = json_decode($val);
        if ($d && isset($d->imagefile)) $val = $d->imagefile;
    }
    $val = trim((string)$val);
    if (!$val) return '';
    return preg_match('/^https?:\/\//i', $val) ? $val : $root . '/' . ltrim($val, '/');
}

// ── Procesar formulario si viene un POST de este módulo ──
$formSent    = false;
$formSuccess = null;
$formMsg     = '';

if ($input->getMethod() === 'POST' && $input->post->getString('hmb_ct_submit', '') === '1') {

    if (!Session::checkToken()) {
        $formSent    = true;
        $formSuccess = false;
        $formMsg     = 'Token de seguridad inválido.';
    } else {
        $f       = InputFilter::getInstance();
        $nombre  = $f->clean($input->post->getString('nombre',   ''), 'string');
        $email   = $f->clean($input->post->getString('email',    ''), 'email');
        $telefono= $f->clean($input->post->getString('telefono', ''), 'string');
        $motivo  = $f->clean($input->post->getString('motivo',   ''), 'string');
        $mensaje = $f->clean($input->post->getString('mensaje',  ''), 'string');
        $dest    = trim((string)$params->get('email_contacto', ''));
        $prefijo = (string)$params->get('form_prefijo', '[HMB Contacto]');

        $ok = false;
        if ($dest && $nombre && $email && $mensaje) {
            try {
                $mailer = Factory::getMailer();
                $mailer->addRecipient($dest);
                $mailer->setSubject(trim($prefijo . ' ' . $motivo . ' — ' . $nombre));
                $body  = "NUEVO MENSAJE DE CONTACTO\n" . str_repeat('=', 40) . "\n\n";
                $body .= "Nombre:   $nombre\nEmail:    $email\n";
                if ($telefono) $body .= "Teléfono: $telefono\n";
                $body .= "Motivo:   $motivo\n\nMensaje:\n$mensaje\n";
                $mailer->setBody($body);
                $mailer->isHTML(false);
                $result = $mailer->Send();
                $ok     = ($result === true || $result === 1);
            } catch (\Throwable $e) {
                $ok = false;
            }
        }

        $formSent    = true;
        $formSuccess = $ok;
        $formMsg     = $ok
            ? (string)$params->get('form_msg_ok',  '¡Mensaje enviado!')
            : (string)$params->get('form_msg_err', 'Error al enviar.');
    }
}

// ── Variables para el template ──
$titulo      = (string)$params->get('titulo_pagina', 'Contáctanos');
$subtit      = (string)$params->get('subtitulo', 'Contratación · Colaboraciones · Prensa');
$intro       = $params->get('texto_intro', '');
$contr       = $params->get('texto_contratacion', '');
$heroUrl     = hmb_ct_img($root, $params->get('imagen_hero'));
$formActivo  = (int)$params->get('form_activo', 1);
$emailDst    = trim((string)$params->get('email_contacto', ''));
$emailVis    = (int)$params->get('email_visible', 1);
$waNum       = preg_replace('/\D/', '', (string)$params->get('whatsapp_numero', ''));
$waTxt       = rawurlencode((string)$params->get('whatsapp_texto', 'Hola! Contacto desde la web HMB.'));
$waCan       = trim((string)$params->get('whatsapp_canal', ''));

$motivosRaw  = (string)$params->get('motivos_lista', '');
$motivos     = array_values(array_filter(array_map('trim', explode("\n", $motivosRaw))));

$redes = array_filter([
    'Instagram'   => (string)$params->get('red_instagram', ''),
    'Facebook'    => (string)$params->get('red_facebook', ''),
    'YouTube'     => (string)$params->get('red_youtube', ''),
    'TikTok'      => (string)$params->get('red_tiktok', ''),
    'Spotify'     => (string)$params->get('red_spotify', ''),
    'X / Twitter' => (string)$params->get('red_twitter', ''),
    'SoundCloud'  => (string)$params->get('red_soundcloud', ''),
    'Bandcamp'    => (string)$params->get('red_bandcamp', ''),
]);

$fotos = array_values(array_filter([
    hmb_ct_img($root, $params->get('foto_1')),
    hmb_ct_img($root, $params->get('foto_2')),
    hmb_ct_img($root, $params->get('foto_3')),
    hmb_ct_img($root, $params->get('foto_4')),
    hmb_ct_img($root, $params->get('foto_5')),
    hmb_ct_img($root, $params->get('foto_6')),
]));

$token       = Session::getFormToken();
$currentUrl  = Uri::current();

require ModuleHelper::getLayoutPath('mod_hmb_contacto', $params->get('layout', 'default'));
