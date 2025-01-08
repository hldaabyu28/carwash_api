<?php
header("Content-Type: application/json");
include 'config.php';

// Ambil semua data transaksi
$sql = "SELECT * FROM transaksi";
$result = $conn->query($sql);

$transaksi = [];
while ($row = $result->fetch_assoc()) {
    $transaksi[] = $row;
}

http_response_code(200); // OK
echo json_encode(["success" => true, "data" => $transaksi]);

$conn->close();
