<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | SI-HARKAT</title>

    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f5f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        
        .login-container {
            width: 100%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 0;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-left {
            background: #ffffff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #111827;
            position: relative;
            overflow: hidden;
            border-right: 1px solid #eef2f7;
        }
        
        .login-left::before,
        .login-left::after {
            display: none;
        }
        
        .login-logo {
            margin-bottom: 30px;
            z-index: 2;
            position: relative;
        }
        
        .login-logo img {
            max-height: 100px;
            max-width: 220px;
            margin-bottom: 16px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.08));
        }
        
        .login-logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #111827;
        }
        
        .login-logo p {
            font-size: 1.1rem;
            font-weight: 400;
            opacity: 0.9;
            color: #374151;
        }
        
        .login-illustration {
            width: 100%;
            max-width: 460px;
            margin: 18px auto 0;
        }

        .login-illustration svg {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .login-right {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #4b5563;
            font-size: 0.95rem;
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: #6b7280;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-control {
            height: 50px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            padding-left: 50px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: #111827;
        }
        
        .form-control::placeholder {
            color: #999;
        }
        
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.18);
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 1.1rem;
            z-index: 3;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 3;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #111827;
        }
        
        .form-group.password-group .form-control {
            padding-right: 50px;
        }
        
        .btn-login {
            height: 50px;
            border-radius: 10px;
            background: #1f2937;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            color: #ffffff;
            transition: all 0.3s ease;
            box-shadow: 0 6px 18px rgba(31, 41, 55, 0.22);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            background: #111827;
            box-shadow: 0 10px 24px rgba(17, 24, 39, 0.28);
            color: #ffffff;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .mood-wrap {
            margin-bottom: 18px;
        }

        .mood-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 10px;
        }

        .mood-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
        }

        .mood-btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            border-radius: 12px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: transform 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
            cursor: pointer;
            user-select: none;
        }

        .mood-btn:hover {
            transform: translateY(-1px);
            border-color: #9ca3af;
        }

        .mood-btn.is-selected {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.18);
        }

        .mood-hint {
            margin-top: 8px;
            font-size: 0.82rem;
            color: #6b7280;
        }
        
        @media (max-width: 992px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 600px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .login-container {
                max-width: 500px;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            }

            .login-left {
                display: none;
            }

            .login-right {
                padding: 28px 20px;
            }

            .login-header h2 {
                font-size: 1.6rem;
            }

            .mood-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }
    </style>
    <?php $this->load->view('pwa/head'); ?>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="login-logo">
                <?php
                $logo_url = get_logo_kampus();
                $nama_kampus = get_nama_kampus();
                $nama_prodi = get_nama_prodi();
                if ($logo_url): ?>
                    <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo" onerror="this.style.display='none'; document.getElementById('logo-placeholder').style.display='block';">
                    <div id="logo-placeholder" style="display: none;">
                        <h1><?php echo htmlspecialchars($nama_kampus); ?></h1>
                    </div>
                <?php else: ?>
                    <h1><?php echo htmlspecialchars($nama_kampus ? $nama_kampus : 'SIAP'); ?></h1>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($nama_prodi ? $nama_prodi : 'Sistem Informasi Administrasi Prodi'); ?></p>
            </div>
            <div class="login-illustration" aria-hidden="true">
                <!-- Lightweight inline SVG illustration (no external assets) -->
                <svg viewBox="0 0 900 520" xmlns="http://www.w3.org/2000/svg" role="img">
                    <defs>
                        <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#eef2ff"/>
                            <stop offset="1" stop-color="#f8fafc"/>
                        </linearGradient>
                        <linearGradient id="g2" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0" stop-color="#111827"/>
                            <stop offset="1" stop-color="#374151"/>
                        </linearGradient>
                    </defs>
                    <rect x="0" y="0" width="900" height="520" fill="url(#g1)"/>
                    <circle cx="120" cy="100" r="40" fill="#e5e7eb"/>
                    <circle cx="760" cy="90" r="26" fill="#e5e7eb"/>
                    <circle cx="820" cy="150" r="18" fill="#e5e7eb"/>
                    <rect x="150" y="140" width="520" height="270" rx="18" fill="#ffffff" stroke="#e5e7eb"/>
                    <rect x="190" y="180" width="220" height="18" rx="9" fill="#e5e7eb"/>
                    <rect x="190" y="212" width="320" height="18" rx="9" fill="#e5e7eb"/>
                    <rect x="190" y="244" width="280" height="18" rx="9" fill="#e5e7eb"/>
                    <rect x="190" y="298" width="420" height="70" rx="14" fill="#f3f4f6"/>
                    <rect x="610" y="160" width="160" height="250" rx="18" fill="#ffffff" stroke="#e5e7eb"/>
                    <rect x="640" y="188" width="100" height="26" rx="13" fill="#111827"/>
                    <rect x="640" y="226" width="110" height="10" rx="5" fill="#e5e7eb"/>
                    <rect x="640" y="246" width="90" height="10" rx="5" fill="#e5e7eb"/>
                    <rect x="640" y="266" width="120" height="10" rx="5" fill="#e5e7eb"/>
                    <circle cx="260" cy="410" r="44" fill="#e5e7eb"/>
                    <rect x="230" y="452" width="160" height="18" rx="9" fill="url(#g2)"/>
                    <circle cx="520" cy="410" r="44" fill="#e5e7eb"/>
                    <rect x="490" y="452" width="160" height="18" rx="9" fill="url(#g2)"/>
                    <rect x="120" y="470" width="660" height="10" rx="5" fill="#d1d5db"/>
                </svg>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2>Online Academic Service</h2>
                <p class="mb-1"><strong><?php echo htmlspecialchars($nama_kampus ? $nama_kampus : 'Universitas'); ?></strong></p>
                <p class="login-subtitle">Akses ke seluruh layanan akademik kampus dengan mudah dan cepat.</p>
            </div>
            
            <?php echo form_open('Auth/login', array('method' => 'post', 'id' => 'loginForm')); ?>
                <div class="form-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus value="<?php echo htmlspecialchars(set_value('username')); ?>">
                    <?php echo form_error('username', '<div class="error-message">', '</div>'); ?>
                </div>
                
                <div class="form-group password-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    <?php echo form_error('password', '<div class="error-message">', '</div>'); ?>
                </div>

                <div class="mood-wrap">
                    <div class="mood-title">Mood kamu hari ini?</div>
                    <input type="hidden" name="mood" id="moodInput" value="<?php echo htmlspecialchars(set_value('mood')); ?>">
                    <div class="mood-grid" id="moodGrid">
                        <button type="button" class="mood-btn" data-mood="😀" aria-label="Mood: Sangat senang" title="Sangat senang">😀</button>
                        <button type="button" class="mood-btn" data-mood="😄" aria-label="Mood: Senang" title="Senang">😄</button>
                        <button type="button" class="mood-btn" data-mood="🙂" aria-label="Mood: Baik" title="Baik">🙂</button>
                        <button type="button" class="mood-btn" data-mood="😐" aria-label="Mood: Biasa saja" title="Biasa saja">😐</button>
                        <button type="button" class="mood-btn" data-mood="😴" aria-label="Mood: Mengantuk" title="Mengantuk">😴</button>
                        <button type="button" class="mood-btn" data-mood="🤒" aria-label="Mood: Kurang sehat" title="Kurang sehat">🤒</button>
                        <button type="button" class="mood-btn" data-mood="😡" aria-label="Mood: Kesal" title="Kesal">😡</button>
                        <button type="button" class="mood-btn" data-mood="🥳" aria-label="Mood: Semangat" title="Semangat">🥳</button>
                        <button type="button" class="mood-btn" data-mood="😰" aria-label="Mood: Cemas" title="Cemas">😰</button>
                        <button type="button" class="mood-btn" data-mood="🤔" aria-label="Mood: Penasaran" title="Penasaran">🤔</button>
                    </div>
                    <?php echo form_error('mood', '<div class="error-message">', '</div>'); ?>
                    <div class="mood-hint">Opsional. Pilih emoji jika ingin.</div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    
    <!-- SweetAlert2 untuk Flash Messages -->
    <script>
    $(document).ready(function() {
        <?php if ($this->session->flashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: <?php echo json_encode($this->session->flashdata('success')); ?>,
                confirmButtonText: 'OK',
                confirmButtonColor: '#5b1025',
                timer: 3000,
                timerProgressBar: true
            });
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                html: <?php echo json_encode($this->session->flashdata('error')); ?>,
                confirmButtonText: 'OK',
                confirmButtonColor: '#5b1025'
            });
        <?php endif; ?>
        
        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            const passwordInput = $('#password');
            const passwordType = passwordInput.attr('type') === 'password' ? 'text' : 'password';
            passwordInput.attr('type', passwordType);
            
            // Toggle icon
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // Mood selection (optional)
        function setSelectedMood(mood) {
            $('#moodInput').val(mood);
            $('#moodGrid .mood-btn').removeClass('is-selected');
            $('#moodGrid .mood-btn').each(function() {
                if ($(this).data('mood') === mood) {
                    $(this).addClass('is-selected');
                }
            });
        }

        // Restore selection after validation error
        const initialMood = ($('#moodInput').val() || '').trim();
        if (initialMood) {
            setSelectedMood(initialMood);
        }

        $('#moodGrid').on('click', '.mood-btn', function() {
            setSelectedMood($(this).data('mood'));
        });
    });
    </script>
<?php $this->load->view('pwa/register'); ?>
</body>
</html>

