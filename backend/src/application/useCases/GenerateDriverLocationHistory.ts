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
}

export class GenerateDriverLocationHistory {
  constructor(
    private motoristaRepository: IMotoristaRepository,
    private locacaoRepository: ILocacaoRepository
  ) {}

  async execute({ startDate, endDate }: FilterOptions = {}): Promise<DriverLocationHistory[]> {
    const motoristas = await this.motoristaRepository.list();
    const locacoes = await this.locacaoRepository.list();

    const filteredLocacoes = locacoes.filter((locacao) => {
      const dataInicio = locacao.dataInicio;
      if (startDate && dataInicio < startDate) return false;
      if (endDate && dataInicio > endDate) return false;
      return true;
    });

    const report: DriverLocationHistory[] = motoristas.map((motorista: Motorista) => {
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

    return report;
  }
}