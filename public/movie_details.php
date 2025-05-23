<?php
// movie_details.php
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
        echo "<p style='color: red;'>Error fetching movie details: " . $e->getMessage() . "</p>";
    }
}

if (!$movie) {
    echo "<p>Movie not found.</p>";
    include 'footer.php';
    exit();
}
?>

<h2><?php echo htmlspecialchars($movie['title']); ?></h2>

<p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie['release_date']); ?></p>
<p><strong>Runtime:</strong> <?php echo htmlspecialchars($movie['runtime']); ?> minutes</p>
<p><strong>Description:</strong> <?php echo htmlspecialchars($movie['description']); ?></p>

<h3>Genres</h3>
<?php
try {
    $stmt = $pdo->prepare("SELECT g.name FROM genres g JOIN movie_genres mg ON g.id = mg.genre_id WHERE mg.movie_id = ?");
    $stmt->execute([$movie['id']]);
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($genres) {
        echo "<ul>";
        foreach ($genres as $genre) {
            echo "<li>" . htmlspecialchars($genre['name']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No genres associated with this movie.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error fetching genres: " . $e->getMessage() . "</p>";
}
?>

<h3>Cast & Crew</h3>
<?php
try {
    $stmt = $pdo->prepare("SELECT p.name, mp.job FROM people p JOIN movie_people mp ON p.id = mp.person_id WHERE mp.movie_id = ? ORDER BY mp.job, p.name");
    $stmt->execute([$movie['id']]);
    $people = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($people) {
        echo "<ul>";
        foreach ($people as $person) {
            echo "<li>" . htmlspecialchars($person['name']) . " (" . htmlspecialchars($person['job']) . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No cast or crew listed for this movie.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error fetching cast and crew: " . $e->getMessage() . "</p>";
}
?>

<h3>Ratings</h3>
<?php
try {
    $stmt = $pdo->prepare("SELECT u.username, r.rating, r.review FROM ratings r JOIN users u ON r.user_id = u.id WHERE r.movie_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$movie['id']]);
    $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($ratings) {
        echo "<ul>";
        foreach ($ratings as $rating) {
            echo "<li><strong>" . htmlspecialchars($rating['username']) . ":</strong> " . htmlspecialchars($rating['rating']) . "/10";
            if (!empty($rating['review'])) {
                echo " - \"" . htmlspecialchars($rating['review']) . "\"";
            }
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No ratings yet for this movie.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error fetching ratings: " . $e->getMessage() . "</p>";
}
?>

<p><a href="movies.php">Back to Movies List</a></p>

<?php include '../includes/footer.php'; ?>