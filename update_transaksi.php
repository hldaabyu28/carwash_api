<?php
header("Content-Type: application/json");
include 'config.php';

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Metode tidak diizinkan. Gunakan POST."]);
    exit;
}

// Validasi parameter ID
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parameter ID harus disertakan di URL."]);
    exit;
}

$id = $_GET['id'];

// Validasi input POST
$required = ['noBuktiTransaksi', 'noPolisi', 'pemilik', 'tanggalTransaksi', 'jenisKendaraan', 'tarif'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Kolom '$field' tidak boleh kosong."]);
        exit;
    }
}

// Ambil data
$noBuktiTransaksi = $_POST['noBuktiTransaksi'];
$noPolisi = $_POST['noPolisi'];
$pemilik = $_POST['pemilik'];
$tanggalTransaksi = $_POST['tanggalTransaksi'];
$jenisKendaraan = $_POST['jenisKendaraan'];
$tarif = $_POST['tarif'];
$FotoPath = null;

// Cek apakah ada gambar baru yang diupload
if (isset($_FILES['imageUrl']) && $_FILES['imageUrl']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $uniqueName = uniqid() . '_' . basename($_FILES['imageUrl']['name']);
    $FotoPath = $uploadDir . $uniqueName;

    if (!move_uploaded_file($_FILES['imageUrl']['tmp_name'], $FotoPath)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Gagal mengupload gambar."]);
        exit;
    }
}

// Jika tidak ada gambar yang diupload, ambil imageUrl lama dari database
if (!$FotoPath) {
    $query = $conn->prepare("SELECT imageUrl FROM transaksi WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->bind_result($existingImage);
    if ($query->fetch()) {
        $FotoPath = $existingImage;
    }
    $query->close();
}

// Update ke database
$sql = "UPDATE transaksi 
        SET noBuktiTransaksi = ?, noPolisi = ?, pemilik = ?, tanggalTransaksi = ?, jenisKendaraan = ?, tarif = ?, imageUrl = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $noBuktiTransaksi, $noPolisi, $pemilik, $tanggalTransaksi, $jenisKendaraan, $tarif, $FotoPath, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Transaksi berhasil diperbarui."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Gagal memperbarui: " . $stmt->error]);
}

$stmt->close();
$conn->close();
