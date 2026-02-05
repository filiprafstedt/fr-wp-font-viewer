// -----------------------------
// FR Font Viewer JS
// -----------------------------

function sanitizeFontFamily(file) {
    // Convert spaces to dashes and remove unwanted characters
    return 'fr-font-' + file.replace(/\s+/g, '-').replace(/[^a-zA-Z0-9-_]/g, '');
}

function buildFeatureSettings(features) {
    return features
        .map(f => `"${f.tag}" ${f.enabled ? 1 : 0}`)
        .join(', ');
}

function applyFontFeatures(stage, features) {
    const css = buildFeatureSettings(features);
    stage.style.fontFeatureSettings = css || 'normal';
}

function updateFeaturesFromUI(panel, stage) {
    const features = [];
    panel.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        features.push({
            tag: cb.dataset.tag,
            enabled: cb.checked
        });
    });
    applyFontFeatures(stage, features);
}

function renderFeatureCheckboxes(container, features, stage, toggleBtn) {
    const panel = document.createElement('div');
    panel.className = 'fr-ot-panel';
    panel.hidden = true;

    features.forEach(feature => {
        if (!feature.enabled) return;

        const label = document.createElement('label');
        label.className = 'fr-ot-item';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = true;
        checkbox.dataset.tag = feature.tag;

        checkbox.addEventListener('change', () => {
            updateFeaturesFromUI(panel, stage);
        });

        label.appendChild(checkbox);
        label.appendChild(document.createTextNode(' ' + feature.name));
        panel.appendChild(label);
    });

    container.appendChild(panel);

    // Toggle button wiring
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            panel.hidden = !panel.hidden;
        });
    }

    // Initial apply
    updateFeaturesFromUI(panel, stage);
}

function renderFeatureDropdown(container) {
    if (!window.FRFontViewer?.features?.length) return;

    const select = document.createElement('select');
    select.className = 'fr-feature-select';

    FRFontViewer.features.forEach(feature => {
        const option = document.createElement('option');
        option.value = feature.tag;
        option.textContent = feature.name;
        option.dataset.enabled = feature.enabled ? '1' : '0';
        select.appendChild(option);
    });

    container.appendChild(select);
    return select;
}

(function () {

    function loadFont(file, ajaxUrl) {
        if (document.querySelector('style[data-font="' + file + '"]')) return;

        const family = sanitizeFontFamily(file);

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

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.fr-font-viewer').forEach(function (viewer) {

            const features = JSON.parse(viewer.dataset.features || '[]');
            const stage   = viewer.querySelector('.fr-font-stage');
            const select  = viewer.querySelector('.fr-font-select');
            const ajaxUrl = viewer.dataset.ajaxUrl;
            const defaultFont = viewer.dataset.defaultFont;

            /* -------------------------
               Font loading
            -------------------------- */
            function applyFont(file) {
                loadFont(file, ajaxUrl);
                const family = sanitizeFontFamily(file);
                stage.style.fontFamily = family;
            }

            // Apply default font immediately
            applyFont(defaultFont);

            /* -------------------------
               OpenType Features
            -------------------------- */
            const controls = viewer.querySelector('.fr-controls');
            if (controls && window.FRFontViewer?.features?.length) {
                const otToggle = viewer.querySelector('.fr-ot-toggle');

                // Checkbox panel
                renderFeatureCheckboxes(controls, FRFontViewer.features, stage, otToggle);

                // Dropdown (optional alternative)
                const dropdown = renderFeatureDropdown(controls);
                if (dropdown) {
                    dropdown.addEventListener('change', () => {
                        const selected = dropdown.value;
                        const updated = FRFontViewer.features.map(f => ({
                            ...f,
                            enabled: f.tag === selected
                        }));
                        applyFontFeatures(stage, updated);
                    });
                }

                // Apply initial features
                applyFontFeatures(stage, FRFontViewer.features);
            }

            /* -------------------------
               Font selector
            -------------------------- */
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

        });
    });

})();
