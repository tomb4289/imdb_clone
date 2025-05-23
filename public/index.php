<?php
// index.php
include '../includes/db_connect.php';
include '../includes/header.php';
?>

<h2>Welcome to the IMDb Clone!</h2>
<p>This is a simple CRUD application for managing movies, genres, people, and more.</p>

<h3>Recent Movies</h3>
<?php
try {
    $stmt = $pdo->query("SELECT id, title, release_date FROM movies ORDER BY release_date DESC LIMIT 5");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($movies) {
        echo "<ul>";
        foreach ($movies as $movie) {
            echo "<li><a href='movie_details.php?id=" . $movie['id'] . "'>" . htmlspecialchars($movie['title']) . "</a> (" . htmlspecialchars($movie['release_date']) . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No movies found.</p>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php include '../includes/footer.php'; ?>