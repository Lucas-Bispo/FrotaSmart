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
  page?: number;  // Número da página (começa em 1)
  limit?: number; // Quantidade de veículos por página
}

interface PaginatedVehicleCostReport {
  data: VehicleCostReport[];
  total: number;       // Total de veículos
  page: number;        // Página atual
  limit: number;       // Itens por página
  totalPages: number;  // Total de páginas
}

export class GenerateVehicleCostReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
    private manutencaoRepository: IManutencaoRepository,
    private multaRepository: IMultaRepository
  ) {}

  async execute({ page = 1, limit = 10 }: FilterOptions = {}): Promise<PaginatedVehicleCostReport> {
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