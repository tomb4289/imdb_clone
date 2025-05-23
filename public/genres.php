<?php
// genres.php
include '../includes/db_connect.php';
include '../includes/header.php';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_genre'])) {
    $name = $_POST['name'];
    try {
        $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
        $stmt->execute([$name]);
        echo "<p style='color: green;'>Genre added successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error adding genre: " . $e->getMessage() . "</p>";
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        echo "<p style='color: green;'>Genre deleted successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error deleting genre: " . $e->getMessage() . "</p>";
    }
}

// Fetch all genres
try {
    $stmt = $pdo->query("SELECT id, name FROM genres ORDER BY name ASC");
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error fetching genres: " . $e->getMessage() . "</p>";
    $genres = [];
}
?>

<h2>Genres</h2>

<h3>Add New Genre</h3>
<form action="genres.php" method="POST">
    <div class="form-group">
        <label for="name">Genre Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
        <button type="submit" name="add_genre">Add Genre</button>
    </div>
</form>

<h3>All Genres</h3>
<?php if ($genres): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($genres as $genre): ?>
                <tr>
                    <td><?php echo htmlspecialchars($genre['id']); ?></td>
                    <td><?php echo htmlspecialchars($genre['name']); ?></td>
                    <td>
                        <a href="edit_genre.php?id=<?php echo $genre['id']; ?>" class="button edit">Edit</a>
                        <a href="genres.php?action=delete&id=<?php echo $genre['id']; ?>" class="button delete" onclick="return confirm('Are you sure you want to delete this genre?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No genres found.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>