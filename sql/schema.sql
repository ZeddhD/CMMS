-- University Table
CREATE TABLE UNIVERSITY (
  UniID INT AUTO_INCREMENT PRIMARY KEY,
  UniName VARCHAR(100) NOT NULL,
  Location VARCHAR(100),
  Domain VARCHAR(100)
);
-- Preload Universities
INSERT INTO UNIVERSITY (UniName, Location)
VALUES ('University of Dhaka', 'Dhaka'),
  ('BRAC University', 'Dhaka'),
  ('North South University', 'Dhaka'),
  (
    'American International University-Bangladesh',
    'Dhaka'
  ),
  ('Independent University, Bangladesh', 'Dhaka'),
  ('United International University', 'Dhaka'),
  ('Daffodil International University', 'Dhaka'),
  (
    'Ahsanullah University of Science and Technology',
    'Dhaka'
  ),
  ('East West University', 'Dhaka'),
  (
    'Bangladesh University of Business and Technology',
    'Dhaka'
  ),
  ('Green University of Bangladesh', 'Dhaka'),
  ('University of Liberal Arts Bangladesh', 'Dhaka'),
  ('Stamford University Bangladesh', 'Dhaka'),
  ('Primeasia University', 'Dhaka'),
  (
    'International University of Business Agriculture and Technology',
    'Dhaka'
  ),
  ('Bangladesh University', 'Dhaka'),
  ('World University of Bangladesh', 'Dhaka'),
  ('Canadian University of Bangladesh', 'Dhaka'),
  ('Eastern University', 'Dhaka'),
  ('University of Asia Pacific', 'Dhaka'),
  ('State University of Bangladesh', 'Dhaka'),
  ('Sonargaon University', 'Dhaka'),
  ('Manarat International University', 'Dhaka');
-- Student Table
CREATE TABLE STUDENT (
  StudentID INT AUTO_INCREMENT PRIMARY KEY,
  UniID INT,
  Name VARCHAR(100),
  Email VARCHAR(100) UNIQUE,
  PasswordHash VARCHAR(255),
  RegistrationDate DATE,
  FOREIGN KEY (UniID) REFERENCES UNIVERSITY(UniID)
);
-- Admin Table
CREATE TABLE ADMIN (
  AdminID INT AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(100),
  Email VARCHAR(100) UNIQUE,
  PasswordHash VARCHAR(255),
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Course Table
CREATE TABLE COURSE (
  CourseID INT AUTO_INCREMENT PRIMARY KEY,
  UniID INT,
  CourseCode VARCHAR(20),
  CourseTitle VARCHAR(100),
  CreditHours INT,
  FOREIGN KEY (UniID) REFERENCES UNIVERSITY(UniID)
);
-- Enrollment Table
CREATE TABLE ENROLLMENT (
  EnrollmentID INT AUTO_INCREMENT PRIMARY KEY,
  StudentID INT,
  CourseID INT,
  Semester VARCHAR(20),
  BestOfQuizCount INT,
  TotalQuizPlanned INT,
  TotalAssignmentPlanned INT,
  QuizCompletedCount INT,
  AssignmentCompletedCount INT,
  FOREIGN KEY (StudentID) REFERENCES STUDENT(StudentID),
  FOREIGN KEY (CourseID) REFERENCES COURSE(CourseID)
);
-- Assessment Table
CREATE TABLE ASSESSMENT (
  AssessmentID INT AUTO_INCREMENT PRIMARY KEY,
  EnrollmentID INT,
  AssessmentType ENUM('quiz', 'assignment', 'exam'),
  Title VARCHAR(100),
  MaxMarks INT,
  WeightInBestOf INT,
  DueDate DATE,
  DueTime TIME,
  Status VARCHAR(20),
  IsMandatory BOOLEAN,
  MarksObtained INT,
  FOREIGN KEY (EnrollmentID) REFERENCES ENROLLMENT(EnrollmentID)
);
-- Class Session Table
CREATE TABLE CLASS_SESSION (
  ClassSessionID INT AUTO_INCREMENT PRIMARY KEY,
  EnrollmentID INT,
  DayOfWeek ENUM('Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'),
  StartTime TIME,
  EndTime TIME,
  Room VARCHAR(50),
  Mode ENUM('online', 'offline'),
  FOREIGN KEY (EnrollmentID) REFERENCES ENROLLMENT(EnrollmentID)
);
-- Study Material Table
CREATE TABLE STUDY_MATERIAL (
  MaterialID INT AUTO_INCREMENT PRIMARY KEY,
  CourseID INT,
  StudentID INT,
  Title VARCHAR(100),
  MaterialType VARCHAR(50),
  URL_or_Path TEXT,
  UploadDate DATE,
  FOREIGN KEY (CourseID) REFERENCES COURSE(CourseID),
  FOREIGN KEY (StudentID) REFERENCES STUDENT(StudentID)
);