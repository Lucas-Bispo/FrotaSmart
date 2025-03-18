import { describe, it, expect, beforeEach, afterEach } from "vitest";
import { CreateLocacao } from "../src/application/useCases/CreateLocacao";
import { LocacaoRepository } from "../src/infrastructure/repositories/LocacaoRepository";
import { Locacao } from "../src/domain/entities/Locacao";
import prisma from "../src/prisma";

describe("CreateLocacao", () => {
  beforeEach(async () => {
    // Limpar dados existentes
    await prisma.locacao.deleteMany();
    await prisma.veiculo.deleteMany();
    await prisma.motorista.deleteMany();
    await prisma.secretaria.deleteMany();

    // Criar dados necessários
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
    // Limpar após cada teste
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
      dataInicio: new Date("2025-03-16"),
      destino: "São Paulo",
      dataFim: new Date("2025-03-20"),
      km: 100,
    };

    const locacao = await useCase.execute(input);

    expect(locacao).toBeInstanceOf(Locacao);
    expect(locacao.id).toBeDefined();
  });
});