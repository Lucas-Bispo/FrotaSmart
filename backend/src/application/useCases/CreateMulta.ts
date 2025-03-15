import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Multa } from "../../domain/entities/Multa";
import { ICreateMultaDTO } from "../dtos/ICreateMultaDTO";

export class CreateMulta {
  constructor(private multaRepository: IMultaRepository) {}

  async execute({ veiculoId, motoristaId, data, tipo, valor, descricao }: ICreateMultaDTO): Promise<Multa> {
    const multa = new Multa(veiculoId, motoristaId, data, tipo, valor, descricao);
    return this.multaRepository.create(multa);
  }
}