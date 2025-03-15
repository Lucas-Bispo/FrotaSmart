import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Veiculo } from "../../domain/entities/Veiculo";

interface VehicleCostReport {
  veiculoId: number;
  placa: string;
  totalManutencao: number;
  totalMultas: number;
  totalGeral: number;
}

export class GenerateVehicleCostReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
    private manutencaoRepository: IManutencaoRepository,
    private multaRepository: IMultaRepository
  ) {}

  async execute(): Promise<VehicleCostReport[]> {
    const veiculos = await this.veiculoRepository.list();
    const manutencoes = await this.manutencaoRepository.list();
    const multas = await this.multaRepository.list();

    const report: VehicleCostReport[] = veiculos.map((veiculo: Veiculo) => {
      const manutencaoCosts = manutencoes
        .filter((m) => m.veiculoId === veiculo.id)
        .reduce((sum, m) => sum + (m.custo || 0), 0);

      const multaCosts = multas
        .filter((m) => m.veiculoId === veiculo.id)
        .reduce((sum, m) => sum + m.valor, 0);

      return {
        veiculoId: veiculo.id!,
        placa: veiculo.placa,
        totalManutencao: manutencaoCosts,
        totalMultas: multaCosts,
        totalGeral: manutencaoCosts + multaCosts,
      };
    });

    return report;
  }
}