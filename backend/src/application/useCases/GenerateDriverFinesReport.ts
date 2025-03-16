import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Motorista } from "../../domain/entities/Motorista";

interface DriverFinesReport {
  motoristaId: number;
  nome: string;
  multas: {
    id: number;
    data: Date;
    valor: number;
    descricao: string | null; // Ajustado para aceitar null
  }[];
  totalMultas: number;
  totalValor: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
  page?: number;
  limit?: number;
  sort?: "totalMultas" | "totalValor" | "nome";
  order?: "asc" | "desc";
  exportFormat?: "csv";
}

interface PaginatedDriverFinesReport {
  data: DriverFinesReport[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export class GenerateDriverFinesReport {
  constructor(
    private motoristaRepository: IMotoristaRepository,
    private multaRepository: IMultaRepository
  ) {}

  async execute({
    startDate,
    endDate,
    page = 1,
    limit = 10,
    sort,
    order = "asc",
    exportFormat,
  }: FilterOptions = {}): Promise<PaginatedDriverFinesReport | string> {
    const motoristas = await this.motoristaRepository.list();
    const multas = await this.multaRepository.list();

    const filteredMultas = multas.filter((multa) => {
      const data = multa.data;
      if (startDate && data < startDate) return false;
      if (endDate && data > endDate) return false;
      return true;
    });

    let report: DriverFinesReport[] = motoristas.map((motorista: Motorista) => {
      const motoristaMultas = filteredMultas.filter((m) => m.motoristaId === motorista.id);
      const totalValor = motoristaMultas.reduce((sum, m) => sum + m.valor, 0);

      return {
        motoristaId: motorista.id!,
        nome: motorista.nome,
        multas: motoristaMultas.map((m) => ({
          id: m.id!,
          data: m.data,
          valor: m.valor,
          descricao: m.descricao, // Agora compatível com string | null
        })),
        totalMultas: motoristaMultas.length,
        totalValor,
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
      const headers = ["Motorista ID", "Nome", "Total Multas", "Total Valor"];
      const rows = report.map((item) =>
        [item.motoristaId, item.nome, item.totalMultas, item.totalValor].join(",")
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