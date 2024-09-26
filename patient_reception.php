<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $PatientID = $_POST['PatientID'] ?? null;
    $DoctorID = $_POST['DoctorID'] ?? null;
    $ArrivalDateTime = $_POST['ArrivalDateTime'] ?? null;

    if ($action == "admit") {
        $sql = "INSERT INTO EmergencyAdmissions (PatientID, DoctorID, ArrivalDateTime) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$PatientID, $DoctorID, $ArrivalDateTime]);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil des patients</title>
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
        <h1>Accueil des patients</h1>
        <form method="POST" action="">
            <input type="hidden" name="action" value="admit">
            <label>Patient: 
                <select name="PatientID">
                    <option value="">Sélectionner un patient</option>
                    <?php
                    $sql = "SELECT * FROM Patients";
                    foreach ($conn->query($sql) as $row) {
                        echo "<option value='{$row['PatientID']}'>{$row['FirstName']} {$row['LastName']} ({$row['SocialSecurityNumber']})</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Médecin: 
                <select name="DoctorID">
                    <option value="">Sélectionner un médecin</option>
                    <?php
                    $sql = "SELECT * FROM Doctors";
                    foreach ($conn->query($sql) as $row) {
                        echo "<option value='{$row['DoctorID']}'>{$row['FirstName']} {$row['LastName']} ({$row['Specialty']})</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Date et heure d'arrivée: <input type="datetime-local" name="ArrivalDateTime" required></label><br>
            <input type="submit" value="Admettre le patient">
        </form>

        <h2>Patients admis</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Médecin</th>
                <th>Date et heure d'arrivée</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT ea.AdmissionID, p.FirstName AS PatientFirstName, p.LastName AS PatientLastName, d.FirstName AS DoctorFirstName, d.LastName AS DoctorLastName, ea.ArrivalDateTime
                    FROM EmergencyAdmissions ea
                    JOIN Patients p ON ea.PatientID = p.PatientID
                    JOIN Doctors d ON ea.DoctorID = d.DoctorID";
            foreach ($conn->query($sql) as $row) {
                echo "<tr>";
                echo "<td>{$row['AdmissionID']}</td>";
                echo "<td>{$row['PatientFirstName']} {$row['PatientLastName']}</td>";
                echo "<td>{$row['DoctorFirstName']} {$row['DoctorLastName']}</td>";
                echo "<td>{$row['ArrivalDateTime']}</td>";
                echo "<td class='actions'>
                        <form method='POST' action='doctor_care.php'>
                            <input type='hidden' name='AdmissionID' value='{$row['AdmissionID']}'>
                            <input type='submit' value='Traiter'>
                        </form>
                    </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
