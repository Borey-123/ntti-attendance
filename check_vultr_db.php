<?php

$host = '66.42.61.106';
$db   = 'ntti_attendance';
$user = 'ntti_user';
$pass = 'Ntti@123456789!';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check teachers table
    $stmt = $pdo->query("SELECT id, name, name_kh, department FROM teachers WHERE name_kh LIKE '%ß%' OR name LIKE '%ß%' OR department LIKE '%ß%'");
    $corruptedTeachers = $stmt->fetchAll();
    echo "Corrupted Teachers: " . count($corruptedTeachers) . "\n";
    print_r($corruptedTeachers);

    // Check attendance manual_note
    $stmt = $pdo->query("SELECT id, teacher_id, manual_note FROM attendance WHERE manual_note LIKE '%ß%'");
    $corruptedAttendance = $stmt->fetchAll();
    echo "Corrupted Attendance: " . count($corruptedAttendance) . "\n";
    print_r($corruptedAttendance);
    
    // Check departments table if it exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'departments'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT * FROM departments WHERE name_kh LIKE '%ß%' OR description LIKE '%ß%'");
        $corruptedDepts = $stmt->fetchAll();
        echo "Corrupted Departments: " . count($corruptedDepts) . "\n";
        print_r($corruptedDepts);
    }
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
