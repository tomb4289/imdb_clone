<?php
// public/users.php
require_once '../includes/db_connect.php'; // Corrected path
require_once '../classes/User.php';     // Include the new User class
include '../includes/header.php';       // Corrected path

$userManager = new User($pdo); // Instantiate the User manager

// Handle Create (add new user)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Always hash passwords!
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    if ($userManager->create($username, $email, $password_hash)) {
        echo "<p style='color: green;'>User added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error adding user: " . $e->getMessage() . "</p>";
    }
}

// Handle Delete (using POST form for security)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = $_POST['id']; // Get ID from hidden input in the form
    if ($userManager->delete($id)) {
        echo "<p style='color: green;'>User deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error deleting user.</p>";
    }
}


// Fetch all users using the class method
$users = $userManager->getAll();
?>

<h2>Users</h2>

<h3>Add New User</h3>
<form action="users.php" method="POST">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div class="form-group">
        <button type="submit" name="add_user">Add User</button>
    </div>
</form>

<h3>All Users</h3>
<?php if ($users): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="button edit">Edit</a>
                        <form action="users.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete <?php echo addslashes(htmlspecialchars($user['username'])); ?>?');">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                            <button type="submit" name="delete_user" class="button delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>