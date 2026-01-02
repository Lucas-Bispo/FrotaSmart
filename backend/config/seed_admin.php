<?php
// Inclui a conexão com o banco de dados
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../models/UserModel.php'; // Adiciona o UserModel para usar a função de hash

echo "Iniciando script de criação de usuário administrador...\n";

// Verifica e cria a coluna 'role' se ela não existir (migração automática)
try {
    $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($colCheck->rowCount() === 0) {
        echo "Coluna 'role' não encontrada. Adicionando à tabela users...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'gerente', 'motorista') NOT NULL DEFAULT 'gerente'");
    }
} catch (PDOException $e) {
    echo "Aviso ao verificar estrutura: " . $e->getMessage() . "\n";
}

// Garante que a tabela de veículos exista
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS veiculos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        placa VARCHAR(20) NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        status ENUM('ativo', 'manutencao') NOT NULL DEFAULT 'ativo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabela 'veiculos' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela veiculos: " . $e->getMessage() . "\n";
}

// Dados do primeiro administrador
$admin_username = 'admin_frota';
$admin_password = $_ENV['ADMIN_DEFAULT_PASS'] ?? 'SenhaPadrao123'; // Pega do .env ou usa fallback
$admin_role = 'admin';

try {
    // 1. Verificar se o usuário já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => $admin_username]);
    if ($stmt->fetchColumn() > 0) {
        echo "Usuário '{$admin_username}' já existe. Nenhuma ação necessária.\n";
        exit;
    }

    // 2. Hash da senha
    $hash = password_hash($admin_password, PASSWORD_DEFAULT);

    // 3. Inserir no banco de dados
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$admin_username, $hash, $admin_role]);

    echo "✅ Usuário Administrador criado com sucesso!\n";
    echo "Usuário: {$admin_username}\n";
    echo "Senha: {$admin_password} (Lembre-se de mudar após o login!)\n";
    echo "Role: {$admin_role}\n";

} catch (PDOException $e) {
    error_log("Erro no Seeder: " . $e->getMessage());
    die("❌ Erro ao tentar criar o usuário administrador. Verifique os logs.");
}

?>