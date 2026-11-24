<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $page_title; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <?php foreach ($breadcrumb as $item): ?>
                            <?php if (isset($item['active']) && $item['active']): ?>
                                <li class="breadcrumb-item active"><?php echo $item['label']; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo $item['url']; ?>"><?php echo $item['label']; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $this->session->flashdata('error'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header py-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Informasi
                            </h5>
                        </div>
                        <div class="card-body py-2">
                            <?php if ($tahun_aktif): ?>
                                <p class="mb-0">
                                    <strong>Tahun Akademik Aktif:</strong> 
                                    <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                                </p>
                            <?php else: ?>
                                <p class="text-danger mb-0">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Mapping MK -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book"></i> Mapping MK
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Mapping mata kuliah dengan dosen pengampu per kelas.
                            </p>
                            <a href="<?php echo site_url('Laporan/export_mapping_mk'); ?>" class="btn btn-primary btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Jadwal Kuliah -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-success card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt"></i> Jadwal Kuliah
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Jadwal kuliah lengkap dengan ruang, waktu, dan dosen.
                            </p>
                            <a href="<?php echo site_url('Laporan/export_jadwal_kuliah'); ?>" class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Jadwal UTS -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-info card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clipboard-list"></i> Jadwal UTS
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Jadwal UTS lengkap dengan pengawas dan sesi.
                            </p>
                            <a href="<?php echo site_url('Laporan/export_jadwal_uts'); ?>" class="btn btn-info btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Jadwal UAS -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-warning card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clipboard-check"></i> Jadwal UAS
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Jadwal UAS lengkap dengan pengawas dan sesi.
                            </p>
                            <a href="<?php echo site_url('Laporan/export_jadwal_uas'); ?>" class="btn btn-warning btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Honor UTS -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-danger card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-money-bill-wave"></i> Honor UTS
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Honor UTS (pembuatan soal, koreksi, pengawas).
                            </p>
                            <a href="<?php echo site_url('Laporan/export_honor_uts'); ?>" class="btn btn-danger btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Honor UAS -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-secondary card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-money-bill-alt"></i> Honor UAS
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Honor UAS (pembuatan soal, koreksi, pengawas).
                            </p>
                            <a href="<?php echo site_url('Laporan/export_honor_uas'); ?>" class="btn btn-secondary btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Rekap Berita Acara -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-dark card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-alt"></i> Rekap Berita Acara
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Daftar mahasiswa tidak hadir UTS/UAS.
                            </p>
                            <a href="<?php echo site_url('Laporan/export_rekap_berita_acara'); ?>" class="btn btn-dark btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Daftar Mahasiswa Per Kelas -->
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card card-teal card-outline h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users"></i> Mahasiswa Per Kelas
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-2">
                                Daftar mahasiswa dikelompokkan per kelas.
                            </p>
                            <a href="<?php echo site_url('Laporan/export_mahasiswa_per_kelas'); ?>" class="btn btn-teal btn-sm btn-block">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .card-teal {
        border-color: #20c997;
    }
    .card-teal .card-header {
        background-color: #20c997;
        color: white;
    }
    .btn-teal {
        background-color: #20c997;
        border-color: #20c997;
        color: white;
    }
    .btn-teal:hover {
        background-color: #1aa179;
        border-color: #1aa179;
        color: white;
    }
    .card-body.p-2 {
        padding: 0.5rem !important;
    }
    .card-header h5 {
        font-size: 0.95rem;
    }
    .card-text.small {
        font-size: 0.85rem;
        line-height: 1.3;
    }
</style>
