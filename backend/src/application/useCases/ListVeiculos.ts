import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { Veiculo } from "../../domain/entities/Veiculo";

export class ListVeiculos {
  constructor(private veiculoRepository: IVeiculoRepository) {}

  async execute(): Promise<Veiculo[]> {
    return this.veiculoRepository.list();
  }
}