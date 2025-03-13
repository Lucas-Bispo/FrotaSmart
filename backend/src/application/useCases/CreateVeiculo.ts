import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { Veiculo } from "../../domain/entities/Veiculo";
import { ICreateVeiculoDTO } from "../dtos/ICreateVeiculoDTO";

export class CreateVeiculo {
  constructor(private veiculoRepository: IVeiculoRepository) {}

  async execute({ placa, tipo, secretariaId }: ICreateVeiculoDTO): Promise<Veiculo> {
    const veiculoExists = await this.veiculoRepository.findByPlaca(placa);
    if (veiculoExists) {
      throw new Error("Veículo já existe");
    }
    const veiculo = new Veiculo(placa, tipo, secretariaId);
    return this.veiculoRepository.create(veiculo);
  }
}