import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Motorista } from "../../domain/entities/Motorista";

interface DriverLocationHistoryReport {
  motoristaId: number;
  nome: string;
  locacoes: {
    id: number;
    veiculoId: number;
    placa: string;
    dataInicio: Date;
    dataFim: Date | null;
    km: number;
  }[];
  totalLocacoes: number;
  totalKm: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
  page?: number;
  limit?: number;
  sort?: "totalKm" | "totalLocacoes" | "nome";
  order?: "asc" | "desc";
  exportFormat?: "csv";
}

interface PaginatedDriverLocationHistoryReport {
  data: DriverLocationHistoryReport[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export class GenerateDriverLocationHistoryReport {
  constructor(
    private motoristaRepository: IMotoristaRepository,
    private locacaoRepository: ILocacaoRepository
  ) {}

  async execute({
    startDate,
    endDate,
    page = 1,
    limit = 10,
    sort,
    order = "asc",
    exportFormat,
  }: FilterOptions = {}): Promise<PaginatedDriverLocationHistoryReport | string> {
    const motoristas = await this.motoristaRepository.list();
    const locacoes = await this.locacaoRepository.list();

    const filteredLocacoes = locacoes.filter((locacao) => {
      const dataInicio = locacao.dataInicio;
      if (startDate && dataInicio < startDate) return false;
      if (endDate && dataInicio > endDate) return false;
      return true;
    });

    let report: DriverLocationHistoryReport[] = motoristas.map((motorista: Motorista) => {
      const motoristaLocacoes = filteredLocacoes.filter((l) => l.motoristaId === motorista.id);
      const totalKm = motoristaLocacoes.reduce((sum, l) => sum + (l.km || 0), 0);

      return {
        motoristaId: motorista.id!,
        nome: motorista.nome,
        locacoes: motoristaLocacoes.map((l) => ({
          id: l.id!,
          veiculoId: l.veiculoId,
          placa: l.veiculo?.placa || "N/A", // Assume relação com Veiculo; ajuste se necessário
          dataInicio: l.dataInicio,
          dataFim: l.dataFim,
          km: l.km || 0,
        })),
        totalLocacoes: motoristaLocacoes.length,
        totalKm,
      };
    });

    // Aplicar ordenação
    if (sort) {
      report.sort((a, b) => {
        const valueA = a[sort];
        const valueB = b[sort];
        if (order === "asc") {
          return valueA > valueB ? 1 : -1;
        } else {
          return valueA < valueB ? 1 : -1;
        }
      });
    }

    const total = motoristas.length;
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedData = report.slice(startIndex, endIndex);
    const totalPages = Math.ceil(total / limit);

    // Exportação para CSV
    if (exportFormat === "csv") {
      const headers = ["Motorista ID", "Nome", "Total Locações", "Total KM"];
      const rows = report.map((item) =>
        [item.motoristaId, item.nome, item.totalLocacoes, item.totalKm].join(",")
      );
      const csv = [headers.join(","), ...rows].join("\n");
      return csv;
    }

    return {
      data: paginatedData,
      total,
      page,
      limit,
      totalPages,
    };
  }
}