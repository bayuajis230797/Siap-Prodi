<style>
.wa-thread-shell{display:flex;height:calc(100vh - 180px);min-height:520px;border-radius:12px;overflow:hidden;border:1px solid #e4e6eb;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.wa-thread-side{width:340px;border-right:1px solid #e9edef;display:none;flex-direction:column;background:#fff}
.wa-thread-side-head{padding:14px 16px;background:#f0f2f5;font-weight:600;color:#111b21}
.wa-thread-side-item{display:flex;gap:10px;padding:10px 14px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f2f5}
.wa-thread-side-item:hover,.wa-thread-side-item.active{background:#f0f2f5;color:inherit;text-decoration:none}
.wa-thread-side-item.active{border-left:3px solid #00a884}
.wa-thread-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(145deg,#00a884,#008069);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0}
.wa-thread-main{flex:1;display:flex;flex-direction:column;min-width:0;background:#efeae2}
.wa-thread-head{padding:10px 16px;background:#f0f2f5;border-bottom:1px solid #e9edef;display:flex;align-items:center;justify-content:space-between;gap:12px}
.wa-thread-head-title{font-weight:600;color:#111b21;margin:0;font-size:1rem}
.wa-thread-body{flex:1;overflow-y:auto;padding:18px 16px;background-image:radial-gradient(rgba(0,0,0,.04) 1px, transparent 1px);background-size:18px 18px}
.wa-bubble-row{display:flex;margin-bottom:8px}
.wa-bubble-row.mine{justify-content:flex-end}
.wa-bubble{max-width:min(78%,520px);padding:8px 10px 6px;border-radius:8px;box-shadow:0 1px 1px rgba(0,0,0,.06);position:relative;word-wrap:break-word}
.wa-bubble.theirs{background:#fff;border-top-left-radius:0}
.wa-bubble.mine{background:#d9fdd3;border-top-right-radius:0}
.wa-bubble-sender{font-size:.72rem;font-weight:700;color:#008069;margin-bottom:2px}
.wa-bubble-body{font-size:.92rem;color:#111b21;line-height:1.4}
.wa-bubble-meta{display:flex;justify-content:flex-end;align-items:center;gap:4px;margin-top:4px;font-size:.68rem;color:#667781}
.wa-bubble.mine .wa-bubble-meta{color:#667781}
.wa-thread-compose{background:#f0f2f5;padding:10px 12px;border-top:1px solid #e9edef}
.wa-thread-compose textarea{border-radius:10px;border:none;resize:none;box-shadow:inset 0 0 0 1px #e9edef}
.wa-thread-compose .btn-send{border-radius:50%;width:44px;height:44px;padding:0;background:#00a884;border-color:#00a884}
.wa-thread-compose .btn-send:hover{background:#008069;border-color:#008069}
@media(min-width:992px){.wa-thread-side{display:flex}}
</style>

<?php
$thread_title = isset($page_title) ? $page_title : 'Percakapan';
$thread_initial = mb_strtoupper(mb_substr(trim($thread_title), 0, 1));
$side_conversations = isset($conversations) && is_array($conversations) ? $conversations : array();
?>

<div class="wa-thread-shell">
    <div class="wa-thread-side">
        <div class="wa-thread-side-head d-flex justify-content-between align-items-center">
            <span>Pesan</span>
            <a href="<?php echo site_url('Chat/compose'); ?>" class="btn btn-sm btn-success rounded-pill px-2 py-0" title="Pesan baru"><i class="fas fa-pen"></i></a>
        </div>
        <div style="overflow-y:auto;flex:1;">
            <?php if (!empty($side_conversations)): ?>
                <?php foreach ($side_conversations as $c): ?>
                    <?php
                    $ct = chat_conversation_title($c, $user_id);
                    $ci = mb_strtoupper(mb_substr(trim($ct), 0, 1));
                    $active = ((int) $c->id === (int) $conversation->id);
                    ?>
                    <a href="<?php echo site_url('Chat/thread/' . $c->id); ?>" class="wa-thread-side-item <?php echo $active ? 'active' : ''; ?>">
                        <div class="wa-thread-avatar" style="width:36px;height:36px;font-size:.85rem;"><?php echo htmlspecialchars($ci); ?></div>
                        <div style="min-width:0;flex:1;">
                            <div style="font-weight:600;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($ct); ?></div>
                            <div style="font-size:.75rem;color:#667781;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo htmlspecialchars(mb_strimwidth(strip_tags($c->last_message ?? ''), 0, 40, '...')); ?>
                            </div>
                        </div>
                        <?php if ((int) $c->unread_count > 0 && !$active): ?>
                            <span class="badge badge-success" style="background:#25d366;"><?php echo (int) $c->unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="<?php echo site_url('Chat'); ?>" class="wa-thread-side-item">← Semua pesan</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="wa-thread-main">
        <div class="wa-thread-head">
            <div class="d-flex align-items-center gap-2" style="gap:10px;">
                <a href="<?php echo site_url('Chat'); ?>" class="d-lg-none text-muted mr-1"><i class="fas fa-arrow-left"></i></a>
                <div class="wa-thread-avatar"><?php echo htmlspecialchars($thread_initial); ?></div>
                <div>
                    <h3 class="wa-thread-head-title"><?php echo htmlspecialchars($thread_title); ?></h3>
                    <?php if ($conversation->type === 'broadcast'): ?>
                        <small class="text-muted">Broadcast</small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:6px;">
                <?php if ($conversation->type === 'broadcast'): ?>
                    <a href="<?php echo site_url('Chat/broadcast/' . $conversation->id); ?>" class="btn btn-outline-secondary btn-sm">Statistik</a>
                <?php endif; ?>
                <a href="<?php echo site_url('Chat'); ?>" class="btn btn-outline-secondary btn-sm d-none d-lg-inline-block">Daftar</a>
            </div>
        </div>

        <div class="wa-thread-body" id="chatThreadBox">
            <?php if (empty($messages)): ?>
                <div class="text-center text-muted py-5">Belum ada pesan. Mulai percakapan di bawah.</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $is_mine = ((int) $msg->sender_user_id === $user_id); ?>
                    <div class="wa-bubble-row <?php echo $is_mine ? 'mine' : ''; ?>">
                        <div class="wa-bubble <?php echo $is_mine ? 'mine' : 'theirs'; ?>">
                            <?php if (!$is_mine): ?>
                                <div class="wa-bubble-sender"><?php echo htmlspecialchars(chat_display_name($msg->sender_user_id)); ?></div>
                            <?php endif; ?>
                            <div class="wa-bubble-body"><?php echo nl2br(htmlspecialchars($msg->body)); ?></div>
                            <div class="wa-bubble-meta">
                                <?php if ($msg->is_priority): ?><span class="badge badge-warning mr-1">Penting</span><?php endif; ?>
                                <span><?php echo date('H:i', strtotime($msg->created_at)); ?></span>
                                <?php if ($is_mine && isset($read_map[$msg->id])): ?>
                                    <?php $rc = $read_map[$msg->id]; ?>
                                    <?php if ($rc && $rc->is_read): ?>
                                        <i class="fas fa-check-double" style="color:#53bdeb;" title="Dibaca <?php echo date('d/m/Y H:i', strtotime($rc->read_at)); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check" title="Terkirim"></i>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="wa-thread-compose">
            <?php if (!empty($is_admin_monitor) || (isset($can_reply) && !$can_reply)): ?>
                <div class="alert alert-info mb-0 py-2 px-3 small">
                    <i class="fas fa-eye"></i> Mode pantau admin — Anda melihat percakapan antar role tanpa mengirim balasan di sini.
                    <a href="<?php echo site_url('Chat/compose'); ?>" class="alert-link">Tulis pesan baru</a> jika ingin menghubungi pengguna.
                </div>
            <?php else: ?>
            <?php echo form_open('Chat/reply/' . $conversation->id); ?>
                <div class="d-flex align-items-end" style="gap:10px;">
                    <div class="flex-grow-1">
                        <textarea name="body" class="form-control" rows="2" placeholder="Ketik pesan..." required></textarea>
                        <?php if ($this->session->userdata('role') !== 'mahasiswa'): ?>
                            <div class="form-check mt-1 mb-0">
                                <input class="form-check-input" type="checkbox" name="is_priority" value="1" id="is_priority_reply">
                                <label class="form-check-label small text-muted" for="is_priority_reply">Pesan penting (wajib dibaca saat login)</label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-success btn-send" title="Kirim"><i class="fas fa-paper-plane"></i></button>
                </div>
            <?php echo form_close(); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var box = document.getElementById('chatThreadBox');
    if (box) box.scrollTop = box.scrollHeight;
});
</script>
