<?php
// public/edit_user.php
require_once '../includes/db_connect.php';
require_once '../classes/User.php';
include '../includes/header.php';

$userManager = new User($pdo);

$user = null;
$user_id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);

if ($user_id) {
    $user = $userManager->getById($user_id);
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // New password (optional)
    $password_hash = null;

    // Only update password if a new one is provided
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($userManager->update($id, $username, $email, $password_hash)) {
        echo "<p style='color: green;'>User updated successfully!</p>";
        // Re-fetch user data to show updated values
        $user = $userManager->getById($id);
    } else {
        echo "<p style='color: red;'>Error updating user.</p>";
    }
}

if (!$user) {
    echo "<p>User not found.</p>";
    include '../includes/footer.php';
    exit();
}
?>

<h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>

<form action="edit_user.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>
    <div class="form-group">
        <label for="password">New Password (leave blank to keep current):</label>
        <input type="password" id="password" name="password">
    </div>
    <div class="form-group">
        <button type="submit" name="update_user">Update User</button>
    </div>
</form>

<p><a href="users.php">Back to Users List</a></p>

<?php include '../includes/footer.php'; ?>