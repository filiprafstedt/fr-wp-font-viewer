(function () {

    function loadFont(file, ajaxUrl) {
        if (document.querySelector('style[data-font="' + file + '"]')) return;

        const family = 'fr-font-' + file.replace(/[^a-z0-9]/gi, '');
        const style = document.createElement('style');
        style.setAttribute('data-font', file);
        style.textContent =
            "@font-face{" +
            "font-family:'" + family + "';" +
            "src:url('" + ajaxUrl + "?action=wpfv_font&file=" + encodeURIComponent(file) + "') format('woff2');" +
            "font-display:swap;" +
            "}";

        document.head.appendChild(style);
    }

    const OT_FEATURES = [
        { tag: 'kern', label: 'Kerning' },
        { tag: 'liga', label: 'Standard Ligatures' },
        { tag: 'dlig', label: 'Discretionary Ligatures' },
        { tag: 'clig', label: 'Contextual Ligatures' },
        { tag: 'calt', label: 'Contextual Alternates' },
        { tag: 'smcp', label: 'Small Caps' },
        { tag: 'case', label: 'Case Sensitive Forms' },
        { tag: 'onum', label: 'Oldstyle Numerals' },
        { tag: 'lnum', label: 'Lining Numerals' },
        { tag: 'ss01', label: 'Stylistic Set 01' },
        { tag: 'ss02', label: 'Stylistic Set 02' }
    ];

    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.fr-font-viewer').forEach(function (viewer) {

            const stage   = viewer.querySelector('.fr-font-stage');
            const select  = viewer.querySelector('.fr-font-select');
            const ajaxUrl = viewer.dataset.ajaxUrl;
            const defaultFont = viewer.dataset.defaultFont;

            /* -------------------------
               Font loading
            -------------------------- */
            function applyFont(file) {
                loadFont(file, ajaxUrl);
                const family = 'fr-font-' + file.replace(/[^a-z0-9]/gi, '');
                stage.style.fontFamily = family;
            }

            applyFont(defaultFont);

            select.addEventListener('change', function () {
                applyFont(this.value);
            });

            /* -------------------------
               Alignment
            -------------------------- */
            viewer.querySelectorAll('.fr-alignment button').forEach(btn => {
                btn.addEventListener('click', () => {
                    stage.style.textAlign = btn.dataset.align;

                    viewer.querySelectorAll('.fr-alignment button')
                        .forEach(b => b.classList.toggle('is-active', b === btn));
                });
            });

            /* -------------------------
               Font size
            -------------------------- */
            viewer.querySelector('.fr-font-size')
                .addEventListener('input', e => {
                    stage.style.fontSize = e.target.value + 'px';
                });

            /* -------------------------
               Line height
            -------------------------- */
            viewer.querySelector('.fr-line-height')
                .addEventListener('input', e => {
                    stage.style.lineHeight = e.target.value;
                });

            /* -------------------------
               OpenType features
            -------------------------- */
            const otToggle = viewer.querySelector('.fr-ot-toggle');
            const otPanel  = viewer.querySelector('.fr-ot-panel');
            const activeOT = JSON.parse(viewer.dataset.otFeatures || '[]');

            otPanel.innerHTML = '';

            OT_FEATURES.forEach(feature => {
                const label = document.createElement('label');
                label.className = 'fr-ot-item';

                const checked = activeOT.includes(feature.tag) ? 'checked' : '';

                label.innerHTML =
                    `<input type="checkbox" data-feature="${feature.tag}" ${checked}>
                     ${feature.label}`;

                otPanel.appendChild(label);
            });

            function syncOT() {
                const active = [];
                otPanel.querySelectorAll('input:checked').forEach(cb => {
                    active.push(`"${cb.dataset.feature}" 1`);
                });
                stage.style.fontFeatureSettings =
                    active.length ? active.join(', ') : 'normal';
            }

            syncOT();

            otPanel.addEventListener('change', syncOT);

            otToggle.addEventListener('click', () => {
                otPanel.hidden = !otPanel.hidden;
            });

        });

    });

})();
