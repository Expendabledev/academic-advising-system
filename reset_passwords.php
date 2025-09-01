<?php
// Run this script to reset the default passwords
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // Generate correct password hashes
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $supervisorHash = password_hash('supervisor123', PASSWORD_DEFAULT);
    $studentHash = password_hash('student123', PASSWORD_DEFAULT);
    
    // Update passwords in database
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
    
    // Update admin password
    $stmt->execute([$adminHash, 'admin']);
    echo "Admin password updated (username: admin, password: admin123)\n";
    
    // Update supervisor password
    $stmt->execute([$supervisorHash, 'supervisor']);
    echo "Supervisor password updated (username: supervisor, password: supervisor123)\n";
    
    // Update student password
    $stmt->execute([$studentHash, 'student']);
    echo "Student password updated (username: student, password: student123)\n";
    
    echo "\nAll passwords have been updated successfully!\n";
    echo "You can now login with:\n";
    echo "- Admin: username=admin, password=admin123\n";
    echo "- Supervisor: username=supervisor, password=supervisor123\n";
    echo "- Student: username=student, password=student123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>