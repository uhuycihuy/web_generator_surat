<?php
// Template untuk surat tugas - surat_tugas.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .kop-surat {
            font-weight: bold;
            font-size: 12pt;
            line-height: 1.2;
        }
        .alamat {
            font-size: 10pt;
            margin-top: 10px;
            line-height: 1.3;
        }
        .judul-surat {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin: 20px 0;
        }
        .nomor-surat {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 20px;
        }
        .isi-surat {
            text-align: justify;
            margin-bottom: 20px;
        }
        .tabel-pegawai {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }
        .tabel-pegawai th, .tabel-pegawai td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        .tabel-pegawai th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .col-no {
            width: 5%;
            text-align: center;
        }
        .col-nama {
            width: 45%;
        }
        .col-jabatan {
            width: 50%;
        }
        .nama-pegawai {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .nip-pangkat {
            font-size: 9pt;
            line-height: 1.2;
        }
        .penutup {
            text-align: justify;
            margin: 20px 0;
        }
        .ttd {
            margin-top: 30px;
            float: right;
            width: 300px;
            text-align: center;
        }
        .ttd-jabatan {
            margin-bottom: 80px;
        }
        .ttd-nama {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .ttd-nip {
            font-size: 10pt;
        }
        .tembusan {
            clear: both;
            margin-top: 100px;
            font-size: 10pt;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Header Kop Surat -->
    <div class="header">
        <div class="kop-surat">
            KEMENTERIAN PENDIDIKAN TINGGI,<br>
            SAINS, DAN TEKNOLOGI<br><br>
            <strong>DIREKTORAT JENDERAL SAINS DAN TEKNOLOGI</strong>
        </div>
        <div class="alamat">
            Jalan Jenderal Sudirman, Senayan, Jakarta 10270<br>
            Telepon (021) 57946104, Pusat Panggilan ULT DIKTI 126<br>
            Laman <u>www.kemdiktisaintek.go.id</u>
        </div>
    </div>

    <!-- Judul Surat -->
    <div class="judul-surat">
        SURAT TUGAS
    </div>

    <!-- Nomor Surat -->
    <div class="nomor-surat"> 
        Nomor: ___________________
    </div>

    <!-- Isi Pembuka -->
    <div class="isi-surat">
        Dalam rangka kegiatan <strong><?= htmlspecialchars($acara) ?></strong>, dengan ini 
        <?= htmlspecialchars($pejabatJabatan) ?> menugaskan kepada nama di bawah ini,
    </div>

    <!-- Tabel Daftar Pegawai -->
    <table class="tabel-pegawai">
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-nama">Nama, NIP, Pangkat dan Golongan</th>
                <th class="col-jabatan">Jabatan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($daftarPegawai as $pegawai): ?>
            <tr>
                <td class="col-no"><?= $no ?>.</td>
                <td class="col-nama">
                    <div class="nama-pegawai"><?= htmlspecialchars($pegawai['nama_pegawai']) ?></div>
                    <?php if (!empty($pegawai['nip'])): ?>
                    <div class="nip-pangkat">
                        <?= htmlspecialchars($pegawai['nip']) ?><br>
                        <?php if (!empty($pegawai['pangkat'])): ?>
                        <?= htmlspecialchars($pegawai['pangkat']) ?><?php if (!empty($pegawai['golongan'])): ?>, <?= htmlspecialchars($pegawai['golongan']) ?><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td class="col-jabatan"><?= htmlspecialchars($pegawai['jabatan']) ?></td>
            </tr>
            <?php $no++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Isi Tugas -->
    <div class="isi-surat">
        Untuk hadir dan melaksanakan tugas dalam kegiatan dimaksud yang akan diselenggarakan 
        pada hari <?= $tanggalFormatted ?>, 
        bertempat di <?= htmlspecialchars($lokasi) ?>.
    </div>

    <!-- DIPA -->
    <div class="isi-surat">
        Biaya kegiatan dibebankan kepada DIPA Satuan Kerja Direktorat Jenderal Sains dan Teknologi, 
        Nomor: <?= htmlspecialchars($dipa) ?>.
    </div>

    <!-- Penutup -->
    <div class="penutup">
        Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan yang bersangkutan 
        diharapkan membuat laporan.
    </div>

    <!-- Tanda Tangan -->
    <div class="ttd">
        <div class="ttd-jabatan"><?= htmlspecialchars($pejabatJabatan) ?>,</div>
        <div class="ttd-nama"><?= htmlspecialchars($namaPejabat) ?></div>
        <div class="ttd-nip">NIP <?= htmlspecialchars($nipPejabat) ?></div>
    </div>

    <div class="clear"></div>

    <!-- Tembusan -->
    <?php if (!empty($tembusan)): ?>
    <div class="tembusan">
        <strong>Tembusan:</strong><br>
        <?= nl2br(htmlspecialchars($tembusan)) ?>
    </div>
    <?php endif; ?>
</body>
</html>