import { describe, it, expect } from "vitest";
import { CreateLocacao } from "../src/application/useCases/CreateLocacao";
import { LocacaoRepository } from "../src/infrastructure/repositories/LocacaoRepository";
import { Locacao } from "../src/domain/entities/Locacao";

describe("CreateLocacao", () => {
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