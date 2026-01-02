<?php
require_once 'backend/config/db.php'; // Conexão DB
require_once 'backend/models/UserModel.php'; // Model com register

$model = new UserModel();
$model->register('admin', 'senha123'); // Username 'admin', senha plain 'senha123' – hash auto
echo "Usuário 'admin' criado/atualizado com sucesso! Senha para login: senha123";
?>