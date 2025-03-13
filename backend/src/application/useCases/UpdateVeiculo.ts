import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { Veiculo } from "../../domain/entities/Veiculo";
import { IUpdateVeiculoDTO } from "../dtos/IUpdateVeiculoDTO";

export class UpdateVeiculo {
  constructor(private veiculoRepository: IVeiculoRepository) {}

  async execute(id: number, data: IUpdateVeiculoDTO): Promise<Veiculo> {
    const veiculo = await this.veiculoRepository.findById(id);
    if (!veiculo) {
      throw new Error("Veículo não encontrado");
    }
    return this.veiculoRepository.update(id, data);
  }
}