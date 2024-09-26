-- Database: hospital_management

-- Doctors Table
CREATE TABLE Doctors (
    DoctorID INT AUTO_INCREMENT PRIMARY KEY,
    CPSNumber VARCHAR(10) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    Gender VARCHAR(10),
    Specialty VARCHAR(50),
    Availability VARCHAR(100) DEFAULT 'Available'
);

-- Patients Table
CREATE TABLE Patients (
    PatientID INT AUTO_INCREMENT PRIMARY KEY,
    SocialSecurityNumber VARCHAR(20) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    Gender VARCHAR(10),
    DateOfBirth DATE NOT NULL,
    DoctorID INT,
    FOREIGN KEY (DoctorID) REFERENCES Doctors(DoctorID)
);

-- EmergencyAdmissions Table
CREATE TABLE EmergencyAdmissions (
    AdmissionID INT AUTO_INCREMENT PRIMARY KEY,
    PatientID INT NOT NULL,
    DoctorID INT NOT NULL,
    ArrivalDateTime DATETIME NOT NULL,
    DepartureDateTime DATETIME,
    Tariff DECIMAL(10, 2),
    FOREIGN KEY (PatientID) REFERENCES Patients(PatientID),
    FOREIGN KEY (DoctorID) REFERENCES Doctors(DoctorID)
);

-- Diagnosis Table
CREATE TABLE Diagnosis (
    DiagnosisID INT AUTO_INCREMENT PRIMARY KEY,
    AdmissionID INT NOT NULL,
    EmergencyType VARCHAR(50),
    Symptoms TEXT,
    TreatmentPrescription TEXT,
    FOREIGN KEY (AdmissionID) REFERENCES EmergencyAdmissions(AdmissionID)
);

-- EmergencyTypes Table
CREATE TABLE EmergencyTypes (
    EmergencyTypeID INT AUTO_INCREMENT PRIMARY KEY,
    TypeName VARCHAR(50)
);

-- TreatmentPrescriptions Table
CREATE TABLE TreatmentPrescriptions (
    PrescriptionID INT AUTO_INCREMENT PRIMARY KEY,
    DiagnosisID INT NOT NULL,
    MedicationName VARCHAR(100),
    Dosage VARCHAR(50),
    FOREIGN KEY (DiagnosisID) REFERENCES Diagnosis(DiagnosisID)
);

-- TreatmentPayments Table
CREATE TABLE TreatmentPayments (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    AdmissionID INT NOT NULL,
    AmountPaid DECIMAL(10, 2) NOT NULL,
    PaymentDateTime DATETIME NOT NULL,
    FOREIGN KEY (AdmissionID) REFERENCES EmergencyAdmissions(AdmissionID)
);

-- TreatmentRooms Table
CREATE TABLE TreatmentRooms (
    RoomID INT AUTO_INCREMENT PRIMARY KEY,
    RoomNumber VARCHAR(10) NOT NULL,
    Availability VARCHAR(100) DEFAULT 'Available'
);
