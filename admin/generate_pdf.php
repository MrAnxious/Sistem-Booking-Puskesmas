<?php
// Start session terlebih dahulu
if (session_status() == PHP_SESSION_NONE) {
}

// Include database config
include '../config/database.php';

// Cek authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Default filter: bulan ini
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$poli_filter = isset($_GET['poli']) ? $_GET['poli'] : '';

// Ambil data poli untuk filter
$poli_query = "SELECT * FROM poli ORDER BY nama_poli";
$poli_result = mysqli_query($conn, $poli_query);

// Build query untuk laporan
$laporan_query = "SELECT p.nama_poli, 
                         COUNT(d.id) as total_kunjungan,
                         SUM(CASE WHEN d.status = 'selesai' THEN 1 ELSE 0 END) as total_hadir,
                         SUM(CASE WHEN d.status = 'tidak hadir' THEN 1 ELSE 0 END) as total_tidak_hadir
                  FROM pendaftaran d 
                  JOIN poli p ON d.id_poli = p.id 
                  WHERE DATE_FORMAT(d.tanggal_periksa, '%Y-%m') = '$bulan'";

if (!empty($poli_filter)) {
    $laporan_query .= " AND d.id_poli = $poli_filter";
}

$laporan_query .= " GROUP BY p.id, p.nama_poli ORDER BY total_kunjungan DESC";

$laporan_result = mysqli_query($conn, $laporan_query);

// Total statistik
$total_query = "SELECT 
                COUNT(*) as total_kunjungan,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN status = 'tidak hadir' THEN 1 ELSE 0 END) as total_tidak_hadir
                FROM pendaftaran 
                WHERE DATE_FORMAT(tanggal_periksa, '%Y-%m') = '$bulan'";
$total_result = mysqli_query($conn, $total_query);
$total_stats = mysqli_fetch_assoc($total_result);

// Persentase kehadiran
$persentase_hadir = $total_stats['total_kunjungan'] > 0 ?
    round(($total_stats['total_hadir'] / $total_stats['total_kunjungan']) * 100, 2) : 0;

// Data untuk detail kunjungan
$detail_query = "SELECT p.nama_lengkap, poli.nama_poli, d.tanggal_periksa, d.status, d.tanggal_daftar
                 FROM pendaftaran d 
                 JOIN pasien p ON d.id_pasien = p.id 
                 JOIN poli ON d.id_poli = poli.id 
                 WHERE DATE_FORMAT(d.tanggal_periksa, '%Y-%m') = '$bulan'";
if (!empty($poli_filter)) {
    $detail_query .= " AND d.id_poli = $poli_filter";
}
$detail_query .= " ORDER BY d.tanggal_periksa DESC, poli.nama_poli";
$detail_result = mysqli_query($conn, $detail_query);

// Hitung total detail
$total_detail = mysqli_num_rows($detail_result);

// Include Dompdf
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$options->set('isPhpEnabled', false);
$options->set('chroot', realpath('../'));

$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kunjungan Pasien</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 3px solid #16a085; 
            padding-bottom: 15px;
        }
        .title { 
            font-size: 24px; 
            font-weight: bold; 
            color: #16a085; 
            margin-bottom: 5px;
        }
        .subtitle { 
            font-size: 16px; 
            color: #666; 
            margin-bottom: 5px;
        }
        .info-container { 
            display: table; 
            width: 100%;
            margin: 20px 0; 
            font-size: 12px;
        }
        .info-column { 
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-item { 
            margin-bottom: 5px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            font-size: 10px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        .table th { 
            background-color: #e8f5e9; 
            font-weight: bold; 
            color: #16a085;
        }
        .stats-container { 
            margin: 20px 0; 
        }
        .stat-row { 
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .stat-card { 
            display: table-cell;
            width: 25%;
            border: 1px solid #c8e6c9; 
            padding: 15px; 
            text-align: center;
            background-color: #f8fffa;
            vertical-align: top;
        }
        .stat-number { 
            font-size: 24px; 
            font-weight: bold; 
            color: #16a085; 
            margin-bottom: 5px;
            display: block;
        }
        .stat-label { 
            font-size: 12px; 
            color: #666; 
            display: block;
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #16a085;
            margin: 25px 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #e8f5e9;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-highlight { background-color: #f1f8e9; }
        .page-break { page-break-before: always; }
        .signature-area {
            margin-top: 50px;
        }
        .signature-row {
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin: 40px 0 5px 0;
            width: 200px;
            display: inline-block;
        }
        .note-box {
            text-align: center; 
            margin: 20px 0; 
            font-style: italic; 
            color: #666;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN KUNJUNGAN PASIEN</div>
        <div class="subtitle">PUSKESMAS NALUMSARI</div>
        <div class="subtitle">Periode: ' . date('F Y', strtotime($bulan . '-01')) . '</div>
    </div>
    
    <div class="info-container">
        <div class="info-column">
            <div class="info-item"><strong>Tanggal Cetak:</strong> ' . date('d/m/Y H:i:s') . '</div>
            <div class="info-item"><strong>Dibuat Oleh:</strong> ' . htmlspecialchars($_SESSION['nama']) . ' (Admin)</div>
        </div>
        <div class="info-column">
            <div class="info-item"><strong>Periode Laporan:</strong> ' . date('F Y', strtotime($bulan . '-01')) . '</div>';

if (!empty($poli_filter)) {
    mysqli_data_seek($poli_result, 0);
    $poli_name = '';
    while ($poli = mysqli_fetch_assoc($poli_result)) {
        if ($poli['id'] == $poli_filter) {
            $poli_name = htmlspecialchars($poli['nama_poli']);
            break;
        }
    }
    $html .= '<div class="info-item"><strong>Poli:</strong> ' . $poli_name . '</div>';
} else {
    $html .= '<div class="info-item"><strong>Poli:</strong> Semua Poli</div>';
}

$html .= '
        </div>
    </div>
    
    <div class="section-title">STATISTIK UTAMA</div>
    <div class="stats-container">
        <div class="stat-row">
            <div class="stat-card">
                <span class="stat-number">' . $total_stats['total_kunjungan'] . '</span>
                <span class="stat-label">Total Kunjungan</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">' . $total_stats['total_hadir'] . '</span>
                <span class="stat-label">Pasien Hadir</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">' . $total_stats['total_tidak_hadir'] . '</span>
                <span class="stat-label">Tidak Hadir</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">' . $persentase_hadir . '%</span>
                <span class="stat-label">Tingkat Kehadiran</span>
            </div>
        </div>
    </div>
    
    <div class="section-title">RINGKASAN KUNJUNGAN PER POLI</div>
    <table class="table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="35%">Nama Poli</th>
                <th width="12%" class="text-center">Total Kunjungan</th>
                <th width="12%" class="text-center">Hadir</th>
                <th width="15%" class="text-center">Tidak Hadir</th>
                <th width="21%" class="text-center">Persentase Hadir</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
$total_all_kunjungan = 0;
$total_all_hadir = 0;
$total_all_tidak_hadir = 0;

mysqli_data_seek($laporan_result, 0);
while ($laporan = mysqli_fetch_assoc($laporan_result)) {
    $persentase = $laporan['total_kunjungan'] > 0 ?
        round(($laporan['total_hadir'] / $laporan['total_kunjungan']) * 100, 2) : 0;

    $total_all_kunjungan += $laporan['total_kunjungan'];
    $total_all_hadir += $laporan['total_hadir'];
    $total_all_tidak_hadir += $laporan['total_tidak_hadir'];

    $html .= '<tr>
                <td class="text-center">' . $no . '</td>
                <td>' . htmlspecialchars($laporan['nama_poli']) . '</td>
                <td class="text-center">' . $laporan['total_kunjungan'] . '</td>
                <td class="text-center">' . $laporan['total_hadir'] . '</td>
                <td class="text-center">' . $laporan['total_tidak_hadir'] . '</td>
                <td class="text-center">' . $persentase . '%</td>
              </tr>';
    $no++;
}

$total_persentase = $total_all_kunjungan > 0 ?
    round(($total_all_hadir / $total_all_kunjungan) * 100, 2) : 0;

$html .= '<tr class="bg-highlight">
            <td colspan="2" class="text-center"><strong>TOTAL</strong></td>
            <td class="text-center"><strong>' . $total_all_kunjungan . '</strong></td>
            <td class="text-center"><strong>' . $total_all_hadir . '</strong></td>
            <td class="text-center"><strong>' . $total_all_tidak_hadir . '</strong></td>
            <td class="text-center"><strong>' . $total_persentase . '%</strong></td>
          </tr>';

$html .= '</tbody>
    </table>';

// Detail transaksi (hanya ditampilkan jika data tidak terlalu banyak)
if ($total_detail > 0 && $total_detail <= 100) {
    $html .= '<div class="page-break"></div>
    <div class="section-title">DETAIL TRANSAKSI KUNJUNGAN</div>
    <table class="table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="30%">Nama Pasien</th>
                <th width="25%">Poli</th>
                <th width="15%" class="text-center">Tanggal Periksa</th>
                <th width="15%" class="text-center">Status</th>
                <th width="10%" class="text-center">Waktu Daftar</th>
            </tr>
        </thead>
        <tbody>';

    $no_detail = 1;
    mysqli_data_seek($detail_result, 0);

    while ($detail = mysqli_fetch_assoc($detail_result)) {
        $status_style = '';
        if ($detail['status'] == 'selesai') {
            $status_style = 'style="color: #16a085; font-weight: bold;"';
        } elseif ($detail['status'] == 'tidak hadir') {
            $status_style = 'style="color: #dc2626;"';
        }

        $html .= '<tr>
                    <td class="text-center">' . $no_detail . '</td>
                    <td>' . htmlspecialchars($detail['nama_lengkap']) . '</td>
                    <td>' . htmlspecialchars($detail['nama_poli']) . '</td>
                    <td class="text-center">' . date('d/m/Y', strtotime($detail['tanggal_periksa'])) . '</td>
                    <td class="text-center" ' . $status_style . '>' . ucfirst($detail['status']) . '</td>
                    <td class="text-center">' . date('H:i', strtotime($detail['tanggal_daftar'])) . '</td>
                  </tr>';
        $no_detail++;
    }

    $html .= '</tbody>
    </table>';
} else {
    $html .= '<div class="note-box">
                Catatan: Detail transaksi tidak ditampilkan karena jumlah data terlalu banyak (' . $total_detail . ' records)
              </div>';
}

$html .= '
    <div class="footer">
        <strong>Dokumen ini dibuat secara otomatis oleh Sistem Puskesmas Online</strong><br>
        ' . date('d F Y H:i:s') . ' | Halaman 1
    </div>
    
    <div class="signature-area">
        <div class="signature-row">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div>Kepala Puskesmas</div>
                <div class="signature-line"></div>
                <div>(_______________________)</div>
            </div>
            <div class="signature-box">
                <div>Yang Membuat,</div>
                <div>Administrator</div>
                <div class="signature-line"></div>
                <div>(' . htmlspecialchars($_SESSION['nama']) . ')</div>
            </div>
        </div>
    </div>
</body>
</html>';

try {
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output PDF untuk di-download
    $dompdf->stream('laporan_kunjungan_' . $bulan . '.pdf', [
        'Attachment' => true
    ]);

} catch (Exception $e) {
    // Jika terjadi error, tampilkan pesan
    echo "Error generating PDF: " . $e->getMessage();
    exit();
}

exit();
?>