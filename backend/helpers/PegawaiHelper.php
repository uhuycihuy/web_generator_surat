<?php
function processPegawaiList($selectedPegawai, $db) {
    $daftarPegawai = [];
    
    foreach ($selectedPegawai as $nipPegawai) {
        if (strpos($nipPegawai, 'L|') === 0) {
            // Pegawai eksternal
            $parts = explode('|', $nipPegawai);
            $daftarPegawai[] = [
                'nama_pegawai' => $parts[1] ?? 'Nama Eksternal',
                'nip' => '-',
                'pangkat' => '-',
                'golongan' => '-',
                'jabatan' => $parts[2] ?? 'Jabatan Eksternal',
                'is_external' => true
            ];
        } else {
            // Pegawai internal
            $stmt = $db->prepare("SELECT * FROM pegawai WHERE nip = ?");
            $stmt->execute([$nipPegawai]);
            $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($pegawai) {
                $pegawai['is_external'] = false;
                $daftarPegawai[] = $pegawai;
            }
        }
    }
    
    return $daftarPegawai;
}
?>