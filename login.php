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
if (!isset($data['username'], $data['password'])) {
    http_response_code(400); // Permintaan buruk
    echo json_encode(["success" => false, "message" => "Kolom username dan password harus diisi."]);
    exit;
}

$username = $data['username'];
$password = $data['password'];

// Cari pengguna berdasarkan username
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401); // Tidak diotorisasi
    echo json_encode(["success" => false, "message" => "Username atau password salah."]);
    $stmt->close();
    $conn->close();
    exit;
}

// Verifikasi password
$row = $result->fetch_assoc();
if (password_verify($password, $row['password'])) {
    // Jika login berhasil
    http_response_code(200); // OK
    echo json_encode(["success" => true, "message" => "Login berhasil."]);
} else {
    // Jika password salah
    http_response_code(401); // Tidak diotorisasi
    echo json_encode(["success" => false, "message" => "Username atau password salah."]);
}

$stmt->close();
$conn->close();
