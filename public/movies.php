<?php
// movies.php
include '../includes/db_connect.php';
include '../includes/header.php';

// Handle Create (add new movie)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $release_date = $_POST['release_date'];
    $description = $_POST['description'];
    $runtime = $_POST['runtime'];

    try {
        $stmt = $pdo->prepare("INSERT INTO movies (title, release_date, description, runtime) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $release_date, $description, $runtime]);
        echo "<p style='color: green;'>Movie added successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error adding movie: " . $e->getMessage() . "</p>";
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        echo "<p style='color: green;'>Movie deleted successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error deleting movie: " . $e->getMessage() . "</p>";
    }
}

// Fetch all movies
try {
    $stmt = $pdo->query("SELECT id, title, release_date, runtime FROM movies ORDER BY title ASC");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error fetching movies: " . $e->getMessage() . "</p>";
    $movies = [];
}
?>

<h2>Movies</h2>

<h3>Add New Movie</h3>
<form action="movies.php" method="POST">
    <div class="form-group">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required>
    </div>
    <div class="form-group">
        <label for="release_date">Release Date:</label>
        <input type="date" id="release_date" name="release_date">
    </div>
    <div class="form-group">
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4"></textarea>
    </div>
    <div class="form-group">
        <label for="runtime">Runtime (minutes):</label>
        <input type="number" id="runtime" name="runtime">
    </div>
    <div class="form-group">
        <button type="submit" name="add_movie">Add Movie</button>
    </div>
</form>

<h3>All Movies</h3>
<?php if ($movies): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Release Date</th>
                <th>Runtime</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movies as $movie): ?>
                <tr>
                    <td><?php echo htmlspecialchars($movie['id']); ?></td>
                    <td><a href="movie_details.php?id=<?php echo $movie['id']; ?>"><?php echo htmlspecialchars($movie['title']); ?></a></td>
                    <td><?php echo htmlspecialchars($movie['release_date']); ?></td>
                    <td><?php echo htmlspecialchars($movie['runtime']); ?></td>
                    <td>
                        <a href="edit_movie.php?id=<?php echo $movie['id']; ?>" class="button edit">Edit</a>
                        <a href="movies.php?action=delete&id=<?php echo $movie['id']; ?>" class="button delete" onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No movies found.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>