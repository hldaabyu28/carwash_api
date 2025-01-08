<?php
header("Content-Type: application/json");
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Metode tidak diizinkan
    echo json_encode(["success" => false, "message" => "Metode tidak diizinkan. Gunakan DELETE."]);
    exit;
}

// Ambil parameter id dari query string
if (!isset($_GET['id'])) {
    http_response_code(400); // Permintaan buruk
    echo json_encode(["success" => false, "message" => "id harus disertakan di URL."]);
    exit;
}

$id = intval($_GET['id']);

// Hapus transaksi dari database
$stmt = $conn->prepare("DELETE FROM transaksi WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Transaksi berhasil dihapus."]);
} else {
    http_response_code(500); // Kesalahan server
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
