// TODO: implement public/js/sit-lookup.js
/**
 * sit-lookup.js — Salary Index Table AJAX lookup
 * DOLE RO9 Payroll System
 *
 * Drop at: public/js/sit-lookup.js
 * Include in any Blade view that needs live SIT lookup:
 *   <script src="{{ asset('js/sit-lookup.js') }}"></script>
 *
 * Expected DOM elements (IDs are configurable via SITLookup.init):
 *   #salary_grade   — <select> SG 1–33
 *   #step           — <select> Step 1–8
 *   #sit_year       — <select> or <input> CY (e.g. 2022)
 *   #basic_salary   — <input type="text"> — receives the fetched amount
 *   #sit_status     — any element — receives status messages (optional)
 */

(function (window) {
    'use strict';

    // ── Default config ────────────────────────────────────────────
    const DEFAULTS = {
        sgId       : 'salary_grade',
        stepId     : 'step',
        yearId     : 'sit_year',
        salaryId   : 'basic_salary',
        statusId   : 'sit_status',   // optional — leave blank to skip
        apiUrl     : '/api/sit',
        debounceMs : 250,
    };

    // ── Utility: debounce ─────────────────────────────────────────
    function debounce(fn, ms) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    // ── Format number as Philippine peso (no ₱ symbol for inputs) ─
    function formatAmount(raw) {
        const num = parseFloat(raw);
        if (isNaN(num)) return '';
        return num.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    // ── Core fetch function ────────────────────────────────────────
    function fetchSIT(cfg) {
        const sgEl   = document.getElementById(cfg.sgId);
        const stepEl = document.getElementById(cfg.stepId);
        const yearEl = document.getElementById(cfg.yearId);
        const salEl  = document.getElementById(cfg.salaryId);
        const statEl = cfg.statusId ? document.getElementById(cfg.statusId) : null;

        if (!sgEl || !stepEl || !salEl) return; // Guard: required elements missing

        const sg   = sgEl.value;
        const step = stepEl.value;
        const year = yearEl ? yearEl.value : '';

        // Don't fire if fields are blank / unselected
        if (!sg || !step) {
            if (statEl) statEl.textContent = '';
            return;
        }

        // Build query string
        const params = new URLSearchParams({ sg, step });
        if (year) params.append('year', year);

        // Visual feedback
        salEl.disabled = true;
        salEl.value    = 'Loading…';
        if (statEl) {
            statEl.textContent  = '';
            statEl.className    = '';
        }

        // Get CSRF token from meta tag (set in app.blade.php)
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        fetch(`${cfg.apiUrl}?${params.toString()}`, {
            method : 'GET',
            headers: {
                'Accept'       : 'application/json',
                'X-CSRF-TOKEN' : csrfToken,
            },
            credentials: 'same-origin',
        })
        .then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, status: res.status, data };
            });
        })
        .then(function ({ ok, status, data }) {
            salEl.disabled = false;

            if (ok && data.amount !== undefined) {
                // Store raw numeric value on the element for form submission
                salEl.dataset.rawAmount = data.amount;
                salEl.value = formatAmount(data.amount);

                // Also keep a hidden input in sync if it exists
                const hiddenEl = document.getElementById(cfg.salaryId + '_raw');
                if (hiddenEl) hiddenEl.value = data.amount;

                if (statEl) {
                    statEl.textContent = '\u2713 SG ' + data.sg + ' Step ' + data.step + ' — CY ' + data.year;
                    statEl.className   = 'sit-status-ok';
                }
            } else {
                salEl.value = '';
                if (statEl) {
                    statEl.textContent = data.error || 'No rate found for this SG/Step/Year.';
                    statEl.className   = 'sit-status-err';
                }
            }
        })
        .catch(function (err) {
            salEl.disabled = false;
            salEl.value    = '';
            if (statEl) {
                statEl.textContent = 'Network error. Please try again.';
                statEl.className   = 'sit-status-err';
            }
            console.error('[SIT Lookup]', err);
        });
    }

    // ── Public API ────────────────────────────────────────────────
    window.SITLookup = {
        /**
         * Initialise the lookup listeners.
         *
         * @param {object} options  Override any DEFAULTS key.
         *
         * Example (use all defaults):
         *   SITLookup.init();
         *
         * Example (custom IDs):
         *   SITLookup.init({ sgId: 'emp_sg', stepId: 'emp_step', yearId: 'emp_year' });
         */
        init: function (options) {
            const cfg      = Object.assign({}, DEFAULTS, options || {});
            const doFetch  = debounce(() => fetchSIT(cfg), cfg.debounceMs);

            ['sgId', 'stepId', 'yearId'].forEach(function (key) {
                const el = document.getElementById(cfg[key]);
                if (el) {
                    el.addEventListener('change', doFetch);
                }
            });
        },

        /**
         * Manually trigger a lookup (e.g. after programmatically setting values).
         * @param {object} options  Same as init() options.
         */
        fetch: function (options) {
            const cfg = Object.assign({}, DEFAULTS, options || {});
            fetchSIT(cfg);
        },
    };

}(window));