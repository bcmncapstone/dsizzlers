import 'alpinejs';
import './bootstrap';

function extractInlineConfirmMessage(attributeValue) {
    if (!attributeValue) {
        return null;
    }

    const match = attributeValue.match(/return\s+(?:window\.)?confirm\((['"`])([\s\S]*?)\1\)/);
    return match ? match[2] : null;
}

function submitFormWithSubmitter(form, submitter) {
    if (!form) {
        return;
    }

    if (typeof form.requestSubmit === 'function') {
        if (submitter) {
            form.requestSubmit(submitter);
            return;
        }

        form.requestSubmit();
        return;
    }

    let tempInput = null;
    if (submitter?.name) {
        tempInput = document.createElement('input');
        tempInput.type = 'hidden';
        tempInput.name = submitter.name;
        tempInput.value = submitter.value;
        form.appendChild(tempInput);
    }

    form.submit();

    if (tempInput) {
        tempInput.remove();
    }
}

// Only keep non-nav JS (e.g., flash alert)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
        const targetId = toggleButton.getAttribute('data-password-toggle');
        if (!targetId) {
            return;
        }

        const passwordInput = document.getElementById(targetId);
        if (!(passwordInput instanceof HTMLInputElement)) {
            return;
        }

        const updateToggleState = () => {
            const isVisible = passwordInput.type === 'text';
            toggleButton.textContent = isVisible ? 'Hide' : 'Show';
            toggleButton.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
            toggleButton.setAttribute('aria-pressed', String(isVisible));
        };

        toggleButton.addEventListener('click', () => {
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            updateToggleState();
            passwordInput.focus({ preventScroll: true });
            const inputLength = passwordInput.value.length;
            passwordInput.setSelectionRange(inputLength, inputLength);
        });

        updateToggleState();
    });

    document.querySelectorAll('.js-flash-alert').forEach((alertEl) => {
        const timeout = Number.parseInt(alertEl.dataset.timeout || '3000', 10);
        if (!Number.isFinite(timeout) || timeout <= 0) {
            return;
        }
        setTimeout(() => {
            alertEl.style.transition = 'opacity 0.3s ease';
            alertEl.style.opacity = '0';
            setTimeout(() => {
                alertEl.remove();
            }, 320);
        }, timeout);
    });

    // Hamburger menu toggle
    const navbarToggle = document.querySelector('[data-navbar-toggle]');
    const navbarMenu = document.querySelector('[data-navbar-menu]');
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', () => {
            const expanded = navbarToggle.getAttribute('aria-expanded') === 'true';
            navbarToggle.setAttribute('aria-expanded', !expanded);
            navbarMenu.classList.toggle('is-open');
        });
    }

    const confirmModal = document.getElementById('ds-confirm-modal');
    const confirmMessage = document.getElementById('ds-confirm-message');
    const confirmOk = document.getElementById('ds-confirm-ok');
    const confirmCancel = document.getElementById('ds-confirm-cancel');
    const confirmBackdrop = document.getElementById('ds-confirm-backdrop');

    let confirmResolver = null;
    let previousFocus = null;

    function closeConfirmModal(result) {
        if (!confirmModal || !confirmResolver) {
            return;
        }

        confirmModal.classList.remove('is-visible');
        confirmModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('ds-confirm-open');

        const resolve = confirmResolver;
        confirmResolver = null;
        resolve(result);

        if (previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    }

    window.showConfirmModal = function(message, options = {}) {
        if (!confirmModal || !confirmMessage || !confirmOk || !confirmCancel) {
            return Promise.resolve(window.confirm(message));
        }

        if (confirmResolver) {
            closeConfirmModal(false);
        }

        confirmMessage.textContent = message || 'Are you sure you want to continue?';
        confirmOk.textContent = options.confirmText || 'Confirm';
        confirmCancel.textContent = options.cancelText || 'Cancel';

        previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;

        confirmModal.classList.add('is-visible');
        confirmModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('ds-confirm-open');

        window.setTimeout(() => {
            confirmOk.focus();
        }, 10);

        return new Promise((resolve) => {
            confirmResolver = resolve;
        });
    };

    [confirmCancel, confirmBackdrop].forEach((element) => {
        element?.addEventListener('click', () => closeConfirmModal(false));
    });

    confirmOk?.addEventListener('click', () => closeConfirmModal(true));

    document.addEventListener('keydown', (event) => {
        if (!confirmModal?.classList.contains('is-visible')) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeConfirmModal(false);
        }
    });

    document.querySelectorAll('form[onsubmit], button[onclick], a[onclick], input[onclick]').forEach((element) => {
        ['onsubmit', 'onclick'].forEach((attribute) => {
            const message = extractInlineConfirmMessage(element.getAttribute(attribute));
            if (!message) {
                return;
            }

            element.dataset.confirm = message;
            element.removeAttribute(attribute);
        });
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
            return;
        }

        if (form.dataset.confirmApproved === 'true') {
            delete form.dataset.confirmApproved;
            return;
        }

        event.preventDefault();
        const confirmed = await window.showConfirmModal(form.dataset.confirm, {
            confirmText: form.dataset.confirmButton || 'Confirm',
        });

        if (!confirmed) {
            return;
        }

        form.dataset.confirmApproved = 'true';
        submitFormWithSubmitter(form, event.submitter);
    }, true);

    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('button[data-confirm], a[data-confirm], input[data-confirm]');
        if (!trigger) {
            return;
        }

        if (trigger.dataset.confirmApproved === 'true') {
            delete trigger.dataset.confirmApproved;
            return;
        }

        event.preventDefault();

        const confirmed = await window.showConfirmModal(trigger.dataset.confirm, {
            confirmText: trigger.dataset.confirmButton || 'Confirm',
        });

        if (!confirmed) {
            return;
        }

        trigger.dataset.confirmApproved = 'true';

        if (trigger instanceof HTMLAnchorElement && trigger.href) {
            window.location.assign(trigger.href);
            return;
        }

        if ((trigger instanceof HTMLButtonElement || trigger instanceof HTMLInputElement) && trigger.form) {
            submitFormWithSubmitter(trigger.form, trigger);
            return;
        }

        trigger.click();
    }, true);
});
