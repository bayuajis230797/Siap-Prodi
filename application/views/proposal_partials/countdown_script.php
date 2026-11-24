<script>
(function () {
    function pad(n) { return String(n).padStart(2, '0'); }
    function tick(el) {
        var diff = parseInt(el.getAttribute('data-timestamp'), 10) * 1000 - Date.now();
        var set = function (u, v) { var x = el.querySelector('[data-unit="' + u + '"]'); if (x) { x.textContent = v; } };
        var st = el.querySelector('[data-status]');
        if (diff <= 0) {
            set('hari', '0'); set('jam', '00'); set('menit', '00'); set('detik', '00');
            if (st) { st.textContent = 'Sidang telah dimulai atau selesai.'; }
            return;
        }
        set('hari', Math.floor(diff / 86400000));
        set('jam', pad(Math.floor(diff % 86400000 / 3600000)));
        set('menit', pad(Math.floor(diff % 3600000 / 60000)));
        set('detik', pad(Math.floor(diff % 60000 / 1000)));
        if (st) { st.textContent = ''; }
    }
    function run() { document.querySelectorAll('.proposal-countdown').forEach(tick); }
    run();
    setInterval(run, 1000);
})();
</script>
