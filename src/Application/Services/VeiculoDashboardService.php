<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\Repositories\VeiculoRepositoryInterface;

final class VeiculoDashboardService
{
    public function __construct(
        private readonly VeiculoRepositoryInterface $repository
    ) {
    }

    /**
     * @return list<array<string, int|string|null>>
     */
    public function listarPorFiltro(string $filtro = 'ativos'): array
    {
        $ativos = $this->repository->findAll();
        $arquivados = $this->repository->findArchived();

        $veiculos = match ($filtro) {
            'arquivados' => $arquivados,
            'todos' => array_merge($ativos, $arquivados),
            default => $ativos,
        };

        usort($veiculos, [$this, 'compareVeiculos']);

        return array_map(
            fn (Veiculo $veiculo): array => $this->toDashboardRow($veiculo),
            $veiculos
        );
    }

    public function contarArquivados(): int
    {
        return count($this->repository->findArchived());
    }

    private function compareVeiculos(Veiculo $left, Veiculo $right): int
    {
        $leftArchived = $left->estaArquivado();
        $rightArchived = $right->estaArquivado();

        if ($leftArchived !== $rightArchived) {
            return $leftArchived ? 1 : -1;
        }

        if ($leftArchived && $rightArchived) {
            $archivedComparison = strcmp((string) $right->arquivadoEm(), (string) $left->arquivadoEm());
            if ($archivedComparison !== 0) {
                return $archivedComparison;
            }
        }

        $statusComparison = $this->statusRank($left->status()) <=> $this->statusRank($right->status());
        if ($statusComparison !== 0) {
            return $statusComparison;
        }

        $secretariaComparison = strcasecmp((string) $left->secretariaLotada(), (string) $right->secretariaLotada());
        if ($secretariaComparison !== 0) {
            return $secretariaComparison;
        }

        return strcmp($left->placaFormatada(), $right->placaFormatada());
    }

    private function statusRank(string $status): int
    {
        return match ($status) {
            'disponivel', 'em_viagem', 'reservado' => 0,
            'em_manutencao' => 1,
            default => 2,
        };
    }

    /**
     * @return array<string, int|string|null>
     */
    private function toDashboardRow(Veiculo $veiculo): array
    {
        $detalhes = $veiculo->detalhesCadastro();

        return [
            'placa' => $veiculo->placaFormatada(),
            'modelo' => $veiculo->modelo(),
            'status' => $veiculo->status(),
            'renavam' => $veiculo->renavam(),
            'chassi' => $veiculo->chassi(),
            'ano_fabricacao' => $veiculo->anoFabricacao(),
            'tipo' => $veiculo->tipo(),
            'combustivel' => $veiculo->combustivel(),
            'secretaria_lotada' => $veiculo->secretariaLotada(),
            'quilometragem_inicial' => $veiculo->quilometragemInicial(),
            'data_aquisicao' => $veiculo->dataAquisicao(),
            'documentos_observacoes' => $veiculo->documentosObservacoes(),
            'deleted_at' => $detalhes['deleted_at'] ?? null,
        ];
    }
}
