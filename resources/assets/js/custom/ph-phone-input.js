/**
 * Philippine phone input (+63).
 *
 * Replaces intl-tel-input: the clinic works with Philippine numbers only, so there is no country
 * picker - every input marked `data-ph-phone` gets a fixed "+63" prefix and accepts
 *
 *   917 123 4567 | 0917 123 4567 | +63 917 123 4567 | 63917... | (035) 422-1234 | +63 2 8123 4567
 *
 * and is tidied to the national form (917 123 4567) on blur/paste/submit. The server does the
 * authoritative check (App\Support\PhilippinePhone) - keep the two normalisers in sync:
 *   mobile   9XXXXXXXXX          (10 digits, starts with 9)
 *   landline [2-8]XXXXXXX(XX)    (area code + number, 8-10 digits)
 */
(function (window, document) {
    "use strict";

    const SELECTOR = "input[data-ph-phone]";
    const READY_ATTR = "data-ph-phone-ready";
    const FALLBACK_MESSAGE =
        "Enter a valid Philippine number: a mobile number such as +63 917 123 4567 (or 0917 123 4567), or a landline with its area code.";

    function message() {
        try {
            const text = window.Lang && Lang.get("js.invalid_ph_number");
            if (text && text !== "js.invalid_ph_number") {
                return text;
            }
        } catch (e) {
            // Lang not loaded (or locale without the key) - use the English default.
        }

        return FALLBACK_MESSAGE;
    }

    /** National significant number or null - mirrors PhilippinePhone::national() in PHP. */
    function toNational(value) {
        let digits = String(value === null || value === undefined ? "" : value).replace(/\D+/g, "");

        if (digits === "") {
            return null;
        }

        if (digits.startsWith("0063")) {
            digits = digits.slice(4);
        } else if (digits.startsWith("63") && digits.length >= 10) {
            digits = digits.slice(2);
        }

        digits = digits.replace(/^0/, "");

        if (/^9\d{9}$/.test(digits) || /^[2-8]\d{7,9}$/.test(digits)) {
            return digits;
        }

        return null;
    }

    /** "917 123 4567" (mobile) / "35 4221234" (landline, area code first). */
    function format(national) {
        if (!national) {
            return "";
        }

        if (/^9\d{9}$/.test(national)) {
            return national.slice(0, 3) + " " + national.slice(3, 6) + " " + national.slice(6);
        }

        const areaLength = national.charAt(0) === "2" ? 1 : 2;

        return national.slice(0, areaLength) + " " + national.slice(areaLength);
    }

    /** "+63 917 123 4567" for a readable Philippine number, otherwise the text unchanged (read-only display fields). */
    function display(value) {
        const national = toNational(value);

        return national === null ? (value === null || value === undefined ? "" : String(value)) : "+63 " + format(national);
    }

    function feedbackFor(input) {
        return input.closest(".ph-phone-group").nextElementSibling;
    }

    function showError(input, text) {
        const feedback = feedbackFor(input);
        input.classList.add("is-invalid");
        input.setAttribute("aria-invalid", "true");
        if (feedback) {
            feedback.textContent = text;
            feedback.style.display = "block";
        }
    }

    function clearError(input) {
        const feedback = feedbackFor(input);
        input.classList.remove("is-invalid");
        input.removeAttribute("aria-invalid");
        if (feedback) {
            feedback.textContent = "";
            feedback.style.display = "none";
        }
    }

    /**
     * Tidy the value and show/clear the inline error. Returns true when the field is acceptable
     * (empty or a valid Philippine number).
     */
    function validate(input) {
        const raw = input.value.trim();

        if (raw === "") {
            input.value = "";
            clearError(input);

            return true;
        }

        const national = toNational(raw);

        if (national === null) {
            showError(input, message());

            return false;
        }

        input.value = format(national);
        clearError(input);

        return true;
    }

    function enhance(input) {
        if (input.getAttribute(READY_ATTR)) {
            return;
        }
        input.setAttribute(READY_ATTR, "1");

        const group = document.createElement("div");
        group.className =
            "input-group ph-phone-group" + (input.classList.contains("form-control-lg") ? " input-group-lg" : "");

        const prefix = document.createElement("span");
        prefix.className = "input-group-text ph-phone-prefix";
        prefix.textContent = "+63";
        prefix.title = "Philippines";

        const feedback = document.createElement("div");
        feedback.className = "invalid-feedback ph-phone-feedback";
        feedback.style.display = "none";

        input.parentNode.insertBefore(group, input);
        group.appendChild(prefix);
        group.appendChild(input);
        group.insertAdjacentElement("afterend", feedback);

        input.setAttribute("type", "tel");
        input.setAttribute("inputmode", "tel");
        input.setAttribute("autocomplete", "off");
        input.setAttribute("maxlength", "24");
        input.setAttribute("placeholder", "9XX XXX XXXX");

        // Show whatever is stored in the tidy national form; a legacy value that is not a Philippine
        // number stays visible and is flagged so it can be corrected.
        if (input.value.trim() !== "") {
            validate(input);
        }

        input.addEventListener("input", function () {
            // Digits and the separators people type/paste; anything else is dropped immediately.
            const cleaned = input.value.replace(/[^\d+\s()\-.]/g, "");
            if (cleaned !== input.value) {
                input.value = cleaned;
            }
            if (input.classList.contains("is-invalid")) {
                clearError(input);
            }
        });
        input.addEventListener("blur", function () {
            validate(input);
        });
        input.addEventListener("change", function () {
            validate(input);
        });
        input.addEventListener("paste", function () {
            setTimeout(function () {
                validate(input);
            }, 0);
        });
    }

    function init(root) {
        (root || document).querySelectorAll(SELECTOR).forEach(enhance);
    }

    /** Validate every phone input of a form; focuses the first bad one. */
    function validateForm(form) {
        let firstInvalid = null;

        form.querySelectorAll(SELECTOR).forEach(function (input) {
            if (!input.getAttribute(READY_ATTR)) {
                enhance(input);
            }
            if (!validate(input) && firstInvalid === null) {
                firstInvalid = input;
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
            if (typeof window.displayErrorMessage === "function") {
                window.displayErrorMessage(message());
            }

            return false;
        }

        return true;
    }

    // Runs before every jQuery/Livewire submit handler, so AJAX forms are gated too.
    document.addEventListener(
        "submit",
        function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.querySelector(SELECTOR)) {
                return;
            }
            if (!validateForm(form)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        },
        true
    );

    document.addEventListener("DOMContentLoaded", function () {
        init(document);
    });
    document.addEventListener("turbo:load", function () {
        init(document);
    });

    // Inputs added later (modals/partials loaded over AJAX).
    if (typeof MutationObserver === "function") {
        let queued = false;
        const observe = function () {
            new MutationObserver(function () {
                if (queued) {
                    return;
                }
                queued = true;
                window.requestAnimationFrame(function () {
                    queued = false;
                    init(document);
                });
            }).observe(document.body, { childList: true, subtree: true });
        };

        if (document.body) {
            observe();
        } else {
            document.addEventListener("DOMContentLoaded", observe);
        }
    }

    window.PhPhone = { init, validate, validateForm, toNational, format, display };
})(window, document);
