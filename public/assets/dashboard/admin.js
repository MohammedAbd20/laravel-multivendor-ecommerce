(function () {
    'use strict';

    // Mobile sidebar
    var body = document.body;
    document.querySelectorAll('[data-sb-toggle]').forEach(function (el) {
        el.addEventListener('click', function () { body.classList.toggle('sb-open'); });
    });
    document.querySelectorAll('.scrim').forEach(function (el) {
        el.addEventListener('click', function () { body.classList.remove('sb-open'); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') body.classList.remove('sb-open'); });

    // One shared confirmation dialog for any <form data-confirm="...">
    var modalEl = document.getElementById('confirmModal');
    if (modalEl && window.bootstrap) {
        var modal = new bootstrap.Modal(modalEl);
        var pending = null;
        document.addEventListener('submit', function (e) {
            var form = e.target;
            var message = form.getAttribute && form.getAttribute('data-confirm');
            if (!message || form.dataset.confirmed === '1') return;
            // data-confirm-only="a,b": only ask when the form's status field is one of these values
            var only = form.getAttribute('data-confirm-only');
            if (only) {
                var fieldName = form.getAttribute('data-confirm-field') || 'status';
                var field = form.querySelector('[name="' + fieldName + '"]') || document.querySelector('[form="' + form.id + '"][name="' + fieldName + '"]');
                if (!field || only.split(',').indexOf(field.value) === -1) return;
            }
            e.preventDefault();
            pending = form;
            modalEl.querySelector('[data-confirm-message]').textContent = message;
            var btn = modalEl.querySelector('[data-confirm-ok]');
            btn.textContent = form.getAttribute('data-confirm-label') || 'Confirm';
            modal.show();
        });
        modalEl.querySelector('[data-confirm-ok]').addEventListener('click', function () {
            if (!pending) return;
            pending.dataset.confirmed = '1';
            modal.hide();
            pending.submit();
        });
    }

    // Bulk selection: <input data-check-all="group"> controls <input data-check="group">
    document.querySelectorAll('[data-check-all]').forEach(function (master) {
        var group = master.getAttribute('data-check-all');
        var boxes = function () { return document.querySelectorAll('[data-check="' + group + '"]'); };
        var bar = document.querySelector('[data-bulk-bar="' + group + '"]');
        var update = function () {
            var n = Array.prototype.filter.call(boxes(), function (b) { return b.checked; }).length;
            if (bar) {
                bar.hidden = n === 0;
                var c = bar.querySelector('[data-bulk-count]');
                if (c) c.textContent = n + ' selected';
            }
            master.checked = n > 0 && n === boxes().length;
            master.indeterminate = n > 0 && n < boxes().length;
        };
        master.addEventListener('change', function () {
            boxes().forEach(function (b) { b.checked = master.checked; });
            update();
        });
        boxes().forEach(function (b) { b.addEventListener('change', update); });
        update();
    });
})();
