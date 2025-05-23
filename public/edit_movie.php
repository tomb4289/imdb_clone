<?php
// edit_movie.php
include '../includes/db_connect.php';
include '../includes/header.php';

$movie = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching movie: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_movie'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $release_date = $_POST['release_date'];
    $description = $_POST['description'];
    $runtime = $_POST['runtime'];

    try {
        $stmt = $pdo->prepare("UPDATE movies SET title = ?, release_date = ?, description = ?, runtime = ? WHERE id = ?");
        $stmt->execute([$title, $release_date, $description, $runtime, $id]);
        echo "<p style='color: green;'>Movie updated successfully!</p>";
        // Refresh movie data after update
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error updating movie: " . $e->getMessage() . "</p>";
    }
}

if (!$movie) {
    echo "<p>Movie not found.</p>";
    include 'footer.php';
    exit();
}
?>

<h2>Edit Movie: <?php echo htmlspecialchars($movie['title']); ?></h2>

<form action="edit_movie.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($movie['id']); ?>">
    <div class="form-group">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
    </div>
    <div class="form-group">
        <label for="release_date">Release Date:</label>
        <input type="date" id="release_date" name="release_date" value="<?php echo htmlspecialchars($movie['release_date']); ?>">
    </div>
    <div class="form-group">
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($movie['description']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="runtime">Runtime (minutes):</label>
        <input type="number" id="runtime" name="runtime" value="<?php echo htmlspecialchars($movie['runtime']); ?>">
    </div>
    <div class="form-group">
        <button type="submit" name="update_movie">Update Movie</button>
    </div>
</form>

<p><a href="movies.php">Back to Movies List</a></p>

<?php include '../includes/footer.php'; ?>