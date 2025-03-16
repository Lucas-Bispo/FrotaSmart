import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";

interface SummaryReport {
  totalVeiculos: number;
  totalLocacoes: number;
  totalKm: number;
  totalManutencao: number;
  totalMultas: number;
  totalGeral: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
  exportFormat?: "csv"; // Renomeado para evitar conflito com 'export'
}

export class GenerateSummaryReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
    private locacaoRepository: ILocacaoRepository,
    private manutencaoRepository: IManutencaoRepository,
    private multaRepository: IMultaRepository
  ) {}

  async execute({ startDate, endDate, exportFormat }: FilterOptions = {}): Promise<SummaryReport | string> { // Linha 29 corrigida
    const veiculos = await this.veiculoRepository.list();
    const locacoes = await this.locacaoRepository.list();
    const manutencoes = await this.manutencaoRepository.list();
    const multas = await this.multaRepository.list();

    // Filtrar locações por data
    const filteredLocacoes = locacoes.filter((locacao) => {
      const dataInicio = locacao.dataInicio;
      if (startDate && dataInicio < startDate) return false;
      if (endDate && dataInicio > endDate) return false;
      return true;
    });

    // Filtrar manutenções por data
    const filteredManutencoes = manutencoes.filter((manutencao) => {
      const data = manutencao.data;
      if (startDate && data < startDate) return false;
      if (endDate && data > endDate) return false;
      return true;
    });

    // Filtrar multas por data
    const filteredMultas = multas.filter((multa) => {
      const data = multa.data;
      if (startDate && data < startDate) return false;
      if (endDate && data > endDate) return false;
      return true;
    });

    // Calcular totais
    const totalVeiculos = veiculos.length;
    const totalLocacoes = filteredLocacoes.length;
    const totalKm = filteredLocacoes.reduce((sum, l) => sum + (l.km || 0), 0);
    const totalManutencao = filteredManutencoes.reduce((sum, m) => sum + (m.custo || 0), 0);
    const totalMultas = filteredMultas.reduce((sum, m) => sum + m.valor, 0);
    const totalGeral = totalManutencao + totalMultas;

    const report: SummaryReport = {
      totalVeiculos,
      totalLocacoes,
      totalKm,
      totalManutencao,
      totalMultas,
      totalGeral,
    };

    if (exportFormat === "csv") { // Ajustado para o novo nome
      const headers = ["Total Veículos", "Total Locações", "Total KM", "Total Manutenção", "Total Multas", "Total Geral"];
      const values = [
        report.totalVeiculos,
        report.totalLocacoes,
        report.totalKm,
        report.totalManutencao,
        report.totalMultas,
        report.totalGeral,
      ];
      const csv = [headers.join(","), values.join(",")].join("\n");
      return csv;
    }

    return report;
  }
}