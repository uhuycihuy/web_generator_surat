<?php
// Template untuk surat undangan - surat_undangan.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Undangan</title>
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
        .nomor-lampiran {
            margin: 20px 0;
            font-size: 11pt;
        }
        .nomor-lampiran div {
            margin-bottom: 5px;
        }
        .yth {
            margin: 20px 0;
            font-size: 11pt;
        }
        .yth-header {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .daftar-undangan {
            margin-left: 20px;
            margin-bottom: 20px;
        }
        .daftar-undangan div {
            margin-bottom: 5px;
        }
        .isi-surat {
            text-align: justify;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .detail-acara {
            margin: 20px 0;
            padding-left: 20px;
        }
        .detail-acara div {
            margin-bottom: 8px;
            display: flex;
        }
        .detail-label {
            width: 120px;
            flex-shrink: 0;
        }
        .detail-separator {
            width: 20px;
            flex-shrink: 0;
        }
        .detail-value {
            flex: 1;
        }
        .online-info {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .online-info h4 {
            margin: 0 0 10px 0;
            font-size: 11pt;
            font-weight: bold;
        }
        .agenda-section {
            margin: 20px 0;
        }
        .agenda-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .narahubung {
            margin: 20px 0;
            font-size: 10pt;
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
        .link {
            color: blue;
            text-decoration: underline;
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

    <!-- Nomor, Lampiran, Hal -->
    <div class="nomor-lampiran">
        <div><strong>Nomor</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= !empty($rangka) ? htmlspecialchars($rangka) : '___________________' ?></div>
        <div><strong>Lampiran</strong>&nbsp;: satu lembar</div>
        <div><strong>Hal</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Undangan</div>
    </div>

    <!-- Yth. -->
    <div class="yth">
        <div class="yth-header">Yth. Peserta Kegiatan</div>
        <div>(daftar terlampir)</div>
    </div>

    <!-- Isi Pembuka -->
    <div class="isi-surat">
        Dalam rangka membangun <em><?= htmlspecialchars($agenda) ?></em>, kami 
        bermaksud menyelenggarakan rapat yang ditujukan bagi para dosen dan peneliti. Sehubungan 
        dengan hal tersebut, kami mengundang Bapak/Ibu untuk berkenan hadir dan berpartisipasi dalam 
        rapat yang akan dilaksanakan pada
    </div>

    <!-- Detail Acara -->
    <div class="detail-acara">
        <div>
            <span class="detail-label">hari, tanggal</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><?= htmlspecialchars($hariTanggal) ?></span>
        </div>
        <div>
            <span class="detail-label">waktu</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><?= htmlspecialchars($waktu) ?></span>
        </div>
        
        <?php if ($jenisAcara === 'online'): ?>
        <div>
            <span class="detail-label">media</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><?= htmlspecialchars($media) ?></span>
        </div>
        <?php if (!empty($rapat_id)): ?>
        <div>
            <span class="detail-label">rapat id</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><?= htmlspecialchars($rapat_id) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($sandi)): ?>
        <div>
            <span class="detail-label">kata sandi</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><?= htmlspecialchars($sandi) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($tautan)): ?>
        <div>
            <span class="detail-label">tautan</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><span class="link"><?= htmlspecialchars($tautan) ?></span></span>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div>
            <span class="detail-label">tempat</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><?= htmlspecialchars($tempat) ?></span>
        </div>
        <?php endif; ?>
        
        <div>
            <span class="detail-label">agenda</span>
            <span class="detail-separator">:</span>
            <span class="detail-value"><?= htmlspecialchars($agenda) ?></span>
        </div>
    </div>

    <!-- Informasi Tambahan Online -->
    <?php if ($jenisAcara === 'online'): ?>
    <div class="online-info">
        <h4>Informasi Rapat Online</h4>
        <p>Rapat akan dilaksanakan secara online menggunakan <?= htmlspecialchars($media) ?>. 
        Pastikan Bapak/Ibu telah menyiapkan koneksi internet yang stabil dan perangkat yang mendukung.</p>
    </div>
    <?php endif; ?>

    <!-- Agenda Detail (jika ada opsional) -->
    <?php if (!empty($opsional)): ?>
    <div class="agenda-section">
        <div class="agenda-title">Informasi Tambahan:</div>
        <div class="isi-surat"><?= nl2br(htmlspecialchars($opsional)) ?></div>
    </div>
    <?php endif; ?>

    <!-- Harapan Kehadiran -->
    <div class="isi-surat">
        Besar harapan kami Bapak/Ibu dapat meluangkan waktu untuk hadir dalam rapat dimaksud guna 
        memberikan pemahaman yang lebih mendalam mengenai pentingnya integritas akademik, 
        khususnya dalam pengelolaan jurnal ilmiah, serta memperkuat kolaborasi antar civitas akademika 
        dan lembaga riset.
    </div>

    <!-- Narahubung -->
    <?php if (!empty($narahubung)): ?>
    <div class="narahubung">
        Untuk informasi lebih lanjut mengenai rapat dan konfirmasi kehadiran, tim Bapak/Ibu dapat 
        menghubungi <?= htmlspecialchars($narahubung) ?><?php if (!empty($no_narahubung)): ?> di nomor <?= htmlspecialchars($no_narahubung) ?><?php endif; ?>.
    </div>
    <?php endif; ?>

    <!-- Penutup -->
    <div class="isi-surat">
        Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan 
        terima kasih.
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

    <!-- Halaman Kedua: Daftar Lampiran -->
    <div style="page-break-before: always; margin-top: 50px;">
        <div class="nomor-lampiran">
            <div><strong>Lampiran</strong></div>
            <div><strong>Nomor</strong>&nbsp;&nbsp;&nbsp;: <?= !empty($rangka) ? htmlspecialchars($rangka) : '___________________' ?></div>
            <div><strong>Tanggal</strong>&nbsp;: <?= date('d F Y') ?></div>
        </div>

        <div class="yth" style="margin-top: 30px;">
            <div class="yth-header">Yth.</div>
            <div class="daftar-undangan">
                <?php $no = 1; ?>
                <?php foreach ($daftarPegawai as $pegawai): ?>
                <div><?= $no ?>. <?= htmlspecialchars($pegawai['nama_pegawai']) ?>, <?= htmlspecialchars($pegawai['jabatan']) ?></div>
                <?php $no++; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>