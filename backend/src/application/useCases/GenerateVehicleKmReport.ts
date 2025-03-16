import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Veiculo } from "../../domain/entities/Veiculo";
import PDFDocument from "pdfkit";

interface VehicleKmReport {
  veiculoId: number;
  placa: string;
  totalLocacoes: number;
  totalKm: number;
}

interface FilterOptions {
  startDate?: Date;
  endDate?: Date;
  page?: number;
  limit?: number;
  sort?: "totalKm" | "totalLocacoes" | "placa";
  order?: "asc" | "desc";
  exportFormat?: "csv" | "pdf"; // Adicionado "csv" e "pdf"
}

interface PaginatedVehicleKmReport {
  data: VehicleKmReport[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export class GenerateVehicleKmReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
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
  }: FilterOptions = {}): Promise<PaginatedVehicleKmReport | string | Buffer> {
    const veiculos = await this.veiculoRepository.list();
    const locacoes = await this.locacaoRepository.list();

    const filteredLocacoes = locacoes.filter((locacao) => {
      const dataInicio = locacao.dataInicio;
      if (startDate && dataInicio < startDate) return false;
      if (endDate && dataInicio > endDate) return false;
      return true;
    });

    let report: VehicleKmReport[] = veiculos.map((veiculo: Veiculo) => {
      const veiculoLocacoes = filteredLocacoes.filter((l) => l.veiculoId === veiculo.id);
      const totalKm = veiculoLocacoes.reduce((sum, l) => sum + (l.km || 0), 0);

      return {
        veiculoId: veiculo.id!,
        placa: veiculo.placa,
        totalLocacoes: veiculoLocacoes.length,
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

    const total = veiculos.length;
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedData = report.slice(startIndex, endIndex);
    const totalPages = Math.ceil(total / limit);

    if (exportFormat === "csv") {
      const headers = ["Veículo ID", "Placa", "Total Locações", "Total KM"];
      const rows = report.map((item) =>
        [item.veiculoId, item.placa, item.totalLocacoes, item.totalKm].join(",")
      );
      const csv = [headers.join(","), ...rows].join("\n");
      return csv;
    }

    if (exportFormat === "pdf") {
      const doc = new PDFDocument({ size: "A4", margin: 50 });
      const buffers: Buffer[] = [];

      doc.on("data", (chunk: Buffer) => buffers.push(chunk));
      doc.on("end", () => {});

      // Cabeçalho
      doc.fontSize(20).text("Relatório de Quilometragem por Veículo", 50, 50, { align: "center" });
      doc.fontSize(12).text(`Gerado em: ${new Date().toLocaleDateString()}`, 50, 80, { align: "center" });
      doc.moveDown(2);

      // Tabela
      let yPosition = 120;
      const tableWidth = 500;
      const colWidths = [50, 150, 100, 100]; // ID, Placa, Locações, KM
      const rowHeight = 20;

      doc.fontSize(10).font("Helvetica-Bold");
      doc.text("ID", 50, yPosition);
      doc.text("Placa", 100, yPosition);
      doc.text("Locações", 250, yPosition);
      doc.text("Total KM", 350, yPosition);
      doc.moveTo(50, yPosition + rowHeight).lineTo(50 + tableWidth, yPosition + rowHeight).stroke();
      yPosition += rowHeight + 5;

      doc.font("Helvetica");
      report.forEach((item) => {
        doc.text(item.veiculoId.toString(), 50, yPosition);
        doc.text(item.placa, 100, yPosition);
        doc.text(item.totalLocacoes.toString(), 250, yPosition);
        doc.text(item.totalKm.toString(), 350, yPosition);
        yPosition += rowHeight;

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