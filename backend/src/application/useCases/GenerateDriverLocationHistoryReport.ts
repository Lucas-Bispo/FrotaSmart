import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { Motorista } from "../../domain/entities/Motorista";
import PDFDocument from "pdfkit"; // Corrigido: Importação padrão

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
  exportFormat?: "csv" | "pdf";
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
    private locacaoRepository: ILocacaoRepository,
    private veiculoRepository: IVeiculoRepository
  ) {}

  async execute({
    startDate,
    endDate,
    page = 1,
    limit = 10,
    sort,
    order = "asc",
    exportFormat,
  }: FilterOptions = {}): Promise<PaginatedDriverLocationHistoryReport | string | Buffer> {
    const motoristas = await this.motoristaRepository.list();
    const locacoes = await this.locacaoRepository.list();
    const veiculos = await this.veiculoRepository.list();

    const veiculoMap = new Map(veiculos.map((v) => [v.id!, v]));

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
          placa: veiculoMap.get(l.veiculoId)?.placa || "N/A",
          dataInicio: l.dataInicio,
          dataFim: l.dataFim || null,
          km: l.km || 0,
        })),
        totalLocacoes: motoristaLocacoes.length,
        totalKm,
      };
    });

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

    if (exportFormat === "csv") {
      const headers = ["Motorista ID", "Nome", "Total Locações", "Total KM"];
      const rows = report.map((item) =>
        [item.motoristaId, item.nome, item.totalLocacoes, item.totalKm].join(",")
      );
      const csv = [headers.join(","), ...rows].join("\n");
      return csv;
    }

    if (exportFormat === "pdf") {
      const doc = new PDFDocument({ size: "A4", margin: 50 }); // Corrigido: Uso direto da classe
      const buffers: Buffer[] = [];

      doc.on("data", (chunk: Buffer) => buffers.push(chunk)); // Corrigido: Tipagem explícita de chunk
      doc.on("end", () => {});

      // Cabeçalho
      doc.fontSize(20).text("Relatório de Histórico de Locação por Motorista", 50, 50, { align: "center" });
      doc.fontSize(12).text(`Gerado em: ${new Date().toLocaleDateString()}`, 50, 80, { align: "center" });
      doc.moveDown(2);

      // Tabela de motoristas
      let yPosition = 120;
      const tableWidth = 500;
      const colWidths = [50, 150, 80, 80, 80];
      const rowHeight = 20;

      doc.fontSize(10).font("Helvetica-Bold");
      doc.text("ID", 50, yPosition);
      doc.text("Nome", 100, yPosition);
      doc.text("Locações", 250, yPosition);
      doc.text("Total KM", 330, yPosition);
      doc.moveTo(50, yPosition + rowHeight).lineTo(50 + tableWidth, yPosition + rowHeight).stroke();
      yPosition += rowHeight + 5;

      doc.font("Helvetica");
      report.forEach((item) => {
        doc.text(item.motoristaId.toString(), 50, yPosition);
        doc.text(item.nome, 100, yPosition, { width: 140, ellipsis: true });
        doc.text(item.totalLocacoes.toString(), 250, yPosition);
        doc.text(item.totalKm.toString(), 330, yPosition);
        yPosition += rowHeight;

        if (item.locacoes.length > 0) {
          doc.fontSize(8).font("Helvetica-Oblique");
          item.locacoes.forEach((loc) => {
            doc.text(
              ` - Locação ${loc.id}: ${loc.placa}, ${loc.dataInicio.toLocaleDateString()} - ${loc.dataFim?.toLocaleDateString() || "N/A"}, ${loc.km} km`,
              60,
              yPosition,
              { width: 450 }
            );
            yPosition += 15;
          });
          doc.font("Helvetica");
        }

        if (yPosition > 700) {
          doc.addPage();
          yPosition = 50;
        }
      });

      doc.end();

      return new Promise((resolve) => {
        doc.on("end", () => resolve(Buffer.concat(buffers)));
      });
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