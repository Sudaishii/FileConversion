document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#recordsTable tbody');
    const search = document.getElementById('search');
    const btnRefresh = document.getElementById('btnRefresh');
    const btnNew = document.getElementById('btnNew');

    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');

    async function fetchList() {
        const res = await fetch('admin.php?action=list');
        const data = await res.json();
        return data;
    }

    function renderTable(rows) {
        tableBody.innerHTML = '';
        rows.forEach(r => {
            const tr = document.createElement('tr');
            const created = r.created_at ? new Date(r.created_at).toLocaleString() : '';
            tr.innerHTML = `
                <td>${r.account_number}</td>
                <td>${escapeHtml(r.first_name)}</td>
                <td>${escapeHtml(r.last_name)}</td>
                <td>${r.mobile_number || ''}</td>
                <td>${escapeHtml(r.email_add || '')}</td>
                <td>${r.dob || ''}</td>
                <td>${created}</td>
                <td class="admin-actions">
                    <button data-action="edit" data-account="${r.account_number}">Edit</button>
                    <button data-action="delete" data-account="${r.account_number}" style="background:#d32f2f;">Delete</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function escapeHtml(s) {
        if (!s) return '';
        return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]));
    }

    async function loadAndRender() {
        const rows = await fetchList();
        renderTable(rows);
    }

    // Inject and wire the admin edit/create form from the template
    function renderAdminForm() {
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = '';
        const tpl = document.getElementById('adminFormTemplate');
        if (!tpl) { console.error('adminFormTemplate not found'); return; }
        const clone = tpl.content.cloneNode(true);
        modalBody.appendChild(clone);

        const adminForm = modalBody.querySelector('#adminForm');
        const btnCancel = modalBody.querySelector('#btnCancel');

        if (btnCancel) btnCancel.addEventListener('click', () => closeModal());

        if (adminForm) {
            let isSubmittingAdmin = false;
            adminForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (isSubmittingAdmin) { console.log('[adminForm] submission blocked: already in progress'); return; }
                isSubmittingAdmin = true;
                const submitButtons = adminForm.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitButtons.forEach(b => b.disabled = true);
                try {
                    const fd = new FormData(adminForm);
                    const account = fd.get('account_number');
                    const action = account ? 'update' : 'create';
                    const res = await fetch(`admin.php?action=${action}`, { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        closeModal();
                        await loadAndRender();
                        alert('Saved');
                    } else {
                        alert('Error: ' + (data.error || 'Unknown'));
                    }
                } catch (err) {
                    console.error('[adminForm] save failed', err);
                    alert('Error saving record');
                } finally {
                    isSubmittingAdmin = false;
                    submitButtons.forEach(b => b.disabled = false);
                }
            });
        }

        // focus first input for accessibility
        const firstInput = modalBody.querySelector('input, select, textarea');
        if (firstInput) firstInput.focus();
    }


    function disableProtectedFields(container, data = {}) {
        const toDisable = ['#account_number', '#firstname', '#middlename', '#lastname', '#suffix', '#dob', '#sex', '#nationality', '#pob'];
        toDisable.forEach(sel => {
            const el = container.querySelector(sel);
            if (!el) return;
            if (el.tagName === 'SELECT') {
                const val = el.value;
                el.setAttribute('disabled', 'disabled');
                const h = document.createElement('input');
                h.type = 'hidden';
                h.name = el.name;
                h.value = val;
                container.querySelector('form').appendChild(h);
            } else {
                el.readOnly = true;
                el.classList.add('readonly-field');
            }
        });

        // For beneficiaries: if data.beneficiaries exists, keep inputs editable so admin can update them.
        // If no beneficiaries, leave inputs empty and editable (admin can add them via the form submit flow).
        const benExists = data.beneficiaries && Object.keys(data.beneficiaries).length > 0;
        const benSelectors = ['#spouse_firstname','#spouse_lastname','#spouse_middlename','#child1_firstname','#child1_middlename','#child1_lastname','#child1_dob','#other_benef1_firstname','#other_benef1_middlename','#other_benef1_lastname','#other_benef1_relationship','#other_benef1_dob'];
        benSelectors.forEach(sel => {
            const el = container.querySelector(sel);
            if (!el) return;
            // make editable only if there is existing beneficiaries data (per your request)
            if (benExists) {
                el.readOnly = false;
                el.classList.remove('readonly-field');
            } else {
                // allow admin to add beneficiaries if desired (editable)
                el.readOnly = false;
                el.classList.remove('readonly-field');
            }
        });

        // Ensure the fields you wanted editable remain editable: civil_status, home_address, phone, email
        const editable = ['#civil_status','#home_address','#phone','#email','#created_at'];
        editable.forEach(sel => {
            const el = container.querySelector(sel);
            if (!el) return;
            if (el.tagName === 'SELECT') {
                el.removeAttribute('disabled');
            } else {
                el.readOnly = false;
                el.classList.remove('readonly-field');
            }
        });
    }

    tableBody.addEventListener('click', async (e) => {
        // if a button was clicked, handle action
        const btn = e.target.closest('button');
        if (btn) {
            const action = btn.dataset.action;
            const account = btn.dataset.account;
            if (action === 'edit') {
                openEdit(account);
            } else if (action === 'delete') {
                if (!confirm('Delete this record?')) return;
                await deleteRecord(account);
                await loadAndRender();
            }
            return;
        }

        // otherwise, open full details when a row is clicked
        const tr = e.target.closest('tr');
        if (!tr) return;
        const firstCell = tr.querySelector('td');
        if (!firstCell) return;
        const account = firstCell.textContent.trim();
        openEdit(account);
    });

    search.addEventListener('input', async () => {
        const q = search.value.trim().toLowerCase();
        const rows = await fetchList();
        const filtered = rows.filter(r => (
            String(r.account_number).includes(q) ||
            (r.first_name && r.first_name.toLowerCase().includes(q)) ||
            (r.last_name && r.last_name.toLowerCase().includes(q)) ||
            (r.mobile_number && r.mobile_number.includes(q))
        ));
        renderTable(filtered);
    });

    btnRefresh.addEventListener('click', loadAndRender);
    btnNew.addEventListener('click', () => openCreate());



    function openModal() {
        modal.style.display = 'flex';
        // prevent background scrolling while modal is open
        document.body.style.overflow = 'hidden';
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) modalContent.scrollTop = 0;
    }
    function closeModal() {
        modal.style.display = 'none';
        // re-enable background scrolling
        document.body.style.overflow = '';
        const modalBody = document.getElementById('modalBody');
        if (modalBody) modalBody.innerHTML = '';
        modalTitle.textContent = '';
    }

    // Close modal on Escape key when it's open (admin convenience)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });

    async function openEdit(account) {
        modalTitle.textContent = 'Edit Record';
        // render admin form in modal
        renderAdminForm();
        const modalBody = document.getElementById('modalBody');
        try {
            const res = await fetch(`admin.php?action=get&account=${encodeURIComponent(account)}`);
            const data = await res.json();
            if (data.error) { alert(data.error); return; }
            fillForm(modalBody, data);
            // disable protected fields, but allow editing of these: civil_status, home_address, phone, email
            disableProtectedFields(modalBody, data);
            openModal();
        } catch (err) {
            console.error('Failed to load record:', err);
            alert('Failed to load record');
        }
    }

    async function openCreate() {
        modalTitle.textContent = 'Create New Record';
        const modalBody = document.getElementById('modalBody');
        // load the registration form from the public index page and inject into modal
        try {
            const res = await fetch('../index.html');
            const text = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const regForm = doc.querySelector('#registrationForm');
            if (regForm && modalBody) {
                modalBody.innerHTML = ''; // clear existing content
                const imported = document.importNode(regForm, true);
                modalBody.appendChild(imported);

                // Add a modal Cancel button so admin can close the New overlay
                if (!modalBody.querySelector('#modalCancelBtn')) {
                    const cancelWrap = document.createElement('div');
                    cancelWrap.style.cssText = 'display:flex;gap:0.6rem;justify-content:flex-end;margin-top:1rem;';
                    cancelWrap.innerHTML = `<button type="button" id="modalCancelBtn" class="submit-btn" style="background:#999;">Cancel</button>`;
                    modalBody.appendChild(cancelWrap);
                    const btnModalCancel = modalBody.querySelector('#modalCancelBtn');
                    if (btnModalCancel) btnModalCancel.addEventListener('click', () => closeModal());
                }

                // initialize form behavior and provide an onSuccess callback to close modal & refresh list
                if (window.initRegistrationForm) {
                    window.initRegistrationForm(modalBody, {
                        onSuccess: async () => {
                            closeModal();
                            await loadAndRender();
                            alert('Successfully registered');
                        }
                    });
                }
                // focus first input for quick data entry
                const firstInput = modalBody.querySelector('input, select, textarea');
                if (firstInput) firstInput.focus();
                openModal();
            } else {
                // fallback: render admin form template
                renderAdminForm();
                openModal();
            }
        } catch (err) {
            console.error('Failed to load registration form:', err);
            // fallback: render admin form
            renderAdminForm();
            openModal();
        }
    }

    function fillForm(container, data) {
        if (!container || !data) return;
        const set = (selector, value) => {
            const el = container.querySelector(selector);
            if (el) el.value = value || '';
        };

        set('#account_number', data.account_number || '');
        set('#firstname', data.first_name || '');
        set('#middlename', data.middle_name || '');
        set('#lastname', data.last_name || '');
        set('#phone', data.mobile_number || '');
        set('#email', data.email_add || '');
        set('#dob', data.dob || '');
        set('#home_address', data.home_address || '');
        set('#pob', data.pob || '');

        // render related data sections
        const ben = data.beneficiaries || {};
        const ov = data.overseas || {};
        const cert = data.certification || {};

        // beneficiaries inputs (if present)
        const setVal = (selector, value) => {
            const el = container.querySelector(selector);
            if (el) el.value = value || '';
        };

        setVal('#spouse_firstname', ben.spouse_fname || ben.spouse_firstname || '');
        setVal('#spouse_lastname', ben.spouse_lname || ben.spouse_lastname || '');
        setVal('#spouse_middlename', ben.spouse_mname || ben.spouse_middlename || '');

        setVal('#child1_firstname', ben.child_fname || '');
        setVal('#child1_middlename', ben.child_mname || '');
        setVal('#child1_lastname', ben.child_lname || '');
        setVal('#child1_dob', ben.dob || '');

        setVal('#other_benef1_firstname', ben.other_fname || '');
        setVal('#other_benef1_middlename', ben.other_mname || '');
        setVal('#other_benef1_lastname', ben.other_lname || '');
        setVal('#other_benef1_relationship', ben.relation || '');
        setVal('#other_benef1_dob', ben.dob || '');

        // overseas & certification partial display (keep read-only preview)
        const overseasSection = container.querySelector('#overseasSection');
        if (overseasSection) {
            overseasSection.innerHTML = `
                <h5>Overseas / SE</h5>
                <div class="Account-Details">
                    <div class="field"><label>Profession/Business</label><input readonly value="${escapeHtml(ov.profession_business||'')}"></div>
                    <div class="field"><label>Foreign Address</label><input readonly value="${escapeHtml(ov.foreign_address||'')}"></div>
                    <div class="field"><label>Monthly Earning</label><input readonly value="${escapeHtml(String(ov.monthly_earning||''))}"></div>
                </div>
            `;
        }

        const certificationSection = container.querySelector('#certificationSection');
        if (certificationSection) {
            certificationSection.innerHTML = `
                <h5>Certification & Biometric</h5>
                <div class="Account-Details">
                    <div class="field"><label>Printed Name</label><input readonly value="${escapeHtml(cert.printedName_path||'')}"></div>
                    <div class="field"><label>Signature</label><input readonly value="${escapeHtml(String(cert.singature_path||''))}"></div>
                    <div class="field"><label>Thumb/Index</label><input readonly value="${escapeHtml(cert.thumb_path||'')} ${escapeHtml(cert.index_path||'')}"></div>
                </div>
            `;
        }

        // created_at
        setVal('#created_at', data.created_at || '');
    }



    async function deleteRecord(account) {
        const fd = new FormData();
        fd.append('account_number', account);
        const res = await fetch('admin.php?action=delete', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) alert('Delete failed: ' + (data.error || 'Unknown'));
    }

    // initial load
    loadAndRender();
});