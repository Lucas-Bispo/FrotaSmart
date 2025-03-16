import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Veiculo } from "../../domain/entities/Veiculo";
import { Locacao } from "../../domain/entities/Locacao";

interface VehicleKmReport {
  veiculoId: number;
  placa: string;
  tipo: string;
  locacoes: {
    id: number;
    motoristaId: number;
    dataInicio: Date;
    dataFim?: Date;
    destino: string;
    km?: number;
  }[];
  totalKm: number;
  totalLocacoes: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
}

export class GenerateVehicleKmReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
    private locacaoRepository: ILocacaoRepository
  ) {}

  async execute({ startDate, endDate }: FilterOptions = {}): Promise<VehicleKmReport[]> {
    const veiculos = await this.veiculoRepository.list();
    const locacoes = await this.locacaoRepository.list();

    const filteredLocacoes = locacoes.filter((locacao) => {
      const dataInicio = locacao.dataInicio;
      if (startDate && dataInicio < startDate) return false;
      if (endDate && dataInicio > endDate) return false;
      return true;
    });

    const report: VehicleKmReport[] = veiculos.map((veiculo: Veiculo) => {
      const veiculoLocacoes = filteredLocacoes.filter((l) => l.veiculoId === veiculo.id);
      const totalKm = veiculoLocacoes.reduce((sum, l) => sum + (l.km || 0), 0);

      return {
        veiculoId: veiculo.id!,
        placa: veiculo.placa,
        tipo: veiculo.tipo,
        locacoes: veiculoLocacoes.map((l: Locacao) => ({
          id: l.id!,
          motoristaId: l.motoristaId,
          dataInicio: l.dataInicio,
          dataFim: l.dataFim,
          destino: l.destino,
          km: l.km,
        })),
        totalKm,
        totalLocacoes: veiculoLocacoes.length,
      };
    });

    return report;
  }
}