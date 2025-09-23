-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2025 at 04:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `generator_surat`
--

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `nip` varchar(20) NOT NULL,
  `nama_pegawai` varchar(100) NOT NULL,
  `pangkat` varchar(50) DEFAULT NULL,
  `golongan` varchar(10) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`nip`, `nama_pegawai`, `pangkat`, `golongan`, `jabatan`) VALUES
('196603071991031001', 'Sjaeful Irwan', 'Pembina Utama Muda', 'IV/c', 'Analis Kebijakan Ahli Madya'),
('196707171993031002', 'Agus Susilohadi', 'Pembina Tk. I', 'IV/b', 'Analis Kebijakan Ahli Madya'),
('196801031990012001', 'Sri Murwati', 'Penata Tk. I', 'III/d', 'Pengolah Data dan Informasi'),
('196803021993032001', 'Sylvia Supartiningsih', 'Pembina Tk. I', 'IV/b', 'Analis Kebijakan Ahli Madya'),
('196804271989121001', 'Budi Sutrisno', 'Penata Muda Tk. I', 'III/b', 'Pengadministrasi Perkantoran'),
('196809201994032002', 'Dyah Kartiningdyah', 'Pembina', 'IV/a', 'Analis Kebijakan Ahli Madya'),
('196906161993032001', 'Vera Susilawati Sihombing', 'Penata Muda Tk. I', 'III/b', 'Pengadministrasi Perkantoran'),
('197001261993082001', 'Dwi Asmarina', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('197008151990092001', 'Sadariah', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('197011012002121001', 'Djoko Prihantoro', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('197011222005011001', 'Mahmud Yudonegoro', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('197105102005011001', 'Bakhtiar', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('197106121992031006', 'Dedy Saputra', 'Pembina Tk. I', 'IV/b', 'Analis Kebijakan Ahli Madya'),
('197107041993031002', 'Rusdih', 'Penata Muda Tk. I', 'III/b', 'Pengadministrasi Perkantoran'),
('197110041993031001', 'Naryana', 'Pembina', 'IV/a', 'Perencana Ahli Muda'),
('197203231998032001', 'Koeni Pudyastuti', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('197204162008101001', 'Priya Sesotya', 'Penata', 'III/c', 'Pengadministrasi Perkantoran'),
('197303052009101002', 'Sukistiyanto', 'Penata Muda Tk. I', 'III/b', 'Penelaah Teknis Kebijakan'),
('197402131999031001', 'Yudi Darma', 'Pembina Utama Madya', 'IV/d', 'Profesor/Guru Besar'),
('197404302009101002', 'Ismail', 'Pengatur Muda', 'II/a', 'Pengadministrasi Perkantoran'),
('197408162014091002', 'Aris Winarna', 'Penata Muda', 'III/a', 'Pengolah Data dan Informasi'),
('197607262009102001', 'Eva Wany Ellyza', 'Penata Tk. I', 'III/d', 'Analis Kebijakan Ahli Muda'),
('197607292010122001', 'Russy Arumsari', 'Penata Tk. I', 'III/d', 'Analis Kebijakan Ahli Muda'),
('197611142010121001', 'Jimmi', 'Penata Tk. I', 'III/d', 'Analis Kebijakan Ahli Muda'),
('197701272005011001', 'Adi Nuryanto', 'Pembina Tk. I', 'IV/b', 'Direktur Kemitraan dan Penyelarasan Dunia Usaha dan Dunia Industri, Direktorat Jenderal Pendidikan V'),
('197702112008011007', 'Arief Sanjaya', 'Penata Tk. I', 'III/d', 'Analis Pengelolaan Keuangan APBN Ahli Muda'),
('197705192009102002', 'Erna Widyastuti, S.H., M.H.', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('197705302010121003', 'Frenky Albert Sitompul', 'Penata Tk. I', 'III/d', 'Arsiparis Ahli Pertama'),
('197707112009102003', 'Anggit Pulungsih', 'Penata', 'III/c', 'Pengadministrasi Perkantoran'),
('197708301999021001', 'Bunyamin Sahid', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('197709272010121001', 'Ardi Wasita Kusumah', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('197710272009122002', 'Ardi Findyartini', 'Pembina', 'IV/a', 'Profesor/Guru Besar'),
('197712072014091001', 'Wisnu Wiguna', 'Penata Muda', 'III/a', 'Pengadministrasi Perkantoran'),
('197807132005012001', 'Wiwin Yudiarti', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('197808282001121001', 'Rafid Prentha', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('197809092003121003', 'Yudhi Kurniawan', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('197810022005021002', 'Pramono, S.E., Ak., M.Ak.', 'Pembina Tk. I', 'IV/b', 'APK APBN Ahli Madya'),
('197811062014091003', 'Burhanuddin Ali', 'Penata Muda', 'III/a', 'Pengolah Data dan Informasi'),
('197811172002121001', 'Irwanto', 'Penata Tk. I', 'III/d', 'Pengolah Data dan Informasi'),
('197811212010121001', 'Teguh Susanto', 'Penata Tk. I', 'III/d', 'Arsiparis Ahli Pertama'),
('197812022008112001', 'Isabel Sibarani', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('197812252006041004', 'Andante Candra Isana Purbokusumo', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('197901022005012010', 'Eni Susanti', 'Pembina', 'IV/a', 'Analis Kebijakan Ahli Muda'),
('197901142003121000', 'M Samsuri', 'Pembina Utama Muda', 'IV/c', 'Kepala Lembaga Layanan Pendidikan Tinggi Wilayah IV'),
('197904212007122002', 'Neneng Khafidho', 'Pembina', 'IV/a', 'Analis Kebijakan Ahli Madya'),
('197908112006042027', 'Indri Hapsari', 'Penata Tk. I', 'III/d', 'Analis Kebijakan Ahli Muda'),
('197910032008121003', 'Agung Budi Prasetyo', 'Penata', 'III/c', 'Pengolah Data dan Informasi'),
('197910112005012001', 'Carolina', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('198001232012121002', 'Mohamad Ichsan, S.Si., M.Hum.', 'Penata Tk. I', 'III/d', 'Perencana Ahli Muda'),
('198002052003122003', 'Ratna Prabandari', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('198003282009101002', 'Raden Nur Cahyono', 'Pengatur', 'II/c', 'Pengadministrasi Perkantoran'),
('198007132014091002', 'Dimas Raditya Trilaksono', 'Penata', 'III/c', 'Penelaah Teknis Kebijakan'),
('198101282010121003', 'Moh.Rif`An Jauhari', 'Penata Tk. I', 'III/d', 'Pranata Hubungan Masyarakat Ahli Muda'),
('198104032006041003', 'Iim Ibrahim Umar', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('198105152005011004', 'Rizal Alfian', 'Pembina', 'IV/a', 'Penelaah Teknis Kebijakan'),
('198107252010122001', 'Roosida Taufani', 'Penata Tk. I', 'III/d', 'Analis Kebijakan Ahli Madya'),
('198203112010121002', 'Leonardo Sahat Hamonangan', 'Penata Tk. I', 'III/d', 'Pengolah Data dan Informasi'),
('198205082009101001', 'Harmoko, S.Sos', 'Penata Muda Tk. I', 'III/b', 'Penelaah Teknis Kebijakan'),
('198301232010011013', 'Riki Andesco', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('198303212009101002', 'Rendhany', 'Penata', 'III/c', 'Pengolah Data dan Informasi'),
('198304102010122006', 'Citra Amitiurna', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('198401302010122001', 'Paramita Wikansari', 'Penata Tk. I', 'III/d', 'Analis Kebijakan Ahli Muda'),
('198402082010121004', 'Dona Verri Herlambang', 'Penata Muda Tk. I', 'III/b', 'Pengolah Data dan Informasi'),
('198403152009121005', 'Yoggi Herdani', 'Penata', 'III/c', 'Pranata Hubungan Masyarakat Ahli Muda'),
('198405162008011007', 'Erwiyanto Mohammad Irvan', 'Pengatur', 'II/c', 'Pengadministrasi Perkantoran'),
('198501272010122006', 'Ari Widayati', 'Penata Tk. I', 'III/d', 'Analis Pengelolaan Keuangan APBN Ahli Muda'),
('198504222014041002', 'Bayu Tri Prasetyo', 'Penata', 'III/c', 'Penelaah Teknis Kebijakan'),
('198506122010122005', 'Rian Sari', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('198605092009101001', 'Bambang Tulus   Setyadi', 'Pengatur Muda Tk. I', 'II/b', 'Pengadministrasi Perkantoran'),
('198608082010121007', 'Agus Wibowo', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('198612122023212058', 'Uswatun Hasanah', 'Penata Muda', 'III/a', 'Pranata Hubungan Masyarakat Ahli Pertama'),
('198710282010121006', 'Maulana Okto', 'Penata Tk. I', 'III/d', 'Kepala Subbagian Tata Usaha, Direktorat Kemitraan dan Penyelarasan Dunia Usaha dan Dunia Industri, D'),
('198806282011012012', 'Sri Marasi Lm Aritonang', 'Penata Tk. I', 'III/d', 'Penelaah Teknis Kebijakan'),
('198808282018012001', 'Rizqi Ratna Utami', 'Penata Muda Tk. I', 'III/b', 'Pranata Keuangan APBN Mahir'),
('198905032022032008', 'Pretty Lastsweet Ademeydhie Fitria', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199107082019032022', 'Aditya Nur Rochma', 'Penata Muda Tk. I', 'III/b', 'Pengolah Data dan Informasi'),
('199108162022031006', 'Fauzan Adhi Nugroho, S.Pd.', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199109232019032021', 'Fatimatuz Zahro', 'Penata Muda', 'III/a', 'Pengolah Data dan Informasi'),
('199110242015042001', 'Octaviani Khristiya Hapsari', 'Penata', 'III/c', 'Penelaah Teknis Kebijakan'),
('199111102019031018', 'Satria Yudha Herawan', 'Penata Muda Tk. I', 'III/b', 'Penelaah Teknis Kebijakan'),
('199210232022032005', 'Annisantyas Nugraheny', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199305022022032022', 'Fanny Silviane', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199308242022031001', 'Ahmad Nazar Rinaldi', 'Pengatur', 'II/c', 'Pengolah Data dan Informasi'),
('199310072023212029', 'Iradhatie Wurinanda', 'Penata Muda', 'III/a', 'Pranata Hubungan Masyarakat Ahli Pertama'),
('199312262019031007', 'Ayodya Mahendra Widiandana', 'Penata Muda', 'III/a', 'Pengolah Data dan Informasi'),
('199407222022031019', 'Muhammad Tirta Purnomo', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199506222018012002', 'Sinthya Yunita', 'Penata Muda Tk. I', 'III/b', 'Penelaah Teknis Kebijakan'),
('199510072022032016', 'Dela Fahriana Havityaningtyas', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199510102022032029', 'Rahel Eunike Priskila', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199609202022032025', 'Lufty Syofaah', 'Pengatur', 'II/c', 'Pengolah Data dan Informasi'),
('199609292022032025', 'Wilyan Ade Siwi', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199701082022031013', 'Nuansa Fajar Ramadhan', 'Penata Muda', 'III/a', 'Penelaah Teknis Kebijakan'),
('199701312019031001', 'Zumhur Pranata Sitorus', 'Pengatur Tk. I', 'II/d', 'Pengolah Data dan Informasi'),
('199702052022032021', 'Aini Amlia Saman', 'Pengatur', 'II/c', 'Pengolah Data dan Informasi'),
('199910242022032004', 'Ayu Wulandari', 'Pengatur', 'II/c', 'Pengolah Data dan Informasi');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `no_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`nip`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`no_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `no_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
