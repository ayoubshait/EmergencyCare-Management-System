<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $PatientID = $_POST['PatientID'] ?? null;
    $AdmissionID = $_POST['AdmissionID'] ?? null;
    $DoctorID = $_POST['DoctorID'] ?? null;
    $EmergencyType = $_POST['EmergencyType'] ?? null;
    $Symptoms = $_POST['Symptoms'] ?? null;
    $TreatmentPrescription = $_POST['TreatmentPrescription'] ?? null;

    if ($action == "admit") {
        // Admit the patient
        $sql = "INSERT INTO EmergencyAdmissions (PatientID, DoctorID, ArrivalDateTime) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$PatientID, $DoctorID])) {
            echo "Patient successfully admitted.";
        } else {
            echo "Error admitting patient.";
        }
    } elseif ($action == "diagnose") {
        // Ensure PatientID and AdmissionID are not empty before proceeding
        if (!empty($PatientID) && !empty($AdmissionID)) {
            // Insert diagnosis
            $sql = "INSERT INTO Diagnosis (AdmissionID, EmergencyType, Symptoms, TreatmentPrescription) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$AdmissionID, $EmergencyType, $Symptoms, $TreatmentPrescription])) {
                echo "Diagnosis successfully recorded.";
            } else {
                echo "Error recording diagnosis.";
            }
        } else {
            echo "Error: PatientID or AdmissionID is empty.";
        }
    } elseif ($action == "release") {
        // Release the patient by setting the departure time
        if (!empty($AdmissionID)) {
            $sql = "UPDATE EmergencyAdmissions SET DepartureDateTime = NOW() WHERE AdmissionID = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$AdmissionID])) {
                echo "Patient successfully released.";
            } else {
                echo "Error releasing patient.";
            }
        } else {
            echo "Error: AdmissionID is empty.";
        }
    }
}

// Fetch all patients and admissions for display and selection
$patients = $conn->query("SELECT * FROM Patients")->fetchAll(PDO::FETCH_ASSOC);
$admissions = $conn->query("SELECT * FROM EmergencyAdmissions WHERE DepartureDateTime IS NULL")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $conn->query("SELECT * FROM Doctors WHERE Availability = 'Disponible'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all diagnoses for display
$diagnoses = $conn->query("
    SELECT d.*, p.FirstName AS PatientFirstName, p.LastName AS PatientLastName 
    FROM Diagnosis d 
    JOIN EmergencyAdmissions ea ON d.AdmissionID = ea.AdmissionID 
    JOIN Patients p ON ea.PatientID = p.PatientID
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch released patients
$releasedPatients = $conn->query("
    SELECT ea.*, p.FirstName, p.LastName 
    FROM EmergencyAdmissions ea 
    JOIN Patients p ON ea.PatientID = p.PatientID 
    WHERE ea.DepartureDateTime IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prise en charge par le médecin</title>
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
        <h1>Prise en charge par le médecin</h1>
        <h2>Le patient est traité par un médecin</h2>
        <p>Vérification du dossier du patient.</p>

        <h2>Admission du patient</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="admit">
            <label>Patient: 
                <select name="PatientID" required>
                    <option value="">Sélectionner un patient</option>
                    <?php
                    foreach ($patients as $patient) {
                        echo "<option value='{$patient['PatientID']}'>{$patient['FirstName']} {$patient['LastName']}</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Médecin: 
                <select name="DoctorID" required>
                    <option value="">Sélectionner un médecin</option>
                    <?php
                    foreach ($doctors as $doctor) {
                        echo "<option value='{$doctor['DoctorID']}'>{$doctor['FirstName']} {$doctor['LastName']}</option>";
                    }
                    ?>
                </select>
            </label><br>
            <input type="submit" value="Admettre le patient">
        </form>

        <h2>Saisie du diagnostic et de l'ordonnance</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="diagnose">
            <label>Patient: 
                <select name="PatientID" required>
                    <option value="">Sélectionner un patient</option>
                    <?php
                    foreach ($patients as $patient) {
                        echo "<option value='{$patient['PatientID']}'>{$patient['FirstName']} {$patient['LastName']}</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Admission pour ce patient: 
                <select name="AdmissionID" required>
                    <option value="">Sélectionner une admission</option>
                    <?php
                    foreach ($admissions as $admission) {
                        echo "<option value='{$admission['AdmissionID']}'>Admission ID: {$admission['AdmissionID']} - Arrivée: {$admission['ArrivalDateTime']}</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Type d'urgence: <input type="text" name="EmergencyType" required></label><br>
            <label>Symptômes: <textarea name="Symptoms" required></textarea></label><br>
            <label>Prescription de traitement: <textarea name="TreatmentPrescription" required></textarea></label><br>
            <input type="submit" value="Enregistrer le diagnostic et l'ordonnance">
        </form>

        <h2>Libérer les patients</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date d'arrivée</th>
                    <th>Libérer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($admissions as $admission) {
                    $patient = array_filter($patients, function ($p) use ($admission) {
                        return $p['PatientID'] == $admission['PatientID'];
                    });
                    $patient = reset($patient);
                    echo "<tr>
                            <td>{$patient['FirstName']} {$patient['LastName']}</td>
                            <td>{$admission['ArrivalDateTime']}</td>
                            <td>
                                <form method='POST' action=''>
                                    <input type='hidden' name='action' value='release'>
                                    <input type='hidden' name='AdmissionID' value='{$admission['AdmissionID']}'>
                                    <input type='submit' value='Libérer'>
                                </form>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Liste des diagnostics</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Type d'urgence</th>
                    <th>Symptômes</th>
                    <th>Prescription</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($diagnoses as $diagnosis) {
                    echo "<tr>
                            <td>{$diagnosis['PatientFirstName']} {$diagnosis['PatientLastName']}</td>
                            <td>{$diagnosis['EmergencyType']}</td>
                            <td>{$diagnosis['Symptoms']}</td>
                            <td>{$diagnosis['TreatmentPrescription']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Patients libérés</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date d'arrivée</th>
                    <th>Date de départ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($releasedPatients as $patient) {
                    echo "<tr>
                            <td>{$patient['FirstName']} {$patient['LastName']}</td>
                            <td>{$patient['ArrivalDateTime']}</td>
                            <td>{$patient['DepartureDateTime']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
