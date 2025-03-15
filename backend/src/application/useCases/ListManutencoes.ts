import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { Manutencao } from "../../domain/entities/Manutencao";

export class ListManutencoes {
  constructor(private manutencaoRepository: IManutencaoRepository) {}

  async execute(): Promise<Manutencao[]> {
    return this.manutencaoRepository.list();
  }
}