import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { Motorista } from "../../domain/entities/Motorista";

export class ListMotoristas {
  constructor(private motoristaRepository: IMotoristaRepository) {}

  async execute(): Promise<Motorista[]> {
    return this.motoristaRepository.list();
  }
}