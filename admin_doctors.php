<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $DoctorID = $_POST['DoctorID'] ?? null;
    $CPSNumber = $_POST['CPSNumber'] ?? null;
    $LastName = $_POST['LastName'] ?? null;
    $FirstName = $_POST['FirstName'] ?? null;
    $Gender = $_POST['Gender'] ?? null;
    $Specialty = $_POST['Specialty'] ?? null;
    $Availability = $_POST['Availability'] ?? null;

    if ($action == "add") {
        $sql = "INSERT INTO Doctors (CPSNumber, LastName, FirstName, Gender, Specialty, Availability) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$CPSNumber, $LastName, $FirstName, $Gender, $Specialty, $Availability]);
    } elseif ($action == "update") {
        $sql = "UPDATE Doctors SET CPSNumber=?, LastName=?, FirstName=?, Gender=?, Specialty=?, Availability=? WHERE DoctorID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$CPSNumber, $LastName, $FirstName, $Gender, $Specialty, $Availability, $DoctorID]);
    } elseif ($action == "delete") {
        $sql = "DELETE FROM Doctors WHERE DoctorID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$DoctorID]);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des médecins</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Hôpital</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="admin_doctors.php">Médecins</a></li>
                    <li><a href="admin_patients.php">Patients</a></li>
                    <li><a href="admin_rooms.php">Salles</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <h1>Gestion des médecins</h1>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <label>N° de carte CPS: <input type="text" name="CPSNumber" required></label><br>
            <label>Nom: <input type="text" name="LastName" required></label><br>
            <label>Prénom: <input type="text" name="FirstName" required></label><br>
            <label>Genre: <input type="text" name="Gender"></label><br>
            <label>Spécialité: <input type="text" name="Specialty"></label><br>
            <label>Disponibilité: <input type="text" name="Availability"></label><br>
            <input type="submit" value="Ajouter le médecin">
        </form>

        <h2>Médecins existants</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>N° CPS</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Genre</th>
                <th>Spécialité</th>
                <th>Disponibilité</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT * FROM Doctors";
            foreach ($conn->query($sql) as $row) {
                echo "<tr>";
                echo "<td>{$row['DoctorID']}</td>";
                echo "<td>{$row['CPSNumber']}</td>";
                echo "<td>{$row['LastName']}</td>";
                echo "<td>{$row['FirstName']}</td>";
                echo "<td>{$row['Gender']}</td>";
                echo "<td>{$row['Specialty']}</td>";
                echo "<td>{$row['Availability']}</td>";
                echo "<td class='actions'>
                        <form method='POST' action=''>
                            <input type='hidden' name='action' value='delete'>
                            <input type='hidden' name='DoctorID' value='{$row['DoctorID']}'>
                            <input type='submit' value='Supprimer'>
                        </form>
                        <form method='POST' action=''>
                            <input type='hidden' name='action' value='update'>
                            <input type='hidden' name='DoctorID' value='{$row['DoctorID']}'>
                            <input type='text' name='CPSNumber' value='{$row['CPSNumber']}'>
                            <input type='text' name='LastName' value='{$row['LastName']}'>
                            <input type='text' name='FirstName' value='{$row['FirstName']}'>
                            <input type='text' name='Gender' value='{$row['Gender']}'>
                            <input type='text' name='Specialty' value='{$row['Specialty']}'>
                            <input type='text' name='Availability' value='{$row['Availability']}'>
                            <input type='submit' value='Modifier'>
                        </form>
                    </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
