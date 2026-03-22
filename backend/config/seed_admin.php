<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../models/UserModel.php';

if (!is_cli_request()) {
    http_response_code(403);
    exit('Este script só pode ser executado via CLI.' . PHP_EOL);
}

echo "Iniciando script de configuração do banco de dados...\n";

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'gerente', 'motorista') NOT NULL DEFAULT 'gerente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Tabela 'users' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela users: " . $e->getMessage() . "\n";
}

try {
    $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($colCheck->rowCount() === 0) {
        echo "Coluna 'role' não encontrada. Adicionando à tabela users...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'gerente', 'motorista') NOT NULL DEFAULT 'gerente'");
    }
} catch (PDOException $e) {
    echo "Aviso ao verificar estrutura: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS veiculos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        placa VARCHAR(20) NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        status ENUM('ativo', 'manutencao') NOT NULL DEFAULT 'ativo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_veiculos_placa (placa)
    )");
    echo "Tabela 'veiculos' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela veiculos: " . $e->getMessage() . "\n";
}

try {
    $indexCheck = $pdo->query("SHOW INDEX FROM veiculos WHERE Key_name = 'uk_veiculos_placa'");
    if ($indexCheck->rowCount() === 0) {
        echo "Índice único da placa não encontrado. Adicionando...\n";
        $pdo->exec("ALTER TABLE veiculos ADD CONSTRAINT uk_veiculos_placa UNIQUE (placa)");
    }
} catch (PDOException $e) {
    echo "Aviso ao verificar índice de placa: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS manutencoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        data DATE NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        custo DECIMAL(10, 2) NOT NULL,
        descricao TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
    )");
    echo "Tabela 'manutencoes' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela manutencoes: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS secretarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL UNIQUE,
        responsavel VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Tabela 'secretarias' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela secretarias: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS motoristas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        cnh_numero VARCHAR(20) NOT NULL UNIQUE,
        cnh_categoria VARCHAR(5) NOT NULL,
        cnh_vencimento DATE NOT NULL,
        status ENUM('ativo', 'afastado', 'ferias') DEFAULT 'ativo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "Tabela 'motoristas' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela motoristas: " . $e->getMessage() . "\n";
}

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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
        FOREIGN KEY (motorista_id) REFERENCES motoristas(id),
        FOREIGN KEY (secretaria_id) REFERENCES secretarias(id)
    )");
    echo "Tabela 'viagens' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao verificar tabela viagens: " . $e->getMessage() . "\n";
}

$adminUsername = 'admin_frota';
$adminPassword = $_ENV['ADMIN_DEFAULT_PASS'] ?? bin2hex(random_bytes(6));
$adminRole = 'admin';

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
    $stmt->execute([':username' => $adminUsername]);

    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$adminUsername, $hash, $adminRole]);

        echo "✅ Usuário Administrador criado com sucesso.\n";
        echo "Usuário: {$adminUsername}\n";
        echo "Senha temporária: {$adminPassword}\n";
        echo "Altere a senha imediatamente após o primeiro login.\n";
    } else {
        echo "ℹ️ Usuário '{$adminUsername}' já existe. Nenhuma ação necessária para o seed.\n";
    }
} catch (PDOException $e) {
    error_log('Erro no seeder: ' . $e->getMessage());
    echo "❌ Erro ao tentar criar o usuário administrador.\n";
}

echo "Script finalizado.\n";
?>
