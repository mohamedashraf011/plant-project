<?php
session_start();

// Database connection details
$servername = "localhost"; 
$username = "root";
$password = "123456";
$dbname = "plant_website";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case "register":
            $name = $conn->real_escape_string($_POST['name']); 
            $email = $conn->real_escape_string($_POST['email']);
            $password = $_POST['password'];

            $sql = "INSERT INTO users (username, email, password) VALUES ('$name', '$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                header("Location: ../login.html");
                exit();
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
            break;

        case "login":
            $email = $_POST['email'];
            $password = $_POST['password'];

            $sql = "SELECT * FROM users WHERE email = '$email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); 
                if ($password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: ../index.html"); 
                    exit();
                } else {
                    echo "Invalid password.";
                }
            } else {
                echo "No account found with that email.";
            }
            break;

        case "update_profile":
            if (!isset($_SESSION['user_id'])) {
                die("You must be logged in to update your information.");
            }

            $user_id = $_SESSION['user_id'];
            $new_email = $_POST['new_email'];
            $new_password = $_POST['new_password']; 

            $updates = [];
            if (!empty($new_email)) {
                $updates[] = "email = '$new_email'";
            }
            if (!empty($new_password)) {
                $updates[] = "password = '$new_password'";
            }

            if (!empty($updates)) {
                $update_query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = $user_id";
                if ($conn->query($update_query) === TRUE) {
                    echo "Profile updated successfully.";
                    header("Location: ../profile.html");
                    exit();
                } else {
                    echo "Error updating profile: " . $conn->error;
                }
            } else {
                echo "No updates provided.";
            }
            break;

        case "delete_account":
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(["error" => "You must be logged in to delete your account."]);
                exit();
            }

            $user_id = $_SESSION['user_id'];
            $sql = "DELETE FROM users WHERE id = $user_id";

            if ($conn->query($sql) === TRUE) {
                session_destroy();
                header("Location: ../login.html");
                exit();
            } else {
                echo "Error deleting account: " . $conn->error; 
            }
            break;

        case "get_user_info":
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(["error" => "User not logged in."]);
                exit();
            }

            $user_id = $_SESSION['user_id'];
            $sql = "SELECT username, email FROM users WHERE id = $user_id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "User not found."]);
            }
            break;

        default:
            echo "Invalid action."
    }
} else {
    echo "No action specified.";
}

$conn->close();
?>
