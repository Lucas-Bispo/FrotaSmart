import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";

export class DeleteVeiculo {
  constructor(private veiculoRepository: IVeiculoRepository) {}

  async execute(id: number): Promise<void> {
    const veiculo = await this.veiculoRepository.findById(id);
    if (!veiculo) {
      throw new Error("Veículo não encontrado");
    }
    await this.veiculoRepository.delete(id);
  }
}