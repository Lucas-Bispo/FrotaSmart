<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/../config/security.php';

use FrotaSmart\Application\Exceptions\ApplicationException;
use FrotaSmart\Application\Services\AuditTrailService;
use FrotaSmart\Application\Services\VeiculoService;
use FrotaSmart\Domain\Exceptions\DomainException;
use FrotaSmart\Infrastructure\Audit\ErrorLogAuditLogger;
use FrotaSmart\Infrastructure\Audit\RequestAuditContextProvider;
use FrotaSmart\Infrastructure\Config\PdoConnectionFactory;
use FrotaSmart\Infrastructure\Persistence\PdoVeiculoRepository;

final class VeiculoController
{
    public function __construct(
        private readonly VeiculoService $service,
        private readonly AuditTrailService $auditTrail
    ) {
    }

    public static function fromGlobals(): self
    {
        secure_session_start();
        require_same_origin_post();
        self::assertAuthenticated();

        return new self(
            new VeiculoService(
                new PdoVeiculoRepository(
                    PdoConnectionFactory::make()
                )
            ),
            new AuditTrailService(
                new ErrorLogAuditLogger(),
                new RequestAuditContextProvider()
            )
        );
    }

    public function handle(?string $action): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || $action === null) {
            return;
        }

        $this->ensureManagementAccess();

        if (! verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $this->flashAndRedirect('error', 'Requisicao invalida. Atualize a pagina e tente novamente.');
        }

        try {
            $result = match ($action) {
                'add_veiculo' => $this->processAdd($_POST),
                'update_veiculo' => $this->processUpdate($_POST),
                'delete_veiculo' => $this->processDelete($_POST),
                default => ['level' => 'error', 'message' => 'Acao de veiculo nao suportada.'],
            };

            $this->flashAndRedirect($result['level'], $result['message']);
        } catch (ApplicationException | DomainException $exception) {
            $this->flashAndRedirect('error', $exception->getMessage());
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->flashAndRedirect('error', 'A placa informada ja esta cadastrada.');
            }

            error_log('Erro ao processar veiculo: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao processar o veiculo.');
        } catch (Throwable $throwable) {
            error_log('Erro inesperado no fluxo de veiculos: ' . $throwable->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao processar o veiculo.');
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{level:string,message:string}
     */
    public function processAdd(array $input): array
    {
        $veiculo = $this->service->cadastrar(
            (string) ($input['placa'] ?? ''),
            (string) ($input['modelo'] ?? ''),
            (string) ($input['status'] ?? '')
        );

        $this->auditTrail->recordMutation(
            'veiculo.created',
            'create',
            'veiculo',
            $veiculo->placaFormatada(),
            [
                'placa' => $veiculo->placaFormatada(),
                'status' => $veiculo->status(),
            ]
        );

        return [
            'level' => 'success',
            'message' => 'Veiculo adicionado com sucesso.',
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{level:string,message:string}
     */
    public function processUpdate(array $input): array
    {
        $placaAtual = (string) ($input['placa_atual'] ?? $input['placa_original'] ?? $input['placa'] ?? '');
        $veiculo = $this->service->atualizar(
            $placaAtual,
            (string) ($input['placa'] ?? ''),
            (string) ($input['modelo'] ?? ''),
            (string) ($input['status'] ?? '')
        );

        $this->auditTrail->recordMutation(
            'veiculo.updated',
            'update',
            'veiculo',
            $veiculo->placaFormatada(),
            [
                'placa_anterior' => $placaAtual,
                'placa' => $veiculo->placaFormatada(),
                'status' => $veiculo->status(),
            ]
        );

        return [
            'level' => 'success',
            'message' => 'Veiculo atualizado com sucesso.',
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{level:string,message:string}
     */
    public function processDelete(array $input): array
    {
        $placa = (string) ($input['placa'] ?? '');
        $veiculo = $this->service->buscarPorPlaca($placa);

        if ($veiculo === null) {
            throw new DomainException('Veiculo nao encontrado para remocao.');
        }

        $this->service->remover($placa);

        $this->auditTrail->recordMutation(
            'veiculo.deleted',
            'delete',
            'veiculo',
            $veiculo->placaFormatada(),
            [
                'placa' => $veiculo->placaFormatada(),
                'status' => $veiculo->status(),
            ]
        );

        return [
            'level' => 'success',
            'message' => 'Veiculo excluido com sucesso.',
        ];
    }

    private static function assertAuthenticated(): void
    {
        if (! isset($_SESSION['user'])) {
            header('Location: /login.php');
            exit;
        }
    }

    private function ensureManagementAccess(): void
    {
        $allowedRoles = ['admin', 'gerente'];

        if (! in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
            $this->flashAndRedirect('error', 'Voce nao tem permissao para alterar a frota.');
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /dashboard.php');
        exit;
    }
}
