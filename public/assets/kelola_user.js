document.addEventListener('DOMContentLoaded', () => {
    const endpoint = window.USER_ENDPOINT;
    const currentUserId = Number(window.CURRENT_USER_ID || 0);
    const flash = document.getElementById('userFlash');
    const userGrid = document.querySelector('.user-grid');
    const userCountEl = document.querySelector('[data-user-count] .count-number');
    const adminCountEl = document.querySelector('[data-admin-count] .count-number');

    if (!endpoint || !userGrid) {
        return;
    }

    function showFlash(message, type = 'success') {
        if (!flash) return;
        flash.textContent = message;
        flash.classList.remove('is-error', 'is-visible');
        if (type === 'error') {
            flash.classList.add('is-error');
        }
        flash.hidden = false;
        requestAnimationFrame(() => flash.classList.add('is-visible'));
    }

    function hideFlash(delay = 3000) {
        if (!flash) return;
        setTimeout(() => {
            flash.classList.remove('is-visible', 'is-error');
            flash.hidden = true;
            flash.textContent = '';
        }, delay);
    }

    function escapeHTML(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[char]);
    }

    function escapeSelector(value) {
        const stringValue = String(value);
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(stringValue);
        }
        return stringValue.replace(/[^a-zA-Z0-9_-]/g, '\\$&');
    }

    function adjustCounts({ totalDelta = 0, adminDelta = 0 } = {}) {
        if (userCountEl && totalDelta !== 0) {
            const nextValue = Math.max(0, (parseInt(userCountEl.textContent, 10) || 0) + totalDelta);
            userCountEl.textContent = String(nextValue);
        }
        if (adminCountEl && adminDelta !== 0) {
            const nextValue = Math.max(0, (parseInt(adminCountEl.textContent, 10) || 0) + adminDelta);
            adminCountEl.textContent = String(nextValue);
        }
    }

    function createEmptyStateCard() {
        const template = document.createElement('template');
        template.innerHTML = `
            <article class="user-card user-card--empty">
                <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                <p>Belum ada user terdaftar. Tambahkan minimal satu akun User.</p>
            </article>
        `.trim();
        return template.content.firstElementChild;
    }

    function updateEmptyState() {
        const existingCards = userGrid.querySelectorAll('.user-card[data-user-id]').length;
        const emptyCard = userGrid.querySelector('.user-card--empty');

        if (existingCards === 0) {
            if (!emptyCard) {
                const newEmptyCard = createEmptyStateCard();
                userGrid.appendChild(newEmptyCard);
            }
        } else if (emptyCard) {
            emptyCard.remove();
        }
    }

    function buildUserCardMarkup(user) {
        const isAdmin = user.role === 'admin';
        const isSelf = Number(user.no_id) === currentUserId;
        const bodyId = `user-body-${user.no_id}`;

        const roleLabel = user.role === 'admin' ? 'Administrator' : 'User';
        const roleClass = `role-${user.role}`;

        const headerMeta = `
            <div class="card-meta">
                <span class="role-chip ${roleClass}">${escapeHTML(roleLabel)}</span>
                ${isSelf ? '<span class="self-chip" title="Akun Anda"><i class="fa-solid fa-circle-user"></i> Anda</span>' : ''}
            </div>
        `;

        const adminBody = `
            <div class="card-body card-body--static">
                <p class="admin-note">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    Akun administrator bersifat hanya-baca dan tidak dapat diubah dari dashboard.
                </p>
                <p class="admin-note__hint">Hubungi super administrator jika membutuhkan perubahan.</p>
            </div>
        `;

        const userBody = `
            <div class="card-body" id="${bodyId}">
                <form class="user-form" data-action="update">
                    <input type="hidden" name="no_id" value="${escapeHTML(user.no_id)}">
                    <div class="form-field">
                        <label>Username</label>
                        <input type="text" name="username" required value="${escapeHTML(user.username)}">
                    </div>
                    <div class="form-field">
                        <label>Password lama <span>(wajib saat mengganti)</span></label>
                        <input type="password" name="old_password" minlength="6" placeholder="Masukkan password sekarang">
                    </div>
                    <div class="form-field">
                        <label>Password baru <span>(opsional)</span></label>
                        <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak diganti">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span>Simpan</span>
                        </button>
                        <button type="button" class="btn btn-danger" data-action="delete" data-no-id="${escapeHTML(user.no_id)}">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </div>
                </form>
            </div>
        `;

        return `
            <article class="user-card${isAdmin ? ' user-card--admin' : ''}" data-user-id="${escapeHTML(user.no_id)}" data-role="${escapeHTML(user.role)}">
                <header class="card-header">
                    <div class="card-title-row">
                        <h2>${escapeHTML(user.username)}</h2>
                        ${headerMeta}
                    </div>
                </header>
                ${isAdmin ? adminBody : userBody}
            </article>
        `;
    }

    function createUserCardElement(user) {
        const template = document.createElement('template');
        template.innerHTML = buildUserCardMarkup(user).trim();
        return template.content.firstElementChild;
    }

    function highlightCard(card) {
        if (!card) return;
        card.classList.add('user-card--highlight');
        setTimeout(() => card.classList.remove('user-card--highlight'), 1200);
    }

    function upsertUserCard(user) {
        const selector = `.user-card[data-user-id="${escapeSelector(user.no_id)}"]`;
        let card = userGrid.querySelector(selector);
        const isNew = !card;
        const newCard = createUserCardElement(user);

        if (isNew) {
            updateEmptyState();
            const createCard = userGrid.querySelector('.user-card--create');
            if (createCard && createCard.nextSibling) {
                userGrid.insertBefore(newCard, createCard.nextSibling);
            } else {
                userGrid.appendChild(newCard);
            }
            adjustCounts({
                totalDelta: 1,
                adminDelta: user.role === 'admin' ? 1 : 0
            });
        } else if (card) {
            userGrid.replaceChild(newCard, card);
        }

        card = newCard;
        highlightCard(card);
        updateEmptyState();

        if (Number(user.no_id) === currentUserId) {
            const sidebarName = document.querySelector('.sidebar .profile-name');
            if (sidebarName) {
                sidebarName.textContent = user.username;
            }
        }

        return card;
    }

    function deleteUserCard(userId) {
        const selector = `.user-card[data-user-id="${escapeSelector(userId)}"]`;
        const card = userGrid.querySelector(selector);
        if (!card) return;

        const role = card.dataset.role || 'user';
        card.remove();
        adjustCounts({
            totalDelta: -1,
            adminDelta: role === 'admin' ? -1 : 0
        });
        updateEmptyState();
    }

    function parseResponse(response) {
        return response
            .text()
            .then((text) => (text ? JSON.parse(text) : {}))
            .catch(() => ({ success: false, message: 'Respons server tidak valid' }));
    }

    async function submitForm(form, action) {
        const submitBtn = form.querySelector('button[type="submit"]');

        if (action === 'update') {
            const passwordField = form.querySelector('input[name="password"]');
            const oldPasswordField = form.querySelector('input[name="old_password"]');
            const newPassword = passwordField ? passwordField.value.trim() : '';
            const oldPassword = oldPasswordField ? oldPasswordField.value.trim() : '';

            if (newPassword !== '' && oldPassword === '') {
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

            const data = await parseResponse(response);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Permintaan gagal diproses');
            }

            const payload = data.data || {};

            if (action === 'create' && payload.user) {
                upsertUserCard(payload.user);
                form.reset();
            }

            if (action === 'update' && payload.user) {
                upsertUserCard(payload.user);
            }

            showFlash(data.message || 'Berhasil disimpan');
            hideFlash(2600);
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

            const data = await parseResponse(response);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal menghapus user');
            }

            const deletedId = data.data?.deleted_id ?? noId;
            deleteUserCard(deletedId);

            showFlash(data.message || 'User dihapus');
            hideFlash(2600);
        } catch (error) {
            showFlash(error.message || 'Terjadi kesalahan', 'error');
            hideFlash(3200);
        } finally {
            button.disabled = false;
        }
    }

    userGrid.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('[data-action="delete"]');
        if (deleteButton) {
            event.preventDefault();
            handleDelete(deleteButton);
        }
    });

    userGrid.addEventListener('submit', (event) => {
        const form = event.target.closest('.user-form');
        if (!form) return;
        event.preventDefault();
        const action = form.dataset.action;
        if (!action) return;
        submitForm(form, action);
    });

    updateEmptyState();
});
