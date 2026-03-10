<?php
// Inclui a conexão com o banco de dados
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../models/UserModel.php';

echo "Iniciando script de configuração do banco de dados...\n";

// 1. GARANTIR QUE AS TABELAS EXISTAM (ORDEM CORRETA PARA CHAVES ESTRANGEIRAS)

// Tabela de usuários
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'gerente', 'motorista') NOT NULL DEFAULT 'gerente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabela 'users' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela users: " . $e->getMessage() . "\n";
}

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

// Tabela de veículos
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

// Tabela de manutenções
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS manutencoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        data DATE NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        custo DECIMAL(10, 2) NOT NULL,
        descricao TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
    )");
    echo "Tabela 'manutencoes' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela manutencoes: " . $e->getMessage() . "\n";
}

// Tabela de secretarias
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS secretarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL UNIQUE,
        responsavel VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabela 'secretarias' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela secretarias: " . $e->getMessage() . "\n";
}

// Tabela de motoristas
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS motoristas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        cnh_numero VARCHAR(20) NOT NULL UNIQUE,
        cnh_categoria VARCHAR(5) NOT NULL,
        cnh_vencimento DATE NOT NULL,
        status ENUM('ativo', 'afastado', 'ferias') DEFAULT 'ativo',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "Tabela 'motoristas' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela motoristas: " . $e->getMessage() . "\n";
}

// Tabela de viagens
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS viagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        motorista_id INT NOT NULL,
        secretaria_id INT NOT NULL,
        km_saida INT NOT NULL,
        km_chegada INT,
        destino TEXT NOT NULL,
        data_saida DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_retorno DATETIME,
        status ENUM('em_curso', 'concluida', 'cancelada') DEFAULT 'em_curso',
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
        FOREIGN KEY (motorista_id) REFERENCES motoristas(id),
        FOREIGN KEY (secretaria_id) REFERENCES secretarias(id)
    )");
    echo "Tabela 'viagens' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela viagens: " . $e->getMessage() . "\n";
}

// 2. CRIAÇÃO DO USUÁRIO ADMINISTRADOR PADRÃO

$admin_username = 'admin_frota';
$admin_password = $_ENV['ADMIN_DEFAULT_PASS'] ?? 'SenhaPadrao123';
$admin_role = 'admin';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => $admin_username]);
    
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$admin_username, $hash, $admin_role]);

        echo "✅ Usuário Administrador criado com sucesso!\n";
        echo "Usuário: {$admin_username}\n";
        echo "Senha: {$admin_password} (Lembre-se de mudar após o login!)\n";
    } else {
        echo "ℹ️ Usuário '{$admin_username}' já existe. Nenhuma ação necessária para o seed.\n";
    }
} catch (PDOException $e) {
    error_log("Erro no Seeder: " . $e->getMessage());
    echo "❌ Erro ao tentar criar o usuário administrador.\n";
}

echo "Script finalizado.\n";
?>
