import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";

export class DeleteManutencao {
  constructor(private manutencaoRepository: IManutencaoRepository) {}

  async execute(id: number): Promise<void> {
    const manutencao = await this.manutencaoRepository.findById(id);
    if (!manutencao) {
      throw new Error("Manutenção não encontrada");
    }
    await this.manutencaoRepository.delete(id);
  }
}