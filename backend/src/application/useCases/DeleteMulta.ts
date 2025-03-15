import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";

export class DeleteMulta {
  constructor(private multaRepository: IMultaRepository) {}

  async execute(id: number): Promise<void> {
    const multa = await this.multaRepository.findById(id);
    if (!multa) {
      throw new Error("Multa não encontrada");
    }
    await this.multaRepository.delete(id);
  }
}