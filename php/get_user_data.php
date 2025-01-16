<?php
session_start(); // Start the session to manage user state (e.g., login status).

// Database connection details
$servername = "localhost"; 
$username = "root";
$password = "123456";
$dbname = "plant_website";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Terminate script if connection fails.
}

// Check if the request is POST and if the "action" parameter is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action']; // Identify the requested action.

    // Use a switch statement to determine the appropriate functionality.
    switch ($action) {
        case "register":
            // User registration logic
            $name = $conn->real_escape_string($_POST['name']); // Sanitize 'name' input to prevent SQL injection.
            $email = $conn->real_escape_string($_POST['email']); // Sanitize 'email' input.
            $password = $_POST['password']; // Retrieve the password (plain text for now, should be hashed in real applications).

            // SQL query to insert a new user
            $sql = "INSERT INTO users (username, email, password) VALUES ('$name', '$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                header("Location: ../login.html"); // Redirect to the login page upon successful registration.
                exit(); // Stop further script execution after the redirect.
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error; // Display an error message if the query fails.
            }
            break;

        case "login":
            // User login logic
            $email = $_POST['email']; // Get the user's email.
            $password = $_POST['password']; // Get the user's password.

            // SQL query to fetch the user by email
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $result = $conn->query($sql);

            // Check if the user exists
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch user data as an associative array.
                if ($password === $user['password']) { // Verify password (direct comparison; hashing recommended).
                    $_SESSION['user_id'] = $user['id']; // Store the user's ID in the session.
                    $_SESSION['username'] = $user['username']; // Store the username in the session.
                    header("Location: ../index.html"); // Redirect to the homepage.
                    exit();
                } else {
                    echo "Invalid password."; // Display an error if the password is incorrect.
                }
            } else {
                echo "No account found with that email."; // Display an error if no user is found.
            }
            break;

        case "update_profile":
            // Profile update logic
            if (!isset($_SESSION['user_id'])) {
                die("You must be logged in to update your information."); // Restrict access to logged-in users.
            }

            $user_id = $_SESSION['user_id']; // Retrieve the logged-in user's ID.
            $new_email = $_POST['new_email']; // Get the new email from the request.
            $new_password = $_POST['new_password']; // Get the new password from the request.

            // Prepare an array to store update fields
            $updates = [];
            if (!empty($new_email)) {
                $updates[] = "email = '$new_email'"; // Add email update if provided.
            }
            if (!empty($new_password)) {
                $updates[] = "password = '$new_password'"; // Add password update if provided.
            }

            // If there are fields to update, execute the update query
            if (!empty($updates)) {
                $update_query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = $user_id";
                if ($conn->query($update_query) === TRUE) {
                    echo "Profile updated successfully."; // Success message
                    header("Location: ../profile.html"); // Redirect to the profile page.
                    exit();
                } else {
                    echo "Error updating profile: " . $conn->error; // Display an error if the query fails.
                }
            } else {
                echo "No updates provided."; // Inform the user if no updates were made.
            }
            break;

        case "delete_account":
            // Account deletion logic
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401); // Send a 401 Unauthorized response.
                echo json_encode(["error" => "You must be logged in to delete your account."]);
                exit();
            }

            $user_id = $_SESSION['user_id']; // Get the logged-in user's ID.
            $sql = "DELETE FROM users WHERE id = $user_id"; // SQL query to delete the user.

            if ($conn->query($sql) === TRUE) {
                session_destroy(); // Destroy the session after account deletion.
                header("Location: ../login.html"); // Redirect to the login page.
                exit();
            } else {
                echo "Error deleting account: " . $conn->error; // Display an error if the query fails.
            }
            break;

        case "get_user_info":
            // Fetch user information logic
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401); // Send a 401 Unauthorized response.
                echo json_encode(["error" => "User not logged in."]);
                exit();
            }

            $user_id = $_SESSION['user_id']; // Get the logged-in user's ID.
            $sql = "SELECT username, email FROM users WHERE id = $user_id"; // SQL query to fetch user data.
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch the user's data.
                echo json_encode($user); // Return user data as a JSON response.
            } else {
                http_response_code(404); // Send a 404 Not Found response.
                echo json_encode(["error" => "User not found."]);
            }
            break;

        default:
            echo "Invalid action."; // Handle invalid action requests.
    }
} else {
    echo "No action specified."; // Inform the user if no action is provided.
}

// Close the database connection
$conn->close();
?>
