import { describe, it, expect, beforeEach, afterEach } from "vitest";
import { CreateLocacao } from "../src/application/useCases/CreateLocacao";
import { LocacaoRepository } from "../src/infrastructure/repositories/LocacaoRepository";
import { Locacao } from "../src/domain/entities/Locacao";
import prisma from "../src/prisma";

describe("CreateLocacao", () => {
  beforeEach(async () => {
    await prisma.locacao.deleteMany();
    await prisma.veiculo.deleteMany();
    await prisma.motorista.deleteMany();
    await prisma.secretaria.deleteMany();

    await prisma.secretaria.create({
      data: {
        id: 1,
        nome: "Secretaria de Transporte",
      },
    });

    await prisma.veiculo.create({
      data: {
        id: 1,
        placa: "ABC-1234",
        tipo: "Carro",
        secretariaId: 1,
      },
    });

    await prisma.motorista.create({
      data: {
        id: 1,
        cpf: "12345678901",
        nome: "João",
        cnh: "123456789",
        secretariaId: 1,
      },
    });
  });

  afterEach(async () => {
    await prisma.locacao.deleteMany();
    await prisma.veiculo.deleteMany();
    await prisma.motorista.deleteMany();
    await prisma.secretaria.deleteMany();
  });

  it("should create a locacao successfully", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const input = {
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-18"),
      destino: "São Paulo",
      dataFim: new Date("2025-03-20"),
      km: 100,
    };

    const locacao = await useCase.execute(input);

    expect(locacao).toBeInstanceOf(Locacao);
    expect(locacao.id).toBeDefined();
  });

  it("should throw an error if motoristaId does not exist", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const input = {
      motoristaId: 999,
      veiculoId: 1,
      dataInicio: new Date("2025-03-18"),
      destino: "São Paulo",
      dataFim: new Date("2025-03-20"),
      km: 100,
    };

    await expect(useCase.execute(input)).rejects.toThrow("Motorista não encontrado.");
  });

  it("should throw an error if veiculoId does not exist", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const input = {
      motoristaId: 1,
      veiculoId: 999,
      dataInicio: new Date("2025-03-18"),
      destino: "São Paulo",
      dataFim: new Date("2025-03-20"),
      km: 100,
    };

    await expect(useCase.execute(input)).rejects.toThrow("Veículo não encontrado.");
  });

  it("should throw an error if dataFim is before dataInicio", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const input = {
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-20"),
      destino: "São Paulo",
      dataFim: new Date("2025-03-18"),
      km: 100,
    };

    await expect(useCase.execute(input)).rejects.toThrow("Data fim não pode ser anterior à data início.");
  });

  it("should throw an error if locacao overlaps with existing one", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);

    await useCase.execute({
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-18"),
      destino: "São Paulo",
      dataFim: new Date("2025-03-20"),
      km: 100,
    });

    const overlappingInput = {
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-19"),
      destino: "Rio de Janeiro",
      dataFim: new Date("2025-03-22"),
      km: 150,
    };

    await expect(useCase.execute(overlappingInput)).rejects.toThrow("O veículo já está locado neste período.");
  });

  it("should throw an error if vehicle is in an active locacao without dataFim", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);

    await useCase.execute({
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-18"),
      destino: "São Paulo",
      dataFim: undefined,
      km: 100,
    });

    const newInput = {
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-19"),
      destino: "Rio de Janeiro",
      dataFim: new Date("2025-03-22"),
      km: 150,
    };

    await expect(useCase.execute(newInput)).rejects.toThrow(
      "O veículo já está locado em um período ativo sem data de fim."
    );
  });

  it("should throw an error if km is negative", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const input = {
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-18"),
      destino: "São Paulo",
      dataFim: new Date("2025-03-20"),
      km: -50, // Negativo
    };

    await expect(useCase.execute(input)).rejects.toThrow("A quilometragem não pode ser negativa.");
  });

  it("should create a locacao without dataFim successfully", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const input = {
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-18"),
      destino: "São Paulo",
      dataFim: undefined,
      km: 100,
    };

    const locacao = await useCase.execute(input);

    expect(locacao).toBeInstanceOf(Locacao);
    expect(locacao.id).toBeDefined();
    expect(locacao.dataFim).toBeUndefined();
  });

  it("should throw an error if dataInicio is in the past", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const input = {
      motoristaId: 1,
      veiculoId: 1,
      dataInicio: new Date("2025-03-10"), // Antes de 17/03/2025
      destino: "São Paulo",
      dataFim: new Date("2025-03-15"),
      km: 100,
    };

    await expect(useCase.execute(input)).rejects.toThrow("A data de início não pode ser no passado.");
  });

  it("should throw an error if destino is empty or whitespace", async () => {
    const repository = new LocacaoRepository();
    const useCase = new CreateLocacao(repository);
    const inputs = [
      {
        motoristaId: 1,
        veiculoId: 1,
        dataInicio: new Date("2025-03-18"),
        destino: "", // Vazio
        dataFim: new Date("2025-03-20"),
        km: 100,
      },
      {
        motoristaId: 1,
        veiculoId: 1,
        dataInicio: new Date("2025-03-18"),
        destino: "   ", // Apenas espaços
        dataFim: new Date("2025-03-20"),
        km: 100,
      },
    ];

    for (const input of inputs) {
      await expect(useCase.execute(input)).rejects.toThrow("O destino não pode ser vazio.");
    }
  });
});