import { describe, it, expect, beforeEach, afterEach } from "vitest";
import { UpdateLocacao } from "../src/application/useCases/UpdateLocacao";
import { LocacaoRepository } from "../src/infrastructure/repositories/LocacaoRepository";
import { Locacao } from "../src/domain/entities/Locacao";
import prisma from "../src/prisma";

describe("UpdateLocacao", () => {
  beforeEach(async () => {
    await prisma.locacao.deleteMany();
    await prisma.veiculo.deleteMany();
    await prisma.motorista.deleteMany();
    await prisma.secretaria.deleteMany();

    await prisma.secretaria.create({
      data: { id: 1, nome: "Secretaria de Transporte" },
    });

    await prisma.veiculo.create({
      data: { id: 1, placa: "ABC-1234", tipo: "Carro", secretariaId: 1 },
    });

    await prisma.veiculo.create({
      data: { id: 2, placa: "XYZ-5678", tipo: "Caminhão", secretariaId: 1 },
    });

    await prisma.motorista.create({
      data: { id: 1, cpf: "12345678901", nome: "João", cnh: "123456789", secretariaId: 1 },
    });

    await prisma.motorista.create({
      data: { id: 2, cpf: "98765432109", nome: "Maria", cnh: "987654321", secretariaId: 1 },
    });

    await prisma.locacao.create({
      data: {
        id: 1,
        motoristaId: 1,
        veiculoId: 1,
        dataInicio: new Date("2025-03-18"),
        destino: "São Paulo",
        dataFim: new Date("2025-03-20"),
        km: 100,
      },
    });
  });

  afterEach(async () => {
    await prisma.locacao.deleteMany();
    await prisma.veiculo.deleteMany();
    await prisma.motorista.deleteMany();
    await prisma.secretaria.deleteMany();
  });

  it("should update a locacao successfully", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = {
      destino: "Rio de Janeiro",
      dataFim: new Date("2025-03-21"),
      km: 150,
    };

    const locacao = await useCase.execute(1, data);

    expect(locacao).toBeInstanceOf(Locacao);
    expect(locacao.id).toBe(1);
    expect(locacao.destino).toBe("Rio de Janeiro");
    expect(locacao.dataFim).toEqual(new Date("2025-03-21"));
    expect(locacao.km).toBe(150);
  });

  it("should throw an error if locacao does not exist", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { destino: "Rio de Janeiro" };

    await expect(useCase.execute(999, data)).rejects.toThrow("Locação não encontrada.");
  });

  it("should throw an error if motoristaId does not exist", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { motoristaId: 999 };

    await expect(useCase.execute(1, data)).rejects.toThrow("Motorista não encontrado.");
  });

  it("should throw an error if veiculoId does not exist", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { veiculoId: 999 };

    await expect(useCase.execute(1, data)).rejects.toThrow("Veículo não encontrado.");
  });

  it("should throw an error if dataFim is before dataInicio", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { dataFim: new Date("2025-03-17") };

    await expect(useCase.execute(1, data)).rejects.toThrow("Data fim não pode ser anterior à data início.");
  });

  it("should throw an error if locacao overlaps with another", async () => {
    await prisma.locacao.create({
      data: {
        id: 2,
        motoristaId: 2,
        veiculoId: 1,
        dataInicio: new Date("2025-03-22"),
        destino: "Curitiba",
        dataFim: new Date("2025-03-25"),
        km: 200,
      },
    });

    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { dataFim: new Date("2025-03-23") };

    await expect(useCase.execute(1, data)).rejects.toThrow("O veículo já está locado neste período.");
  });

  it("should throw an error if km is negative", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { km: -50 };

    await expect(useCase.execute(1, data)).rejects.toThrow("A quilometragem não pode ser negativa.");
  });

  it("should throw an error if destino is empty", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { destino: "" };

    await expect(useCase.execute(1, data)).rejects.toThrow("O destino não pode ser vazio.");
  });

  it("should throw an error if dataInicio is in the past", async () => {
    const repository = new LocacaoRepository();
    const useCase = new UpdateLocacao(repository);
    const data = { dataInicio: new Date("2025-03-10") };

    await expect(useCase.execute(1, data)).rejects.toThrow("A data de início não pode ser no passado.");
  });
});