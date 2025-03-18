import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";
import { ICreateLocacaoDTO } from "../dtos/ICreateLocacaoDTO";
import prisma from "../../prisma";

export class CreateLocacao {
  constructor(private locacaoRepository: ILocacaoRepository) {}

  async execute({ motoristaId, veiculoId, dataInicio, destino, dataFim, km }: ICreateLocacaoDTO): Promise<Locacao> {
    // Validar existência do motorista
    const motoristaExists = await prisma.motorista.findUnique({
      where: { id: motoristaId },
    });
    if (!motoristaExists) {
      throw new Error("Motorista não encontrado.");
    }

    // Validar existência do veículo
    const veiculoExists = await prisma.veiculo.findUnique({
      where: { id: veiculoId },
    });
    if (!veiculoExists) {
      throw new Error("Veículo não encontrado.");
    }

    // Validar dataInicio não está no passado (baseado em hoje, 17 de março de 2025)
    const today = new Date("2025-03-17"); // Simula a data atual
    if (dataInicio < today) {
      throw new Error("A data de início não pode ser no passado.");
    }

    // Validar dataFim >= dataInicio (se dataFim for fornecida)
    if (dataFim && dataFim < dataInicio) {
      throw new Error("Data fim não pode ser anterior à data início.");
    }

    // Validar km >= 0
    if (km < 0) {
      throw new Error("A quilometragem não pode ser negativa.");
    }

    // Validar destino não vazio
    if (!destino || destino.trim() === "") {
      throw new Error("O destino não pode ser vazio.");
    }

    // Buscar locações existentes para o veículo
    const existingLocacoes = await this.locacaoRepository.findByVeiculoId(veiculoId);

    // Verificar sobreposição de datas
    for (const locacao of existingLocacoes) {
      const hasDataFim = locacao.dataFim !== undefined && locacao.dataFim !== null;
      const newHasDataFim = dataFim !== undefined && dataFim !== null;

      if (!hasDataFim) {
        throw new Error("O veículo já está locado em um período ativo sem data de fim.");
      }

      if (
        dataInicio <= locacao.dataFim! &&
        (!newHasDataFim || (dataFim && dataFim >= locacao.dataInicio))
      ) {
        throw new Error("O veículo já está locado neste período.");
      }
    }

    // Criar a nova locação
    const locacao = new Locacao(motoristaId, veiculoId, dataInicio, destino, dataFim, km);
    return this.locacaoRepository.create(locacao);
  }
}