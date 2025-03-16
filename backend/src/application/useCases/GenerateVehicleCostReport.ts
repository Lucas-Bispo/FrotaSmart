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

interface FilterOptions {
  page?: number;
  limit?: number;
  sort?: "totalGeral" | "totalManutencao" | "totalMultas" | "placa"; // Campos para ordenação
  order?: "asc" | "desc"; // Direção da ordenação
}

interface PaginatedVehicleCostReport {
  data: VehicleCostReport[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export class GenerateVehicleCostReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
    private manutencaoRepository: IManutencaoRepository,
    private multaRepository: IMultaRepository
  ) {}

  async execute({ page = 1, limit = 10, sort, order = "asc" }: FilterOptions = {}): Promise<PaginatedVehicleCostReport> {
    const veiculos = await this.veiculoRepository.list();
    const manutencoes = await this.manutencaoRepository.list();
    const multas = await this.multaRepository.list();

    let report: VehicleCostReport[] = veiculos.map((veiculo: Veiculo) => {
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

    // Aplicar ordenação
    if (sort) {
      report.sort((a, b) => {
        const valueA = sort === "placa" ? a.placa : a[sort];
        const valueB = sort === "placa" ? b.placa : b[sort];
        if (order === "asc") {
          return valueA > valueB ? 1 : -1;
        } else {
          return valueA < valueB ? 1 : -1;
        }
      });
    }

    const total = veiculos.length;
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedData = report.slice(startIndex, endIndex);
    const totalPages = Math.ceil(total / limit);

    return {
      data: paginatedData,
      total,
      page,
      limit,
      totalPages,
    };
  }
}