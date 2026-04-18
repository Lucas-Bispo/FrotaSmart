<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../../backend/models/VeiculoModel.php';
require_once __DIR__ . '/../../backend/models/ViagemModel.php';

secure_session_start();

if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de viagens.');
    header('Location: /login.php');
    exit;
}

$connection = \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make();
$viagemModel = new ViagemModel($connection);
$veiculoModel = new VeiculoModel($connection);
$motoristaModel = new MotoristaModel($connection);

$filtroStatus = trim((string) ($_GET['status'] ?? ''));
$filtroSecretaria = trim((string) ($_GET['secretaria'] ?? ''));

$viagens = $viagemModel->listByFilters([
    'status' => $filtroStatus !== '' ? $filtroStatus : null,
    'secretaria' => $filtroSecretaria !== '' ? $filtroSecretaria : null,
]);
$veiculos = $veiculoModel->getAllVeiculos();
$motoristas = $motoristaModel->getAllMotoristas();
$canManage = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE);
$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingViagem = $editingId > 0 && $canManage ? $viagemModel->findById($editingId) : null;

$emCurso = 0;
$concluidas = 0;
$kmPercorridos = 0;
$secretarias = [];

foreach ($viagens as $viagem) {
    if (($viagem['status'] ?? '') === 'em_curso') {
        $emCurso++;
    }
    if (($viagem['status'] ?? '') === 'concluida') {
        $concluidas++;
    }

    if (!empty($viagem['km_chegada']) && (int) $viagem['km_chegada'] >= (int) $viagem['km_saida']) {
        $kmPercorridos += (int) $viagem['km_chegada'] - (int) $viagem['km_saida'];
    }

    $secretaria = trim((string) ($viagem['secretaria'] ?? ''));
    if ($secretaria !== '') {
        $secretarias[$secretaria] = true;
    }
}

ksort($secretarias);
$pageTitle = 'Viagens';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Viagens e Rotas</h1>
        <p class="text-slate-500 text-sm">Controle administrativo do uso da frota por secretaria, motorista e veiculo.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Perfil atual: <?php echo htmlspecialchars((string) $_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-xs text-slate-500">O historico de uso da frota passa a ficar auditavel e consultavel.</span>
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

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Viagens filtradas</p>
        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo count($viagens); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Em curso</p>
        <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $emCurso; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Concluidas</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2"><?php echo $concluidas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">KM percorridos</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo number_format((float) $kmPercorridos, 0, ',', '.'); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1 space-y-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">
                <?php echo $editingViagem ? 'Editar viagem' : 'Nova viagem'; ?>
            </h2>
            <p class="text-sm text-slate-500 mb-5">Registre a operacao com secretaria, responsavel, trajeto, horario e hodometro.</p>

            <?php if (empty($veiculos) || empty($motoristas)): ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Cadastre pelo menos um veiculo e um motorista antes de registrar viagens.
                </div>
            <?php elseif ($canManage): ?>
                <form method="POST" action="/viagens.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editingViagem ? 'update_viagem' : 'add_viagem'; ?>">
                    <?php if ($editingViagem): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $editingViagem['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Secretaria solicitante</label>
                        <input type="text" name="secretaria" required value="<?php echo htmlspecialchars((string) ($editingViagem['secretaria'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Solicitante ou responsavel</label>
                        <input type="text" name="solicitante" required value="<?php echo htmlspecialchars((string) ($editingViagem['solicitante'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Veiculo</label>
                        <select name="veiculo_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione um veiculo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?php echo (int) $veiculo['id']; ?>" <?php echo ((int) ($editingViagem['veiculo_id'] ?? 0) === (int) $veiculo['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $veiculo['placa'] . ' - ' . (string) $veiculo['modelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Motorista</label>
                        <select name="motorista_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione um motorista</option>
                            <?php foreach ($motoristas as $motorista): ?>
                                <option value="<?php echo (int) $motorista['id']; ?>" <?php echo ((int) ($editingViagem['motorista_id'] ?? 0) === (int) $motorista['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $motorista['nome'] . ' - ' . (string) $motorista['secretaria'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Origem</label>
                            <input type="text" name="origem" required value="<?php echo htmlspecialchars((string) ($editingViagem['origem'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Destino</label>
                            <input type="text" name="destino" required value="<?php echo htmlspecialchars((string) ($editingViagem['destino'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Finalidade</label>
                        <textarea name="finalidade" required class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[100px]"><?php echo htmlspecialchars((string) ($editingViagem['finalidade'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Data e hora de saida</label>
                            <input type="datetime-local" name="data_saida" required value="<?php echo htmlspecialchars((string) ($editingViagem['data_saida'] ?? date('Y-m-d\TH:i')), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Data e hora de retorno</label>
                            <input type="datetime-local" name="data_retorno" value="<?php echo htmlspecialchars((string) ($editingViagem['data_retorno'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">KM inicial</label>
                            <input type="text" name="km_saida" required value="<?php echo htmlspecialchars((string) ($editingViagem['km_saida'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">KM final</label>
                            <input type="text" name="km_chegada" value="<?php echo htmlspecialchars((string) ($editingViagem['km_chegada'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['em_curso', 'concluida', 'cancelada'] as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo (($editingViagem['status'] ?? 'em_curso') === $status) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Observacoes</label>
                        <textarea name="observacoes" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[90px]"><?php echo htmlspecialchars((string) ($editingViagem['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                            <?php echo $editingViagem ? 'Salvar alteracoes' : 'Registrar viagem'; ?>
                        </button>
                        <?php if ($editingViagem): ?>
                            <a href="/viagens.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura ao modulo de viagens.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-700">Filtros</h2>
                    <p class="text-sm text-slate-500">Consulte viagens por status e secretaria solicitante.</p>
                </div>
                <form method="GET" action="/viagens.php" class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full lg:w-auto">
                    <select name="status" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os status</option>
                        <?php foreach (['em_curso', 'concluida', 'cancelada'] as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo $filtroStatus === $status ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="secretaria" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as secretarias</option>
                        <?php foreach (array_keys($secretarias) as $secretaria): ?>
                            <option value="<?php echo htmlspecialchars($secretaria, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filtroSecretaria === $secretaria ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($secretaria, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 rounded-xl bg-slate-900 px-4 py-3 text-white hover:bg-slate-800">Filtrar</button>
                        <a href="/viagens.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Historico de viagens</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Operacao</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo e motorista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trajeto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">KM e status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($viagens)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Nenhuma viagem encontrada para os filtros informados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($viagens as $viagem): ?>
                            <?php
                            $badgeClass = match ((string) $viagem['status']) {
                                'em_curso' => 'bg-blue-100 text-blue-800',
                                'concluida' => 'bg-emerald-100 text-emerald-800',
                                default => 'bg-slate-200 text-slate-700',
                            };
                            ?>
                            <tr class="hover:bg-slate-50 transition align-top">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $viagem['secretaria'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $viagem['solicitante'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars((string) $viagem['data_saida'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars((string) $viagem['placa'] . ' - ' . (string) $viagem['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $viagem['motorista_nome'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars((string) $viagem['origem'], ENT_QUOTES, 'UTF-8'); ?> -> <?php echo htmlspecialchars((string) $viagem['destino'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1 max-w-xs"><?php echo htmlspecialchars((string) $viagem['finalidade'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-700">Saida: <?php echo number_format((float) $viagem['km_saida'], 0, ',', '.'); ?> km</div>
                                    <div class="text-sm text-slate-700">Retorno: <?php echo $viagem['km_chegada'] !== null ? number_format((float) $viagem['km_chegada'], 0, ',', '.') . ' km' : 'Em aberto'; ?></div>
                                    <div class="mt-2">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $viagem['status'])), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManage): ?>
                                        <a href="/viagens.php?edit=<?php echo (int) $viagem['id']; ?>" class="text-blue-600 hover:text-blue-800">
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
