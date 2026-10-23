#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Piezas comunes de construir.py y desplegar.py (HMBNEW, Joomla 6).

El repo guarda las extensiones con las MISMAS rutas que tienen en el
servidor (src/components/com_x, src/modules/mod_y, ...), asi que subir es
copiar cada fichero a la misma ruta bajo clickandbuilds/HMBNEW. La plantilla
vive en templates/hmb (fuera de src) y se sube igual.
"""

import hashlib
import os
import xml.etree.ElementTree as ET

RAIZ = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(RAIZ, 'src')
DIST = os.path.join(RAIZ, 'dist')

USUARIO = 'u108199308'
SERVIDOR = 'access909012048.webspace-data.io'
CLAVE = os.path.expanduser('~/.ssh/ionos')
RAIZ_REMOTA = 'clickandbuilds/HMBNEW'
URL = 'https://heavymetalbrothers.com'

# Carpetas locales que se sincronizan: (carpeta local, prefijo en servidor)
ORIGENES = [(SRC, ''), (os.path.join(RAIZ, 'templates'), 'templates')]

# Nunca se entregan, aunque aparezcan por error en el arbol.
PROHIBIDOS = ('configuration.php', '.htaccess', 'lic_config.php')


def sha256(ruta):
    h = hashlib.sha256()

    with open(ruta, 'rb') as f:
        for trozo in iter(lambda: f.read(1 << 20), b''):
            h.update(trozo)

    return h.hexdigest()


def ficheros_locales():
    """{ruta relativa al servidor (con /): ruta local} de todo lo sincronizable."""
    res = {}

    for base, prefijo in ORIGENES:
        if not os.path.isdir(base):
            continue

        for dirpath, _dirs, nombres in os.walk(base):
            for n in nombres:
                if n in PROHIBIDOS:
                    continue

                local = os.path.join(dirpath, n)
                rel = os.path.relpath(local, base).replace(os.sep, '/')
                res[(prefijo + '/' + rel) if prefijo else rel] = local

    return res


def extensiones():
    """[(nombre, [carpetas relativas al servidor], ficheros sueltos)].

    Una extension son sus carpetas (administrator/components/N, components/N,
    modules/N, plugins/grupo/N, media/N) mas los ficheros de idioma que
    llevan su nombre. La plantilla va como 'tpl_hmb'.
    """
    res = {}

    def anotar(nombre, carpeta):
        res.setdefault(nombre, []).append(carpeta)

    for tipo in ('administrator/components', 'components', 'modules'):
        d = os.path.join(SRC, tipo)

        if os.path.isdir(d):
            for n in sorted(os.listdir(d)):
                if os.path.isdir(os.path.join(d, n)):
                    anotar(n, tipo + '/' + n)

    dp = os.path.join(SRC, 'plugins')

    if os.path.isdir(dp):
        for g in sorted(os.listdir(dp)):
            for n in sorted(os.listdir(os.path.join(dp, g))):
                anotar('plg_%s_%s' % (g, n), 'plugins/%s/%s' % (g, n))

    dm = os.path.join(SRC, 'media')

    if os.path.isdir(dm):
        for n in sorted(os.listdir(dm)):
            if n in res:
                anotar(n, 'media/' + n)
            else:
                for clave in list(res):
                    if n == clave or (clave.startswith('plg_') and n == clave):
                        anotar(clave, 'media/' + n)
                        break
                else:
                    res.setdefault(n, []).append('media/' + n)

    if os.path.isdir(os.path.join(RAIZ, 'templates', 'hmb')):
        res['tpl_hmb'] = ['templates/hmb']

    return res


def manifiesto(carpetas):
    """Primer .xml con <extension> dentro de las carpetas -> (ruta, version)."""
    for c in carpetas:
        d = os.path.join(SRC, c) if not c.startswith('templates') else os.path.join(RAIZ, c)

        if not os.path.isdir(d):
            continue

        for n in sorted(os.listdir(d)):
            if n.endswith('.xml'):
                ruta = os.path.join(d, n)

                try:
                    raiz = ET.parse(ruta).getroot()
                except ET.ParseError:
                    continue

                if raiz.tag == 'extension':
                    return ruta, (raiz.findtext('version') or '').strip()

    return None, ''
