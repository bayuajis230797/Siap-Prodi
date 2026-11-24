<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-pen"></i> Tulis Pesan Baru</h3>
            </div>
            <div class="card-body">
                <?php echo form_open('Chat/send'); ?>
                    <input type="hidden" name="mode" value="direct">
                    <div class="form-group">
                        <label>Penerima <span class="text-danger">*</span></label>
                        <input type="hidden" name="recipient_user_id" id="recipient_user_id" required>
                        <input type="text" class="form-control" id="recipient_search" placeholder="Ketik NIM, nama, atau username..." autocomplete="off">
                        <div id="recipient_results" class="list-group mt-1" style="display:none; max-height:200px; overflow-y:auto;"></div>
                        <small class="text-muted">Pilih penerima dari daftar suggestion.</small>
                    </div>
                    <div class="form-group">
                        <label>Isi Pesan <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="5" required></textarea>
                    </div>
                    <?php if ($role !== 'mahasiswa'): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_priority" value="1" id="is_priority_direct">
                        <label class="form-check-label" for="is_priority_direct">Pesan penting (pop-up wajib dibaca saat login mahasiswa)</label>
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim</button>
                    <a href="<?php echo site_url('Chat'); ?>" class="btn btn-secondary">Batal</a>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var input = document.getElementById('recipient_search');
    var hidden = document.getElementById('recipient_user_id');
    var results = document.getElementById('recipient_results');
    if (!input) return;

    var timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        var q = input.value.trim();
        hidden.value = '';
        if (q.length < 2) {
            results.style.display = 'none';
            return;
        }
        timer = setTimeout(function() {
            fetch('<?php echo site_url('Chat/search_users'); ?>?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    results.innerHTML = '';
                    if (!data.success || !data.data.length) {
                        results.innerHTML = '<div class="list-group-item text-muted">Tidak ditemukan</div>';
                        results.style.display = 'block';
                        return;
                    }
                    data.data.forEach(function(item) {
                        var a = document.createElement('button');
                        a.type = 'button';
                        a.className = 'list-group-item list-group-item-action';
                        a.textContent = item.label + ' [' + item.role + ']';
                        a.addEventListener('click', function() {
                            hidden.value = item.id;
                            input.value = item.label;
                            results.style.display = 'none';
                        });
                        results.appendChild(a);
                    });
                    results.style.display = 'block';
                });
        }, 300);
    });
})();
</script>
