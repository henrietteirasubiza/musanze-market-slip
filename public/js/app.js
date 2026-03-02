// app.js — client-side helpers for AgriOrder

document.addEventListener('DOMContentLoaded', function () {

    // ---- Auto-compute total on order form ----
    const qtyInput   = document.getElementById('quantity');
    const priceInput = document.getElementById('unit_price');
    const totalEl    = document.getElementById('total_preview');

    function computeTotal() {
        if (!qtyInput || !priceInput || !totalEl) return;
        const qty   = parseFloat(qtyInput.value)   || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = qty * price;
        totalEl.textContent = 'RWF ' + total.toLocaleString('en-RW', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    if (qtyInput)   qtyInput.addEventListener('input', computeTotal);
    if (priceInput) priceInput.addEventListener('input', computeTotal);
    computeTotal(); // run once on load in case form has pre-filled values

    // ---- Print button ----
    const printBtn = document.getElementById('print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    // ---- Auto-dismiss alerts after 4 seconds ----
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });

    // ---- Confirm before deleting ----
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(btn.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // ---- Navigation filter ----
    const navFilter = document.querySelector('.nav-filter');
    const navLinks = document.querySelectorAll('.nav-links li');
    if (navFilter && navLinks.length) {
        navFilter.addEventListener('input', function () {
            const term = navFilter.value.toLowerCase();
            navLinks.forEach(function (li) {
                const text = li.textContent.toLowerCase();
                li.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // ---- Mark active nav link ----
    const path = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-links a').forEach(function (a) {
        if (a.getAttribute('href').endsWith(path)) {
            a.classList.add('active');
        }
    });
});
