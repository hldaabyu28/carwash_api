<?php
header("Content-Type: application/json");
include 'config.php';

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Metode tidak diizinkan
    echo json_encode(["success" => false, "message" => "Metode tidak diizinkan. Gunakan GET."]);
    exit;
}

// Validasi apakah parameter `id` ada di URL
if (!isset($_GET['id'])) {
    http_response_code(400); // Permintaan buruk
    echo json_encode(["success" => false, "message" => "Parameter ID harus disertakan di URL."]);
    exit;
}

$id = $_GET['id']; // Ambil `id` dari URL

// Query untuk mendapatkan detail transaksi
$sql = "SELECT * FROM transaksi WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $result = $stmt->get_result();

    // Jika transaksi ditemukan
    if ($result->num_rows > 0) {
        $transaksi = $result->fetch_assoc();
        echo json_encode(["success" => true, "data" => $transaksi]);
    } else {
        http_response_code(404); // Data tidak ditemukan
        echo json_encode(["success" => false, "message" => "Transaksi tidak ditemukan."]);
    }
} else {
    http_response_code(500); // Kesalahan server
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan pada server."]);
}

$stmt->close();
$conn->close();
