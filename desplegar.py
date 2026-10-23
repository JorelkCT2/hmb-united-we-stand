#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Sube a HMBNEW (heavymetalbrothers.com) lo que haya cambiado y lo COMPRUEBA.

    python desplegar.py --solo-mirar     compara con el servidor, no toca nada
    python desplegar.py                  sube solo los ficheros distintos
    python desplegar.py --solo ruta/    limita a ficheros que empiecen por ruta/

Como funciona (mismo espiritu que SociWeb/desplegar.py):
  1. Calcula sha256 local de src/ y templates/hmb y pide al servidor el de
     los mismos ficheros. Solo se tocan los distintos o nuevos.
  2. Antes de pisar, guarda los ficheros que existen en un tar.gz del
     servidor: clickandbuilds/HMBNEW/tmp/hmb_backups/<fecha>.tar.gz
  3. Sube a una carpeta de preparacion y mueve fichero a fichero con mv
     (atomico): o esta el viejo entero o el nuevo entero.
  4. Vuelve a pedir las huellas al servidor y exige que cuadren, y mira que
     la portada responda 200 y no muestre errores de PHP.
No entrega nunca configuration.php ni .htaccess (ver hmbcommon.PROHIBIDOS).
Para volver atras: tar xzf del backup en la raiz de HMBNEW.
"""

import argparse
import io
import subprocess
import sys
import tarfile
import time
import urllib.request

import hmbcommon as c

DESTINO = '%s@%s' % (c.USUARIO, c.SERVIDOR)
SSH = ['ssh', '-i', c.CLAVE, '-o', 'BatchMode=yes', '-o', 'ConnectTimeout=20', DESTINO]


def ssh(orden, entrada=None):
    return subprocess.run(SSH + [orden], input=entrada, capture_output=True)


def huellas_remotas(rels):
    """{ruta: sha256} de los que existen en el servidor."""
    if not rels:
        return {}

    lista = ('\n'.join(rels) + '\n').encode('utf-8')
    r = ssh('cd %s && xargs -d "\\n" sha256sum 2>/dev/null' % c.RAIZ_REMOTA, lista)
    res = {}

    for linea in r.stdout.decode('utf-8', 'replace').splitlines():
        h, _, ruta = linea.partition('  ')

        if len(h) == 64:
            res[ruta] = h

    return res


def comparar(locales):
    rels = sorted(locales)
    remotas = huellas_remotas(rels)
    nuevos = [r for r in rels if r not in remotas]
    distintos = [r for r in rels if r in remotas and remotas[r] != c.sha256(locales[r])]

    return nuevos, distintos


def portada_ok():
    try:
        p = urllib.request.Request(c.URL + '/', headers={'User-Agent': 'hmb-desplegar'})

        with urllib.request.urlopen(p, timeout=60) as r:
            cuerpo = r.read(400000).decode('utf-8', 'replace')

            if r.status != 200:
                return False, 'HTTP %s' % r.status

        for mala in ('Fatal error', 'Parse error', 'Call to undefined', '500 - '):
            if mala in cuerpo:
                return False, 'la portada contiene "%s"' % mala

        return True, ''
    except Exception as e:
        return False, str(e)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--solo-mirar', action='store_true')
    ap.add_argument('--solo', default='', help='prefijo de ruta a limitar')
    a = ap.parse_args()

    locales = {r: p for r, p in c.ficheros_locales().items() if r.startswith(a.solo)}
    print('Locales: %d ficheros. Comparando con el servidor...' % len(locales))
    nuevos, distintos = comparar(locales)

    for r in nuevos:
        print('  + nuevo      ' + r)

    for r in distintos:
        print('  ~ distinto   ' + r)

    cambios = nuevos + distintos
    print('Nuevos: %d  Distintos: %d  Iguales: %d' % (len(nuevos), len(distintos),
                                                       len(locales) - len(cambios)))

    if a.solo_mirar or not cambios:
        if not cambios:
            print('Nada que subir.')
        return

    sello = time.strftime('%Y%m%d-%H%M%S')
    prep = '%s/tmp/hmb_staging_%s' % (c.RAIZ_REMOTA, sello)
    copia = '%s/tmp/hmb_backups/%s.tar.gz' % (c.RAIZ_REMOTA, sello)

    if distintos:
        lista = ('\n'.join(distintos) + '\n').encode('utf-8')
        # tmp/ cuelga de la web: la carpeta de copias se cierra siempre al acceso publico.
        r = ssh('cd %s && mkdir -p tmp/hmb_backups && printf "Require all denied\\nDeny from all\\n" '
                '> tmp/hmb_backups/.htaccess && tar czf tmp/hmb_backups/%s.tar.gz -T -'
                % (c.RAIZ_REMOTA, sello), lista)

        if r.returncode != 0:
            sys.exit('Copia previa FALLIDA, no se sube nada: ' + r.stderr.decode('utf-8', 'replace'))

        print('Copia previa: ' + copia)

    buf = io.BytesIO()

    with tarfile.open(fileobj=buf, mode='w:gz') as t:
        for r in cambios:
            t.add(locales[r], arcname=r)

    r = ssh('mkdir -p %s && tar xzf - -C %s' % (prep, prep), buf.getvalue())

    if r.returncode != 0:
        sys.exit('Subida FALLIDA (nada pisado): ' + r.stderr.decode('utf-8', 'replace'))

    orden = ' && '.join(
        'mkdir -p "$(dirname "%s/%s")" && mv -f "%s/%s" "%s/%s"'
        % (c.RAIZ_REMOTA, f, prep, f, c.RAIZ_REMOTA, f) for f in cambios)
    r = ssh(orden + ' && rm -rf ' + prep)

    if r.returncode != 0:
        sys.exit('Movimiento FALLIDO: ' + r.stderr.decode('utf-8', 'replace') +
                 '\nRestaurar: ssh ... "cd %s && tar xzf %s"' % (c.RAIZ_REMOTA, copia))

    remotas = huellas_remotas(cambios)
    mal = [f for f in cambios if remotas.get(f) != c.sha256(locales[f])]

    if mal:
        print('NO CUADRAN tras subir:\n  ' + '\n  '.join(mal))
        sys.exit(1)

    ok, motivo = portada_ok()

    if not ok:
        print('ATENCION: la portada no esta bien (%s). Para volver atras: '
              'cd %s && tar xzf %s' % (motivo, c.RAIZ_REMOTA, copia))
        sys.exit(1)

    print('Subidos y verificados: %d ficheros. Portada OK.' % len(cambios))


if __name__ == '__main__':
    main()
