import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";
import { ICreateLocacaoDTO } from "../dtos/ICreateLocacaoDTO";

export class CreateLocacao {
  constructor(private locacaoRepository: ILocacaoRepository) {}

  async execute({ veiculoId, motoristaId, dataInicio, destino, dataFim, km }: ICreateLocacaoDTO): Promise<Locacao> {
    const locacao = new Locacao(veiculoId, motoristaId, dataInicio, destino, dataFim, km);
    return this.locacaoRepository.create(locacao);
  }
}