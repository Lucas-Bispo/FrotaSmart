import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Veiculo } from "../../domain/entities/Veiculo";
import PDFDocument from "pdfkit";

interface VehicleCostReport {
  veiculoId: number;
  placa: string;
  totalManutencao: number;
  totalMultas: number;
  totalGeral: number;
}

interface FilterOptions {
  page?: number;
  limit?: number;
  sort?: "totalGeral" | "totalManutencao" | "totalMultas" | "placa";
  order?: "asc" | "desc";
  exportFormat?: "csv" | "pdf"; // Adicionado "pdf"
}

interface PaginatedVehicleCostReport {
  data: VehicleCostReport[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export class GenerateVehicleCostReport {
  constructor(
    private veiculoRepository: IVeiculoRepository,
    private manutencaoRepository: IManutencaoRepository,
    private multaRepository: IMultaRepository
  ) {}

  async execute({
    page = 1,
    limit = 10,
    sort,
    order = "asc",
    exportFormat,
  }: FilterOptions = {}): Promise<PaginatedVehicleCostReport | string | Buffer> {
    const veiculos = await this.veiculoRepository.list();
    const manutencoes = await this.manutencaoRepository.list();
    const multas = await this.multaRepository.list();

    let report: VehicleCostReport[] = veiculos.map((veiculo: Veiculo) => {
      const veiculoManutencoes = manutencoes.filter((m) => m.veiculoId === veiculo.id);
      const veiculoMultas = multas.filter((m) => m.veiculoId === veiculo.id);

      const totalManutencao = veiculoManutencoes.reduce((sum, m) => sum + (m.custo || 0), 0);
      const totalMultas = veiculoMultas.reduce((sum, m) => sum + (m.valor || 0), 0);
      const totalGeral = totalManutencao + totalMultas;

      return {
        veiculoId: veiculo.id!,
        placa: veiculo.placa,
        totalManutencao,
        totalMultas,
        totalGeral,
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
      const headers = ["Veículo ID", "Placa", "Total Manutenção (R$)", "Total Multas (R$)", "Total Geral (R$)"];
      const rows = report.map((item) =>
        [item.veiculoId, item.placa, item.totalManutencao.toFixed(2), item.totalMultas.toFixed(2), item.totalGeral.toFixed(2)].join(",")
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
      doc.fontSize(20).text("Relatório de Custos por Veículo", 50, 50, { align: "center" });
      doc.fontSize(12).text(`Gerado em: ${new Date().toLocaleDateString()}`, 50, 80, { align: "center" });
      doc.moveDown(2);

      // Tabela
      let yPosition = 120;
      const tableWidth = 500;
      const colWidths = [50, 100, 100, 100, 100]; // ID, Placa, Manutenção, Multas, Total
      const rowHeight = 20;

      doc.fontSize(10).font("Helvetica-Bold");
      doc.text("ID", 50, yPosition);
      doc.text("Placa", 100, yPosition);
      doc.text("Manutenção (R$)", 200, yPosition);
      doc.text("Multas (R$)", 300, yPosition);
      doc.text("Total (R$)", 400, yPosition);
      doc.moveTo(50, yPosition + rowHeight).lineTo(50 + tableWidth, yPosition + rowHeight).stroke();
      yPosition += rowHeight + 5;

      doc.font("Helvetica");
      report.forEach((item) => {
        doc.text(item.veiculoId.toString(), 50, yPosition);
        doc.text(item.placa, 100, yPosition);
        doc.text(item.totalManutencao.toFixed(2), 200, yPosition);
        doc.text(item.totalMultas.toFixed(2), 300, yPosition);
        doc.text(item.totalGeral.toFixed(2), 400, yPosition);
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