import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";

interface IUpdateLocacaoDTO {
  veiculoId?: number;
  motoristaId?: number;
  dataInicio?: Date;
  dataFim?: Date;
  destino?: string;
  km?: number;
}

export class UpdateLocacao {
  constructor(private locacaoRepository: ILocacaoRepository) {}

  async execute(id: number, data: IUpdateLocacaoDTO): Promise<Locacao> {
    const locacao = await this.locacaoRepository.findById(id);
    if (!locacao) {
      throw new Error("Locação não encontrada");
    }
    return this.locacaoRepository.update(id, data);
  }
}