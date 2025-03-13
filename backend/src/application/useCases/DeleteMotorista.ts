import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";

export class DeleteMotorista {
  constructor(private motoristaRepository: IMotoristaRepository) {}

  async execute(id: number): Promise<void> {
    const motorista = await this.motoristaRepository.findById(id);
    if (!motorista) {
      throw new Error("Motorista não encontrado");
    }
    await this.motoristaRepository.delete(id);
  }
}