#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Valida src/ y la plantilla y genera dist/ (un ZIP por extension).

    python construir.py                 valida todo y construye los ZIP
    python construir.py --solo-validar  valida, no escribe nada

Valida: que cada manifiesto sea XML bien formado y que todos los .php pasen
`php -l`. Si algo falla, sale con codigo 1 y NO construye: media entrega
rota es peor que ninguna.

Los ZIP llevan las rutas tal cual estan en el servidor (overlay), sirven de
copia/entrega y de punto de restauracion. La instalacion normal es
desplegar.py, que copia ficheros a las mismas rutas.
"""

import argparse
import json
import os
import subprocess
import sys
import zipfile

import hmbcommon as c


def validar():
    errores = []
    php = 'php'

    for base, _p in c.ORIGENES:
        for dirpath, _d, nombres in os.walk(base):
            for n in nombres:
                ruta = os.path.join(dirpath, n)

                if n.endswith('.php'):
                    r = subprocess.run([php, '-l', ruta], capture_output=True, text=True)

                    if r.returncode != 0:
                        errores.append((r.stdout + r.stderr).strip())

                elif n.endswith('.xml'):
                    import xml.etree.ElementTree as ET

                    try:
                        ET.parse(ruta)
                    except ET.ParseError as e:
                        errores.append('%s: %s' % (ruta, e))

    return errores


def construir():
    os.makedirs(c.DIST, exist_ok=True)
    locales = c.ficheros_locales()
    indice = {}

    for nombre, carpetas in sorted(c.extensiones().items()):
        _m, version = c.manifiesto(carpetas)
        prefijos = tuple(x + '/' for x in carpetas)
        # ficheros de idioma sueltos que llevan el nombre de la extension
        sueltos = [r for r in locales
                   if '/language/' in '/' + r and r.endswith('.ini') and nombre in os.path.basename(r)]
        rels = sorted(set([r for r in locales if r.startswith(prefijos)] + sueltos))

        if not rels:
            continue

        zip_nombre = '%s_%s.zip' % (nombre, version or 'sinversion')

        with zipfile.ZipFile(os.path.join(c.DIST, zip_nombre), 'w', zipfile.ZIP_DEFLATED) as z:
            for r in rels:
                z.write(locales[r], r)

        indice[nombre] = {'version': version, 'zip': zip_nombre, 'ficheros': len(rels),
                          'sha256': c.sha256(os.path.join(c.DIST, zip_nombre))}
        print('  %-28s %-10s %3d ficheros' % (nombre, version or '-', len(rels)))

    with open(os.path.join(c.DIST, 'INDICE.json'), 'w', encoding='utf-8') as f:
        json.dump(indice, f, indent=2, ensure_ascii=False)

    print('dist/: %d ZIP' % len(indice))


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--solo-validar', action='store_true')
    a = ap.parse_args()

    print('Validando...')
    errores = validar()

    if errores:
        print('\n'.join(errores))
        print('FALLO: %d error(es). No se construye.' % len(errores))
        sys.exit(1)

    print('OK.')

    if not a.solo_validar:
        construir()


if __name__ == '__main__':
    main()
