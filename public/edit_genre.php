<?php
// edit_genre.php
include '../includes/db_connect.php';
include '../includes/header.php';

$genre = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        $genre = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching genre: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_genre'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];

    try {
        $stmt = $pdo->prepare("UPDATE genres SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        echo "<p style='color: green;'>Genre updated successfully!</p>";
        // Refresh genre data after update
        $stmt = $pdo->prepare("SELECT * FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        $genre = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error updating genre: " . $e->getMessage() . "</p>";
    }
}

if (!$genre) {
    echo "<p>Genre not found.</p>";
    include 'footer.php';
    exit();
}
?>

<h2>Edit Genre: <?php echo htmlspecialchars($genre['name']); ?></h2>

<form action="edit_genre.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($genre['id']); ?>">
    <div class="form-group">
        <label for="name">Genre Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($genre['name']); ?>" required>
    </div>
    <div class="form-group">
        <button type="submit" name="update_genre">Update Genre</button>
    </div>
</form>

<p><a href="genres.php">Back to Genres List</a></p>

<?php include 'footer.php'; ?>