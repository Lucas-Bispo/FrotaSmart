import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";
import { IUpdateLocacaoDTO } from "../dtos/IUpdateLocacaoDTO";

export class UpdateLocacao {
  constructor(private locacaoRepository: ILocacaoRepository) {}

  async execute(id: number, data: IUpdateLocacaoDTO): Promise<Locacao> {
    const locacao = await this.locacaoRepository.findById(id);
    if (!locacao) {
      throw new Error("Locação não encontrada");
    }

    // Preparar os dados atualizados
    const updatedData: Partial<Locacao> = {};
    if (data.veiculoId !== undefined) updatedData.veiculoId = data.veiculoId;
    if (data.motoristaId !== undefined) updatedData.motoristaId = data.motoristaId;
    if (data.dataInicio !== undefined) updatedData.dataInicio = data.dataInicio;
    if (data.dataFim !== undefined) updatedData.dataFim = data.dataFim;
    if (data.destino !== undefined) updatedData.destino = data.destino;
    if (data.km !== undefined) updatedData.km = data.km;

    // Verificar sobreposição apenas se veiculoId, dataInicio ou dataFim forem alterados
    if (data.veiculoId !== undefined || data.dataInicio !== undefined || data.dataFim !== undefined) {
      const veiculoIdToCheck = data.veiculoId ?? locacao.veiculoId;
      const dataInicioToCheck = data.dataInicio ?? locacao.dataInicio;
      const dataFimToCheck = data.dataFim ?? locacao.dataFim;

      const existingLocacoes = await this.locacaoRepository.findByVeiculoId(veiculoIdToCheck);
      for (const existing of existingLocacoes) {
        // Ignorar a locação atual
        if (existing.id === id) continue;

        const hasDataFim = existing.dataFim !== undefined && existing.dataFim !== null;
        const newHasDataFim = dataFimToCheck !== undefined && dataFimToCheck !== null;

        if (!hasDataFim) {
          throw new Error("O veículo já está locado em um período ativo sem data de fim.");
        }

        if (
          dataInicioToCheck <= existing.dataFim! &&
          (!newHasDataFim || (dataFimToCheck && dataFimToCheck >= existing.dataInicio))
        ) {
          throw new Error("O veículo já está locado neste período.");
        }
      }
    }

    // Atualizar a locação
    return this.locacaoRepository.update(id, updatedData);
  }
}