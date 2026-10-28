// UniBus - small UI helpers (no libraries needed)

// Mobile menu toggle
document.querySelectorAll('.nav-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var links = document.getElementById('nav-links');
        var open = links.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
});

// Ask before dangerous actions: <form data-confirm="Are you sure?">
document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        if (!window.confirm(form.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
});

// Live price on the booking form
var seatsInput = document.querySelector('[data-seat-price]');
if (seatsInput) {
    var out = document.getElementById('live-total');
    var price = parseFloat(seatsInput.getAttribute('data-seat-price'));
    var update = function () {
        var n = parseInt(seatsInput.value, 10) || 0;
        out.textContent = (n * price).toFixed(3) + ' OMR';
    };
    seatsInput.addEventListener('input', update);
    update();
}

// Payment method cards: highlight the selected one
document.querySelectorAll('.pay-option input').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.pay-option').forEach(function (o) { o.classList.remove('selected'); });
        radio.closest('.pay-option').classList.add('selected');
    });
});

// Hide success messages after a few seconds
setTimeout(function () {
    document.querySelectorAll('.alert-success').forEach(function (a) {
        a.style.transition = 'opacity .4s';
        a.style.opacity = '0';
        setTimeout(function () { a.remove(); }, 400);
    });
}, 5000);
