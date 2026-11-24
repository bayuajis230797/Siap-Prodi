<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>SIHARKAT</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($this->config->item('csrf_protection')): ?>
    <meta name="csrf-token-name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf-token" content="<?php echo $this->security->get_csrf_hash(); ?>">
    <?php endif; ?>
    <?php $this->load->view('pwa/head'); ?>
    <style>
    /* Modern file upload dropzone (semua input[type=file]) */
    .siap-file-field{margin-bottom:.35rem}
    .siap-file-drop{border:1.5px dashed #c5cdd6;border-radius:12px;background:linear-gradient(180deg,#f8fafc 0%,#eef2f7 100%);padding:18px 16px;text-align:center;cursor:pointer;transition:border-color .2s,background .2s,box-shadow .2s;position:relative}
    .siap-file-drop:hover,.siap-file-drop.is-dragover{border-color:#0d6efd;background:linear-gradient(180deg,#eef5ff 0%,#e3effe 100%);box-shadow:0 0 0 3px rgba(13,110,253,.12)}
    .siap-file-drop.has-file{border-color:#198754;border-style:solid;background:linear-gradient(180deg,#f0fff6 0%,#e6f7ee 100%)}
    .siap-file-drop .siap-file-icon{font-size:1.55rem;color:#0d6efd;margin-bottom:6px}
    .siap-file-drop.has-file .siap-file-icon{color:#198754}
    .siap-file-drop .siap-file-title{font-weight:600;color:#1f2937;font-size:.95rem;margin:0}
    .siap-file-drop .siap-file-hint{font-size:.8rem;color:#6b7280;margin:.2rem 0 0}
    .siap-file-drop .siap-file-name{font-size:.82rem;color:#0f5132;font-weight:600;margin-top:6px;word-break:break-all}
    .siap-file-drop input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
    .siap-file-drop input[type=file].form-control-file,.siap-file-drop input[type=file].form-control{padding:0;border:0;background:transparent;height:100%}
    #navbar-notif-item{position:relative}
    #siap-notif-toast-stack{position:absolute;top:calc(100% + 6px);right:0;z-index:1050;display:flex;flex-direction:column;gap:8px;width:300px;max-width:min(300px,calc(100vw - 24px));pointer-events:none}
    .siap-notif-toast{pointer-events:auto;background:#111b21;color:#fff;border-radius:10px;padding:10px 12px;box-shadow:0 8px 24px rgba(0,0,0,.28);display:flex;gap:10px;align-items:flex-start;animation:siapNotifIn .25s ease-out;position:relative}
    .siap-notif-toast::before{content:"";position:absolute;top:-6px;right:14px;width:12px;height:12px;background:#111b21;transform:rotate(45deg);border-radius:2px}
    .siap-notif-toast a{color:#fff;text-decoration:none}
    .siap-notif-toast .siap-notif-icon{width:32px;height:32px;border-radius:50%;background:#dc3545;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.85rem}
    .siap-notif-toast .siap-notif-title{font-weight:700;font-size:.85rem;margin:0 0 2px}
    .siap-notif-toast .siap-notif-body{font-size:.78rem;opacity:.9;margin:0;line-height:1.35}
    .siap-notif-toast .siap-notif-close{background:transparent;border:0;color:#fff;opacity:.7;margin-left:auto;padding:0 2px;font-size:1rem;line-height:1;cursor:pointer}
    #navbar-notif-link.siap-notif-pulse{animation:siapNotifPulse .9s ease-in-out 2}
    @keyframes siapNotifIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
    @keyframes siapNotifPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.18)}}
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<?php
$global_notifikasi_unread_count = isset($global_notifikasi_unread_count) ? (int) $global_notifikasi_unread_count : 0;
$global_notifikasi_items = isset($global_notifikasi_items) && is_array($global_notifikasi_items) ? $global_notifikasi_items : array();
$global_chat_unread_count = isset($global_chat_unread_count) ? (int) $global_chat_unread_count : 0;
$global_chat_priority_messages = isset($global_chat_priority_messages) && is_array($global_chat_priority_messages) ? $global_chat_priority_messages : array();
$chat_allowed_roles = array('mahasiswa', 'dosen', 'sekprodi', 'admin', 'kaprodi');
$show_chat_ui = $this->session->userdata('user_id') && in_array(strtolower((string) $this->session->userdata('role')), $chat_allowed_roles, true);
$siap_notif_max_id = 0;
if (!empty($global_notifikasi_items)) {
    foreach ($global_notifikasi_items as $_n) {
        if (!empty($_n->id) && (int) $_n->id > $siap_notif_max_id) {
            $siap_notif_max_id = (int) $_n->id;
        }
    }
}
?>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <?php if (!empty($global_ta_list) && count($global_ta_list) > 1): ?>
            <li class="nav-item d-flex align-items-center mr-2">
                <?php echo form_open('Tahun_akademik_lihat/pilih', array('class' => 'form-inline', 'id' => 'form-ta-lihat')); ?>
                    <i class="far fa-calendar-alt mr-1 text-muted" title="Tahun akademik"></i>
                    <select name="id_tahun_akademik" class="form-control form-control-sm" style="max-width: 190px;" onchange="this.form.submit()" title="Pilih tahun akademik yang ingin dilihat">
                        <?php $ta_terpilih = !empty($global_ta_lihat) ? (int) $global_ta_lihat->id : (!empty($global_ta_resmi) ? (int) $global_ta_resmi->id : 0); ?>
                        <?php foreach ($global_ta_list as $ta_opt): ?>
                            <option value="<?php echo (int) $ta_opt->id; ?>" <?php echo ((int) $ta_opt->id === $ta_terpilih) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ta_opt->tahun_akademik . ' ' . ucfirst($ta_opt->semester)); ?><?php echo !empty($ta_opt->is_aktif) ? ' (berjalan)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php echo form_close(); ?>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url('Aplikasi_mobile'); ?>" title="Pasang Aplikasi (PWA)"><i class="fab fa-android"></i></a>
            </li>
            <?php if ($show_chat_ui): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url('Chat'); ?>" title="Pesan" id="navbar-chat-link">
                    <i class="far fa-comments"></i>
                    <span class="badge badge-danger navbar-badge" id="navbar-chat-badge" style="<?php echo $global_chat_unread_count > 0 ? '' : 'display:none;'; ?>"><?php echo (int) $global_chat_unread_count; ?></span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item dropdown" id="navbar-notif-item">
                <a class="nav-link" id="navbar-notif-link" data-toggle="dropdown" data-bs-toggle="dropdown" href="#" title="Notifikasi">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-danger navbar-badge" id="navbar-notif-badge" style="<?php echo !empty($global_notifikasi_unread_count) ? '' : 'display:none;'; ?>"><?php echo (int) $global_notifikasi_unread_count; ?></span>
                </a>
                <div id="siap-notif-toast-stack" aria-live="polite"></div>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header" id="navbar-notif-header">
                        <?php echo (int) ($global_notifikasi_unread_count ?? 0); ?> notifikasi belum dibaca
                    </span>
                    <div class="dropdown-divider"></div>
                    <div id="navbar-notif-list">
                    <?php if (!empty($global_notifikasi_items)): ?>
                        <?php foreach ($global_notifikasi_items as $notif): ?>
                            <a href="<?php echo site_url('Notifikasi/mark_read/' . (int) $notif->id); ?>" class="dropdown-item">
                                <div class="text-wrap">
                                    <strong><?php echo htmlspecialchars($notif->judul); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($notif->pesan); ?></small><br>
                                    <small class="text-muted"><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif->created_at)); ?></small>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="dropdown-item text-muted">Belum ada notifikasi.</span>
                        <div class="dropdown-divider"></div>
                    <?php endif; ?>
                    </div>
                    <a href="<?php echo site_url('Notifikasi/mark_all_read'); ?>" class="dropdown-item dropdown-footer">Tandai semua sudah dibaca</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url('Profile'); ?>" title="Profile">
                    <i class="fas fa-user"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url('Auth/logout'); ?>" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?php echo site_url('Profile'); ?>" class="brand-link">
            <span class="brand-text font-weight-light">SI-HARKAT</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="<?php echo site_url('Profile'); ?>" class="d-block">
                        <?php echo $this->session->userdata('nama') ?? $this->session->userdata('username'); ?>
                    </a>
                    <small class="text-muted"><?php echo ucfirst($this->session->userdata('role')); ?></small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <?php echo isset($sidebar_menu) ? $sidebar_menu : ''; ?>
        </div>
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                                <?php foreach ($breadcrumb as $item): ?>
                                    <?php if (isset($item['active']) && $item['active']): ?>
                                        <li class="breadcrumb-item active"><?php echo $item['label']; ?></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo isset($item['url']) ? $item['url'] : '#'; ?>">
                                                <?php echo $item['label']; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Flash Messages - Akan ditampilkan via SweetAlert2 di script bawah -->

                <!-- Page Content -->
                <?php echo isset($content) ? $content : ''; ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">SI-HARKAT</a> |</strong>
        Sistem Administrasi Prodi Sistem Informasi
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- SweetAlert2 untuk Flash Messages -->
<?php 
$user_role = $this->session->userdata('role');
$is_mahasiswa = ($user_role === 'mahasiswa');
?>
<script>
$(document).ready(function() {
    var flashSwalChain = Promise.resolve();
    function chainFlash(promiseFactory) {
        flashSwalChain = flashSwalChain.then(promiseFactory);
    }
    function afterFlashDialogs() {
        if (typeof window.__runChatPriorityPopups === 'function') {
            window.__runChatPriorityPopups();
        }
    }

    <?php if ($this->config->item('csrf_protection')): ?>
    (function() {
        var csrfName = $('meta[name=csrf-token-name]').attr('content') || '<?php echo $this->config->item('csrf_token_name'); ?>';
        var csrfHash = $('meta[name=csrf-token]').attr('content') || '';
        var csrfCookieName = '<?php echo $this->config->item('csrf_cookie_name'); ?>';
        if (!csrfName) return;

        function syncCsrfToken() {
            if (!csrfCookieName) return csrfHash;
            var parts = document.cookie ? document.cookie.split(';') : [];
            for (var i = 0; i < parts.length; i++) {
                var part = parts[i].trim();
                if (part.indexOf(csrfCookieName + '=') === 0) {
                    var newHash = decodeURIComponent(part.substring(csrfCookieName.length + 1));
                    if (newHash) {
                        csrfHash = newHash;
                        $('meta[name=csrf-token]').attr('content', newHash);
                        $('input[name="' + csrfName + '"]').val(newHash);
                    }
                    break;
                }
            }
            return csrfHash;
        }

        window.getSiapCsrf = function() {
            return { name: csrfName, hash: syncCsrfToken() };
        };

        window.appendSiapCsrf = function(body) {
            var token = syncCsrfToken();
            if (!csrfName || !token) return body;
            if (body instanceof FormData) {
                if (!body.has(csrfName)) {
                    body.append(csrfName, token);
                }
                return body;
            }
            if (typeof body === 'string') {
                if (body.indexOf(encodeURIComponent(csrfName) + '=') !== -1 || body.indexOf(csrfName + '=') !== -1) {
                    return body;
                }
                return body + (body ? '&' : '') + encodeURIComponent(csrfName) + '=' + encodeURIComponent(token);
            }
            if (body && typeof body === 'object') {
                body[csrfName] = token;
                return body;
            }
            return encodeURIComponent(csrfName) + '=' + encodeURIComponent(token);
        };

        // Patch fetch agar semua POST same-origin otomatis bawa CSRF
        if (typeof window.fetch === 'function' && !window.__siapFetchPatched) {
            var nativeFetch = window.fetch.bind(window);
            window.fetch = function(input, init) {
                init = init || {};
                var method = ((init.method || 'GET') + '').toUpperCase();
                var url = (typeof input === 'string') ? input : (input && input.url ? input.url : '');
                var isSameOrigin = !url || url.indexOf('http') !== 0 || url.indexOf(window.location.origin) === 0;
                if (isSameOrigin && (method === 'POST' || method === 'PUT' || method === 'PATCH' || method === 'DELETE')) {
                    init.credentials = init.credentials || 'same-origin';
                    init.headers = init.headers || {};
                    if (init.headers instanceof Headers) {
                        if (!init.headers.has('X-Requested-With')) {
                            init.headers.set('X-Requested-With', 'XMLHttpRequest');
                        }
                    } else if (!init.headers['X-Requested-With']) {
                        init.headers['X-Requested-With'] = 'XMLHttpRequest';
                    }
                    init.body = window.appendSiapCsrf(init.body || '');
                    // FormData: jangan set Content-Type manual
                    if (!(init.body instanceof FormData)) {
                        if (init.headers instanceof Headers) {
                            if (!init.headers.has('Content-Type')) {
                                init.headers.set('Content-Type', 'application/x-www-form-urlencoded');
                            }
                        } else if (!init.headers['Content-Type'] && !init.headers['content-type']) {
                            init.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                        }
                    }
                }
                return nativeFetch(input, init).then(function(response) {
                    syncCsrfToken();
                    return response;
                });
            };
            window.__siapFetchPatched = true;
        }

        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (settings.type && settings.type.toUpperCase() === 'POST') {
                    syncCsrfToken();
                    if (settings.data === undefined || settings.data === null) {
                        settings.data = {};
                    }
                    if (typeof settings.data === 'string') {
                        settings.data = window.appendSiapCsrf(settings.data);
                    } else if (settings.data instanceof FormData) {
                        window.appendSiapCsrf(settings.data);
                    } else if (settings.data && typeof settings.data === 'object') {
                        settings.data[csrfName] = csrfHash;
                    }
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                }
            },
            complete: function(xhr, settings) {
                if (settings.type && settings.type.toUpperCase() === 'POST') {
                    syncCsrfToken();
                }
            }
        });
        $(document).on('submit', 'form', function() {
            var method = ($(this).attr('method') || 'get').toUpperCase();
            if (method !== 'POST') {
                return;
            }
            syncCsrfToken();
            if (!$(this).find('input[name="' + csrfName + '"]').length) {
                $(this).append('<input type="hidden" name="' + csrfName + '" value="' + csrfHash + '">');
            } else {
                $(this).find('input[name="' + csrfName + '"]').val(csrfHash);
            }
        });
    })();
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        var message = <?php echo json_encode($this->session->flashdata('success')); ?>;
        <?php if ($is_mahasiswa): ?>
            chainFlash(function() {
                return Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false
                });
            });
        <?php else: ?>
            chainFlash(function() {
                return Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745'
                });
            });
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
        var errorMessage = <?php echo json_encode($this->session->flashdata('error')); ?>;
        chainFlash(function() {
            return Swal.fire({
                icon: 'error',
                title: 'Error!',
                html: errorMessage,
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        });
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('warning')): ?>
        var warningMessage = <?php echo json_encode($this->session->flashdata('warning')); ?>;
        chainFlash(function() {
            return Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                html: warningMessage,
                confirmButtonText: 'OK',
                confirmButtonColor: '#ffc107'
            });
        });
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('info')): ?>
        var infoMessage = <?php echo json_encode($this->session->flashdata('info')); ?>;
        chainFlash(function() {
            return Swal.fire({
                icon: 'info',
                title: 'Informasi',
                html: infoMessage,
                confirmButtonText: 'OK',
                confirmButtonColor: '#17a2b8'
            });
        });
    <?php endif; ?>

    flashSwalChain.finally(afterFlashDialogs);
});
</script>

<?php if ($show_chat_ui): ?>
<script>
(function() {
    'use strict';

    var CHAT_POLL_INTERVAL = 60000;
    var chatBadge = document.getElementById('navbar-chat-badge');
    var priorityQueue = <?php echo json_encode(array_map(function($m) {
        return array(
            'id' => (int) $m->id,
            'body' => $m->body,
            'sender' => chat_display_name($m->sender_user_id),
            'thread_url' => site_url('Chat/thread/' . $m->id_conversation),
        );
    }, $global_chat_priority_messages), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    var shownPriorityIds = {};
    var isMahasiswa = <?php echo ($is_mahasiswa ? 'true' : 'false'); ?>;

    function updateChatBadge(count) {
        if (!chatBadge) return;
        count = parseInt(count, 10) || 0;
        if (count > 0) {
            chatBadge.textContent = count;
            chatBadge.style.display = '';
        } else {
            chatBadge.style.display = 'none';
        }
    }

    function markPriorityRead(messageId, callback) {
        var formBody = new URLSearchParams();
        <?php if ($this->config->item('csrf_protection')): ?>
        formBody.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');
        <?php endif; ?>
        fetch('<?php echo site_url('Chat/mark_read'); ?>/' + messageId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formBody.toString()
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (typeof callback === 'function') callback(data);
        }).catch(function() {
            if (typeof callback === 'function') callback(null);
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function showNextPriorityPopup() {
        if (!isMahasiswa || typeof Swal === 'undefined') return;
        var next = null;
        for (var i = 0; i < priorityQueue.length; i++) {
            var item = priorityQueue[i];
            if (!shownPriorityIds[item.id]) {
                next = item;
                break;
            }
        }
        if (!next) return;

        shownPriorityIds[next.id] = true;
        Swal.fire({
            title: 'Pesan Penting',
            html: '<p><strong>Dari:</strong> ' + escapeHtml(next.sender) + '</p>' +
                  '<p style="text-align:left;white-space:pre-wrap;">' + escapeHtml(next.body) + '</p>',
            icon: 'warning',
            confirmButtonText: 'Baca & Tutup',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: { confirmButton: 'btn btn-danger' },
            buttonsStyling: false
        }).then(function(result) {
            if (result.isConfirmed) {
                markPriorityRead(next.id, function() {
                    showNextPriorityPopup();
                });
            } else {
                showNextPriorityPopup();
            }
        });
    }

    window.__runChatPriorityPopups = function() {
        if (isMahasiswa && priorityQueue.length > 0) {
            showNextPriorityPopup();
        }
    };

    function pollChatUnread() {
        fetch('<?php echo site_url('Chat/unread_count'); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data && data.success) {
                updateChatBadge(data.count);
            }
        }).catch(function() {});

        if (isMahasiswa) {
            fetch('<?php echo site_url('Chat/priority_pending'); ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (!data || !data.success || !data.items) return;
                data.items.forEach(function(item) {
                    var exists = priorityQueue.some(function(p) { return p.id === item.id; });
                    if (!exists) priorityQueue.push(item);
                });
                if (typeof Swal !== 'undefined' && (!Swal.isVisible || !Swal.isVisible())) {
                    showNextPriorityPopup();
                }
            }).catch(function() {});
        }
    }

    setInterval(pollChatUnread, CHAT_POLL_INTERVAL);
})();
</script>
<?php endif; ?>

<!-- Auto Logout untuk Keamanan -->
<?php if ($this->session->userdata('user_id')): ?>
<script>
(function() {
    'use strict';
    
    // Konfigurasi waktu inactivity (dalam detik)
    var INACTIVITY_TIMEOUT = 25 * 60; // 25 menit (5 menit sebelum session expired)
    var WARNING_TIME = 5 * 60; // 5 menit sebelum logout, tampilkan warning
    var CHECK_INTERVAL = 60; // Cek setiap 1 menit
    
    var inactivityTimer = null;
    var warningTimer = null;
    var warningShown = false;
    var lastActivity = Date.now();
    
    // Event untuk mendeteksi aktivitas user
    var activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    
    function resetTimer() {
        lastActivity = Date.now();
        warningShown = false;
        
        // Clear existing timers
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
        if (warningTimer) {
            clearTimeout(warningTimer);
        }
        
        // Set warning timer (5 menit sebelum logout)
        warningTimer = setTimeout(function() {
            showWarning();
        }, (INACTIVITY_TIMEOUT - WARNING_TIME) * 1000);
        
        // Set logout timer
        inactivityTimer = setTimeout(function() {
            logoutUser();
        }, INACTIVITY_TIMEOUT * 1000);
    }
    
    function showWarning() {
        if (warningShown) return;
        warningShown = true;
        
        var minutesLeft = Math.ceil(WARNING_TIME / 60);
        var message = 'Peringatan: Anda akan otomatis logout dalam ' + minutesLeft + ' menit karena tidak ada aktivitas. Klik "Lanjutkan" untuk tetap login.';
        
        // Tampilkan modal warning
        if (typeof $ !== 'undefined') {
            var modalHtml = '<div class="modal fade" id="inactivityWarningModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">' +
                '<div class="modal-dialog" role="document">' +
                '<div class="modal-content">' +
                '<div class="modal-header bg-warning">' +
                '<h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Peringatan Inaktivitas</h5>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p>' + message + '</p>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-primary" onclick="resetInactivityTimer()">Lanjutkan</button>' +
                '<button type="button" class="btn btn-secondary" onclick="logoutUser()">Logout Sekarang</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            // Hapus modal lama jika ada
            $('#inactivityWarningModal').remove();
            
            // Tambahkan modal ke body
            $('body').append(modalHtml);
            
            // Tampilkan modal
            $('#inactivityWarningModal').modal('show');
        } else {
            // Fallback jika jQuery tidak tersedia
            if (confirm(message + '\n\nKlik OK untuk tetap login, atau Cancel untuk logout sekarang.')) {
                resetTimer();
            } else {
                logoutUser();
            }
        }
    }
    
    function logoutUser() {
        // Hapus modal warning jika ada
        if (typeof $ !== 'undefined') {
            $('#inactivityWarningModal').modal('hide');
        }
        
        // Redirect ke logout
        window.location.href = '<?php echo site_url("Auth/logout"); ?>';
    }
    
    // Fungsi global untuk reset timer (dipanggil dari modal)
    window.resetInactivityTimer = function() {
        if (typeof $ !== 'undefined') {
            $('#inactivityWarningModal').modal('hide');
        }
        resetTimer();
    };
    
    // Fungsi global untuk logout (dipanggil dari modal)
    window.logoutUser = logoutUser;
    
    // Deteksi aktivitas user
    activityEvents.forEach(function(event) {
        document.addEventListener(event, function() {
            resetTimer();
        }, true);
    });
    
    // Deteksi visibility change (tab/window tidak aktif)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Tab/window tidak aktif, tetap hitung waktu
            // Timer tetap berjalan
        } else {
            // Tab/window aktif kembali, reset timer
            resetTimer();
        }
    });
    
    // Deteksi focus window
    window.addEventListener('focus', function() {
        resetTimer();
    });
    
    // Deteksi blur window
    window.addEventListener('blur', function() {
        // Tetap hitung waktu meskipun window tidak fokus
    });
    
    // Cek session secara berkala
    setInterval(function() {
        var timeSinceActivity = (Date.now() - lastActivity) / 1000;
        
        // Jika sudah melewati waktu inactivity, logout
        if (timeSinceActivity >= INACTIVITY_TIMEOUT) {
            logoutUser();
        }
    }, CHECK_INTERVAL * 1000);
    
    // Inisialisasi timer saat halaman dimuat
    resetTimer();
    
    // Cek saat halaman akan di-unload (browser ditutup, tab ditutup, dll)
    window.addEventListener('beforeunload', function(e) {
        // Browser akan menutup, session akan expired di server
    });
    
    console.log('Auto-logout system initialized. Inactivity timeout: ' + INACTIVITY_TIMEOUT + ' seconds');
})();
</script>
<?php endif; ?>
<?php $this->load->view('pwa/register'); ?>
<script>
(function () {
    // Modernisasi semua input file → dropzone
    function enhanceFileInputs(root) {
        var scope = root || document;
        var inputs = scope.querySelectorAll('input[type="file"]:not([data-siap-enhanced])');
        inputs.forEach(function (input) {
            if (input.closest('.siap-file-drop')) {
                input.setAttribute('data-siap-enhanced', '1');
                return;
            }
            input.setAttribute('data-siap-enhanced', '1');
            var wrap = document.createElement('div');
            wrap.className = 'siap-file-field';
            var drop = document.createElement('div');
            drop.className = 'siap-file-drop';
            drop.innerHTML =
                '<div class="siap-file-icon"><i class="fas fa-cloud-upload-alt"></i></div>' +
                '<p class="siap-file-title">Seret & lepas file di sini</p>' +
                '<p class="siap-file-hint">atau klik untuk memilih dari perangkat</p>' +
                '<div class="siap-file-name" style="display:none;"></div>';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(drop);
            drop.appendChild(input);

            var nameEl = drop.querySelector('.siap-file-name');
            function refresh() {
                if (input.files && input.files.length) {
                    drop.classList.add('has-file');
                    var names = [];
                    for (var i = 0; i < input.files.length; i++) names.push(input.files[i].name);
                    nameEl.style.display = '';
                    nameEl.textContent = names.join(', ');
                    drop.querySelector('.siap-file-title').textContent = 'File siap diunggah';
                    drop.querySelector('.siap-file-hint').textContent = 'Klik lagi untuk mengganti file';
                } else {
                    drop.classList.remove('has-file');
                    nameEl.style.display = 'none';
                    nameEl.textContent = '';
                    drop.querySelector('.siap-file-title').textContent = 'Seret & lepas file di sini';
                    drop.querySelector('.siap-file-hint').textContent = 'atau klik untuk memilih dari perangkat';
                }
            }
            input.addEventListener('change', refresh);
            ['dragenter', 'dragover'].forEach(function (ev) {
                drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-dragover'); });
            });
            ['dragleave', 'drop'].forEach(function (ev) {
                drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-dragover'); });
            });
            drop.addEventListener('drop', function (e) {
                if (!e.dataTransfer || !e.dataTransfer.files || !e.dataTransfer.files.length) return;
                try {
                    if (typeof DataTransfer !== 'undefined') {
                        var dt = new DataTransfer();
                        var list = e.dataTransfer.files;
                        var max = input.multiple ? list.length : Math.min(1, list.length);
                        for (var i = 0; i < max; i++) dt.items.add(list[i]);
                        input.files = dt.files;
                    } else {
                        input.files = e.dataTransfer.files;
                    }
                } catch (err) { /* browser lama */ }
                refresh();
            });
            refresh();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { enhanceFileInputs(document); });
    } else {
        enhanceFileInputs(document);
    }

    // Progress % saat submit form yang berisi file
    if (window.__siapUploadProgressBound) return;
    window.__siapUploadProgressBound = true;

    function hasFileSelected(form) {
        var inputs = form.querySelectorAll('input[type="file"]');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].files && inputs[i].files.length > 0) return true;
        }
        return false;
    }

    function showProgressUI() {
        if (document.getElementById('siap-upload-progress-overlay')) return;
        var el = document.createElement('div');
        el.id = 'siap-upload-progress-overlay';
        el.innerHTML =
            '<div style="position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(2px);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;">' +
            '  <div style="background:#fff;border-radius:16px;max-width:440px;width:100%;padding:22px 24px;box-shadow:0 16px 48px rgba(0,0,0,.25);">' +
            '    <div style="display:flex;align-items:center;gap:10px;font-weight:700;margin-bottom:10px;color:#0f172a;">' +
            '      <span style="width:36px;height:36px;border-radius:50%;background:#e8f1ff;color:#0d6efd;display:inline-flex;align-items:center;justify-content:center;"><i class="fas fa-cloud-upload-alt"></i></span>' +
            '      Mengunggah berkas' +
            '    </div>' +
            '    <div id="siap-upload-progress-label" style="font-size:13px;color:#475569;margin-bottom:10px;">0%</div>' +
            '    <div style="height:12px;background:#e2e8f0;border-radius:999px;overflow:hidden;">' +
            '      <div id="siap-upload-progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#0d6efd,#0dcaf0);transition:width .15s linear;"></div>' +
            '    </div>' +
            '    <div id="siap-upload-progress-hint" style="font-size:12px;color:#94a3b8;margin-top:12px;">Jangan tutup halaman sampai selesai.</div>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(el);
    }

    function setProgress(pct, label, hint) {
        var bar = document.getElementById('siap-upload-progress-bar');
        var lab = document.getElementById('siap-upload-progress-label');
        var hin = document.getElementById('siap-upload-progress-hint');
        if (bar) bar.style.width = Math.max(0, Math.min(100, pct)) + '%';
        if (lab && label) lab.textContent = label;
        if (hin && hint) hin.textContent = hint;
    }

    function hideProgressUI() {
        var el = document.getElementById('siap-upload-progress-overlay');
        if (el) el.remove();
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.getAttribute('data-skip-upload-progress') === '1') return;

        var enctype = (form.getAttribute('enctype') || form.enctype || '').toLowerCase();
        var isMultipart = enctype.indexOf('multipart/form-data') !== -1;
        if (!isMultipart || !hasFileSelected(form)) return;

        e.preventDefault();
        e.stopPropagation();

        showProgressUI();
        setProgress(0, '0% — menyiapkan...', 'Mengirim file ke server...');

        var xhr = new XMLHttpRequest();
        xhr.open(form.method || 'POST', form.action, true);
        xhr.responseType = 'document';

        xhr.upload.onprogress = function (ev) {
            if (!ev.lengthComputable) {
                setProgress(10, 'Mengunggah...', 'Transfer ke server berlangsung');
                return;
            }
            var pct = Math.round((ev.loaded / ev.total) * 85);
            setProgress(pct, pct + '% — mengunggah ke server', 'File sedang dikirim...');
        };

        xhr.upload.onload = function () {
            setProgress(88, '88% — memproses ke Google Drive...', 'Mohon tunggu, menyimpan ke Drive institusi');
        };

        xhr.onerror = function () {
            hideProgressUI();
            alert('Gagal mengunggah berkas. Periksa koneksi lalu coba lagi.');
        };

        xhr.onload = function () {
            setProgress(100, '100% — selesai', 'Mengalihkan...');
            if (xhr.responseURL && xhr.responseURL !== form.action) {
                window.location.href = xhr.responseURL;
                return;
            }
            if (xhr.response && xhr.response.documentElement) {
                document.open();
                document.write(xhr.response.documentElement.outerHTML);
                document.close();
                return;
            }
            window.location.reload();
        };

        var fd = new FormData(form);
        xhr.send(fd);
    }, true);
})();

<?php if ($this->session->userdata('user_id')): ?>
(function () {
    // Popup notifikasi baru — semua role yang login
    var pollUrl = <?php echo json_encode(site_url('Notifikasi/poll')); ?>;
    var storageKey = 'siap_notif_last_id_<?php echo (int) $this->session->userdata('user_id'); ?>';
    var lastId = parseInt(localStorage.getItem(storageKey) || '<?php echo (int) $siap_notif_max_id; ?>', 10) || 0;
    var seen = {};
    var stack = document.getElementById('siap-notif-toast-stack');
    if (!stack) return;

    function updateBadge(unread) {
        var badge = document.getElementById('navbar-notif-badge');
        var header = document.getElementById('navbar-notif-header');
        if (badge) {
            if (unread > 0) {
                badge.style.display = '';
                badge.textContent = unread;
            } else {
                badge.style.display = 'none';
            }
        }
        if (header) header.textContent = unread + ' notifikasi belum dibaca';
    }

    function prependDropdownItem(item) {
        var list = document.getElementById('navbar-notif-list');
        if (!list) return;
        var empty = list.querySelector('.text-muted');
        if (empty && empty.textContent && empty.textContent.indexOf('Belum ada') !== -1) {
            list.innerHTML = '';
        }
        var wrap = document.createElement('div');
        wrap.innerHTML =
            '<a class="dropdown-item" href="' + (item.link || '#') + '">' +
            '  <div class="text-wrap"><strong></strong><br><small class="text-muted"></small></div>' +
            '</a><div class="dropdown-divider"></div>';
        wrap.querySelector('strong').textContent = item.judul || 'Notifikasi';
        wrap.querySelector('small').textContent = item.pesan || '';
        while (wrap.firstChild) list.insertBefore(wrap.firstChild, list.firstChild);
    }

    function showToast(item) {
        if (seen[item.id]) return;
        seen[item.id] = true;
        var el = document.createElement('div');
        el.className = 'siap-notif-toast';
        el.innerHTML =
            '<div class="siap-notif-icon"><i class="fas fa-bell"></i></div>' +
            '<div style="flex:1;min-width:0;">' +
            '  <a href="' + (item.link || '#') + '">' +
            '    <p class="siap-notif-title"></p>' +
            '    <p class="siap-notif-body"></p>' +
            '  </a>' +
            '</div>' +
            '<button type="button" class="siap-notif-close" aria-label="Tutup">&times;</button>';
        el.querySelector('.siap-notif-title').textContent = item.judul || 'Notifikasi';
        el.querySelector('.siap-notif-body').textContent = item.pesan || '';
        el.querySelector('.siap-notif-close').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            el.remove();
        });
        el.addEventListener('click', function (e) { e.stopPropagation(); });
        stack.insertBefore(el, stack.firstChild);
        prependDropdownItem(item);

        var link = document.getElementById('navbar-notif-link');
        if (link) {
            link.classList.remove('siap-notif-pulse');
            void link.offsetWidth;
            link.classList.add('siap-notif-pulse');
        }
        setTimeout(function () { if (el.parentNode) el.remove(); }, 8000);
    }

    function poll() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', pollUrl + '?after_id=' + encodeURIComponent(lastId) + '&_=' + Date.now(), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            if (xhr.status !== 200) return;
            var data;
            try { data = JSON.parse(xhr.responseText); } catch (e) { return; }
            if (!data || !data.success) return;
            updateBadge(data.unread || 0);
            if (data.items && data.items.length) {
                data.items.forEach(showToast);
            }
            if (data.max_id && data.max_id > lastId) {
                lastId = data.max_id;
                localStorage.setItem(storageKey, String(lastId));
            }
        };
        xhr.send();
    }

    setTimeout(poll, 2500);
    setInterval(poll, 20000);
})();
<?php endif; ?>
</script>
</body>
</html>





