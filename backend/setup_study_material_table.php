<?php
require 'db.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS `study_material`");

    $sql = "CREATE TABLE `study_material` (
        `MaterialID` INT(11) NOT NULL AUTO_INCREMENT,
        `StudentID` INT(11) NOT NULL,
        `CourseID` INT(11) DEFAULT NULL,
        `Title` VARCHAR(255) NOT NULL,
        `Type` ENUM('PDF','Image','Link') NOT NULL,
        `URL_or_Path` VARCHAR(255) NOT NULL,
        `UploadedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`MaterialID`)
        -- Foreign Key removed due to potential charset/engine mismatch in existing DB
        -- FOREIGN KEY (`StudentID`) REFERENCES `STUDENT`(`StudentID`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'study_material' recreated successfully (No FK).";
} catch (PDOException $e) {
    echo "Error setup table: " . $e->getMessage();
}
?>
