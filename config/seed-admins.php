<?php
/**
 * Admin Seeder
 * 
 * File ini digunakan untuk initialize/seed akun admin default
 * Jalankan dengan:  php config/seed-admins.php
 * 
 * Password semua akun: admin123
 */

require_once __DIR__ . '/db.php';

echo "=== Admin Seeder ===\n\n";

try {
    $db = getDB();
    
    // 3 Akun Admin Default
    $admins = [
        [
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'superadmin',
            'deskripsi' => 'Super Admin - Full Access'
        ],
        [
            'username' => 'admin1',
            'password' => 'admin123',
            'role' => 'admin',
            'deskripsi' => 'Admin 1 - Standard Access'
        ],
        [
            'username' => 'admin2',
            'password' => 'admin123',
            'role' => 'admin',
            'deskripsi' => 'Admin 2 - Standard Access'
        ]
    ];
    
    echo "Inserting admin accounts...\n";
    
    $inserted = 0;
    $updated = 0;
    
    foreach ($admins as $admin) {
        $hashPassword = password_hash($admin['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Check if admin exists
        $stmt = $db->prepare("SELECT id FROM tb_admin WHERE username = ?");
        $stmt->execute([$admin['username']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing
            $stmt = $db->prepare("UPDATE tb_admin SET password = ?, role = ? WHERE username = ?");
            $stmt->execute([$hashPassword, $admin['role'], $admin['username']]);
            echo "✓ Updated: {$admin['username']} ({$admin['deskripsi']})\n";
            $updated++;
        } else {
            // Insert new
            $stmt = $db->prepare("INSERT INTO tb_admin (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$admin['username'], $hashPassword, $admin['role']]);
            echo "✓ Inserted: {$admin['username']} ({$admin['deskripsi']})\n";
            $inserted++;
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Inserted: $inserted\n";
    echo "Updated: $updated\n";
    
    // Show all admins
    echo "\n=== CURRENT ADMIN ACCOUNTS ===\n";
    $result = $db->query("SELECT id, username, role, created_at FROM tb_admin ORDER BY id");
    $admins = $result->fetchAll();
    
    echo "\nID | Username | Role       | Created Date\n";
    echo str_repeat("-", 55) . "\n";
    foreach ($admins as $admin) {
        printf("%-2d | %-8s | %-10s | %s\n", 
            $admin['id'], 
            $admin['username'], 
            $admin['role'],
            $admin['created_at']
        );
    }
    
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "Username: admin, admin1, atau admin2\n";
    echo "Password: admin123\n";
    echo "\n✓ Seeding completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
