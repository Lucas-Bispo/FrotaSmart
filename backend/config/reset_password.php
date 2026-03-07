<?php
require_once __DIR__ . '/db.php';

$username = 'admin_frota';
$new_password = '123456';

echo "--- Redefinição de Senha ---\n";
echo "Usuário alvo: $username\n";

try {
    // Verifica se usuário existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        // Atualiza senha
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $update->execute([':password' => $hash, ':id' => $user['id']]);
        
        echo "✅ Senha atualizada com sucesso!\n";
        echo "Nova senha: $new_password\n";
    } else {
        echo "❌ Usuário não encontrado. Execute o seed_admin.php primeiro.\n";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>