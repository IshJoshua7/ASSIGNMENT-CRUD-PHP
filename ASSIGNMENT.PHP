<?php
//  CONNECT TO DATABASE
include("connect.php");

// Display all PHP errors 
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Helper function for safe printing
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
//  CREATE (INSERT)
if (isset($_POST['insert'])) {
    $PhoneNumber = trim($_POST['PhoneNumber']);
    $FirstName   = trim($_POST['FirstName']);
    $LastName    = trim($_POST['LastName']);
    $Gender      = trim($_POST['Gender']);
    $Province    = trim($_POST['Province']);

    // Basic validation
    if ($PhoneNumber === '' || $FirstName === '' || $LastName === '' || $Gender === '' || $Province === '') {
        echo "<p style='color:red'>All fields are required.</p>";
    } else {
        // Use prepared statement (secure)
        $stmt = $conne->prepare("INSERT INTO INFORMATION (PhoneNumber, FirstName, LastName, Gender, Province)
                                 VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $PhoneNumber, $FirstName, $LastName, $Gender, $Province);
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Record inserted successfully!</p>";
        } else {
            echo "<p style='color:red'>❌ Error inserting record: " . h($stmt->error) . "</p>";
        }
        $stmt->close();
    }
}
//  DELETE
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conne->prepare("DELETE FROM INFORMATION WHERE PhoneNumber = ?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        echo "<p style='color:orange'>🗑️ Record deleted.</p>";
    } else {
        echo "<p style='color:red'>❌ Error deleting record.</p>";
    }
    $stmt->close();
}
//  UPDATE
if (isset($_POST['update'])) {
    $PhoneNumber = $_POST['PhoneNumber'];
    $FirstName   = trim($_POST['FirstName']);
    $LastName    = trim($_POST['LastName']);
    $Gender      = trim($_POST['Gender']);
    $Province    = trim($_POST['Province']);

    $stmt = $conne->prepare("UPDATE INFORMATION 
                             SET FirstName = ?, LastName = ?, Gender = ?, Province = ? 
                             WHERE PhoneNumber = ?");
    $stmt->bind_param("sssss", $FirstName, $LastName, $Gender, $Province, $PhoneNumber);
    if ($stmt->execute()) {
        echo "<p style='color:blue'>✏️ Record updated successfully.</p>";
    } else {
        echo "<p style='color:red'>❌ Error updating record.</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PHP CRUD Example</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f9f9f9; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #0078D7; color: white; }
        form { margin: 10px 0; }
        input[type=text], select { padding: 6px; }
        input[type=submit] { padding: 6px 12px; cursor: pointer; }
        .btn-delete { color: red; text-decoration: none; }
        .btn-delete:hover { text-decoration: underline; }
        h2 { color: #0078D7; }
    </style>
</head>
<body>

<h2>📋 All Records</h2>
<table>
    <tr>
        <th>PhoneNumber</th>
        <th>FirstName</th>
        <th>LastName</th>
        <th>Gender</th>
        <th>Province</th>
        <th>Actions</th>
    </tr>

    <?php
    $result = $conne->query("SELECT * FROM INFORMATION");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <form method="post">
                <tr>
                    <td><input type="text" name="PhoneNumber" value="<?php echo h($row['PhoneNumber']); ?>" readonly></td>
                    <td><input type="text" name="FirstName" value="<?php echo h($row['FirstName']); ?>"></td>
                    <td><input type="text" name="LastName" value="<?php echo h($row['LastName']); ?>"></td>
                    <td>
                        <select name="Gender">
                            <option value="M" <?php if ($row['Gender'] == 'M') echo "selected"; ?>>M</option>
                            <option value="F" <?php if ($row['Gender'] == 'F') echo "selected"; ?>>F</option>
                        </select>
                    </td>
                    <td><input type="text" name="Province" value="<?php echo h($row['Province']); ?>"></td>
                    <td>
                        <input type="submit" name="update" value="Update">
                        <a class="btn-delete" href="?delete_id=<?php echo h($row['PhoneNumber']); ?>" 
                           onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                    </td>
                </tr>
            </form>
            <?php
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center;'>No records found</td></tr>";
    }
    ?>
</table>

<h2>➕ Add New Record</h2>
<form method="post">
    <label>PhoneNumber: <input type="text" name="PhoneNumber" required></label><br><br>
    <label>FirstName: <input type="text" name="FirstName" required></label><br><br>
    <label>LastName: <input type="text" name="LastName" required></label><br><br>
    <label>Gender:
        <input type="radio" name="Gender" value="M" required>Male
        <input type="radio" name="Gender" value="F">Female
    </label><br><br>
    <label>Province: <input type="text" name="Province" required></label><br><br>
    <input type="submit" name="insert" value="Save Record">
</form>

</body>
</html>
