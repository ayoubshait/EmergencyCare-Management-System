<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    if ($action == "add_patient") {
        $SocialSecurityNumber = $_POST['SocialSecurityNumber'];
        $LastName = $_POST['LastName'];
        $FirstName = $_POST['FirstName'];
        $Gender = $_POST['Gender'];
        $DateOfBirth = $_POST['DateOfBirth'];
        $DoctorID = $_POST['DoctorID'];
        
        $sql = "INSERT INTO Patients (SocialSecurityNumber, LastName, FirstName, Gender, DateOfBirth, DoctorID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$SocialSecurityNumber, $LastName, $FirstName, $Gender, $DateOfBirth, $DoctorID]);
    } elseif ($action == "update_patient") {
        $PatientID = $_POST['PatientID'];
        $SocialSecurityNumber = $_POST['SocialSecurityNumber'];
        $LastName = $_POST['LastName'];
        $FirstName = $_POST['FirstName'];
        $Gender = $_POST['Gender'];
        $DateOfBirth = $_POST['DateOfBirth'];
        $DoctorID = $_POST['DoctorID'];
        
        $sql = "UPDATE Patients SET SocialSecurityNumber=?, LastName=?, FirstName=?, Gender=?, DateOfBirth=?, DoctorID=? WHERE PatientID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$SocialSecurityNumber, $LastName, $FirstName, $Gender, $DateOfBirth, $DoctorID, $PatientID]);
    } elseif ($action == "delete_patient") {
        $PatientID = $_POST['PatientID'];
        $sql = "DELETE FROM Patients WHERE PatientID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$PatientID]);
    } elseif ($action == "update_admission") {
        $AdmissionID = $_POST['AdmissionID'];
        $ArrivalDateTime = $_POST['ArrivalDateTime'];
        $DepartureDateTime = $_POST['DepartureDateTime'];
        
        $sql = "UPDATE EmergencyAdmissions SET ArrivalDateTime=?, DepartureDateTime=? WHERE AdmissionID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ArrivalDateTime, $DepartureDateTime, $AdmissionID]);
    } elseif ($action == "delete_admission") {
        $AdmissionID = $_POST['AdmissionID'];
        $sql = "DELETE FROM EmergencyAdmissions WHERE AdmissionID=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$AdmissionID]);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des patients</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Administration - Hôpital</h1>
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
        <h1>Gestion des patients</h1>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_patient">
            <label>N° de sécu: <input type="text" name="SocialSecurityNumber" required></label><br>
            <label>Nom: <input type="text" name="LastName" required></label><br>
            <label>Prénom: <input type="text" name="FirstName" required></label><br>
            <label>Genre: <input type="text" name="Gender"></label><br>
            <label>Date de naissance: <input type="date" name="DateOfBirth" required></label><br>
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
            <input type="submit" value="Ajouter le patient">
        </form>

        <h2>Patients existants</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>N° de sécu</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Genre</th>
                <th>Date de naissance</th>
                <th>Médecin</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT p.*, d.FirstName AS DoctorFirstName, d.LastName AS DoctorLastName FROM Patients p LEFT JOIN Doctors d ON p.DoctorID = d.DoctorID";
            foreach ($conn->query($sql) as $row) {
                echo "<tr>";
                echo "<td>{$row['PatientID']}</td>";
                echo "<td>{$row['SocialSecurityNumber']}</td>";
                echo "<td>{$row['LastName']}</td>";
                echo "<td>{$row['FirstName']}</td>";
                echo "<td>{$row['Gender']}</td>";
                echo "<td>{$row['DateOfBirth']}</td>";
                echo "<td>{$row['DoctorFirstName']} {$row['DoctorLastName']}</td>";
                echo "<td class='actions'>
                        <form method='POST' action=''>
                            <input type='hidden' name='action' value='delete_patient'>
                            <input type='hidden' name='PatientID' value='{$row['PatientID']}'>
                            <input type='submit' value='Supprimer'>
                        </form>
                        <form method='POST' action=''>
                            <input type='hidden' name='action' value='update_patient'>
                            <input type='hidden' name='PatientID' value='{$row['PatientID']}'>
                            <input type='text' name='SocialSecurityNumber' value='{$row['SocialSecurityNumber']}'>
                            <input type='text' name='LastName' value='{$row['LastName']}'>
                            <input type='text' name='FirstName' value='{$row['FirstName']}'>
                            <input type='text' name='Gender' value='{$row['Gender']}'>
                            <input type='date' name='DateOfBirth' value='{$row['DateOfBirth']}'>
                            <select name='DoctorID'>
                                <option value=''>Sélectionner un médecin</option>";
                                $sql = "SELECT * FROM Doctors";
                                foreach ($conn->query($sql) as $doctor) {
                                    $selected = $row['DoctorID'] == $doctor['DoctorID'] ? "selected" : "";
                                    echo "<option value='{$doctor['DoctorID']}' $selected>{$doctor['FirstName']} {$doctor['LastName']} ({$doctor['Specialty']})</option>";
                                }
                                echo "</select>
                            <input type='submit' value='Modifier'>
                        </form>
                    </td>";
                echo "</tr>";
            }
            ?>
        </table>
        
        <h2>Liste des prises en charge</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Médecin</th>
                <th>Date d'arrivée</th>
                <th>Date de départ</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT ea.*, p.LastName AS PatientLastName, p.FirstName AS PatientFirstName, d.LastName AS DoctorLastName, d.FirstName AS DoctorFirstName 
                    FROM EmergencyAdmissions ea 
                    JOIN Patients p ON ea.PatientID = p.PatientID 
                    JOIN Doctors d ON ea.DoctorID = d.DoctorID";
            foreach ($conn->query($sql) as $row) {
                echo "<tr>";
                echo "<td>{$row['AdmissionID']}</td>";
                echo "<td>{$row['PatientFirstName']} {$row['PatientLastName']}</td>";
                echo "<td>{$row['DoctorFirstName']} {$row['DoctorLastName']}</td>";
                echo "<td>{$row['ArrivalDateTime']}</td>";
                echo "<td>{$row['DepartureDateTime']}</td>";
                echo "<td class='actions'>
                        <form method='POST' action=''>
                            <input type='hidden' name='action' value='delete_admission'>
                            <input type='hidden' name='AdmissionID' value='{$row['AdmissionID']}'>
                            <input type='submit' value='Supprimer'>
                        </form>
                        <form method='POST' action
                        =''>
                        <input type='hidden' name='action' value='update_admission'>
                        <input type='hidden' name='AdmissionID' value='{$row['AdmissionID']}'>
                        <input type='datetime-local' name='ArrivalDateTime' value='{$row['ArrivalDateTime']}' required>
                        <input type='datetime-local' name='DepartureDateTime' value='{$row['DepartureDateTime']}'>
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