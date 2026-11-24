<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Progress Proposal TA Mahasiswa</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Registrasi_proposal'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo htmlspecialchars($proposal->nim); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($proposal->nama_mahasiswa); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($proposal->kelas ?: '-'); ?></span></td>
                            </tr>
                            <tr>
                                <th>Tahun Akademik</th>
                                <td>
                                    <?php 
                                    if (isset($proposal->tahun_akademik) && isset($proposal->semester)) {
                                        echo htmlspecialchars($proposal->tahun_akademik . ' ' . ucfirst($proposal->semester));
                                    } elseif (isset($tahun_aktif) && $tahun_aktif) {
                                        echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Progress Proposal TA -->
                <?php if (isset($proposal_progress) && $proposal_progress): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Progress Proposal TA</h5>
                            <div class="card card-outline card-success">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center mb-3">
                                            <div class="progress-circle-wrapper">
                                                <div class="progress-circle" data-percent="<?php echo $proposal_progress['percentage']; ?>" data-color="#28a745">
                                                    <svg class="progress-circle-svg" viewBox="0 0 200 200">
                                                        <circle class="progress-circle-bg" cx="100" cy="100" r="90"></circle>
                                                        <circle class="progress-circle-progress" cx="100" cy="100" r="90" 
                                                                data-percent="<?php echo $proposal_progress['percentage']; ?>"></circle>
                                                    </svg>
                                                    <div class="progress-circle-content">
                                                        <span class="progress-circle-percent"><?php echo $proposal_progress['percentage']; ?>%</span>
                                                        <span class="progress-circle-text"><?php echo $proposal_progress['current_step']; ?>/<?php echo $proposal_progress['total_steps']; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="mb-3">Tahapan Proposal TA</h6>
                                            <div class="progress-steps">
                                                <?php foreach ($proposal_progress['steps'] as $index => $step): ?>
                                                    <div class="progress-step-item <?php echo $step['completed'] ? 'completed' : ''; ?>">
                                                        <div class="step-indicator">
                                                            <?php if ($step['completed']): ?>
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-circle text-muted"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="step-label"><?php echo $step['name']; ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <style>
                    .progress-circle-wrapper {
                        display: flex;
                        justify-content: center;
                    }
                    
                    .progress-circle {
                        position: relative;
                        width: 200px;
                        height: 200px;
                        margin: 0 auto;
                    }
                    
                    .progress-circle-svg {
                        width: 100%;
                        height: 100%;
                        transform: rotate(-90deg);
                    }
                    
                    .progress-circle-bg {
                        fill: none;
                        stroke: #e0e0e0;
                        stroke-width: 15;
                    }
                    
                    .progress-circle-progress {
                        fill: none;
                        stroke-width: 15;
                        stroke-linecap: round;
                        stroke-dasharray: 565.48;
                        stroke-dashoffset: 565.48;
                        transition: stroke-dashoffset 1s ease-in-out;
                    }
                    
                    .progress-circle-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        text-align: center;
                    }
                    
                    .progress-circle-percent {
                        display: block;
                        font-size: 32px;
                        font-weight: bold;
                        color: #333;
                    }
                    
                    .progress-circle-text {
                        display: block;
                        font-size: 14px;
                        color: #666;
                        margin-top: 5px;
                    }
                    
                    .progress-steps {
                        text-align: left;
                        max-height: 400px;
                        overflow-y: auto;
                        padding: 5px;
                    }
                    
                    .progress-step-item {
                        display: flex;
                        align-items: center;
                        padding: 8px 0;
                        border-bottom: 1px solid #f0f0f0;
                    }
                    
                    .progress-step-item:last-child {
                        border-bottom: none;
                    }
                    
                    .progress-step-item.completed .step-label {
                        color: #28a745;
                        font-weight: 500;
                    }
                    
                    .step-indicator {
                        width: 30px;
                        text-align: center;
                        margin-right: 10px;
                    }
                    
                    .step-indicator i {
                        font-size: 18px;
                    }
                    
                    .step-label {
                        flex: 1;
                        font-size: 14px;
                        color: #666;
                    }
                    </style>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var proposalCircle = document.querySelector('.progress-circle-progress');
                        if (proposalCircle) {
                            var proposalPercent = <?php echo $proposal_progress['percentage']; ?>;
                            var proposalColor = '#28a745';
                            var circumference = 2 * Math.PI * 90;
                            var offset = circumference - (proposalPercent / 100) * circumference;
                            proposalCircle.style.strokeDashoffset = offset;
                            proposalCircle.style.stroke = proposalColor;
                        }
                    });
                    </script>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Data progress tidak tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

