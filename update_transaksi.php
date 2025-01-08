<?php
header("Content-Type: application/json");
include 'config.php';

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Metode tidak diizinkan
    echo json_encode(["success" => false, "message" => "Metode tidak diizinkan. Gunakan POST."]);
    exit;
}

// Validasi apakah parameter `id` ada di URL
if (!isset($_GET['id'])) {
    http_response_code(400); // Permintaan buruk
    echo json_encode(["success" => false, "message" => "Parameter ID harus disertakan di URL."]);
    exit;
}

$id = $_GET['id']; // Ambil `id` dari URL

// Validasi input POST
if (!isset($_POST['noBuktiTransaksi'], $_POST['noPolisi'], $_POST['pemilik'], $_POST['tanggalTransaksi'], $_POST['jenisKendaraan'], $_POST['tarif'])) {
    http_response_code(400); // Permintaan buruk
    echo json_encode(["success" => false, "message" => "Kolom wajib tidak lengkap."]);
    exit;
}

$noBuktiTransaksi = $_POST['noBuktiTransaksi'];
$noPolisi = $_POST['noPolisi'];
$pemilik = $_POST['pemilik'];
$tanggalTransaksi = $_POST['tanggalTransaksi'];
$jenisKendaraan = $_POST['jenisKendaraan'];
$tarif = $_POST['tarif'];

// Proses upload foto jika ada
$FotoPath = null;
if (isset($_FILES['imageUrl']) && $_FILES['imageUrl']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    $FotoPath = $uploadDir . uniqid() . '_' . basename($_FILES['imageUrl']['name']);
    if (!move_uploaded_file($_FILES['imageUrl']['tmp_name'], $FotoPath)) {
        http_response_code(500); // Kesalahan server
        echo json_encode(["success" => false, "message" => "Gagal mengupload foto."]);
        exit;
    }
}

// Update data ke database
$sql = "UPDATE transaksi 
        SET noBuktiTransaksi = ?, noPolisi = ?, pemilik = ?, tanggalTransaksi = ?, jenisKendaraan = ?, tarif = ?, imageUrl = ? 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $noBuktiTransaksi, $noPolisi, $pemilik, $tanggalTransaksi, $jenisKendaraan, $tarif, $FotoPath, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Transaksi berhasil diperbarui."]);
} else {
    http_response_code(500); // Kesalahan server
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
