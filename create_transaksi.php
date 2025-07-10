<?php
header("Content-Type: application/json");
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Metode tidak diizinkan. Gunakan POST."]);
    exit;
}

if (!isset($_POST['noBuktiTransaksi'], $_POST['noPolisi'], $_POST['pemilik'], $_POST['tanggalTransaksi'], $_POST['jenisKendaraan'], $_POST['tarif'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Kolom wajib tidak lengkap."]);
    exit;
}

$noBuktiTransaksi = $_POST['noBuktiTransaksi'];
$noPolisi = $_POST['noPolisi'];
$pemilik = $_POST['pemilik'];
$tanggalTransaksi = $_POST['tanggalTransaksi'];
$jenisKendaraan = $_POST['jenisKendaraan'];
$tarif = $_POST['tarif'];

// Proses upload foto
$relativePath = null;
if (isset($_FILES['imageUrl']) && $_FILES['imageUrl']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    $relativePath = $uploadDir . uniqid() . '_' . basename($_FILES['imageUrl']['name']);
    $absolutePath = __DIR__ . '/' . $relativePath;

    // Buat folder jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!move_uploaded_file($_FILES['imageUrl']['tmp_name'], $absolutePath)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Gagal mengupload foto."]);
        exit;
    }
}

// Simpan data ke database
$stmt = $conn->prepare("INSERT INTO transaksi (noBuktiTransaksi, noPolisi, pemilik, tanggalTransaksi, jenisKendaraan, tarif, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $noBuktiTransaksi, $noPolisi, $pemilik, $tanggalTransaksi, $jenisKendaraan, $tarif, $relativePath);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Transaksi berhasil ditambahkan."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>