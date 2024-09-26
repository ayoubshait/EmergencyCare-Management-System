<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $AdmissionID = $_POST['AdmissionID'] ?? null;
    $DepartureDateTime = $_POST['DepartureDateTime'] ?? null;
    $AmountPaid = $_POST['AmountPaid'] ?? null;

    if ($action == "checkout") {
        // Ensure AdmissionID and DepartureDateTime are not empty before proceeding
        if (!empty($AdmissionID) && !empty($DepartureDateTime)) {
            // Update EmergencyAdmissions with DepartureDateTime
            $sql = "UPDATE EmergencyAdmissions SET DepartureDateTime = ? WHERE AdmissionID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$DepartureDateTime, $AdmissionID]);

            // Insert payment record
            $sql = "INSERT INTO TreatmentPayments (AdmissionID, AmountPaid, PaymentDateTime) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$AdmissionID, $AmountPaid, $DepartureDateTime]);
        } else {
            echo "Error: AdmissionID or DepartureDateTime is empty.";
        }
    }
}

// Fetch all admissions for display and selection
$admissions = $conn->query("SELECT ea.AdmissionID, ea.ArrivalDateTime, p.FirstName, p.LastName
                            FROM EmergencyAdmissions ea
                            JOIN Patients p ON ea.PatientID = p.PatientID
                            WHERE ea.DepartureDateTime IS NULL")->fetchAll(PDO::FETCH_ASSOC);

// Fetch prescriptions for display
$prescriptions = $conn->query("SELECT d.AdmissionID, p.FirstName, p.LastName, d.TreatmentPrescription
                               FROM Diagnosis d
                               JOIN EmergencyAdmissions ea ON d.AdmissionID = ea.AdmissionID
                               JOIN Patients p ON ea.PatientID = p.PatientID")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sortie du patient</title>
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
        <h1>Sortie du patient</h1>
        <h2>Récupérer l’ordonnance</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Ordonnance</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($prescriptions as $prescription) {
                    echo "<tr>
                            <td>{$prescription['FirstName']} {$prescription['LastName']}</td>
                            <td>{$prescription['TreatmentPrescription']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Payer les soins non remboursés</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="checkout">
            <label>Admission:
                <select name="AdmissionID" required>
                    <option value="">Sélectionner une admission</option>
                    <?php
                    foreach ($admissions as $admission) {
                        echo "<option value='{$admission['AdmissionID']}'>Admission ID: {$admission['AdmissionID']} - Patient: {$admission['FirstName']} {$admission['LastName']} (Arrivée: {$admission['ArrivalDateTime']})</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Date et heure de sortie: <input type="datetime-local" name="DepartureDateTime" required></label><br>
            <label>Montant payé: <input type="number" step="0.01" name="AmountPaid" required></label><br>
            <input type="submit" value="Clôturer le dossier et payer">
        </form>

        <h2>Patients libérés</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date d'arrivée</th>
                    <th>Date de départ</th>
                    <th>Montant payé</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $releasedPatients = $conn->query("SELECT p.FirstName, p.LastName, ea.ArrivalDateTime, ea.DepartureDateTime, tp.AmountPaid
                                                  FROM EmergencyAdmissions ea
                                                  JOIN Patients p ON ea.PatientID = p.PatientID
                                                  JOIN TreatmentPayments tp ON ea.AdmissionID = tp.AdmissionID
                                                  WHERE ea.DepartureDateTime IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($releasedPatients as $patient) {
                    echo "<tr>
                            <td>{$patient['FirstName']} {$patient['LastName']}</td>
                            <td>{$patient['ArrivalDateTime']}</td>
                            <td>{$patient['DepartureDateTime']}</td>
                            <td>{$patient['AmountPaid']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
