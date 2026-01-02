<?php
ini_set('display_errors', 1); error_reporting(E_ALL);  // Debug temp - remova após fix
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../../backend/models/VeiculoModel.php';  // __DIR__ = views/, /../../ sobe para raiz, /backend/models

try {
    $model = new VeiculoModel();
    $veiculos = $model->getAllVeiculos();
} catch (Exception $e) {
    echo "Erro no Model: " . $e->getMessage();
    $veiculos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Dashboard FrotaSmart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <h1 class="text-2xl font-bold mb-4">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h1>

    <!-- Form Adicionar Veículo -->
    <form method="POST" action="../../backend/controllers/VeiculoController.php" class="mb-8 bg-white p-6 rounded shadow-md">
        <input type="hidden" name="action" value="add_veiculo">
        <label class="block mb-2">Placa:</label>
        <input type="text" name="placa" required class="w-full border p-2 mb-4">
        <label class="block mb-2">Modelo:</label>
        <input type="text" name="modelo" required class="w-full border p-2 mb-4">
        <label class="block mb-2">Status:</label>
        <select name="status" class="w-full border p-2 mb-4">
            <option value="ativo">Ativo</option>
            <option value="manutencao">Manutenção</option>
        </select>
        <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded">Adicionar Veículo</button>
    </form>

    <!-- Tabela Veículos -->
    <table class="min-w-full bg-white border">
    <thead><tr><th>Placa</th><th>Modelo</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
        <?php foreach ($veiculos as $v): ?>
            <tr>
                <td><?php echo htmlspecialchars($v['placa']); ?></td>
                <td><?php echo htmlspecialchars($v['modelo']); ?></td>
                <td><?php echo htmlspecialchars($v['status']); ?></td>
                <td>
                    <!-- Edit Form (simple inline) -->
                    <form method="POST" action="../../backend/controllers/VeiculoController.php" class="inline">
                        <input type="hidden" name="action" value="update_veiculo">
                        <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                        <input type="text" name="placa" value="<?php echo htmlspecialchars($v['placa']); ?>" class="w-20 border">
                        <input type="text" name="modelo" value="<?php echo htmlspecialchars($v['modelo']); ?>" class="w-32 border">
                        <select name="status" class="border">
                            <option value="ativo" <?php if ($v['status'] == 'ativo') echo 'selected'; ?>>Ativo</option>
                            <option value="manutencao" <?php if ($v['status'] == 'manutencao') echo 'selected'; ?>>Manutenção</option>
                        </select>
                        <button type="submit" class="bg-blue-500 text-white py-1 px-2 rounded text-xs">Update</button>
                    </form>
                    <!-- Delete Link -->
                    <a href="../../backend/controllers/VeiculoController.php?action=delete_veiculo&id=<?php echo $v['id']; ?>" class="bg-red-500 text-white py-1 px-2 rounded text-xs" onclick="return confirm('Deletar?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    <a href="../../backend/controllers/AuthController.php?logout=1" class="mt-4 inline-block bg-red-500 text-white py-2 px-4 rounded">Logout</a>
</body>
</html>