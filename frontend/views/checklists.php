<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/ChecklistOperacionalModel.php';
require_once __DIR__ . '/../../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../../backend/models/VeiculoModel.php';
require_once __DIR__ . '/../../backend/models/ViagemModel.php';

secure_session_start();

if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de checklists operacionais.');
    header('Location: /login.php');
    exit;
}

$connection = \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make();
$checklistModel = new ChecklistOperacionalModel($connection);
$veiculoModel = new VeiculoModel($connection);
$motoristaModel = new MotoristaModel($connection);
$viagemModel = new ViagemModel($connection);

$filtroTipo = trim((string) ($_GET['tipo'] ?? ''));
$filtroStatus = trim((string) ($_GET['status'] ?? ''));
$filtroSecretaria = trim((string) ($_GET['secretaria'] ?? ''));

$checklists = $checklistModel->listByFilters([
    'tipo' => $filtroTipo !== '' ? $filtroTipo : null,
    'status' => $filtroStatus !== '' ? $filtroStatus : null,
    'secretaria' => $filtroSecretaria !== '' ? $filtroSecretaria : null,
]);
$veiculos = $veiculoModel->getAllVeiculos();
$motoristas = $motoristaModel->getAllMotoristas();
$viagens = $viagemModel->listByFilters();
$canManage = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE);
$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingChecklist = $editingId > 0 && $canManage ? $checklistModel->findById($editingId) : null;
/** @var array<string, string> $checklistItemLabels */
$checklistItemLabels = [
    'documentacao' => 'Documentacao obrigatoria',
    'pneus' => 'Pneus e rodas',
    'iluminacao' => 'Iluminacao e sinalizacao',
    'equipamentos' => 'Equipamentos obrigatorios',
    'limpeza' => 'Condicoes gerais e limpeza',
];

$totalConformes = 0;
$totalNaoConformes = 0;
$totalPendentes = 0;
$secretarias = [];

foreach ($checklists as $checklist) {
    $status = (string) ($checklist['status_conformidade'] ?? '');

    if ($status === 'conforme') {
        $totalConformes++;
    } elseif ($status === 'nao_conforme') {
        $totalNaoConformes++;
    } else {
        $totalPendentes++;
    }

    $secretaria = trim((string) ($checklist['secretaria'] ?? ''));
    if ($secretaria !== '') {
        $secretarias[$secretaria] = true;
    }
}

ksort($secretarias);

$editingRealizadoEm = '';
if (is_array($editingChecklist) && ! empty($editingChecklist['realizado_em'])) {
    $editingDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $editingChecklist['realizado_em']);
    if ($editingDate instanceof DateTimeImmutable) {
        $editingRealizadoEm = $editingDate->format('Y-m-d\TH:i');
    }
}

$editingItems = [];
if (is_array($editingChecklist) && ! empty($editingChecklist['itens_json'])) {
    $decodedItems = json_decode((string) $editingChecklist['itens_json'], true);
    if (is_array($decodedItems)) {
        foreach ($decodedItems as $item) {
            $code = (string) ($item['codigo'] ?? '');
            if ($code === '') {
                continue;
            }

            $editingItems[$code] = [
                'checked' => ! empty($item['checked']),
                'observacao' => (string) ($item['observacao'] ?? ''),
            ];
        }
    }
}

$editingEvidenceText = '';
if (is_array($editingChecklist) && ! empty($editingChecklist['evidencias_json'])) {
    $decodedEvidence = json_decode((string) $editingChecklist['evidencias_json'], true);
    if (is_array($decodedEvidence)) {
        $evidenceLines = [];
        foreach ($decodedEvidence as $entry) {
            $reference = trim((string) ($entry['referencia'] ?? ''));
            if ($reference !== '') {
                $evidenceLines[] = $reference;
            }
        }

        $editingEvidenceText = implode("\n", $evidenceLines);
    }
}

if ($editingEvidenceText === '' && is_array($editingChecklist) && ! empty($editingChecklist['evidencia_referencia'])) {
    $editingEvidenceText = (string) $editingChecklist['evidencia_referencia'];
}

$pageTitle = 'Checklists Operacionais';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Checklists Operacionais</h1>
        <p class="text-slate-500 text-sm">Inspecao de saida e retorno com rastreabilidade de responsavel, conformidade e evidencia.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Perfil atual: <?php echo htmlspecialchars((string) $_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-xs text-slate-500">Use o modulo para registrar nao conformidades antes e depois da operacao.</span>
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
        <p class="text-sm font-medium text-slate-500 uppercase">Checklists filtrados</p>
        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo count($checklists); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Conformes</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2"><?php echo $totalConformes; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Nao conformes</p>
        <p class="text-3xl font-bold text-rose-600 mt-2"><?php echo $totalNaoConformes; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Pendentes</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo $totalPendentes; ?></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1 space-y-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">
                <?php echo $editingChecklist ? 'Editar checklist' : 'Novo checklist'; ?>
            </h2>
            <p class="text-sm text-slate-500 mb-5">Registre a vistoria da operacao com aceite, nao conformidades e referencia de evidencia.</p>

            <?php if (empty($veiculos) || empty($motoristas)): ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Cadastre pelo menos um veiculo e um motorista antes de registrar checklists.
                </div>
            <?php elseif ($canManage): ?>
                <form method="POST" action="/checklists.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editingChecklist ? 'update_checklist' : 'add_checklist'; ?>">
                    <?php if ($editingChecklist): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $editingChecklist['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                            <select name="tipo" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['saida', 'retorno'] as $tipo): ?>
                                    <option value="<?php echo $tipo; ?>" <?php echo (($editingChecklist['tipo'] ?? 'saida') === $tipo) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($tipo), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status_conformidade" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['conforme', 'nao_conforme', 'pendente'] as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo (($editingChecklist['status_conformidade'] ?? 'conforme') === $status) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Secretaria</label>
                        <input type="text" name="secretaria" required value="<?php echo htmlspecialchars((string) ($editingChecklist['secretaria'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Responsavel pela operacao</label>
                        <input type="text" name="responsavel_operacao" required value="<?php echo htmlspecialchars((string) ($editingChecklist['responsavel_operacao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Veiculo</label>
                        <select name="veiculo_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione um veiculo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?php echo (int) $veiculo['id']; ?>" <?php echo ((int) ($editingChecklist['veiculo_id'] ?? 0) === (int) $veiculo['id']) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo (int) $motorista['id']; ?>" <?php echo ((int) ($editingChecklist['motorista_id'] ?? 0) === (int) $motorista['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $motorista['nome'] . ' - ' . (string) $motorista['secretaria'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Viagem vinculada</label>
                        <select name="viagem_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Sem vinculo direto com viagem</option>
                            <?php foreach (array_slice($viagens, 0, 50) as $viagem): ?>
                                <option value="<?php echo (int) $viagem['id']; ?>" <?php echo ((int) ($editingChecklist['viagem_id'] ?? 0) === (int) $viagem['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars('#' . (string) $viagem['id'] . ' - ' . (string) $viagem['placa'] . ' - ' . (string) $viagem['destino'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Data e hora da vistoria</label>
                        <input type="datetime-local" name="realizado_em" required value="<?php echo htmlspecialchars($editingRealizadoEm !== '' ? $editingRealizadoEm : date('Y-m-d\TH:i'), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700">Itens da inspecao</h3>
                            <p class="text-xs text-slate-500">Marque os itens verificados e registre observacoes quando necessario.</p>
                        </div>
                        <?php foreach ($checklistItemLabels as $code => $label): ?>
                            <?php $itemState = $editingItems[$code] ?? ['checked' => false, 'observacao' => '']; ?>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                                    <input type="checkbox" name="itens[]" value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" <?php echo ! empty($itemState['checked']) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                </label>
                                <input type="text" name="item_observacoes[<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>]" value="<?php echo htmlspecialchars((string) ($itemState['observacao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Observacao opcional do item" class="mt-3 w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nao conformidades</label>
                        <textarea name="nao_conformidades" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[90px]"><?php echo htmlspecialchars((string) ($editingChecklist['nao_conformidades'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Evidencias da vistoria</label>
                        <textarea name="evidencias" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[90px]" placeholder="Uma referencia por linha: link, protocolo, nome de arquivo ou observacao de evidencia"><?php echo htmlspecialchars($editingEvidenceText, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Observacoes</label>
                        <textarea name="observacoes" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[90px]"><?php echo htmlspecialchars((string) ($editingChecklist['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                        <input type="checkbox" name="aceite_responsavel" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" <?php echo ! empty($editingChecklist['aceite_responsavel']) ? 'checked' : ''; ?>>
                        Aceite do responsavel pela operacao registrado
                    </label>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                            <?php echo $editingChecklist ? 'Salvar alteracoes' : 'Registrar checklist'; ?>
                        </button>
                        <?php if ($editingChecklist): ?>
                            <a href="/checklists.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura ao modulo de checklists operacionais.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-700">Filtros</h2>
                    <p class="text-sm text-slate-500">Consulte checklists por tipo, status e secretaria.</p>
                </div>
                <form method="GET" action="/checklists.php" class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full lg:w-auto">
                    <select name="tipo" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os tipos</option>
                        <?php foreach (['saida', 'retorno'] as $tipo): ?>
                            <option value="<?php echo $tipo; ?>" <?php echo $filtroTipo === $tipo ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($tipo), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os status</option>
                        <?php foreach (['conforme', 'nao_conforme', 'pendente'] as $status): ?>
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
                        <a href="/checklists.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Historico de checklists</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Operacao</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo e motorista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Conformidade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Evidencia e aceite</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($checklists)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum checklist encontrado para os filtros informados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($checklists as $checklist): ?>
                            <?php
                            $badgeClass = match ((string) $checklist['status_conformidade']) {
                                'conforme' => 'bg-emerald-100 text-emerald-800',
                                'nao_conforme' => 'bg-rose-100 text-rose-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                            ?>
                            <tr class="hover:bg-slate-50 transition align-top">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(ucfirst((string) $checklist['tipo']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $checklist['secretaria'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars((string) $checklist['realizado_em'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars((string) $checklist['placa'] . ' - ' . (string) $checklist['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $checklist['motorista_nome'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars((string) $checklist['responsavel_operacao'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php if (! empty($checklist['viagem_destino'])): ?>
                                        <div class="text-xs text-cyan-700 mt-1">
                                            Viagem vinculada: <?php echo htmlspecialchars((string) $checklist['viagem_destino'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $checklist['status_conformidade'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <div class="text-xs text-slate-500 mt-2 max-w-xs">
                                        <?php echo htmlspecialchars((string) ($checklist['nao_conformidades'] ?? 'Sem nao conformidades registradas.'), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <?php
                                    $itensResumo = [];
                                    $decodedItems = json_decode((string) ($checklist['itens_json'] ?? '[]'), true);
                                    if (is_array($decodedItems)) {
                                        foreach ($decodedItems as $item) {
                                            if (! empty($item['checked'])) {
                                                $itensResumo[] = (string) ($item['label'] ?? $item['codigo'] ?? '');
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="text-xs text-slate-500 mt-2 max-w-xs">
                                        <?php echo htmlspecialchars($itensResumo === [] ? 'Nenhum item marcado.' : implode(' | ', array_slice($itensResumo, 0, 3)), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $evidenceSummary = [];
                                    $decodedEvidence = json_decode((string) ($checklist['evidencias_json'] ?? '[]'), true);
                                    if (is_array($decodedEvidence)) {
                                        foreach ($decodedEvidence as $entry) {
                                            $reference = trim((string) ($entry['referencia'] ?? ''));
                                            if ($reference !== '') {
                                                $evidenceSummary[] = $reference;
                                            }
                                        }
                                    }
                                    if ($evidenceSummary === [] && ! empty($checklist['evidencia_referencia'])) {
                                        $evidenceSummary[] = (string) $checklist['evidencia_referencia'];
                                    }
                                    ?>
                                    <div class="text-sm text-slate-700"><?php echo htmlspecialchars($evidenceSummary === [] ? 'Sem evidencia vinculada.' : implode(' | ', array_slice($evidenceSummary, 0, 2)), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo count($evidenceSummary); ?> evidencia(s) registrada(s)</div>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo ! empty($checklist['aceite_responsavel']) ? 'Aceite registrado' : 'Aceite pendente'; ?></div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManage): ?>
                                        <a href="/checklists.php?edit=<?php echo (int) $checklist['id']; ?>" class="text-blue-600 hover:text-blue-800">
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
