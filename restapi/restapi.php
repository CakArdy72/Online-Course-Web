<?php
header("Access-Control-Allow-Origin: *"); // Izinkan akses dari semua domain
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS"); // Izinkan metode HTTP tertentu
header("Access-Control-Allow-Headers: Content-Type"); // Izinkan header Content-Type

// Menangani permintaan OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Mengirimkan status 200 OK untuk permintaan OPTIONS
    exit();
}

header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "restapi_db");
if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Mendapatkan request method (GET, POST, PUT, DELETE)
$request_method = $_SERVER['REQUEST_METHOD'];

// Fungsi untuk Create (POST) produk
function createUser($conn) {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->name) && isset($data->email) && isset($data->password)) {
        // Hash password sebelum menyimpan ke database
        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data->name, $data->email, $hashed_password);

        if ($stmt->execute()) {
            echo json_encode(["message" => "User created successfully"]);
        } else {
            echo json_encode(["message" => "Failed to create user"]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
    }
}

// Fungsi untuk Read (GET) semua user
function getUsers($conn) {
    $result = $conn->query("SELECT id, name, email FROM users"); // Jangan tampilkan password
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);
}

// Fungsi untuk Read (GET) user berdasarkan ID
function getUserById($conn, $id) {
    $result = $conn->query("SELECT id, name, email FROM users WHERE id = $id"); // Jangan tampilkan password
    $user = $result->fetch_assoc();
    if ($user) {
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "User not found"]);
    }
}

// Fungsi untuk Update (PUT) user
function updateUser($conn, $id) {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->name) && isset($data->email)) {
        if (isset($data->password) && !empty($data->password)) {
            // Update dengan password
            $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $data->name, $data->email, $hashed_password, $id);
        } else {
            // Update tanpa password
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $data->name, $data->email, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            echo json_encode(["message" => "Failed to update user"]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
    }
}

// Fungsi untuk Delete (DELETE) user
function deleteUser($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "User deleted successfully"]);
    } else {
        echo json_encode(["message" => "Failed to delete user"]);
    }
    $stmt->close();
}

// Menangani berbagai method (GET, POST, PUT, DELETE)
switch ($request_method) {
    case 'POST':
        // Menambahkan user baru
        createUser($conn);
        break;

    case 'GET':
        if (isset($_GET['id'])) {
            // Menampilkan user berdasarkan ID
            $id = $_GET['id'];
            getUserById($conn, $id);
        } else {
            // Menampilkan semua user
            getUsers($conn);
        }
        break;

    case 'PUT':
        // Update user berdasarkan ID
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            updateUser($conn, $id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "User ID is required for update"]);
        }
        break;

    case 'DELETE':
        // Menghapus user berdasarkan ID
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            deleteUser($conn, $id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "User ID is required for delete"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

// Menutup koneksi
$conn->close();
?>
