/**
 * H&M Immobilier — Admin JS (vanilla)
 * Sidebar toggle, flash messages, confirmation modal, upload preview
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar toggle (mobile) ─────────────────────────
    const sidebar      = document.getElementById('sidebar');
    const overlay      = document.getElementById('sidebarOverlay');
    const menuBtn      = document.getElementById('topbarMenuBtn');
    const closeBtn     = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('active');
    }

    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('active');
    }

    menuBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // ── Flash message auto-dismiss (4s) ─────────────────
    const flash = document.getElementById('flashMsg');
    if (flash) {
        const closeFlash = flash.querySelector('.flash-close');
        closeFlash?.addEventListener('click', () => flash.remove());

        setTimeout(() => {
            flash.style.transition = 'opacity 0.4s, transform 0.4s';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-8px)';
            setTimeout(() => flash.remove(), 450);
        }, 4000);
    }

    // ── Confirmation Modal ──────────────────────────────
    const modalOverlay = document.getElementById('confirmModal');
    const modalConfirm = document.getElementById('modalConfirmBtn');
    let   pendingAction = null;

    /**
     * Ouvre le modal de confirmation.
     * @param {string} message  - texte affiché
     * @param {Function} onConfirm - callback si confirmé
     */
    window.confirmAction = function(message, onConfirm) {
        const msgEl = modalOverlay?.querySelector('.modal p');
        if (msgEl) msgEl.textContent = message;
        pendingAction = onConfirm;
        modalOverlay?.classList.add('active');
    };

    modalConfirm?.addEventListener('click', () => {
        if (typeof pendingAction === 'function') pendingAction();
        pendingAction = null;
        modalOverlay?.classList.remove('active');
    });

    document.getElementById('modalCancelBtn')?.addEventListener('click', () => {
        pendingAction = null;
        modalOverlay?.classList.remove('active');
    });

    // Ferme le modal en cliquant l'overlay
    modalOverlay?.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            pendingAction = null;
            modalOverlay.classList.remove('active');
        }
    });

    // ── File Upload Preview ─────────────────────────────
    document.querySelectorAll('.upload-zone').forEach(zone => {
        const fileInput = zone.querySelector('input[type="file"]');
        const preview   = zone.closest('.form-group')?.querySelector('.image-preview');

        zone.addEventListener('click', () => fileInput?.click());

        fileInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;

            // Vérifie le type côté client (double-check)
            const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!allowed.includes(file.type)) {
                alert('Type de fichier non autorisé. Utilisez JPG, PNG, WebP ou GIF.');
                fileInput.value = '';
                return;
            }

            // Vérifie la taille (5Mo max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier dépasse la taille maximale autorisée (5 Mo).');
                fileInput.value = '';
                return;
            }

            // Affiche l'aperçu
            if (preview) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            // Met à jour le texte de la zone
            const label = zone.querySelector('p');
            if (label) label.textContent = file.name;
        });

        // Drag & Drop
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.style.borderColor = 'var(--primary)';
        });

        zone.addEventListener('dragleave', () => {
            zone.style.borderColor = '';
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.style.borderColor = '';
            const file = e.dataTransfer.files?.[0];
            if (file && fileInput) {
                // Simule la sélection via DataTransfer
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    });

    // ── Radio items (sélection visuelle) ────────────────
    document.querySelectorAll('.radio-row').forEach(row => {
        const items = row.querySelectorAll('.radio-item');
        items.forEach(item => {
            const radio = item.querySelector('input[type="radio"]');
            item.addEventListener('click', () => {
                items.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            // Marque l'item initial si le radio est déjà coché
            if (radio?.checked) item.classList.add('selected');
        });
    });

    // ── Suppression de bien (formulaire POST caché) ────
    document.querySelectorAll('[data-delete-url]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const url  = btn.dataset.deleteUrl;
            const name = btn.dataset.deleteName || 'cet élément';

            window.confirmAction(
                `Êtes-vous sûr de vouloir supprimer « ${name} » ? Cette action est irréversible.`,
                () => {
                    // Crée un formulaire POST avec le CSRF token
                    const csrf = document.querySelector('input[name="csrf_token"]')?.value
                              || btn.dataset.csrf || '';
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    const csrfInput = document.createElement('input');
                    csrfInput.type  = 'hidden';
                    csrfInput.name  = 'csrf_token';
                    csrfInput.value = csrf;
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type  = 'hidden';
                    methodInput.name  = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            );
        });
    });

});
