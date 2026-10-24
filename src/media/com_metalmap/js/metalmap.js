/**
 * com_metalmap — Frontend JS v2.1
 * Marcadores verdes (futuros) / rojos (pasados)
 * Clusters: verde, rojo o AZUL si son mixtos
 * Botón "Filtrar" para confirmar el rango de fechas
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const cfg = (window.Joomla && Joomla.getOptions) ? Joomla.getOptions('com_metalmap') : null;
        if (!cfg || !window.L) {
            console.warn('[metalmap] Sin configuración o Leaflet no cargado.');
            return;
        }

        const events  = Array.isArray(cfg.events) ? cfg.events : [];
        const labels  = cfg.labels || {};
        const mapEl   = document.getElementById('mm-map');
        const listEl  = document.getElementById('mm-list-body');
        const countEl = document.getElementById('mm-list-count');
        const searchEl   = document.getElementById('mm-search');
        const countryEl  = document.getElementById('mm-country');
        const dateFromEl = document.getElementById('mm-date-from');
        const dateToEl   = document.getElementById('mm-date-to');
        const dateApplyEl = document.getElementById('mm-date-apply');
        const dateClearEl = document.getElementById('mm-date-clear');
        const typePillEls = document.querySelectorAll('[data-filter]');
        const timePillEls = document.querySelectorAll('[data-time-filter]');

        if (!mapEl) return;

        const TODAY = new Date();
        TODAY.setHours(0, 0, 0, 0);

        const parseDate = (str) => {
            if (!str || str === '0000-00-00') return null;
            const d = new Date(str + 'T00:00:00');
            return isNaN(d) ? null : d;
        };
        const isPast = (ev) => {
            const ref = parseDate(ev.end_date) || parseDate(ev.start_date);
            return ref ? ref < TODAY : false;
        };
        const flagUrl = (cc) => cc ? `https://flagcdn.com/w80/${cc.toLowerCase()}.png` : '';
        const fmtDate = (d) => {
            const dt = parseDate(d);
            if (!dt) return '';
            return dt.toLocaleDateString(document.documentElement.lang || 'es-ES', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
        };
        const dateRange = (ev) => {
            const a = fmtDate(ev.start_date), b = fmtDate(ev.end_date);
            if (a && b && a !== b) return `${a} → ${b}`;
            return a || b || '';
        };
        const escapeHtml = (s) => {
            if (s == null) return '';
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        };

        /* ── MAPA ───────────────────────────────────────────────── */
        const map = L.map(mapEl, {
            center: [cfg.default_lat, cfg.default_lng],
            zoom: cfg.default_zoom,
            minZoom: 3,
            maxZoom: 18,
            worldCopyJump: true,
            zoomControl: true,
            attributionControl: true
        });

        const tileProviders = {
            // CARTO y Stadia piden ahora clave de API (sin ella pintan una marca de agua "API KEY REQUIRED").
            // Para no depender de ninguna clave, el modo oscuro usa teselas de OpenStreetMap oscurecidas por CSS.
            cartodb_dark: {
                url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                attr: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                sub: 'abc', max: 19, cls: 'mm-tiles-dark'
            },
            osm: {
                // OSM tiles muestran nombres en idioma local
                url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                attr: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                sub: 'abc', max: 19
            },
            stadia_dark: {
                url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                attr: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                sub: 'abc', max: 19, cls: 'mm-tiles-dark'
            }
        };
        const provider = tileProviders[cfg.tile_provider] || tileProviders.cartodb_dark;
        L.tileLayer(provider.url, {
            attribution: provider.attr,
            subdomains: provider.sub,
            maxZoom: provider.max,
            className: provider.cls || ''
        }).addTo(map);

        /* ── CLUSTER CON COLORES INTELIGENTES ───────────────────── */
        const cluster = L.markerClusterGroup({
            showCoverageOnHover: false,
            spiderfyOnMaxZoom: true,
            maxClusterRadius: 50,
            iconCreateFunction: function (cluster) {
                const childMarkers = cluster.getAllChildMarkers();
                let pastCount = 0;
                let upcomingCount = 0;
                childMarkers.forEach(m => {
                    if (m.options.isPast) pastCount++;
                    else upcomingCount++;
                });

                let kind = 'mixed';
                if (pastCount === 0)      kind = 'upcoming';
                else if (upcomingCount === 0) kind = 'past';

                const total = childMarkers.length;
                let size = 40;
                if (total >= 100) size = 56;
                else if (total >= 20) size = 48;

                return new L.DivIcon({
                    html: `<div class="mm-cluster mm-cluster--${kind}"><span>${total}</span></div>`,
                    className: 'mm-cluster-wrap',
                    iconSize: new L.Point(size, size)
                });
            }
        });

        /* ── POPUP ──────────────────────────────────────────────── */
        const buildPopup = (ev) => {
            const past = isPast(ev);
            const flag = ev.country_code
                ? `<img class="mm-popup__flag" src="${flagUrl(ev.country_code)}" alt="${escapeHtml(ev.country)}" onerror="this.style.display='none'">`
                : '';
            const dates = dateRange(ev);
            const where = [ev.city, ev.country].filter(Boolean).map(escapeHtml).join(', ');
            const desc  = ev.description ? `<div class="mm-popup__desc">${escapeHtml(ev.description.substring(0, 200))}${ev.description.length > 200 ? '…' : ''}</div>` : '';

            // ─── Bandas: usar bands_list si existe (con enlaces a com_efemerides) ───
            let bandsHtml = '';
            if (Array.isArray(ev.bands_list) && ev.bands_list.length) {
                // Filtramos solo las bandas que SÍ tienen ficha para navegación del modal
                const linkable = ev.bands_list.filter(b => b.url);
                const linkableUrls = JSON.stringify(linkable.map(b => ({name:b.name, url:b.url, type:b.type})));
                // Codificamos en base64 para no romper el atributo HTML con comillas/escapes
                const dataBlob = btoa(unescape(encodeURIComponent(linkableUrls)));

                const items = ev.bands_list.map(b => {
                    if (b.url) {
                        const icon = b.type === 'banda' ? '🎸' : '🎤';
                        // data-mm-band-index: posición en la lista de linkables (para prev/next)
                        const idxInLinkable = linkable.findIndex(x => x.url === b.url);
                        return `<a href="${escapeHtml(b.url)}" `
                             + `class="mm-popup__band-link" `
                             + `data-mm-modal="1" `
                             + `data-mm-band-list="${dataBlob}" `
                             + `data-mm-band-index="${idxInLinkable}" `
                             + `title="Ver ficha en Efemérides">${icon} ${escapeHtml(b.name)}</a>`;
                    }
                    return `<span class="mm-popup__band-plain">${escapeHtml(b.name)}</span>`;
                }).join(', ');
                bandsHtml = `<div class="mm-popup__row"><strong>${escapeHtml(labels.bands || 'Bandas')}:</strong><span>${items}</span></div>`;
            } else if (ev.bands) {
                bandsHtml = `<div class="mm-popup__row"><strong>${escapeHtml(labels.bands || 'Bandas')}:</strong><span>${escapeHtml(ev.bands)}</span></div>`;
            }

            const site  = ev.website ? `<a class="mm-popup__cta" href="${escapeHtml(ev.website)}" target="_blank" rel="noopener noreferrer">⚡ ${escapeHtml(labels.website || 'Web oficial')}</a>` : '';
            const status = past
                ? '<span class="mm-popup__type" style="color:#ff8080;border-color:#8b0000">⏱ Finalizado</span>'
                : '<span class="mm-popup__type" style="color:#7eff7e;border-color:#1b5e20">▶ Próximo</span>';
            const typeIcon = ev.type === 'concert' ? '🎸' : '🎪';

            return `
                <div class="mm-popup">
                    <div class="mm-popup__head">
                        ${flag}
                        <h3 class="mm-popup__title">${escapeHtml(ev.name)}</h3>
                        <span class="mm-popup__type">${typeIcon} ${ev.type === 'concert' ? 'Concierto' : 'Festival'}</span>
                        ${status}
                    </div>
                    <div class="mm-popup__body">
                        ${dates ? `<div class="mm-popup__row"><strong>${escapeHtml(labels.when || 'Cuándo')}:</strong><span>${escapeHtml(dates)}</span></div>` : ''}
                        ${where ? `<div class="mm-popup__row"><strong>${escapeHtml(labels.where || 'Dónde')}:</strong><span>${where}</span></div>` : ''}
                        ${bandsHtml}
                        ${desc}
                        ${site}
                    </div>
                </div>`;
        };

        const makeIcon = (ev) => {
            const past = isPast(ev);
            const cls  = past ? 'mm-marker--past' : 'mm-marker--upcoming';
            const icon = ev.type === 'concert' ? '🎸' : '🎪';
            return L.divIcon({
                className: '',
                html: `<div class="mm-marker ${cls}">${icon}</div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15],
                popupAnchor: [0, -16]
            });
        };

        /* ── MARKERS ────────────────────────────────────────────── */
        const markers = [];
        events.forEach(ev => {
            if (!ev.lat || !ev.lng) return;
            const past = isPast(ev);
            const mk = L.marker([ev.lat, ev.lng], {
                icon: makeIcon(ev),
                title: ev.name,
                isPast: past   // ← lo guardamos para que el cluster lo lea
            });
            mk.bindPopup(buildPopup(ev), { maxWidth: 320, closeButton: true });
            markers.push({ marker: mk, ev });
        });

        markers.forEach(m => cluster.addLayer(m.marker));
        map.addLayer(cluster);

        if (markers.length) {
            try {
                const group = L.featureGroup(markers.map(m => m.marker));
                map.fitBounds(group.getBounds().pad(0.12), { maxZoom: 6 });
            } catch (_) {}
        }

        /* ── LISTA LATERAL ──────────────────────────────────────── */
        const renderList = (items) => {
            if (countEl) countEl.textContent = items.length;
            if (!listEl) return;

            if (!items.length) {
                listEl.innerHTML = `<div class="mm-empty">${escapeHtml(labels.no_events || 'Sin resultados')}</div>`;
                return;
            }

            listEl.innerHTML = items.map(ev => {
                const past = isPast(ev);
                const flag = ev.country_code
                    ? `<img class="mm-item__flag" src="${flagUrl(ev.country_code)}" alt="${escapeHtml(ev.country)}" onerror="this.outerHTML='<div class=\\'mm-item__flag mm-item__flag--ph\\'>—</div>'">`
                    : `<div class="mm-item__flag mm-item__flag--ph">—</div>`;
                return `
                    <div class="mm-item ${past ? 'mm-item--past' : ''}" data-id="${ev.id}">
                        ${flag}
                        <div class="mm-item__body">
                            <div class="mm-item__name">${escapeHtml(ev.name)}</div>
                            <div class="mm-item__meta">
                                <span class="mm-item__date">${escapeHtml(dateRange(ev) || '—')}</span>
                                <span>·</span>
                                <span>${escapeHtml([ev.city, ev.country].filter(Boolean).join(', ') || '—')}</span>
                            </div>
                        </div>
                    </div>`;
            }).join('');

            listEl.querySelectorAll('.mm-item').forEach(el => {
                el.addEventListener('click', () => {
                    const id = parseInt(el.dataset.id, 10);
                    const m = markers.find(x => x.ev.id === id);
                    if (m) {
                        map.setView([m.ev.lat, m.ev.lng], Math.max(map.getZoom(), 8), { animate: true });
                        setTimeout(() => m.marker.openPopup(), 350);
                    }
                });
            });
        };

        /* ── FILTROS ────────────────────────────────────────────── */
        let currentType    = 'all';
        let currentCountry = '';
        let currentSearch  = '';
        let currentTime    = 'all';
        let dateFrom       = null;
        let dateTo         = null;

        const applyFilters = () => {
            cluster.clearLayers();
            const visible = [];
            markers.forEach(({ marker, ev }) => {
                let ok = true;
                if (currentType !== 'all' && ev.type !== currentType) ok = false;
                if (ok && currentCountry && (ev.country_code || '') !== currentCountry) ok = false;
                if (ok && currentSearch) {
                    const hay = (ev.name + ' ' + (ev.city || '') + ' ' + (ev.country || '') + ' ' + (ev.bands || '')).toLowerCase();
                    if (!hay.includes(currentSearch)) ok = false;
                }
                if (ok && currentTime !== 'all') {
                    const past = isPast(ev);
                    if (currentTime === 'upcoming' && past) ok = false;
                    if (currentTime === 'past' && !past)   ok = false;
                }
                if (ok && (dateFrom || dateTo)) {
                    const evStart = parseDate(ev.start_date);
                    const evEnd   = parseDate(ev.end_date) || evStart;
                    if (!evStart) {
                        ok = false;
                    } else {
                        if (dateFrom && evEnd && evEnd < dateFrom) ok = false;
                        if (ok && dateTo && evStart > dateTo)     ok = false;
                    }
                }
                if (ok) {
                    cluster.addLayer(marker);
                    visible.push(ev);
                }
            });
            renderList(visible);
        };

        /* ── EVENTOS ───────────────────────────────────────────── */
        typePillEls.forEach(p => {
            p.addEventListener('click', () => {
                typePillEls.forEach(x => x.classList.remove('mm-pill--active'));
                p.classList.add('mm-pill--active');
                currentType = p.dataset.filter;
                applyFilters();
            });
        });

        timePillEls.forEach(p => {
            p.addEventListener('click', () => {
                const requested = p.dataset.timeFilter;
                if (p.classList.contains('mm-pill--active')) {
                    p.classList.remove('mm-pill--active');
                    currentTime = 'all';
                } else {
                    timePillEls.forEach(x => x.classList.remove('mm-pill--active'));
                    p.classList.add('mm-pill--active');
                    currentTime = requested;
                }
                applyFilters();
            });
        });

        if (countryEl) {
            countryEl.addEventListener('change', e => {
                currentCountry = e.target.value;
                applyFilters();
            });
        }

        if (searchEl) {
            let debounce;
            searchEl.addEventListener('input', e => {
                clearTimeout(debounce);
                debounce = setTimeout(() => {
                    currentSearch = e.target.value.trim().toLowerCase();
                    applyFilters();
                }, 200);
            });
        }

        // El filtro por fechas se aplica SOLO cuando el usuario pulsa el botón "Filtrar"
        if (dateApplyEl) {
            dateApplyEl.addEventListener('click', () => {
                dateFrom = (dateFromEl && dateFromEl.value) ? parseDate(dateFromEl.value) : null;
                dateTo   = (dateToEl   && dateToEl.value)   ? parseDate(dateToEl.value)   : null;
                applyFilters();
            });
        }
        if (dateClearEl) {
            dateClearEl.addEventListener('click', () => {
                if (dateFromEl) dateFromEl.value = '';
                if (dateToEl)   dateToEl.value   = '';
                dateFrom = null;
                dateTo   = null;
                applyFilters();
            });
        }

        renderList(events);

        /* ─────────────────────────────────────────────────────────
           MODAL DE FICHAS (com_efemerides) — iframe overlay
           ───────────────────────────────────────────────────────── */
        const modal       = document.getElementById('mm-modal');
        const modalFrame  = document.getElementById('mm-modal-frame');
        const modalTitle  = document.getElementById('mm-modal-title');
        const modalLoader = document.getElementById('mm-modal-loader');
        const modalPrev   = document.getElementById('mm-modal-prev');
        const modalNext   = document.getElementById('mm-modal-next');
        const modalCount  = document.getElementById('mm-modal-counter');

        // Estado: lista actual de fichas navegables + índice
        let mmList  = [];
        let mmIndex = 0;

        // ─── ENLACES DE BANDAS EN POPUPS → ABREN MODAL ───
        // Cargamos la URL TAL CUAL (sin tmpl=component, que rompe la ruta de
        // com_efemerides). Para ocultar la cabecera/footer del sitio, inyectamos
        // CSS dentro del iframe cuando carga (es el mismo dominio, así que se
        // permite acceder a su DOM).
        const stripChromeCss = `
            /* Ocultar chrome del sitio en el iframe del modal */
            header, .site-header, #header, .header, .page-header,
            footer, .site-footer, #footer, .footer, .page-footer,
            nav, .navigation, #navigation, .nav,
            .navbar, #navbar,
            .top-bar, .topbar, .top-nav, .topnav,
            .main-menu, .mainmenu, #mainmenu,
            .main-nav, .main-navigation, .site-nav, .site-menu,
            .menu, #menu, .menu-horizontal, .menu-vertical,
            .mod-menu, .module_menu, .menu-mainmenu, .menu-main,
            .user-menu, .top-menu, .header-menu, .footer-menu,
            .nav-menu, .navbar-nav, .dropdown-menu, .submenu,
            .t3-mainnav, .t-header, .t3-header,
            .sp-header, .sp-main-menu, .sp-megamenu-parent,
            .helix-ultimate-menu, .hu-header,
            #sp-header, #sp-menu, #sp-top-bar, #sp-top1, #sp-top2,
            #sp-bottom, #sp-footer,
            .breadcrumb, #breadcrumbs, .breadcrumbs,
            aside, .sidebar, #sidebar, .left-sidebar, .right-sidebar,
            .cookie-banner, #cookie-banner, .cookies, #cookies,
            .skip-link, .skip-to-content,
            .social-icons, .social-share, .share-bar,
            .back-to-top, #back-to-top, .scroll-to-top
            {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
            }
            /* Botones / módulos flotantes (Acceso, login, etc.) */
            [class*="floating"], [class*="float-btn"], [id*="floating"],
            [class*="login-btn"], [class*="acceso"], [id*="acceso"],
            [class*="register"], [id*="register"]
            {
                display: none !important;
            }
            /* Reset de espacios */
            html, body, main, #content, #main-content, .main-content,
            .container, .container-fluid, .site-content, .page-content,
            #sp-component, #sp-main-body, .body-innerwrapper {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                background: #fff !important;
            }
            body { overflow-x: hidden !important; }
        `;

        const injectStripChromeInIframe = () => {
            if (!modalFrame) return;
            try {
                const doc = modalFrame.contentDocument || modalFrame.contentWindow.document;
                if (!doc || !doc.body) return;

                // 1) Inyectar CSS amplio (sin duplicar)
                if (!doc.getElementById('mm-strip-chrome')) {
                    const style = doc.createElement('style');
                    style.id = 'mm-strip-chrome';
                    style.textContent = stripChromeCss;
                    doc.head.appendChild(style);
                }

                // 2) Estrategia agresiva: localizar el contenedor del componente
                //    y ocultar TODOS sus hermanos en cada nivel de ancestros.
                //    Así da igual cómo se llame el menú, la franja o el botón
                //    "Acceso" — si no son ancestros del contenido del componente,
                //    desaparecen.
                const candidates = [
                    '#sp-component', '#component', '.com-content',
                    '.component-content', '.componentheading',
                    'main[role="main"]', 'main', '#main', '#main-content',
                    '.main-content', '.com_efemerides',
                    'article'
                ];
                let componentEl = null;
                for (const sel of candidates) {
                    componentEl = doc.querySelector(sel);
                    if (componentEl) break;
                }

                if (componentEl) {
                    // Subir por los ancestros hasta <body>, ocultando hermanos
                    let current = componentEl;
                    let safety = 30;
                    while (current && current !== doc.body && safety-- > 0) {
                        const parent = current.parentElement;
                        if (!parent) break;
                        for (const sibling of Array.from(parent.children)) {
                            if (sibling !== current
                                && sibling.tagName !== 'SCRIPT'
                                && sibling.tagName !== 'STYLE'
                                && sibling.id !== 'mm-strip-chrome') {
                                sibling.style.setProperty('display', 'none', 'important');
                            }
                        }
                        current = parent;
                    }
                }

                // 3) Eliminar cualquier elemento posicionado fixed/absolute
                //    fuera del flujo (botones flotantes tipo "Acceso")
                const allFixed = doc.querySelectorAll('*');
                allFixed.forEach(el => {
                    if (el === componentEl) return;
                    if (componentEl && componentEl.contains(el)) return;
                    const pos = (doc.defaultView || window).getComputedStyle(el).position;
                    if (pos === 'fixed' || (pos === 'absolute' && !el.closest('#mm-strip-chrome'))) {
                        // Solo si está fuera del flujo del componente
                        if (!componentEl || !componentEl.contains(el)) {
                            el.style.setProperty('display', 'none', 'important');
                        }
                    }
                });
            } catch (err) {
                console.warn('[metalmap] No se pudo limpiar iframe:', err);
            }
        };

        const renderModalForIndex = (idx) => {
            if (!mmList.length) return;
            mmIndex = Math.max(0, Math.min(idx, mmList.length - 1));
            const item = mmList[mmIndex];

            const icon = item.type === 'banda' ? '🎸' : '🎤';
            modalTitle.textContent = `${icon} ${item.name}`;
            modalCount.textContent = mmList.length > 1 ? `${mmIndex + 1} / ${mmList.length}` : '';

            modalLoader.hidden = false;
            modalFrame.style.opacity = '0';
            // URL TAL CUAL la generó el servidor (sin tmpl=component)
            modalFrame.src = item.url;

            modalPrev.disabled = (mmIndex === 0);
            modalNext.disabled = (mmIndex === mmList.length - 1);
        };

        const openModal = (list, startIdx) => {
            if (!modal || !Array.isArray(list) || !list.length) return;
            mmList  = list;
            mmIndex = startIdx || 0;
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            renderModalForIndex(mmIndex);
        };

        const closeModal = () => {
            if (!modal) return;
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            modalFrame.src = 'about:blank';
            document.body.style.overflow = '';
            mmList  = [];
            mmIndex = 0;
        };

        // Cuando el iframe termina de cargar:
        //  1. ocultar el loader
        //  2. inyectar CSS para ocultar el chrome del sitio (header/footer/nav)
        if (modalFrame) {
            modalFrame.addEventListener('load', () => {
                injectStripChromeInIframe();
                modalLoader.hidden = true;
                modalFrame.style.opacity = '1';
            });
        }

        // Botones de navegación
        if (modalPrev) modalPrev.addEventListener('click', () => renderModalForIndex(mmIndex - 1));
        if (modalNext) modalNext.addEventListener('click', () => renderModalForIndex(mmIndex + 1));

        // Cerrar: click en backdrop, en botón de cierre, o tecla Esc
        if (modal) {
            modal.querySelectorAll('[data-mm-close]').forEach(el => {
                el.addEventListener('click', closeModal);
            });
        }
        document.addEventListener('keydown', (e) => {
            if (modal && !modal.hidden) {
                if (e.key === 'Escape') closeModal();
                else if (e.key === 'ArrowLeft' && !modalPrev.disabled) renderModalForIndex(mmIndex - 1);
                else if (e.key === 'ArrowRight' && !modalNext.disabled) renderModalForIndex(mmIndex + 1);
            }
        });

        // ─── Interceptar clicks sobre los enlaces de bandas en los popups ───
        // Usamos delegación en document porque los popups de Leaflet se crean al vuelo
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[data-mm-modal="1"]');
            if (!link) return;
            e.preventDefault();
            e.stopPropagation();

            // Recuperar la lista de bandas-con-ficha de ese popup
            const blob = link.getAttribute('data-mm-band-list');
            const idx  = parseInt(link.getAttribute('data-mm-band-index'), 10) || 0;
            if (!blob) {
                // Fallback: abrir solo esa
                openModal([{name: link.textContent.trim(), url: link.href, type: 'banda'}], 0);
                return;
            }
            try {
                const list = JSON.parse(decodeURIComponent(escape(atob(blob))));
                openModal(list, idx);
            } catch (err) {
                console.warn('[metalmap] No se pudo parsear band-list:', err);
                openModal([{name: link.textContent.trim(), url: link.href, type: 'banda'}], 0);
            }
        });
    });
})();
