<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/MotoristaModel.php';

secure_session_start();

if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de motoristas.');
    header('Location: /login.php');
    exit;
}

$model = new MotoristaModel();
$motoristas = $model->getAllMotoristas();
$canManageMotoristas = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE);
$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$editingMotorista = null;
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($editingId > 0 && $canManageMotoristas) {
    $editingMotorista = $model->findById($editingId);
}

$pageTitle = 'Motoristas';
require_once __DIR__ . '/../includes/header.php';

$totalMotoristas = count($motoristas);
$motoristasAtivos = 0;
$cnhsVencendo = $model->countCnhsVencendo();
$today = new DateTimeImmutable('today');
$alertLimit = $today->modify('+30 days');

foreach ($motoristas as $motorista) {
    if (($motorista['status'] ?? '') === 'ativo') {
        $motoristasAtivos++;
    }
}

/**
 * @param string $cpf
 */
function format_cpf(string $cpf): string
{
    $digits = preg_replace('/\D+/', '', $cpf) ?? '';
    if (strlen($digits) !== 11) {
        return $cpf;
    }

    return substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2);
}
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Motoristas</h1>
        <p class="text-slate-500 text-sm">Cadastro operacional de condutores vinculados as secretarias do municipio.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Perfil atual: <?php echo htmlspecialchars((string) $_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-xs text-slate-500">Leitura e gestao seguem as permissoes centrais do sistema.</span>
    </div>
</div>

<?php if ($successMessage): ?>
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700" role="status">
        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700" role="alert">
        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Total de motoristas</p>
        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalMotoristas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Ativos</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2"><?php echo $motoristasAtivos; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">CNH vencendo em 30 dias</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo $cnhsVencendo; ?></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">
                <?php echo $editingMotorista ? 'Editar motorista' : 'Novo motorista'; ?>
            </h2>
            <p class="text-sm text-slate-500 mb-5">Dados essenciais para gestao operacional e futura vinculacao com viagens.</p>

            <?php if ($canManageMotoristas): ?>
                <form method="POST" action="/motoristas.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editingMotorista ? 'update_motorista' : 'add_motorista'; ?>">
                    <?php if ($editingMotorista): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $editingMotorista['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nome completo</label>
                        <input type="text" name="nome" required value="<?php echo htmlspecialchars((string) ($editingMotorista['nome'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">CPF</label>
                        <input type="text" name="cpf" required value="<?php echo htmlspecialchars((string) ($editingMotorista['cpf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Somente numeros ou CPF formatado">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Telefone</label>
                        <input type="text" name="telefone" value="<?php echo htmlspecialchars((string) ($editingMotorista['telefone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Secretaria de lotacao</label>
                        <input type="text" name="secretaria" required value="<?php echo htmlspecialchars((string) ($editingMotorista['secretaria'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">CNH</label>
                            <input type="text" name="cnh_numero" required value="<?php echo htmlspecialchars((string) ($editingMotorista['cnh_numero'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Categoria</label>
                            <select name="cnh_categoria" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'] as $categoria): ?>
                                    <option value="<?php echo $categoria; ?>" <?php echo (($editingMotorista['cnh_categoria'] ?? 'B') === $categoria) ? 'selected' : ''; ?>>
                                        <?php echo $categoria; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Vencimento da CNH</label>
                            <input type="date" name="cnh_vencimento" required value="<?php echo htmlspecialchars((string) ($editingMotorista['cnh_vencimento'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['ativo', 'afastado', 'ferias', 'desligado'] as $statusOption): ?>
                                    <option value="<?php echo $statusOption; ?>" <?php echo (($editingMotorista['status'] ?? 'ativo') === $statusOption) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($statusOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                            <?php echo $editingMotorista ? 'Salvar alteracoes' : 'Cadastrar motorista'; ?>
                        </button>
                        <?php if ($editingMotorista): ?>
                            <a href="/motoristas.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura ao modulo de motoristas.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Motoristas cadastrados</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Motorista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">CNH</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Secretaria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($motoristas)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum motorista cadastrado ate o momento.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($motoristas as $motorista): ?>
                            <?php
                            $cnhVencimento = DateTimeImmutable::createFromFormat('Y-m-d', (string) $motorista['cnh_vencimento']);
                            $isNearExpiry = $cnhVencimento instanceof DateTimeImmutable && $cnhVencimento >= $today && $cnhVencimento <= $alertLimit;
                            ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $motorista['nome'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars(format_cpf((string) $motorista['cpf']), ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars((string) $motorista['cnh_numero'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs <?php echo $isNearExpiry ? 'text-amber-700 font-semibold' : 'text-slate-500'; ?>">
                                        Categoria <?php echo htmlspecialchars((string) $motorista['cnh_categoria'], ENT_QUOTES, 'UTF-8'); ?>
                                        · Vence em <?php echo htmlspecialchars((string) $motorista['cnh_vencimento'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <?php echo htmlspecialchars((string) $motorista['secretaria'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $motorista['status'] === 'ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; ?>">
                                        <?php echo htmlspecialchars(ucfirst((string) $motorista['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManageMotoristas): ?>
                                        <a href="/motoristas.php?edit=<?php echo (int) $motorista['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                            Editar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-400">Somente leitura</span>
                                    <?php endif; ?>
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
