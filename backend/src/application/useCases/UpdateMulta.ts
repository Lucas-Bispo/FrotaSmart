import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Multa } from "../../domain/entities/Multa";

interface IUpdateMultaDTO {
  veiculoId?: number;
  motoristaId?: number;
  data?: Date;
  tipo?: string;
  valor?: number;
  descricao?: string;
}

export class UpdateMulta {
  constructor(private multaRepository: IMultaRepository) {}

  async execute(id: number, data: IUpdateMultaDTO): Promise<Multa> {
    const multa = await this.multaRepository.findById(id);
    if (!multa) {
      throw new Error("Multa não encontrada");
    }
    return this.multaRepository.update(id, data);
  }
}