<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Application\Exceptions\VeiculoAlreadyExistsException;
use FrotaSmart\Application\Exceptions\VeiculoNotFoundException;
use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\Repositories\VeiculoRepositoryInterface;
use FrotaSmart\Domain\ValueObjects\Placa;

final class VeiculoService
{
    public function __construct(
        private readonly VeiculoRepositoryInterface $repository
    ) {
    }

    public function cadastrar(string $placa, string $modelo, string $status): Veiculo
    {
        $veiculo = new Veiculo($placa, $modelo, $status);

        if ($this->repository->existsByPlaca($veiculo->placa())) {
            throw VeiculoAlreadyExistsException::forPlaca($veiculo->placaFormatada());
        }

        $this->repository->save($veiculo);

        return $veiculo;
    }

    public function atualizar(
        string $placaAtual,
        string $novaPlaca,
        string $modelo,
        string $status
    ): Veiculo {
        $placaAtualVo = new Placa($placaAtual);
        $veiculoAtual = $this->repository->findByPlaca($placaAtualVo);

        if ($veiculoAtual === null) {
            throw VeiculoNotFoundException::forPlaca($placaAtualVo->value());
        }

        $veiculoAtualizado = new Veiculo($novaPlaca, $modelo, $status);
        $placaFoiAlterada = ! $placaAtualVo->equals($veiculoAtualizado->placa());

        if ($placaFoiAlterada && $this->repository->existsByPlaca($veiculoAtualizado->placa())) {
            throw VeiculoAlreadyExistsException::forPlaca($veiculoAtualizado->placaFormatada());
        }

        if ($placaFoiAlterada) {
            $this->repository->removeByPlaca($placaAtualVo);
        }

        $this->repository->save($veiculoAtualizado);

        return $veiculoAtualizado;
    }

    public function buscarPorPlaca(string $placa): ?Veiculo
    {
        return $this->repository->findByPlaca(new Placa($placa));
    }

    /**
     * @return list<Veiculo>
     */
    public function listarTodos(): array
    {
        return $this->repository->findAll();
    }

    public function remover(string $placa): void
    {
        $placaVo = new Placa($placa);

        if (! $this->repository->existsByPlaca($placaVo)) {
            throw VeiculoNotFoundException::forPlaca($placaVo->value());
        }

        $this->repository->removeByPlaca($placaVo);
    }
}
