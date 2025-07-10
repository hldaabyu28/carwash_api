<?php
header("Content-Type: application/json");
include 'config.php';

// Periksa apakah metode permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Metode tidak diizinkan
    echo json_encode(["success" => false, "message" => "Metode tidak diizinkan. Gunakan POST."]);
    exit;
}

// Ambil data dari body permintaan
$data = json_decode(file_get_contents("php://input"), true);

// Validasi input
if (!isset($data['username'], $data['password'], $data['nama_lengkap'])) {
    http_response_code(400); // Permintaan buruk
    echo json_encode(["success" => false, "message" => "Semua kolom (username, password, nama_lengkap) harus diisi."]);
    exit;
}

$username = $data['username'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$namaLengkap = $data['nama_lengkap'];



// Gunakan prepared statements untuk menyimpan data
$stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $password, $namaLengkap);

// Cek apakah username sudah ada
$sql = "SELECT username FROM users WHERE username = ?";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("s", $username);
$stmt2->execute();
$stmt2->store_result();

if ($stmt2->num_rows > 0) {
    http_response_code(400); // Permintaan buruk
    echo json_encode(["success" => false, "message" => "Username sudah ada."]);
    exit;
}

if ($stmt->execute()) {
    http_response_code(201); // Sumber daya berhasil dibuat
    echo json_encode(["success" => true, "message" => "Pendaftaran berhasil."]);
} else {
    http_response_code(500); // Kesalahan server
    echo json_encode(["success" => false, "message" => "Kesalahan server: " . $stmt->error]);
}

$stmt->close();
$conn->close();
