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

    /* --- Modal handling for create/edit --- */
    function createModalNode() {
        const tpl = document.createElement('template');
        tpl.innerHTML = `
            <div class="user-modal" role="dialog" aria-modal="true" hidden>
                <div class="user-modal-backdrop"></div>
                <div class="user-modal-panel" tabindex="-1">
                    <header class="modal-header">
                        <h3 class="modal-title">Modal</h3>
                        <button class="modal-close btn btn-secondary">×</button>
                    </header>
                    <div class="modal-body">
                        <form id="userModalForm">
                            <input type="hidden" name="no_id" value="">
                            <div class="form-field">
                                <label>Username</label>
                                <input type="text" name="username" required autocomplete="off">
                            </div>
                            <div class="form-field create-only">
                                <label>Password <span>(minimal 6 karakter)</span></label>
                                <input type="password" name="password" minlength="6" placeholder="Masukkan password">
                            </div>
                            <div class="form-field edit-only" style="display:none;">
                                <label>Password lama <span>(wajib saat mengganti)</span></label>
                                <input type="password" name="old_password" minlength="6" placeholder="Masukkan password sekarang jika ingin ganti">
                            </div>
                            <div class="form-field edit-only" style="display:none;">
                                <label>Password baru <span>(opsional)</span></label>
                                <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak diganti">
                            </div>
                            <input type="hidden" name="role" value="user">
                            <div class="form-actions modal-actions">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <button type="button" class="btn btn-secondary modal-cancel">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `.trim();
        return tpl.content.firstElementChild;
    }

    let modal = null;

    function ensureModal() {
        if (!modal) {
            modal = createModalNode();
            document.body.appendChild(modal);

            // close handlers
            modal.querySelector('.modal-close').addEventListener('click', () => closeModal());
            modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal());
            modal.querySelector('.user-modal-backdrop').addEventListener('click', () => closeModal());

            // form submit -> map to submitForm
            const mform = modal.querySelector('#userModalForm');
            mform.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const formEl = ev.target;
                const noId = formEl.querySelector('input[name="no_id"]').value;
                const action = noId ? 'update' : 'create';
                (async () => {
                    const ok = await submitForm(formEl, action);
                    if (ok) closeModal();
                })();
            });

            // keyboard accessibility: close on ESC
            modal.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        }
        return modal;
    }

    function openCreateModal() {
        const m = ensureModal();
        m.querySelector('.modal-title').textContent = 'Buat User Baru';
        const form = m.querySelector('#userModalForm');
        form.reset();
        form.querySelector('input[name="no_id"]').value = '';
        form.querySelector('input[name="role"]').value = 'user';
        // ensure create-only password visible and required; hide and disable edit-only fields
        const createFields = Array.from(form.querySelectorAll('.create-only'));
        const editFields = Array.from(form.querySelectorAll('.edit-only'));
        createFields.forEach((el) => {
            el.style.display = '';
            const input = el.querySelector('input');
            if (input) { input.required = true; input.disabled = false; }
        });
        editFields.forEach((el) => {
            el.style.display = 'none';
            const inputs = el.querySelectorAll('input');
            inputs.forEach((inp) => { inp.required = false; inp.value = ''; inp.disabled = true; });
        });
        m.hidden = false;
        document.documentElement.classList.add('modal-open');
        requestAnimationFrame(() => {
            m.classList.add('open');
            // autofocus the first input
            const first = m.querySelector('input[name="username"]');
            if (first) first.focus();
        });
    }

    function openEditModal(userId) {
        const card = userGrid.querySelector(`.user-card[data-user-id="${escapeSelector(userId)}"]`);
        if (!card) return;
        const username = card.querySelector('h2')?.textContent?.trim() || '';
        const m = ensureModal();
        m.querySelector('.modal-title').textContent = 'Edit User';
        const form = m.querySelector('#userModalForm');
        form.querySelector('input[name="no_id"]').value = userId;
        form.querySelector('input[name="username"]').value = username;
        form.querySelector('input[name="role"]').value = card.dataset.role || 'user';
        // show edit-only fields and hide/disable create-only password
        const createFields = Array.from(form.querySelectorAll('.create-only'));
        const editFields = Array.from(form.querySelectorAll('.edit-only'));
        createFields.forEach((el) => {
            el.style.display = 'none';
            const input = el.querySelector('input[name="password"]');
            if (input) { input.required = false; input.value = ''; input.disabled = true; }
        });
        editFields.forEach((el) => {
            el.style.display = '';
            const inputs = el.querySelectorAll('input');
            inputs.forEach((inp) => { inp.disabled = false; });
        });
        m.hidden = false;
        requestAnimationFrame(() => m.classList.add('open'));
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('open');
        document.documentElement.classList.remove('modal-open');
        setTimeout(() => {
            if (modal) modal.hidden = true;
        }, 250);
    }

    /* Confirm modal utility (promise-based) */
    function confirmModal(title, message) {
        return new Promise((resolve) => {
            const tpl = document.createElement('template');
            tpl.innerHTML = `
                <div class="user-modal confirm-modal" role="dialog" aria-modal="true">
                    <div class="user-modal-backdrop"></div>
                    <div class="user-modal-panel">
                        <header class="modal-header">
                            <h3 class="modal-title">${title}</h3>
                        </header>
                        <div class="modal-body">
                            <p>${message}</p>
                            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                                <button class="btn btn-secondary confirm-cancel">Batal</button>
                                <button class="btn btn-danger confirm-ok">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            `.trim();
            const node = tpl.content.firstElementChild;
            document.body.appendChild(node);
            // lock background while confirm open
            document.documentElement.classList.add('modal-open');
            // show panel animation (match ensureModal behaviour)
            node.classList.add('open');
            const backdrop = node.querySelector('.user-modal-backdrop');
            const ok = node.querySelector('.confirm-ok');
            const cancel = node.querySelector('.confirm-cancel');

            function cleanup(result) {
                node.remove();
                document.documentElement.classList.remove('modal-open');
                resolve(result);
            }

            ok.addEventListener('click', () => cleanup(true));
            cancel.addEventListener('click', () => cleanup(false));
            backdrop.addEventListener('click', () => cleanup(false));
            // autofocus cancel for easier keyboard dismiss
            if (cancel) cancel.focus();
        });
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
                ${isSelf && !isAdmin ? '<span class="self-chip" title="Akun Anda"><i class="fa-solid fa-circle-user"></i> Anda</span>' : ''}
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
            <div class="card-body compact" id="${bodyId}">
                <div class="user-summary">
                    <div class="summary-left">
                        <!-- username shown in header -->
                    </div>
                </div>
            </div>
        `;

        const actionsHtml = isAdmin ? '' : `
            <div class="card-actions">
                <button class="btn btn-icon btn-edit" data-action="edit" data-user-id="${escapeHTML(user.no_id)}" aria-label="Edit ${escapeHTML(user.username)}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                </button>
                <button class="btn btn-icon btn-delete" data-action="delete" data-no-id="${escapeHTML(user.no_id)}" aria-label="Hapus ${escapeHTML(user.username)}">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                </button>
            </div>
        `;

        return `
            <article class="user-card${isAdmin ? ' user-card--admin' : ''}" data-user-id="${escapeHTML(user.no_id)}" data-role="${escapeHTML(user.role)}">
                <header class="card-header">
                    <div class="card-title-row">
                        <h2>${escapeHTML(user.username)}</h2>
                        ${headerMeta}
                    </div>
                    ${actionsHtml}
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
                    },
                    credentials: 'same-origin'
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
            return true;
        } catch (error) {
            showFlash(error.message || 'Terjadi kesalahan', 'error');
            hideFlash(3200);
            return false;
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

        const confirmed = await confirmModal('Konfirmasi Hapus', `Hapus user ${username || ''}?`);
        if (!confirmed) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('no_id', noId);
        formData.append('entity', 'user');

    // show spinner state
    const originalBtnHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span>';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const data = await parseResponse(response);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal menghapus user');
            }

            const deletedId = data.data?.deleted_id ?? noId;
            // animate removal for UX
            const sel = `.user-card[data-user-id="${escapeSelector(deletedId)}"]`;
            const cardToRemove = userGrid.querySelector(sel);
            if (cardToRemove) {
                cardToRemove.classList.add('user-card--removing');
                setTimeout(() => deleteUserCard(deletedId), 260);
            } else {
                deleteUserCard(deletedId);
            }

            showFlash(data.message || 'User dihapus');
            hideFlash(2600);
        } catch (error) {
            showFlash(error.message || 'Terjadi kesalahan', 'error');
            hideFlash(3200);
        } finally {
            // restore button
            button.disabled = false;
            if (button) button.innerHTML = originalBtnHtml;
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

    // Create button (open modal)
    const openCreateBtn = document.getElementById('openCreateUser');
    if (openCreateBtn) {
        openCreateBtn.addEventListener('click', (ev) => {
            ev.preventDefault();
            openCreateModal();
        });
    }

    // Edit buttons inside grid
    userGrid.addEventListener('click', (ev) => {
        const editBtn = ev.target.closest('[data-action="edit"]');
        if (editBtn) {
            ev.preventDefault();
            const uid = editBtn.getAttribute('data-user-id');
            openEditModal(uid);
            return;
        }
    });
});
