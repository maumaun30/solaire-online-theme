/* ============================================================
   Solaire Online — register / login modal
   Opens #so-reg-modal from any `.so-open-register` trigger, captures
   UTM / click-id attribution on page load, and redirects to
   solaireonline.com with the normalised mobile number + attribution.
   Markup: template-parts/register-modal.php
   ============================================================ */
document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const DEFAULT_SOURCE = "seo";
    const DEFAULT_MEDIUM = "seo";
    const DEFAULT_CAMPAIGN = "so-games-sub";

    const STORAGE_KEY = "so_attr";
    const LEGACY_STORAGE_KEY = "so_attribution";

    const REDIRECT_BASE_URL = "https://solaireonline.com/en/";

    const MODALS = {
        register: {
            modalId: "so-reg-modal",
            closeId: "so-reg-close",
            phoneSelector: ".phoneInput",
            termsSelector: "#so-reg-terms",
            campaign: DEFAULT_CAMPAIGN,
            openSelector: ".so-open-register, #so-register-trigger",
            submitSelector: "#so-reg-submit"
        }
    };

    const DEFAULT_COUNTRY = "ph";
    const FLAG_URL = "https://flagcdn.com/{iso}.svg";

    // Philippines first (default), then the rest alphabetically.
    const COUNTRIES = (window.SOLAIRE_COUNTRIES || [["ph", "Philippines", "63"]])
        .map(function (row) {
            return { iso: row[0], name: row[1], dial: row[2] };
        })
        .sort(function (a, b) {
            if (a.iso === DEFAULT_COUNTRY) return -1;
            if (b.iso === DEFAULT_COUNTRY) return 1;
            return a.name.localeCompare(b.name);
        });

    // Selected country per modal type.
    const selectedCountry = {};

    // Inline errors stay hidden until the visitor has interacted with the
    // form (typed, cleared, toggled the terms or tried to submit).
    const touched = {};

    const ERROR_MESSAGES = {
        required: "Phone number is required.",
        invalid: "Enter a valid mobile number.",
        terms: "Please agree to the terms of use and privacy policy"
    };

    // Digit grouping while typing, as on solaireonline.com:
    // PH 9453 084 255, everything else 867 596 9505.
    const GROUPS_PH = [4, 3, 4];
    const GROUPS_DEFAULT = [3, 3, 4];

    /*
     * Desktop sizing: the card is laid out at 480x640 (solaireonline.com at
     * 1440px) and, from 1024px up, zoomed so it stays 1/3 of the viewport
     * width like theirs — capped so it always fits the viewport height.
     */
    const PANEL_BASE_VIEWPORT = 1440;
    const PANEL_BASE_HEIGHT = 640;
    const PANEL_SCALE_FROM = 1024;

    // Submit button look: greyed out until a valid number + terms are set.
    const SUBMIT_IDLE = ["bg-[#3a3a3a]", "text-white/40"];
    const SUBMIT_READY = ["bg-cta-orange", "text-white"];

    function parseJson(value) {
        try {
            return value ? JSON.parse(value) : {};
        } catch (error) {
            return {};
        }
    }

    function saveAttribution(data) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (error) {}
    }

    function getAttribution() {
        try {
            return parseJson(
                localStorage.getItem(STORAGE_KEY) ||
                localStorage.getItem(LEGACY_STORAGE_KEY)
            );
        } catch (error) {
            return {};
        }
    }

    function setCookie(name, value, days = 30) {
        if (!value) return;

        const expires = new Date(Date.now() + days * 86400000).toUTCString();

        document.cookie =
            name + "=" + encodeURIComponent(value) +
            "; expires=" + expires + "; path=/; SameSite=Lax";
    }

    function syncCookies(attribution) {
        Object.keys(attribution).forEach(function (key) {
            if (attribution[key]) {
                setCookie(key, attribution[key]);
            }
        });
    }

    function captureAttribution() {
        const params = new URLSearchParams(window.location.search);
        const existing = getAttribution();

        const attribution = {
            utm_source: params.get("utm_source") || existing.utm_source || DEFAULT_SOURCE,
            utm_medium: params.get("utm_medium") || existing.utm_medium || DEFAULT_MEDIUM,
            utm_campaign: params.get("utm_campaign") || existing.utm_campaign || DEFAULT_CAMPAIGN,
            utm_term: params.get("utm_term") || existing.utm_term || "",
            utm_content: params.get("utm_content") || existing.utm_content || "",
            affiliate_id: params.get("affiliate_id") || existing.affiliate_id || ""
        };

        ["gclid", "gbraid", "wbraid", "fbclid", "ttclid", "msclkid"].forEach(function (field) {
            const value = params.get(field);

            if (!value || attribution.click_id) return;

            attribution.click_field = field;
            attribution.click_id = value;

            if (field === "gclid" || field === "gbraid" || field === "wbraid") {
                attribution.click_platform = "google";
            } else if (field === "fbclid") {
                attribution.click_platform = "facebook";
            } else if (field === "ttclid") {
                attribution.click_platform = "tiktok";
            } else {
                attribution.click_platform = "microsoft";
            }
        });

        if (!attribution.click_id && existing.click_id) {
            attribution.click_field = existing.click_field;
            attribution.click_id = existing.click_id;
            attribution.click_platform = existing.click_platform;
        }

        saveAttribution(attribution);
        syncCookies(attribution);
    }

    function getModalElement(type) {
        const config = MODALS[type];
        return config ? document.getElementById(config.modalId) : null;
    }

    function getPhoneInput(type) {
        const modal = getModalElement(type);
        return modal ? modal.querySelector(MODALS[type].phoneSelector) : null;
    }

    function getTermsCheckbox(type) {
        const modal = getModalElement(type);
        const selector = MODALS[type] && MODALS[type].termsSelector;
        return modal && selector ? modal.querySelector(selector) : null;
    }

    function getSubmitButton(type) {
        const modal = getModalElement(type);
        return modal ? modal.querySelector(MODALS[type].submitSelector) : null;
    }

    function getCountry(type) {
        return selectedCountry[type] || COUNTRIES[0];
    }

    function isPhilippines(country) {
        return country.iso === "ph";
    }

    // E.164 caps a full number at 15 digits including the country code.
    function maxLocalDigits(country) {
        return isPhilippines(country) ? 10 : 15 - country.dial.length;
    }

    /*
     * Returns the national number without the country code or trunk "0".
     * PH accepts 09171234567, 9171234567, 639171234567, +639171234567
     * and returns 9171234567.
     */
    function toLocalPhone(value, country) {
        let phone = String(value || "").replace(/\D/g, "");

        if (phone.startsWith(country.dial) && phone.length > maxLocalDigits(country)) {
            phone = phone.slice(country.dial.length);
        }

        return phone.replace(/^0+/, "");
    }

    /*
     * PH: mobile only (10 digits starting with 9). Other countries use
     * libphonenumber-js (loaded from jsdelivr) for per-country rules, with a
     * plain length check if it failed to load.
     */
    function isValidLocalPhone(value, country) {
        if (isPhilippines(country)) {
            return /^9\d{9}$/.test(value);
        }

        const lib = window.libphonenumber;

        if (lib && typeof lib.isValidPhoneNumber === "function") {
            try {
                return lib.isValidPhoneNumber(value, country.iso.toUpperCase());
            } catch (error) {
                // Unknown region in the metadata: fall through to the length check.
            }
        }

        return value.length >= 6 && value.length <= maxLocalDigits(country);
    }

    function formatLocalPhone(digits, country) {
        const groups = isPhilippines(country) ? GROUPS_PH : GROUPS_DEFAULT;
        const parts = [];
        let index = 0;

        for (let i = 0; i < groups.length && index < digits.length; i++) {
            parts.push(digits.slice(index, index + groups[i]));
            index += groups[i];
        }

        if (index < digits.length) {
            parts[parts.length - 1] += digits.slice(index);
        }

        return parts.join(" ");
    }

    function phonePlaceholder(country) {
        return isPhilippines(country) ? "9XXX XXX XXX" : "XXX XXX XXXX";
    }

    /*
     * The field sits next to the dial-code button, so it only holds the
     * national number: strip non-digits, a pasted country code and leading
     * zeros, cap the length (PH: 10 digits starting with 9), then regroup
     * with spaces — keeping the caret after the same digit it was after.
     */
    function normalizePhoneInput(input, country) {
        const raw = String(input.value || "");
        const caret = input.selectionStart;
        const digitsBeforeCaret =
            caret === null ? null : raw.slice(0, caret).replace(/\D/g, "").length;

        let digits = raw.replace(/\D/g, "");
        let dropped = 0;

        if (digits.startsWith(country.dial) && digits.length > maxLocalDigits(country)) {
            digits = digits.slice(country.dial.length);
            dropped += country.dial.length;
        }

        const trimmed = digits.replace(/^0+/, "");
        dropped += digits.length - trimmed.length;
        digits = trimmed.slice(0, maxLocalDigits(country));

        const formatted = formatLocalPhone(digits, country);

        if (formatted === raw) return;

        input.value = formatted;

        if (digitsBeforeCaret === null || document.activeElement !== input) return;

        let wanted = Math.max(0, digitsBeforeCaret - dropped);
        let position = 0;

        while (position < formatted.length && wanted > 0) {
            if (/\d/.test(formatted[position])) wanted--;
            position++;
        }

        input.setSelectionRange(position, position);
    }

    /* ---- Validation ----------------------------------------- */

    function getValidationError(type) {
        const phoneInput = getPhoneInput(type);
        const termsCheck = getTermsCheckbox(type);
        const country = getCountry(type);
        const localPhone = toLocalPhone(phoneInput ? phoneInput.value : "", country);

        if (!localPhone) return "required";
        if (!isValidLocalPhone(localPhone, country)) return "invalid";
        if (termsCheck && !termsCheck.checked) return "terms";

        return null;
    }

    function renderValidation(type) {
        const modal = getModalElement(type);
        const phoneInput = getPhoneInput(type);

        if (!modal || !phoneInput) return;

        const error = touched[type] ? getValidationError(type) : null;
        const errorBox = modal.querySelector("#so-reg-error");
        const clearButton = modal.querySelector("#so-reg-clear");
        const submit = getSubmitButton(type);

        if (error) {
            phoneInput.setAttribute("data-invalid", "");
            phoneInput.setAttribute("aria-invalid", "true");
        } else {
            phoneInput.removeAttribute("data-invalid");
            phoneInput.removeAttribute("aria-invalid");
        }

        if (errorBox) {
            errorBox.querySelector("[data-reg-error-text]").textContent =
                error ? ERROR_MESSAGES[error] : "";
            errorBox.classList.toggle("hidden", !error);
            errorBox.classList.toggle("flex", !!error);
        }

        // The error line takes the space above Submit instead of pushing it.
        if (submit) {
            submit.classList.toggle("mt-8", !error);
            submit.classList.toggle("mt-3", !!error);
        }

        if (clearButton) {
            const hasValue = phoneInput.value !== "";
            clearButton.classList.toggle("hidden", !hasValue);
            clearButton.classList.toggle("flex", hasValue);
        }
    }

    /* ---- Country picker ------------------------------------- */

    function getPicker(type) {
        const modal = getModalElement(type);

        if (!modal) return null;

        return {
            button: modal.querySelector("#so-reg-country"),
            panel: modal.querySelector("#so-reg-country-picker"),
            list: modal.querySelector("#so-reg-country-list"),
            backdrop: modal.querySelector("[data-cc-backdrop]")
        };
    }

    function isPickerOpen(type) {
        const picker = getPicker(type);
        return !!(picker && picker.panel && !picker.panel.classList.contains("hidden"));
    }

    function renderCountryList(type) {
        const picker = getPicker(type);

        if (!picker || !picker.list || picker.list.childElementCount) return;

        const html = COUNTRIES.map(function (country) {
            return (
                '<li role="option" aria-selected="false" data-cc-iso="' + country.iso + '" tabindex="-1" ' +
                'class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-3 text-base text-white outline-none transition hover:bg-white/5 focus:bg-white/5 aria-selected:bg-white/10">' +
                    '<img src="' + FLAG_URL.replace("{iso}", country.iso) + '" alt="" loading="lazy" class="h-[18px] w-[26px] shrink-0 object-cover" />' +
                    '<span class="min-w-0 flex-1 truncate">' + country.name + " (+" + country.dial + ")</span>" +
                    '<span data-cc-radio class="h-5 w-5 shrink-0 rounded-full border-2 border-white/70"></span>' +
                "</li>"
            );
        }).join("");

        picker.list.innerHTML = html;
    }

    function markSelected(type) {
        const picker = getPicker(type);
        const country = getCountry(type);

        if (!picker || !picker.list) return;

        picker.list.querySelectorAll("[data-cc-iso]").forEach(function (item) {
            const selected = item.getAttribute("data-cc-iso") === country.iso;
            const radio = item.querySelector("[data-cc-radio]");

            item.setAttribute("aria-selected", selected ? "true" : "false");
            radio.classList.toggle("border-[5px]", selected);
            radio.classList.toggle("border-[#f5993d]", selected);
            radio.classList.toggle("border-2", !selected);
            radio.classList.toggle("border-white/70", !selected);
        });
    }

    function openPicker(type) {
        const picker = getPicker(type);

        if (!picker || !picker.panel) return;

        renderCountryList(type);
        markSelected(type);

        // Hide the on-screen keyboard so the mobile sheet has the room.
        const phoneInput = getPhoneInput(type);
        if (phoneInput && document.activeElement === phoneInput) phoneInput.blur();

        picker.panel.classList.remove("hidden");
        picker.panel.classList.add("flex");
        if (picker.backdrop) picker.backdrop.classList.remove("hidden");
        picker.button.setAttribute("aria-expanded", "true");
        picker.button.querySelector("svg").classList.add("rotate-180");

        const current = picker.list.querySelector('[aria-selected="true"]');
        if (current) {
            current.scrollIntoView({ block: "nearest" });
            current.focus({ preventScroll: true });
        }
    }

    function closePicker(type, returnFocus) {
        const picker = getPicker(type);

        if (!picker || !picker.panel || picker.panel.classList.contains("hidden")) return;

        picker.panel.classList.add("hidden");
        picker.panel.classList.remove("flex");
        if (picker.backdrop) picker.backdrop.classList.add("hidden");
        picker.button.setAttribute("aria-expanded", "false");
        picker.button.querySelector("svg").classList.remove("rotate-180");

        if (returnFocus) picker.button.focus();
    }

    function selectCountry(type, iso) {
        const country = COUNTRIES.find(function (c) { return c.iso === iso; });
        const picker = getPicker(type);
        const phoneInput = getPhoneInput(type);

        if (!country || !picker) return;

        selectedCountry[type] = country;

        picker.button.querySelector("[data-cc-flag]").src = FLAG_URL.replace("{iso}", country.iso);
        picker.button.querySelector("[data-cc-dial]").textContent = "+" + country.dial;
        picker.button.setAttribute("aria-label", country.name + " (+" + country.dial + ")");

        if (phoneInput) {
            phoneInput.placeholder = phonePlaceholder(country);
            normalizePhoneInput(phoneInput, country);
        }

        markSelected(type);
        updateSubmitState(type);
    }

    function wirePicker(type) {
        const picker = getPicker(type);
        const modal = getModalElement(type);

        if (!picker || !picker.button || !picker.panel) return;

        picker.button.addEventListener("click", function (event) {
            event.preventDefault();

            if (isPickerOpen(type)) {
                closePicker(type);
            } else {
                openPicker(type);
            }
        });

        picker.list.addEventListener("click", function (event) {
            const item = event.target.closest("[data-cc-iso]");

            if (!item) return;

            selectCountry(type, item.getAttribute("data-cc-iso"));
            closePicker(type, true);
        });

        // Arrow keys move through the list, Enter / Space picks.
        picker.list.addEventListener("keydown", function (event) {
            const item = event.target.closest("[data-cc-iso]");

            if (!item) return;

            if (event.key === "ArrowDown" || event.key === "ArrowUp") {
                event.preventDefault();
                const next = event.key === "ArrowDown" ? item.nextElementSibling : item.previousElementSibling;
                if (next) next.focus();
            } else if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                selectCountry(type, item.getAttribute("data-cc-iso"));
                closePicker(type, true);
            }
        });

        const closeButton = picker.panel.querySelector("[data-cc-close]");
        if (closeButton) {
            closeButton.addEventListener("click", function (event) {
                event.preventDefault();
                closePicker(type, true);
            });
        }

        // Click anywhere else in the modal (incl. the mobile dim layer) closes it.
        modal.addEventListener("click", function (event) {
            if (
                isPickerOpen(type) &&
                !picker.panel.contains(event.target) &&
                !picker.button.contains(event.target)
            ) {
                closePicker(type);
            }
        });
    }

    function updateSubmitState(type) {
        const button = getSubmitButton(type);
        const phoneInput = getPhoneInput(type);

        renderValidation(type);

        if (!button) return;

        const ready = !!phoneInput && getValidationError(type) === null;

        button.disabled = !ready;
        SUBMIT_IDLE.forEach(function (c) { button.classList.toggle(c, !ready); });
        SUBMIT_READY.forEach(function (c) { button.classList.toggle(c, ready); });
    }

    function buildQuery(phone, attribution) {
        const query = new URLSearchParams();

        if (phone) {
            query.set("mobile", phone);
        }

        query.set("utm_source", attribution.utm_source || DEFAULT_SOURCE);
        query.set("utm_medium", attribution.utm_medium || DEFAULT_MEDIUM);
        query.set("utm_campaign", attribution.utm_campaign || DEFAULT_CAMPAIGN);

        if (attribution.utm_term) query.set("utm_term", attribution.utm_term);
        if (attribution.utm_content) query.set("utm_content", attribution.utm_content);
        if (attribution.affiliate_id) query.set("affiliate_id", attribution.affiliate_id);

        if (attribution.click_field && attribution.click_id) {
            query.set("click_field", attribution.click_field);
            query.set("click_id", attribution.click_id);

            if (attribution.click_platform) {
                query.set("click_platform", attribution.click_platform);
            }
        }

        return query;
    }

    window.submitPhone = function (type) {
        const modalType = type || "register";
        const config = MODALS[modalType];

        if (!config) return;

        const phoneInput = getPhoneInput(modalType);

        if (!phoneInput) return;

        // Show the inline error (if any) instead of submitting.
        touched[modalType] = true;
        updateSubmitState(modalType);

        const error = getValidationError(modalType);

        if (error) {
            if (error !== "terms") phoneInput.focus();
            return;
        }

        const country = getCountry(modalType);
        const localPhone = toLocalPhone(phoneInput.value, country);

        // PH keeps the local 09XXXXXXXXX format; other countries are sent in
        // international form (+<dial><number>).
        const mobile = isPhilippines(country)
            ? "0" + localPhone
            : "+" + country.dial + localPhone;

        const query = buildQuery(mobile, getAttribution());

        window.location.href =
            (config.redirectUrl || REDIRECT_BASE_URL) + "?" + query.toString();
    };

    function fitPanel(type) {
        const modal = getModalElement(type);
        const panel = modal && modal.querySelector("[data-reg-panel]");

        if (!panel) return;

        if (window.innerWidth < PANEL_SCALE_FROM) {
            panel.style.zoom = "";
            return;
        }

        const scale = Math.min(
            window.innerWidth / PANEL_BASE_VIEWPORT,
            (window.innerHeight - 32) / PANEL_BASE_HEIGHT
        );

        panel.style.zoom = String(Math.round(scale * 1000) / 1000);
    }

    function openModal(type) {
        const modal = getModalElement(type);

        if (!modal) return;

        closeAllModals();

        fitPanel(type);
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        modal.setAttribute("aria-hidden", "false");
        if (window.solaireScrollLock) window.solaireScrollLock(true);

        // No auto-focus on the phone field: it would pop the on-screen
        // keyboard on mobile the moment the modal opens.
    }

    function closeModal(type) {
        const modal = getModalElement(type);

        if (!modal || modal.classList.contains("hidden")) return;

        closePicker(type);
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        modal.setAttribute("aria-hidden", "true");
        if (window.solaireScrollLock) window.solaireScrollLock(false);
    }

    function closeAllModals() {
        Object.keys(MODALS).forEach(closeModal);
    }

    function wireModal(type) {
        const modal = getModalElement(type);
        const phoneInput = getPhoneInput(type);
        const termsCheck = getTermsCheckbox(type);
        const closeButton = document.getElementById(MODALS[type].closeId);

        if (!modal) return;

        if (phoneInput) {
            phoneInput.addEventListener("input", function () {
                touched[type] = true;
                normalizePhoneInput(this, getCountry(type));
                updateSubmitState(type);
            });

            // Leaving a field that was typed in re-checks it (e.g. too short).
            phoneInput.addEventListener("blur", function () {
                if (this.value !== "") touched[type] = true;
                updateSubmitState(type);
            });

            const clearButton = modal.querySelector("#so-reg-clear");
            if (clearButton) {
                clearButton.addEventListener("click", function (event) {
                    event.preventDefault();
                    phoneInput.value = "";
                    touched[type] = true;
                    updateSubmitState(type);
                    phoneInput.focus();
                });
            }

            phoneInput.addEventListener("keydown", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    window.submitPhone(type);
                }
            });
        }

        if (termsCheck) {
            termsCheck.addEventListener("change", function () {
                touched[type] = true;
                updateSubmitState(type);
            });
        }

        if (closeButton) {
            closeButton.addEventListener("click", function (event) {
                event.preventDefault();
                closeModal(type);
            });
        }

        // Click on the dimmed backdrop (outside the panel) closes it.
        modal.addEventListener("click", function (event) {
            if (event.target === modal) closeModal(type);
        });

        wirePicker(type);
        updateSubmitState(type);
    }

    captureAttribution();

    document.addEventListener("click", function (event) {
        const types = Object.keys(MODALS);

        for (let i = 0; i < types.length; i++) {
            const type = types[i];

            if (event.target.closest(MODALS[type].openSelector)) {
                // Modal disabled in ACF → not rendered; let the link navigate.
                if (!getModalElement(type)) return;

                event.preventDefault();
                openModal(type);
                return;
            }

            if (event.target.closest(MODALS[type].submitSelector)) {
                event.preventDefault();
                window.submitPhone(type);
                return;
            }
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;

        // Escape closes an open country picker first, then the modal.
        const openPickerType = Object.keys(MODALS).find(isPickerOpen);

        if (openPickerType) {
            closePicker(openPickerType, true);
        } else {
            closeAllModals();
        }
    });

    window.addEventListener("resize", function () {
        Object.keys(MODALS).forEach(fitPanel);
    });

    Object.keys(MODALS).forEach(wireModal);
});
