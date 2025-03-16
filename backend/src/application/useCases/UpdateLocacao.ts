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

    // Criar um objeto com os campos a serem atualizados
    const updatedData: Partial<Locacao> = {};
    if (data.veiculoId !== undefined) updatedData.veiculoId = data.veiculoId;
    if (data.motoristaId !== undefined) updatedData.motoristaId = data.motoristaId;
    if (data.dataInicio !== undefined) updatedData.dataInicio = data.dataInicio;
    if (data.dataFim !== undefined) updatedData.dataFim = data.dataFim;
    if (data.destino !== undefined) updatedData.destino = data.destino;
    if (data.km !== undefined) updatedData.km = data.km;

    // Passar o id e os dados atualizados para o repositório
    return this.locacaoRepository.update(id, updatedData);
  }
}