<?php
// edit_person.php
include '../includes/db_connect.php';
include '../includes/header.php';

$person = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM people WHERE id = ?");
        $stmt->execute([$id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching person: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_person'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $birth_year = $_POST['birth_year'];

    try {
        $stmt = $pdo->prepare("UPDATE people SET name = ?, birth_year = ? WHERE id = ?");
        $stmt->execute([$name, $birth_year, $id]);
        echo "<p style='color: green;'>Person updated successfully!</p>";
        // Refresh person data after update
        $stmt = $pdo->prepare("SELECT * FROM people WHERE id = ?");
        $stmt->execute([$id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error updating person: " . $e->getMessage() . "</p>";
    }
}

if (!$person) {
    echo "<p>Person not found.</p>";
    include 'footer.php';
    exit();
}
?>

<h2>Edit Person: <?php echo htmlspecialchars($person['name']); ?></h2>

<form action="edit_person.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($person['id']); ?>">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($person['name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="birth_year">Birth Year:</label>
        <input type="number" id="birth_year" name="birth_year" value="<?php echo htmlspecialchars($person['birth_year']); ?>">
    </div>
    <div class="form-group">
        <button type="submit" name="update_person">Update Person</button>
    </div>
</form>

<p><a href="people.php">Back to People List</a></p>

<?php include '../includes/footer.php'; ?>