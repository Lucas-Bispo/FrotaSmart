import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";
import { ICreateLocacaoDTO } from "../dtos/ICreateLocacaoDTO";

export class CreateLocacao {
  constructor(private locacaoRepository: ILocacaoRepository) {}

  async execute({ motoristaId, veiculoId, dataInicio, destino, dataFim, km }: ICreateLocacaoDTO): Promise<Locacao> {
    // Buscar locações existentes para o veículo
    const existingLocacoes = await this.locacaoRepository.findByVeiculoId(veiculoId);

    // Verificar sobreposição de datas
    for (const locacao of existingLocacoes) {
      const hasDataFim = locacao.dataFim !== undefined && locacao.dataFim !== null;
      const newHasDataFim = dataFim !== undefined && dataFim !== null;

      // Se a locação existente não tem dataFim, ela está ativa e conflita com qualquer nova locação
      if (!hasDataFim) {
        throw new Error("O veículo já está locado em um período ativo sem data de fim.");
      }

      // Verificar sobreposição: dataInicio <= existing.dataFim && dataFim >= existing.dataInicio
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