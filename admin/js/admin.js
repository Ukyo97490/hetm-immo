/**
 * H&M Immobilier — Admin JS (vanilla)
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar toggle (mobile) ─────────────────────────
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const menuBtn  = document.getElementById('topbarMenuBtn');
    const closeBtn = document.getElementById('sidebarToggle');

    const openSidebar  = () => { sidebar?.classList.add('open'); overlay?.classList.add('active'); };
    const closeSidebar = () => { sidebar?.classList.remove('open'); overlay?.classList.remove('active'); };

    menuBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // ── Flash auto-dismiss ──────────────────────────────
    const flash = document.getElementById('flashMsg');
    if (flash) {
        flash.querySelector('.flash-close')?.addEventListener('click', () => flash.remove());
        setTimeout(() => {
            flash.style.transition = 'opacity 0.4s, transform 0.4s';
            flash.style.opacity   = '0';
            flash.style.transform = 'translateY(-8px)';
            setTimeout(() => flash.remove(), 450);
        }, 4000);
    }

    // ── Confirmation Modal ──────────────────────────────
    const modalOverlay  = document.getElementById('confirmModal');
    const modalConfirm  = document.getElementById('modalConfirmBtn');
    let   pendingAction = null;

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
    modalOverlay?.addEventListener('click', (e) => {
        if (e.target === modalOverlay) { pendingAction = null; modalOverlay.classList.remove('active'); }
    });

    // ── File Upload Preview ─────────────────────────────
    document.querySelectorAll('.upload-zone').forEach(zone => {
        const fileInput = zone.querySelector('input[type="file"]');
        const preview   = zone.closest('.form-group')?.querySelector('.image-preview');

        zone.addEventListener('click', () => fileInput?.click());

        fileInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            if (!['image/jpeg','image/png','image/webp','image/gif'].includes(file.type)) {
                alert('Type non autorisé. Utilisez JPG, PNG, WebP ou GIF.');
                fileInput.value = ''; return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Fichier too grand (max 5 Mo).'); fileInput.value = ''; return;
            }
            if (preview) {
                const reader = new FileReader();
                reader.onload = (ev) => { preview.src = ev.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(file);
            }
            const label = zone.querySelector('p');
            if (label) label.textContent = file.name;
        });

        // Drag & Drop
        zone.addEventListener('dragover',  (e) => { e.preventDefault(); zone.style.borderColor = 'var(--primary)'; });
        zone.addEventListener('dragleave', () => { zone.style.borderColor = ''; });
        zone.addEventListener('drop', (e) => {
            e.preventDefault(); zone.style.borderColor = '';
            const file = e.dataTransfer.files?.[0];
            if (file && fileInput) {
                const dt = new DataTransfer(); dt.items.add(file);
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    });

    // ── Radio visual selection ──────────────────────────
    document.querySelectorAll('.radio-row').forEach(row => {
        row.querySelectorAll('.radio-item').forEach(item => {
            const radio = item.querySelector('input[type="radio"]');
            item.addEventListener('click', () => {
                row.querySelectorAll('.radio-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change', {bubbles:true})); }
            });
            if (radio?.checked) item.classList.add('selected');
        });
    });
});
