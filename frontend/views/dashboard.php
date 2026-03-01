<?php
session_start();
// Verifica se o usuário está logado, se não estiver redireciona para a página de login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Carrega o modelo de veículo para utilizar suas funções
require_once __DIR__ . '/../../backend/models/VeiculoModel.php';

// Carrega o layout flex para a página
require_once __DIR__ . '/../includes/header.php'; 

// Tenta obter todos os veículos do banco de dados
try {
    $model = new VeiculoModel();
    $veiculos = $model->getAllVeiculos(); 
} catch (Exception $e) {
    // Se houver um erro, atribui o erro à variável e reseta as variáveis para seus valores padrão
    $error = $e->getMessage();
    $veiculos = [];
    $totalFrota = $ativos = $manutencao = 0;
}

// Calcula a quantidade total de veículos e quantos estão em operação e manutenção
foreach($veiculos as $v) {
    if ($v['status'] === 'ativo') $ativos++;
    if ($v['status'] === 'manutencao') $manutencao++;
}

// Mostra o painel de controle
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Painel de Controle</h1>
        <p class="text-gray-600 text-sm">Gerenciamento de frotas para prefeituras.</p>
    </div>
    <div class="text-right">
        <span class="block text-sm font-medium text-gray-700">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user']); ?>!</span>
        <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">FrotaSmart v1.0</span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center">
        <div class="p-3 bg-blue-500 rounded-lg mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 uppercase">Total da Frota</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalFrota; ?></p>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center">
        <div class="p-3 bg-green-500 rounded-lg mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 uppercase">Em Operação</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $ativos; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center">
        <div class="p-3 bg-yellow-500 rounded-lg mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 uppercase">Manutenção</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $manutencao; ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Rápido: Novo Veículo</h2>
            <form method="POST" action="../../backend/controllers/VeiculoController.php"> 
                <input type="hidden" name="action" value="add_veiculo">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Placa:</label>
                        <input type="text" name="placa" placeholder="ABC-1234" required class="w-full border rounded-lg p-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modelo:</label>
                        <input type="text" name="modelo" placeholder="Ex: Mercedes OF-1721" required class="w-full border rounded-lg p-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
                        <select name="status" class="w-full border rounded-lg p-2 outline-none">
                            <option value="ativo">Ativo</option>
                            <option value="manutencao">Manutenção</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        Cadastrar Veículo
                    </button>
                </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-700">Frota Atualizada</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veículo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($veiculos as $v): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($v['placa']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($v['modelo']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $v['status'] === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($v['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <a href="../../backend/controllers/VeiculoController.php?action=delete_veiculo&id=<?php echo $v['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</main>
</body>
</html>
