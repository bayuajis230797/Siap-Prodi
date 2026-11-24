<div class="row">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-bullhorn"></i> <?php echo htmlspecialchars($conversation->subject ?: 'Detail Siaran'); ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Chat/thread/' . $conversation->id); ?>" class="btn btn-primary btn-sm">Buka Thread</a>
                    <a href="<?php echo site_url('Chat'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($first_message): ?>
                    <div class="mb-3 p-3 bg-light border rounded">
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($first_message->created_at)); ?>
                            <?php if ($first_message->is_priority): ?><span class="badge badge-warning">Penting</span><?php endif; ?>
                        </small>
                        <div><?php echo nl2br(htmlspecialchars($first_message->body)); ?></div>
                    </div>

                    <?php
                    $read_count = 0;
                    foreach ($receipts as $r) {
                        if ($r->is_read) $read_count++;
                    }
                    $total = count($receipts);
                    ?>
                    <p><strong>Status dibaca:</strong> <?php echo $read_count; ?> / <?php echo $total; ?> mahasiswa</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Penerima</th>
                                    <th width="15%" class="text-center">Status</th>
                                    <th width="20%">Waktu dibaca</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($receipts as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(chat_display_name($r->id_user)); ?></td>
                                        <td class="text-center">
                                            <?php if ($r->is_read): ?>
                                                <span class="badge badge-success">Dibaca</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $r->read_at ? date('d/m/Y H:i', strtotime($r->read_at)) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Belum ada pesan siaran.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
