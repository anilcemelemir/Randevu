/**
 * Mobil öncelikli, bağımlılıksız tarih seçici (takvim).
 *
 * Randevu arama formundaki gizli <input type="date"> alanını görsel bir
 * takvimle zenginleştirir. Bir gün seçildiğinde tarih alanına yazılır ve
 * form gönderilerek o güne ait uygun saatler yüklenir. JavaScript devre
 * dışıysa yerel tarih alanı yedek olarak çalışmaya devam eder.
 */
(function () {
    'use strict';

    var MONTHS = [
        'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
        'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
    ];
    // Pazartesi ile başlayan hafta
    var WEEKDAYS = ['Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct', 'Pz'];

    function pad(n) {
        return (n < 10 ? '0' : '') + n;
    }

    // m 0 tabanlı (0 = Ocak) → "YYYY-MM-DD"
    function toKey(y, m, d) {
        return y + '-' + pad(m + 1) + '-' + pad(d);
    }

    function parseKey(str) {
        if (!str) {
            return null;
        }
        var parts = String(str).split('-');
        if (parts.length !== 3) {
            return null;
        }
        var y = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10);
        var d = parseInt(parts[2], 10);
        if (!y || !m || !d) {
            return null;
        }
        return { y: y, m: m - 1, d: d };
    }

    function initCalendar(root) {
        var input = document.getElementById(root.getAttribute('data-calendar-for'));
        if (!input) {
            return;
        }

        var now = new Date();
        var todayKey = toKey(now.getFullYear(), now.getMonth(), now.getDate());
        var minKey = input.getAttribute('min') || todayKey;

        var selected = parseKey(input.value) || parseKey(minKey) ||
            { y: now.getFullYear(), m: now.getMonth(), d: now.getDate() };
        var view = { y: selected.y, m: selected.m };

        // Zenginleştirme: yerel tarih alanını gizle, takvimi göster.
        // (Alan gizli kalsa da değeri form ile gönderilmeye devam eder.)
        input.hidden = true;
        input.setAttribute('aria-hidden', 'true');
        input.tabIndex = -1;
        root.hidden = false;

        function selectedKey() {
            return toKey(selected.y, selected.m, selected.d);
        }

        function onPick(ev) {
            var key = ev.currentTarget.getAttribute('data-key');
            if (!key) {
                return;
            }
            input.value = key;
            var parsed = parseKey(key);
            if (parsed) {
                selected = parsed;
            }
            var form = input.form;
            if (!form) {
                render();
                return;
            }
            // Zorunlu alanlar (ör. uzman seçimi) eksikse formu göndermeyip
            // seçimi ekranda göster; tarayıcı eksik alanı işaretlesin.
            if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                render();
                return;
            }
            form.submit();
        }

        function render() {
            root.innerHTML = '';

            var head = document.createElement('div');
            head.className = 'calendar-head';

            var prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'calendar-nav';
            prev.setAttribute('aria-label', 'Önceki ay');
            prev.textContent = '‹';

            var title = document.createElement('div');
            title.className = 'calendar-title';
            title.textContent = MONTHS[view.m] + ' ' + view.y;

            var next = document.createElement('button');
            next.type = 'button';
            next.className = 'calendar-nav';
            next.setAttribute('aria-label', 'Sonraki ay');
            next.textContent = '›';

            var minParsed = parseKey(minKey);
            var atMinMonth = minParsed &&
                (view.y < minParsed.y || (view.y === minParsed.y && view.m <= minParsed.m));
            if (atMinMonth) {
                prev.disabled = true;
            }

            prev.addEventListener('click', function () {
                view.m -= 1;
                if (view.m < 0) {
                    view.m = 11;
                    view.y -= 1;
                }
                render();
            });
            next.addEventListener('click', function () {
                view.m += 1;
                if (view.m > 11) {
                    view.m = 0;
                    view.y += 1;
                }
                render();
            });

            head.appendChild(prev);
            head.appendChild(title);
            head.appendChild(next);
            root.appendChild(head);

            var grid = document.createElement('div');
            grid.className = 'calendar-grid';

            var w;
            for (w = 0; w < 7; w++) {
                var weekday = document.createElement('span');
                weekday.className = 'calendar-weekday';
                weekday.textContent = WEEKDAYS[w];
                grid.appendChild(weekday);
            }

            // getDay(): 0 = Pazar ... 6 = Cumartesi. Pazartesi başlangıcına çevir.
            var firstDow = new Date(view.y, view.m, 1).getDay();
            var lead = (firstDow + 6) % 7;
            var b;
            for (b = 0; b < lead; b++) {
                var blank = document.createElement('span');
                blank.className = 'calendar-day is-blank';
                blank.setAttribute('aria-hidden', 'true');
                grid.appendChild(blank);
            }

            var daysInMonth = new Date(view.y, view.m + 1, 0).getDate();
            var chosen = selectedKey();
            var day;
            for (day = 1; day <= daysInMonth; day++) {
                var key = toKey(view.y, view.m, day);
                var cell = document.createElement('button');
                cell.type = 'button';
                cell.className = 'calendar-day';
                cell.textContent = String(day);
                cell.setAttribute('data-key', key);

                if (key < minKey) {
                    cell.disabled = true;
                    cell.classList.add('is-past');
                }
                if (key === todayKey) {
                    cell.classList.add('is-today');
                }
                if (key === chosen) {
                    cell.classList.add('is-selected');
                    cell.setAttribute('aria-current', 'date');
                }

                cell.addEventListener('click', onPick);
                grid.appendChild(cell);
            }

            root.appendChild(grid);
        }

        render();
    }

    function boot() {
        var nodes = document.querySelectorAll('[data-calendar]');
        var i;
        for (i = 0; i < nodes.length; i++) {
            initCalendar(nodes[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
