document.addEventListener('DOMContentLoaded', () => {
    const endpoint = window.USER_ENDPOINT;
    const flash = document.getElementById('userFlash');
    const toggleButtons = document.querySelectorAll('.card-toggle');
    const forms = document.querySelectorAll('.user-form');
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');

    function showFlash(message, type = 'success') {
        if (!flash) return;
        flash.textContent = message;
        flash.classList.remove('is-error', 'is-visible');
        if (type === 'error') {
            flash.classList.add('is-error');
        }
        flash.hidden = false;
        flash.classList.add('is-visible');
    }

    function hideFlash(delay = 3000) {
        if (!flash) return;
        setTimeout(() => {
            flash.classList.remove('is-visible', 'is-error');
            flash.hidden = true;
            flash.textContent = '';
        }, delay);
    }

    function toggleCardBody(button) {
        const card = button.closest('.user-card');
        if (!card) return;
        const body = card.querySelector('.card-body');
        if (!body) return;

        const isHidden = body.hasAttribute('hidden');
        if (isHidden) {
            body.removeAttribute('hidden');
            button.setAttribute('aria-expanded', 'true');
        } else {
            body.setAttribute('hidden', '');
            button.setAttribute('aria-expanded', 'false');
        }
    }

    async function submitForm(form, action) {
        if (!endpoint) return;

        const submitBtn = form.querySelector('button[type="submit"]');

        if (action === 'update') {
            const passwordField = form.querySelector('input[name="password"]');
            const oldPasswordField = form.querySelector('input[name="old_password"]');
            const newPassword = passwordField ? passwordField.value.trim() : '';
            const oldPassword = oldPasswordField ? oldPasswordField.value.trim() : '';

            if (newPassword !== '' && oldPassword === '') {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                showFlash('Isi password lama sebelum mengganti password baru.', 'error');
                hideFlash(3200);
                if (oldPasswordField) {
                    oldPasswordField.focus();
                }
                return;
            }
        }

        const formData = new FormData(form);
        formData.append('action', action);
        formData.append('entity', 'user');

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Permintaan gagal diproses');
            }

            showFlash(data.message || 'Berhasil disimpan');
            hideFlash(3000);
            const scrollY = window.scrollY;
            setTimeout(() => {
                window.location.reload();
                window.scrollTo(0, scrollY);
            }, 2500);
        } catch (error) {
            showFlash(error.message || 'Terjadi kesalahan', 'error');
            hideFlash(3200);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    }

    async function handleDelete(button) {
        if (!endpoint) return;
        const noId = button.getAttribute('data-no-id');
        if (!noId) return;

        const card = button.closest('.user-card');
        const username = card ? card.querySelector('h2')?.textContent?.trim() : '';

        if (!confirm(`Hapus user ${username || ''}?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('no_id', noId);
        formData.append('entity', 'user');

        button.disabled = true;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal menghapus user');
            }

            showFlash(data.message || 'User dihapus');
            hideFlash(3000);
            const scrollY = window.scrollY; 
            setTimeout(() => {
                window.location.reload();
                window.scrollTo(0, scrollY);
            }, 2500);
        } catch (error) {
            showFlash(error.message || 'Terjadi kesalahan', 'error');
            hideFlash(3200);
        } finally {
            button.disabled = false;
        }
    }

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => toggleCardBody(button));
    });

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const action = form.dataset.action;
            if (!action) return;
            submitForm(form, action);
        });
    });

    deleteButtons.forEach((button) => {
        button.addEventListener('click', () => handleDelete(button));
    });
});
