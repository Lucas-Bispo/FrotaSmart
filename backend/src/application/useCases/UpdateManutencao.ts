import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { Manutencao } from "../../domain/entities/Manutencao";

interface IUpdateManutencaoDTO {
  veiculoId?: number;
  data?: Date;
  tipo?: string;
  descricao?: string;
  custo?: number;
}

export class UpdateManutencao {
  constructor(private manutencaoRepository: IManutencaoRepository) {}

  async execute(id: number, data: IUpdateManutencaoDTO): Promise<Manutencao> {
    const manutencao = await this.manutencaoRepository.findById(id);
    if (!manutencao) {
      throw new Error("Manutenção não encontrada");
    }
    return this.manutencaoRepository.update(id, data);
  }
}