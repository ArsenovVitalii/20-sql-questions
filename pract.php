<?php
$db_name = "school_management";

// Database Connection
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*здесь же представлено создание БД*/
if($conn->query("CREATE DATABASE IF NOT EXISTS $db_name")) {
    echo "База данных '$db_name' создана" . "<br>";
} else {
    echo "Ошибка при создании базы данных: " . $conn->error . "<br>";
}

// Create Tables
$queries = [
    "CREATE TABLE IF NOT EXISTS groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;",

    "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        group_id INT,
        FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;",

    "CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;",

    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        teacher_id INT,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",

    "CREATE TABLE IF NOT EXISTS student_courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        course_id INT,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;"
];

if (!$conn->select_db("school_management")) {
    die("Error selecting database: " . $conn->error);
}

foreach ($queries as $query) {
    if (!$conn->query($query)) {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Function to Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $stmt = $conn->prepare("INSERT INTO students (name, id) VALUES (?, ?)");
    $stmt->bind_param("si", $_POST['student_name'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
}

// Function to Add Group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_group'])) {
    $stmt = $conn->prepare("INSERT INTO groups (name) VALUES (?)");
    $stmt->bind_param("s", $_POST['group_name']);
    $stmt->execute();
    $stmt->close();
}

// Fetch Students and Groups
$students = $conn->query("SELECT students.id, students.name AS student_name, groups.name AS group_name FROM students LEFT JOIN groups ON students.group_id = groups.id");
$groups = $conn->query("SELECT id, name FROM groups");

// Fetch Courses
$courses = $conn->query("SELECT id, name FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
</head>
<body>
    <h1>Student Management System</h1>

    <h2>Add New Student</h2>
    <form method="POST">
        <label>Name: <input type="text" name="student_name" required></label><br>
        <label>Group: 
            <select name="group_id">
                <option value="">None</option>
                <?php while ($group = $groups->fetch_assoc()): ?>
                    <option value="<?php echo $group['id']; ?>">
                        <?php echo htmlspecialchars($group['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <h2>Add New Group</h2>
    <form method="POST">
        <label>Group Name: <input type="text" name="group_name" required></label><br>
        <button type="submit" name="add_group">Add Group</button>
    </form>

    <h2>Student List</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Group</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $student['id']; ?></td>
                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['group_name'] ?? 'None'); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Register Student for a Course</h2>
    <form method="POST">
        <label>Student: 
            <select name="student_id">
                <?php $students->data_seek(0); while ($student = $students->fetch_assoc()): ?>
                    <option value="<?php echo $student['id']; ?>">
                        <?php echo htmlspecialchars($student['student_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Course: 
            <select name="course_id">
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <option value="<?php echo $course['id']; ?>">
                        <?php echo htmlspecialchars($course['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <button type="submit" name="register_course">Register</button>
    </form>
</body>
</html>

<?php $conn->close(); ?>
