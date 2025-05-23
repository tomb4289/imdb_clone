<?php
// people.php
include '../includes/db_connect.php';
include '../includes/header.php';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_person'])) {
    $name = $_POST['name'];
    $birth_year = $_POST['birth_year'];

    try {
        $stmt = $pdo->prepare("INSERT INTO people (name, birth_year) VALUES (?, ?)");
        $stmt->execute([$name, $birth_year]);
        echo "<p style='color: green;'>Person added successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error adding person: " . $e->getMessage() . "</p>";
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM people WHERE id = ?");
        $stmt->execute([$id]);
        echo "<p style='color: green;'>Person deleted successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error deleting person: " . $e->getMessage() . "</p>";
    }
}

// Fetch all people
try {
    $stmt = $pdo->query("SELECT id, name, birth_year FROM people ORDER BY name ASC");
    $people = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error fetching people: " . $e->getMessage() . "</p>";
    $people = [];
}
?>

<h2>People</h2>

<h3>Add New Person</h3>
<form action="people.php" method="POST">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="birth_year">Birth Year:</label>
        <input type="number" id="birth_year" name="birth_year">
    </div>
    <div class="form-group">
        <button type="submit" name="add_person">Add Person</button>
    </div>
</form>

<h3>All People</h3>
<?php if ($people): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Birth Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($people as $person): ?>
                <tr>
                    <td><?php echo htmlspecialchars($person['id']); ?></td>
                    <td><?php echo htmlspecialchars($person['name']); ?></td>
                    <td><?php echo htmlspecialchars($person['birth_year']); ?></td>
                    <td>
                        <a href="edit_person.php?id=<?php echo $person['id']; ?>" class="button edit">Edit</a>
                        <a href="people.php?action=delete&id=<?php echo $person['id']; ?>" class="button delete" onclick="return confirm('Are you sure you want to delete this person?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No people found.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>