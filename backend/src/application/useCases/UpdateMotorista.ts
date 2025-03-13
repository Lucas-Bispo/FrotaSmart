import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { Motorista } from "../../domain/entities/Motorista";
import { IUpdateMotoristaDTO } from "../dtos/IUpdateMotoristaDTO";

export class UpdateMotorista {
  constructor(private motoristaRepository: IMotoristaRepository) {}

  async execute(id: number, data: IUpdateMotoristaDTO): Promise<Motorista> {
    const motorista = await this.motoristaRepository.findById(id);
    if (!motorista) {
      throw new Error("Motorista não encontrado");
    }
    return this.motoristaRepository.update(id, data);
  }
}