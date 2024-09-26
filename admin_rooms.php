<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $RoomID = $_POST['RoomID'] ?? null;
    $RoomNumber = $_POST['RoomNumber'] ?? null;
    $Availability = $_POST['Availability'] ?? 'Available';

    if ($action == "add") {
        $sql = "INSERT INTO TreatmentRooms (RoomNumber, Availability) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$RoomNumber, $Availability]);
    } elseif ($action == "update") {
        $sql = "UPDATE TreatmentRooms SET RoomNumber=?, Availability=? WHERE RoomID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$RoomNumber, $Availability, $RoomID]);
    } elseif ($action == "delete") {
        $sql = "DELETE FROM TreatmentRooms WHERE RoomID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$RoomID]);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des salles de soin</title>
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
        <h1>Gestion des salles de soin</h1>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <label>Numéro de salle: <input type="text" name="RoomNumber" required></label><br>
            <label>Disponibilité: <input type="text" name="Availability" value="Available"></label><br>
            <input type="submit" value="Ajouter la salle">
        </form>

        <h2>Salles existantes</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Numéro de salle</th>
                <th>Disponibilité</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT * FROM TreatmentRooms";
            foreach ($conn->query($sql) as $row) {
                echo "<tr>";
                echo "<td>{$row['RoomID']}</td>";
                echo "<td>{$row['RoomNumber']}</td>";
                echo "<td>{$row['Availability']}</td>";
                echo "<td class='actions'>
                        <form method='POST' action=''>
                            <input type='hidden' name='action' value='delete'>
                            <input type='hidden' name='RoomID' value='{$row['RoomID']}'>
                            <input type='submit' value='Supprimer'>
                        </form>
                        <form method='POST' action=''>
                            <input type='hidden' name='action' value='update'>
                            <input type='hidden' name='RoomID' value='{$row['RoomID']}'>
                            <input type='text' name='RoomNumber' value='{$row['RoomNumber']}'>
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
