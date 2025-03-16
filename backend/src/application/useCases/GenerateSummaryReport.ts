import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import PDFDocument from "pdfkit";

interface SummaryReport {
  totalVeiculos: number;
  totalLocacoes: number;
  totalManutencoes: number;
  totalMultas: number;
  custoTotalManutencoes: number;
  custoTotalMultas: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
  exportFormat?: "csv" | "pdf"; // Adicionado "pdf"
}

export class GenerateSummaryReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
    private locacaoRepository: ILocacaoRepository,
    private manutencaoRepository: IManutencaoRepository,
    private multaRepository: IMultaRepository
  ) {}

  async execute({
    startDate,
    endDate,
    exportFormat,
  }: FilterOptions = {}): Promise<SummaryReport | string | Buffer> {
    const veiculos = await this.veiculoRepository.list();
    const locacoes = await this.locacaoRepository.list();
    const manutencoes = await this.manutencaoRepository.list();
    const multas = await this.multaRepository.list();

    const filteredLocacoes = locacoes.filter((l) => {
      if (startDate && l.dataInicio < startDate) return false;
      if (endDate && l.dataInicio > endDate) return false;
      return true;
    });

    const filteredManutencoes = manutencoes.filter((m) => {
      if (startDate && m.data < startDate) return false;
      if (endDate && m.data > endDate) return false;
      return true;
    });

    const filteredMultas = multas.filter((m) => {
      if (startDate && m.data < startDate) return false;
      if (endDate && m.data > endDate) return false;
      return true;
    });

    const report: SummaryReport = {
      totalVeiculos: veiculos.length,
      totalLocacoes: filteredLocacoes.length,
      totalManutencoes: filteredManutencoes.length,
      totalMultas: filteredMultas.length,
      custoTotalManutencoes: filteredManutencoes.reduce((sum, m) => sum + (m.custo || 0), 0),
      custoTotalMultas: filteredMultas.reduce((sum, m) => sum + (m.valor || 0), 0),
    };

    if (exportFormat === "csv") {
      const headers = [
        "Total Veículos",
        "Total Locações",
        "Total Manutenções",
        "Total Multas",
        "Custo Total Manutenções",
        "Custo Total Multas",
      ];
      const row = [
        report.totalVeiculos,
        report.totalLocacoes,
        report.totalManutencoes,
        report.totalMultas,
        report.custoTotalManutencoes,
        report.custoTotalMultas,
      ];
      return [headers.join(","), row.join(",")].join("\n");
    }

    if (exportFormat === "pdf") {
      const doc = new PDFDocument({ size: "A4", margin: 50 });
      const buffers: Buffer[] = [];

      doc.on("data", (chunk: Buffer) => buffers.push(chunk));
      doc.on("end", () => {});

      // Cabeçalho
      doc.fontSize(20).text("Relatório Geral - FrotaSmart", 50, 50, { align: "center" });
      doc.fontSize(12).text(`Gerado em: ${new Date().toLocaleDateString()}`, 50, 80, { align: "center" });
      doc.moveDown(2);

      // Dados
      let yPosition = 120;
      doc.fontSize(12).font("Helvetica-Bold");
      doc.text("Métricas Gerais", 50, yPosition);
      yPosition += 20;

      doc.font("Helvetica");
      doc.text(`Total de Veículos: ${report.totalVeiculos}`, 50, yPosition);
      yPosition += 20;
      doc.text(`Total de Locações: ${report.totalLocacoes}`, 50, yPosition);
      yPosition += 20;
      doc.text(`Total de Manutenções: ${report.totalManutencoes}`, 50, yPosition);
      yPosition += 20;
      doc.text(`Total de Multas: ${report.totalMultas}`, 50, yPosition);
      yPosition += 20;
      doc.text(`Custo Total de Manutenções: R$ ${report.custoTotalManutencoes.toFixed(2)}`, 50, yPosition);
      yPosition += 20;
      doc.text(`Custo Total de Multas: R$ ${report.custoTotalMultas.toFixed(2)}`, 50, yPosition);

      doc.end();

      return new Promise((resolve) => {
        doc.on("end", () => resolve(Buffer.concat(buffers)));
      });
    }

    return report;
  }
}