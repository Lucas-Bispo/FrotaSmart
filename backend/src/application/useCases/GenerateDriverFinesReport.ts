import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Motorista } from "../../domain/entities/Motorista";
import { Multa } from "../../domain/entities/Multa";

interface DriverFinesReport {
  motoristaId: number;
  cpf: string;
  nome: string;
  multas: {
    id: number;
    veiculoId: number;
    data: Date;
    tipo: string;
    valor: number;
    descricao: string | null; // Ajustado para string | null
  }[];
  totalMultas: number;
  totalValor: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
  page?: number;
  limit?: number;
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

  async execute({ startDate, endDate, page = 1, limit = 10 }: FilterOptions = {}): Promise<PaginatedDriverFinesReport> {
    const motoristas = await this.motoristaRepository.list();
    const multas = await this.multaRepository.list();

    const filteredMultas = multas.filter((multa) => {
      const data = multa.data;
      if (startDate && data < startDate) return false;
      if (endDate && data > endDate) return false;
      return true;
    });

    const report: DriverFinesReport[] = motoristas.map((motorista: Motorista) => {
      const motoristaMultas = filteredMultas.filter((m) => m.motoristaId === motorista.id);
      const totalValor = motoristaMultas.reduce((sum, m) => sum + m.valor, 0);

      return {
        motoristaId: motorista.id!,
        cpf: motorista.cpf,
        nome: motorista.nome,
        multas: motoristaMultas.map((m: Multa) => ({
          id: m.id!,
          veiculoId: m.veiculoId,
          data: m.data,
          tipo: m.tipo,
          valor: m.valor,
          descricao: m.descricao, // Já é string | null, compatível com a interface
        })),
        totalMultas: motoristaMultas.length,
        totalValor,
      };
    });

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