import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Motorista } from "../../domain/entities/Motorista";
import { Locacao } from "../../domain/entities/Locacao";

interface DriverLocationHistory {
  motoristaId: number;
  cpf: string;
  nome: string;
  locacoes: {
    id: number;
    veiculoId: number;
    dataInicio: Date;
    dataFim?: Date;
    destino: string;
    km?: number;
  }[];
  totalLocacoes: number;
  totalKm: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
  page?: number;
  limit?: number;
  sort?: "totalLocacoes" | "totalKm" | "nome"; // Campos para ordenação
  order?: "asc" | "desc"; // Direção da ordenação
}

interface PaginatedDriverLocationHistory {
  data: DriverLocationHistory[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export class GenerateDriverLocationHistory {
  constructor(
    private motoristaRepository: IMotoristaRepository,
    private locacaoRepository: ILocacaoRepository
  ) {}

  async execute({ startDate, endDate, page = 1, limit = 10, sort, order = "asc" }: FilterOptions = {}): Promise<PaginatedDriverLocationHistory> {
    const motoristas = await this.motoristaRepository.list();
    const locacoes = await this.locacaoRepository.list();

    const filteredLocacoes = locacoes.filter((locacao) => {
      const dataInicio = locacao.dataInicio;
      if (startDate && dataInicio < startDate) return false;
      if (endDate && dataInicio > endDate) return false;
      return true;
    });

    let report: DriverLocationHistory[] = motoristas.map((motorista: Motorista) => {
      const motoristaLocacoes = filteredLocacoes.filter((l) => l.motoristaId === motorista.id);
      const totalKm = motoristaLocacoes.reduce((sum, l) => sum + (l.km || 0), 0);

      return {
        motoristaId: motorista.id!,
        cpf: motorista.cpf,
        nome: motorista.nome,
        locacoes: motoristaLocacoes.map((l: Locacao) => ({
          id: l.id!,
          veiculoId: l.veiculoId,
          dataInicio: l.dataInicio,
          dataFim: l.dataFim,
          destino: l.destino,
          km: l.km,
        })),
        totalLocacoes: motoristaLocacoes.length,
        totalKm,
      };
    });

    // Aplicar ordenação
    if (sort) {
      report.sort((a, b) => {
        const valueA = sort === "nome" ? a.nome : a[sort];
        const valueB = sort === "nome" ? b.nome : b[sort];
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

    return {
      data: paginatedData,
      total,
      page,
      limit,
      totalPages,
    };
  }
}