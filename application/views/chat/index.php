<style>
.wa-chat-shell{display:flex;height:calc(100vh - 180px);min-height:480px;border-radius:12px;overflow:hidden;border:1px solid #e4e6eb;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.wa-chat-list{width:100%;max-width:100%;display:flex;flex-direction:column;background:#fff}
.wa-chat-list-head{padding:14px 16px;background:#f0f2f5;border-bottom:1px solid #e9edef;display:flex;align-items:center;justify-content:space-between;gap:12px}
.wa-chat-list-head h3{margin:0;font-size:1.15rem;font-weight:600;color:#111b21}
.wa-chat-search{padding:8px 12px;background:#f0f2f5}
.wa-chat-search input{border-radius:8px;border:none;background:#fff;box-shadow:inset 0 0 0 1px #e9edef}
.wa-chat-items{flex:1;overflow-y:auto}
.wa-chat-item{display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f2f5;text-decoration:none;color:inherit;transition:background .15s}
.wa-chat-item:hover{background:#f5f6f6;color:inherit;text-decoration:none}
.wa-chat-avatar{width:48px;height:48px;border-radius:50%;background:linear-gradient(145deg,#00a884,#008069);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;font-size:1.05rem}
.wa-chat-meta{flex:1;min-width:0}
.wa-chat-meta-top{display:flex;justify-content:space-between;align-items:baseline;gap:8px;margin-bottom:2px}
.wa-chat-name{font-weight:600;color:#111b21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wa-chat-time{font-size:.72rem;color:#667781;white-space:nowrap}
.wa-chat-preview{display:flex;justify-content:space-between;align-items:center;gap:8px}
.wa-chat-preview-text{font-size:.85rem;color:#667781;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1}
.wa-chat-unread{background:#25d366;color:#fff;border-radius:999px;min-width:20px;height:20px;padding:0 6px;font-size:.7rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center}
.wa-chat-empty{padding:48px 24px;text-align:center;color:#667781}
@media(min-width:992px){.wa-chat-list{max-width:420px;border-right:1px solid #e9edef}.wa-chat-shell{background:#efeae2}.wa-chat-pane{flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#54656f;padding:24px}.wa-chat-pane-icon{width:72px;height:72px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#00a884;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.08)}}
@media(max-width:991.98px){.wa-chat-pane{display:none!important}}
</style>

<div class="wa-chat-shell">
    <div class="wa-chat-list">
        <?php $is_admin_monitor = !empty($is_admin_monitor); ?>
        <div class="wa-chat-list-head">
            <h3>
                <i class="fas fa-comments text-success mr-1"></i>
                <?php echo $is_admin_monitor ? 'Pantau Pesan' : 'Pesan'; ?>
            </h3>
            <a href="<?php echo site_url('Chat/compose'); ?>" class="btn btn-success btn-sm rounded-pill px-3">
                <i class="fas fa-pen"></i> Baru
            </a>
        </div>
        <?php if ($is_admin_monitor): ?>
        <div class="px-3 pt-2">
            <span class="badge badge-info">Mode admin: melihat chat antar semua role</span>
        </div>
        <?php endif; ?>
        <div class="wa-chat-search">
            <?php if ($is_admin_monitor): ?>
                <form method="get" action="<?php echo site_url('Chat'); ?>" class="mb-0">
                    <input type="search" name="q" value="<?php echo htmlspecialchars($chat_search_q ?? ''); ?>" class="form-control form-control-sm" placeholder="Cari semua percakapan / username...">
                </form>
            <?php else: ?>
                <input type="search" id="waChatFilter" class="form-control form-control-sm" placeholder="Cari percakapan...">
            <?php endif; ?>
        </div>
        <div class="wa-chat-items" id="waChatItems">
            <?php if (empty($conversations)): ?>
                <div class="wa-chat-empty">
                    <i class="far fa-comment-dots fa-2x mb-2 d-block"></i>
                    Belum ada percakapan.<br>
                    <a href="<?php echo site_url('Chat/compose'); ?>" class="btn btn-success btn-sm mt-3 rounded-pill">Tulis Pesan</a>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $c): ?>
                    <?php
                    $title = chat_conversation_title($c, $user_id);
                    $initial = mb_strtoupper(mb_substr(trim($title), 0, 1));
                    $preview = $c->last_message_at
                        ? mb_strimwidth(strip_tags($c->last_message ?? ''), 0, 60, '...')
                        : 'Belum ada pesan';
                    $time = $c->last_message_at ? date('d/m H:i', strtotime($c->last_message_at)) : '';
                    ?>
                    <a href="<?php echo site_url('Chat/thread/' . $c->id); ?>" class="wa-chat-item" data-name="<?php echo htmlspecialchars(mb_strtolower($title)); ?>">
                        <div class="wa-chat-avatar"><?php echo htmlspecialchars($initial); ?></div>
                        <div class="wa-chat-meta">
                            <div class="wa-chat-meta-top">
                                <div class="wa-chat-name"><?php echo htmlspecialchars($title); ?></div>
                                <div class="wa-chat-time"><?php echo htmlspecialchars($time); ?></div>
                            </div>
                            <div class="wa-chat-preview">
                                <div class="wa-chat-preview-text"><?php echo htmlspecialchars($preview); ?></div>
                                <?php if ((int) $c->unread_count > 0): ?>
                                    <span class="wa-chat-unread"><?php echo (int) $c->unread_count; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($pagination)): ?>
            <div class="p-2 border-top"><?php echo $pagination; ?></div>
        <?php endif; ?>
    </div>
    <div class="wa-chat-pane d-none d-lg-flex">
        <div class="wa-chat-pane-icon"><i class="fab fa-whatsapp"></i></div>
        <h4 class="mb-1"><?php echo !empty($is_admin_monitor) ? 'Pantau Percakapan' : 'SIAP Pesan'; ?></h4>
        <p class="mb-0 text-center" style="max-width:320px;font-size:.9rem;">
            <?php echo !empty($is_admin_monitor)
                ? 'Sebagai admin Anda dapat membuka percakapan antar semua role (mode pantau).'
                : 'Pilih percakapan di kiri untuk membuka chat, atau tulis pesan baru.'; ?>
        </p>
    </div>
</div>
<script>
(function(){
    var filter = document.getElementById('waChatFilter');
    var items = document.querySelectorAll('#waChatItems .wa-chat-item');
    if (!filter) return;
    filter.addEventListener('input', function(){
        var q = (this.value || '').toLowerCase().trim();
        items.forEach(function(el){
            var name = el.getAttribute('data-name') || '';
            el.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
        });
    });
})();
</script>
