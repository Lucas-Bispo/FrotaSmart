import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Motorista } from "../../domain/entities/Motorista";
import PDFDocument from "pdfkit";

interface DriverFinesReport {
  motoristaId: number;
  nome: string;
  multas: {
    id: number;
    descricao: string;
    data: Date;
    valor: number;
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
  exportFormat?: "csv" | "pdf"; // Adicionado "pdf"
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
  }: FilterOptions = {}): Promise<PaginatedDriverFinesReport | string | Buffer> {
    const motoristas = await this.motoristaRepository.list();
    const multas = await this.multaRepository.list();

    const filteredMultas = multas.filter((m) => {
      if (startDate && m.data < startDate) return false;
      if (endDate && m.data > endDate) return false;
      return true;
    });

    let report: DriverFinesReport[] = motoristas.map((motorista: Motorista) => {
      const motoristaMultas = filteredMultas.filter((m) => m.motoristaId === motorista.id);
      const totalValor = motoristaMultas.reduce((sum, m) => sum + (m.valor || 0), 0);

      return {
        motoristaId: motorista.id!,
        nome: motorista.nome,
        multas: motoristaMultas.map((m) => ({
          id: m.id!,
          descricao: m.descricao,
          data: m.data,
          valor: m.valor || 0,
        })),
        totalMultas: motoristaMultas.length,
        totalValor,
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
      const headers = ["Motorista ID", "Nome", "Total Multas", "Total Valor"];
      const rows = report.map((item) =>
        [item.motoristaId, item.nome, item.totalMultas, item.totalValor].join(",")
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
      doc.fontSize(20).text("Relatório de Multas por Motorista", 50, 50, { align: "center" });
      doc.fontSize(12).text(`Gerado em: ${new Date().toLocaleDateString()}`, 50, 80, { align: "center" });
      doc.moveDown(2);

      // Tabela
      let yPosition = 120;
      const tableWidth = 500;
      const colWidths = [50, 150, 80, 100]; // ID, Nome, Total Multas, Total Valor
      const rowHeight = 20;

      doc.fontSize(10).font("Helvetica-Bold");
      doc.text("ID", 50, yPosition);
      doc.text("Nome", 100, yPosition);
      doc.text("Multas", 250, yPosition);
      doc.text("Total (R$)", 330, yPosition);
      doc.moveTo(50, yPosition + rowHeight).lineTo(50 + tableWidth, yPosition + rowHeight).stroke();
      yPosition += rowHeight + 5;

      doc.font("Helvetica");
      report.forEach((item) => {
        doc.text(item.motoristaId.toString(), 50, yPosition);
        doc.text(item.nome, 100, yPosition, { width: 140, ellipsis: true });
        doc.text(item.totalMultas.toString(), 250, yPosition);
        doc.text(item.totalValor.toFixed(2), 330, yPosition);
        yPosition += rowHeight;

        if (item.multas.length > 0) {
          doc.fontSize(8).font("Helvetica-Oblique");
          item.multas.forEach((multa) => {
            doc.text(
              ` - Multa ${multa.id}: ${multa.descricao}, ${multa.data.toLocaleDateString()}, R$ ${multa.valor.toFixed(2)}`,
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