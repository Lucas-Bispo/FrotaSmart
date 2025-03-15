import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";

export class ListLocacoes {
  constructor(private locacaoRepository: ILocacaoRepository) {}

  async execute(): Promise<Locacao[]> {
    return this.locacaoRepository.list();
  }
}