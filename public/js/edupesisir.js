'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const submitBackups = new Map();

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Hapus data ini?')) event.preventDefault();
        });
    });

    document.querySelectorAll('form[data-loading]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented || !form.checkValidity()) return;
            const button = form.querySelector('[type="submit"]');
            if (!button) return;
            submitBackups.set(button, Array.from(button.childNodes).map((node) => node.cloneNode(true)));
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm';
            spinner.setAttribute('aria-hidden', 'true');
            const label = document.createElement('span');
            label.textContent = form.dataset.loading;
            button.replaceChildren(spinner, label);
        });
    });

    window.addEventListener('pageshow', () => {
        submitBackups.forEach((content, button) => {
            button.replaceChildren(...content);
            button.disabled = false;
            button.removeAttribute('aria-busy');
        });
        submitBackups.clear();
    });

    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        let previewUrl;
        const target = document.querySelector(input.dataset.preview);
        const initialSource = target?.src;
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            if (!target) return;
            if (!file || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                target.src = initialSource;
                return;
            }
            previewUrl = URL.createObjectURL(file);
            target.src = previewUrl;
            target.classList.remove('d-none');
            target.hidden = false;
            target.alt = 'Pratinjau foto pilihan';
        });
    });

    const themeScript = Array.from(document.scripts).find((script) => script.src.includes('/js/edupesisir.js'));
    const fallbackImage = themeScript ? new URL('../images/place-placeholder.svg', themeScript.src).href : '/images/place-placeholder.svg';
    document.querySelectorAll('img').forEach((img) => {
        const applyFallback = () => {
            if (img.dataset.fallbackApplied) return;
            img.dataset.fallbackApplied = 'true';
            img.src = fallbackImage;
        };
        img.addEventListener('error', applyFallback);
        if (img.complete && img.naturalWidth === 0 && img.getAttribute('src')) applyFallback();
    });

    const recommendationForm = document.querySelector('[data-recommendation-form]');
    if (recommendationForm) {
        const level = recommendationForm.querySelector('[name="id_jenjang"]');
        const dependents = recommendationForm.querySelectorAll('[data-level-dependent]');
        const updateOptions = () => {
            let ready = !!level.value;
            dependents.forEach((select) => {
                let optionsAvailable = 0;
                Array.from(select.options).forEach((option) => {
                    if (!option.value) return;
                    const available = option.dataset.level === level.value;
                    option.hidden = !available;
                    option.disabled = !available;
                    if (available) optionsAvailable += 1;
                    if (!available && option.selected) select.value = '';
                });
                select.disabled = !level.value || optionsAvailable === 0;
                ready = ready && optionsAvailable > 0;
            });
            const submit = recommendationForm.querySelector('[type="submit"]');
            if (submit) submit.disabled = !ready;
            const help = document.getElementById('subject-help');
            if (help) {
                help.setAttribute('aria-live', 'polite');
                help.textContent = !level.value ? 'Pilih jenjang terlebih dahulu untuk melihat mata pelajaran dan sekolah.' : !ready ? 'Mata pelajaran atau sekolah untuk jenjang ini belum tersedia. Silakan pilih jenjang lain atau hubungi admin.' : 'Mata pelajaran dan sekolah telah disesuaikan dengan jenjang pendidikanmu.';
            }
        };
        level.addEventListener('change', updateOptions);
        updateOptions();

        const locateButton = recommendationForm.querySelector('[data-geolocate]');
        const resetButton = recommendationForm.querySelector('[data-geo-reset]');
        const status = recommendationForm.querySelector('[data-geo-status]');
        const useLocation = recommendationForm.querySelector('[name="use_location"]');
        const latitude = recommendationForm.querySelector('[name="latitude"]');
        const longitude = recommendationForm.querySelector('[name="longitude"]');
        let locationRequest = 0;
        const resetLocation = (message = 'Lokasi asal saat ini: koordinat sekolah yang dipilih.') => {
            locationRequest += 1;
            useLocation.value = '0';
            latitude.value = '';
            longitude.value = '';
            status.textContent = message;
            resetButton.classList.add('d-none');
            locateButton.disabled = false;
        };
        resetButton.addEventListener('click', () => resetLocation());
        if (useLocation.value === '1' && latitude.value && longitude.value) {
            status.textContent = 'Lokasi perangkat dipilih sebagai titik asal. Koordinat akan tersimpan bersama hasil perhitungan.';
            resetButton.classList.remove('d-none');
        } else {
            resetLocation();
        }
        locateButton.addEventListener('click', () => {
            resetLocation();
            if (!navigator.geolocation) {
                resetLocation('Browser ini tidak mendukung lokasi perangkat. Rekomendasi tetap menggunakan koordinat sekolah.');
                return;
            }
            const requestId = locationRequest;
            locateButton.disabled = true;
            status.textContent = 'Meminta izin lokasi perangkat. Kamu tetap dapat memakai koordinat sekolah.';
            navigator.geolocation.getCurrentPosition((position) => {
                if (requestId !== locationRequest) return;
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) {
                    resetLocation('Koordinat perangkat tidak valid. Koordinat sekolah akan digunakan.');
                    return;
                }
                latitude.value = String(lat);
                longitude.value = String(lng);
                useLocation.value = '1';
                status.textContent = `Lokasi perangkat berhasil digunakan (akurasi sekitar ${Math.round(position.coords.accuracy)} m). Koordinat tersimpan bersama hasil perhitungan.`;
                locateButton.disabled = false;
                resetButton.classList.remove('d-none');
            }, (error) => {
                if (requestId !== locationRequest) return;
                resetLocation(error.code === 1 ? 'Izin lokasi ditolak. Tidak masalah, rekomendasi tetap memakai koordinat sekolah yang dipilih.' : 'Lokasi perangkat belum dapat diperoleh. Rekomendasi tetap memakai koordinat sekolah yang dipilih.');
            }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 });
        });
        recommendationForm.addEventListener('submit', () => { locationRequest += 1; });
    }

    const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
    const validCoordinates = (item) => item && item.latitude !== null && item.longitude !== null && item.latitude !== '' && item.longitude !== '' && Number.isFinite(Number(item.latitude)) && Number.isFinite(Number(item.longitude)) && Math.abs(Number(item.latitude)) <= 90 && Math.abs(Number(item.longitude)) <= 180;

    document.querySelectorAll('[data-map]').forEach((element) => {
        if (!window.L) {
            element.textContent = 'Peta belum dapat dimuat. Detail koordinat tetap tersedia pada halaman ini.';
            element.classList.add('map-placeholder');
            return;
        }
        let config;
        try {
            config = JSON.parse(document.getElementById(element.dataset.mapConfig).textContent);
        } catch (_) {
            element.textContent = 'Data peta belum tersedia.';
            return;
        }
        element.replaceChildren();
        const map = L.map(element, { scrollWheelZoom: false }).setView([-3.8004, 102.2655], 12);
        const bounds = [];
        const markers = new Map();
        const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom: 19 }).addTo(map);
        const status = document.createElement('p');
        status.className = 'map-caption d-none';
        status.setAttribute('role', 'status');
        element.insertAdjacentElement('afterend', status);
        tiles.on('tileerror', () => {
            status.textContent = 'Latar peta belum termuat. Periksa koneksi internet; penanda dan koordinat tetap tersedia.';
            status.classList.remove('d-none');
        });
        const addMarker = (place, origin = false) => {
            if (!validCoordinates(place)) return;
            const coordinates = [Number(place.latitude), Number(place.longitude)];
            bounds.push(coordinates);
            const markerIcon = document.createElement('i');
            markerIcon.className = origin ? 'bi bi-compass-fill' : 'bi bi-book-fill';
            markerIcon.setAttribute('aria-hidden', 'true');
            const markerRank = document.createElement('span');
            markerRank.textContent = origin ? '' : String(place.rank || '');
            const markerLabel = document.createElement('span');
            markerLabel.className = 'map-marker-content';
            markerLabel.append(markerIcon, markerRank);
            const icon = L.divIcon({ className: `map-marker${origin ? ' origin' : Number(place.rank) === 1 ? ' top' : ''}`, html: markerLabel.outerHTML, iconSize: [32, 32], iconAnchor: [16, 16] });
            const popup = document.createElement('div');
            const heading = document.createElement('strong');
            heading.textContent = origin ? `Titik asal: ${place.label || 'Sekolah'}` : place.nama;
            popup.appendChild(heading);
            if (!origin && place.rank) {
                const detail = document.createElement('span');
                detail.textContent = `Ranking #${place.rank} · ${numberFormat.format(place.jarak)} km · SAW ${Number(place.score).toFixed(4)}`;
                popup.appendChild(detail);
            }
            if (!origin && place.url) {
                try {
                    const targetUrl = new URL(place.url, window.location.origin);
                    if (targetUrl.origin === window.location.origin) {
                        const link = document.createElement('a');
                        link.href = targetUrl.href;
                        link.textContent = 'Lihat detail tempat →';
                        popup.appendChild(link);
                    }
                } catch (_) { /* Ignore malformed archived URLs. */ }
            }
            const marker = L.marker(coordinates, { icon, title: origin ? place.label || 'Titik asal' : place.nama }).addTo(map).bindPopup(popup);
            if (!origin) markers.set(String(place.id), marker);
        };
        (config.places || []).forEach((place) => addMarker(place));
        addMarker(config.origin, true);
        if (bounds.length) map.fitBounds(bounds, { padding: [35, 35], maxZoom: bounds.length === 1 ? 15 : 14 });
        element.eduMap = map;
        element.eduMarkers = markers;
        if (window.ResizeObserver) new ResizeObserver(() => map.invalidateSize()).observe(element);
    });

    document.querySelectorAll('[data-map-focus]').forEach((link) => {
        link.addEventListener('click', () => {
            const target = document.getElementById(link.dataset.mapTarget);
            const marker = target?.eduMarkers?.get(String(link.dataset.mapFocus));
            if (!marker) return;
            target.eduMap.setView(marker.getLatLng(), 15);
            marker.openPopup();
        });
    });
});
