import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { Manutencao } from "../../domain/entities/Manutencao";
import { ICreateManutencaoDTO } from "../dtos/ICreateManutencaoDTO";

export class CreateManutencao {
  constructor(private manutencaoRepository: IManutencaoRepository) {}

  async execute({ veiculoId, data, tipo, descricao, custo }: ICreateManutencaoDTO): Promise<Manutencao> {
    const manutencao = new Manutencao(veiculoId, data, tipo, descricao, custo);
    return this.manutencaoRepository.create(manutencao);
  }
}