import { Router, Request, Response, NextFunction, RequestHandler } from "express";
import { GenerateVehicleCostReport } from "../../../application/useCases/GenerateVehicleCostReport";
import { GenerateDriverLocationHistory } from "../../../application/useCases/GenerateDriverLocationHistory";
import { GenerateVehicleKmReport } from "../../../application/useCases/GenerateVehicleKmReport";
import { GenerateDriverFinesReport } from "../../../application/useCases/GenerateDriverFinesReport";
import { GenerateSummaryReport } from "../../../application/useCases/GenerateSummaryReport";
import { VeiculoRepository } from "../../repositories/VeiculoRepository";
import { ManutencaoRepository } from "../../repositories/ManutencaoRepository";
import { MultaRepository } from "../../repositories/MultaRepository";
import { MotoristaRepository } from "../../repositories/MotoristaRepository";
import { LocacaoRepository } from "../../repositories/LocacaoRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";
import { GenerateDriverLocationHistoryReport } from "../../../application/useCases/GenerateDriverLocationHistoryReport";

const reportRoutes = Router();
const veiculoRepository = new VeiculoRepository();
const manutencaoRepository = new ManutencaoRepository();
const multaRepository = new MultaRepository();
const motoristaRepository = new MotoristaRepository();
const locacaoRepository = new LocacaoRepository();

// ADICIONADO DO TUTORIAL: Ajuste na instância para incluir veiculoRepository
const generateDriverLocationHistoryReport = new GenerateDriverLocationHistoryReport(
  motoristaRepository,
  locacaoRepository,
  veiculoRepository // Adicionado para suprir a necessidade de placas
);

const generateVehicleCostReport = new GenerateVehicleCostReport(veiculoRepository, manutencaoRepository, multaRepository);
const generateDriverLocationHistory = new GenerateDriverLocationHistory(motoristaRepository, locacaoRepository);
const generateVehicleKmReport = new GenerateVehicleKmReport(veiculoRepository, locacaoRepository);
const generateDriverFinesReport = new GenerateDriverFinesReport(motoristaRepository, multaRepository);
const generateSummaryReport = new GenerateSummaryReport(veiculoRepository, locacaoRepository, manutencaoRepository, multaRepository);

const asyncHandler = (
  fn: (req: Request, res: Response, next: NextFunction) => Promise<void>
): RequestHandler => {
  return (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
};

reportRoutes.use(ensureAuthenticated);

reportRoutes.get(
  "/vehicle-costs",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { page, limit, sort, order, exportFormat } = req.query;

    const filters: { 
      page?: number; 
      limit?: number; 
      sort?: "totalGeral" | "totalManutencao" | "totalMultas" | "placa"; 
      order?: "asc" | "desc"; 
      exportFormat?: "csv" | "pdf"; // Adicionado "pdf"
    } = {};

    if (page && typeof page === "string") {
      filters.page = parseInt(page, 10);
      if (isNaN(filters.page) || filters.page <= 0) {
        res.status(400).json({ error: "Página inválida" });
        return;
      }
    }
    if (limit && typeof limit === "string") {
      filters.limit = parseInt(limit, 10);
      if (isNaN(filters.limit) || filters.limit <= 0) {
        res.status(400).json({ error: "Limite inválido" });
        return;
      }
    }
    if (sort && typeof sort === "string") {
      if (!["totalGeral", "totalManutencao", "totalMultas", "placa"].includes(sort)) {
        res.status(400).json({ error: "Campo de ordenação inválido" });
        return;
      }
      filters.sort = sort as "totalGeral" | "totalManutencao" | "totalMultas" | "placa";
    }
    if (order && typeof order === "string") {
      if (!["asc", "desc"].includes(order)) {
        res.status(400).json({ error: "Direção de ordenação inválida" });
        return;
      }
      filters.order = order as "asc" | "desc";
    }
    if (exportFormat && typeof exportFormat === "string") {
      if (!["csv", "pdf"].includes(exportFormat)) {
        res.status(400).json({ error: "Formato de exportação inválido. Use 'csv' ou 'pdf'." });
        return;
      }
      filters.exportFormat = exportFormat as "csv" | "pdf";
    }

    const report = await generateVehicleCostReport.execute(filters);

    if (filters.exportFormat === "csv") {
      res.setHeader("Content-Type", "text/csv");
      res.setHeader("Content-Disposition", "attachment; filename=vehicle_costs_report.csv");
      res.send(report);
    } else if (filters.exportFormat === "pdf") {
      res.setHeader("Content-Type", "application/pdf");
      res.setHeader("Content-Disposition", "attachment; filename=vehicle_costs_report.pdf");
      res.send(report);
    } else {
      res.json(report);
    }
  })
);

// ADICIONADO DO TUTORIAL: Ajuste na rota /driver-location-history para refletir o novo caso de uso
reportRoutes.get(
  "/driver-location-history",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { startDate, endDate, page, limit, sort, order, exportFormat } = req.query;

    const filters: { 
      startDate?: Date; 
      endDate?: Date; 
      page?: number; 
      limit?: number; 
      sort?: "totalKm" | "totalLocacoes" | "nome"; 
      order?: "asc" | "desc"; 
      exportFormat?: "csv";
    } = {};

    if (startDate && typeof startDate === "string") {
      filters.startDate = new Date(startDate);
      if (isNaN(filters.startDate.getTime())) {
        res.status(400).json({ error: "Data de início inválida" });
        return;
      }
    }
    if (endDate && typeof endDate === "string") {
      filters.endDate = new Date(endDate);
      if (isNaN(filters.endDate.getTime())) {
        res.status(400).json({ error: "Data de fim inválida" });
        return;
      }
    }
    if (page && typeof page === "string") {
      filters.page = parseInt(page, 10);
      if (isNaN(filters.page) || filters.page <= 0) {
        res.status(400).json({ error: "Página inválida" });
        return;
      }
    }
    if (limit && typeof limit === "string") {
      filters.limit = parseInt(limit, 10);
      if (isNaN(filters.limit) || filters.limit <= 0) {
        res.status(400).json({ error: "Limite inválido" });
        return;
      }
    }
    if (sort && typeof sort === "string") {
      if (!["totalKm", "totalLocacoes", "nome"].includes(sort)) {
        res.status(400).json({ error: "Campo de ordenação inválido" });
        return;
      }
      filters.sort = sort as "totalKm" | "totalLocacoes" | "nome";
    }
    if (order && typeof order === "string") {
      if (!["asc", "desc"].includes(order)) {
        res.status(400).json({ error: "Direção de ordenação inválida" });
        return;
      }
      filters.order = order as "asc" | "desc";
    }
    if (exportFormat && typeof exportFormat === "string") {
      if (exportFormat !== "csv") {
        res.status(400).json({ error: "Formato de exportação inválido. Use 'csv'." });
        return;
      }
      filters.exportFormat = "csv";
    }

    const report = await generateDriverLocationHistoryReport.execute(filters);

    if (filters.exportFormat === "csv") {
      res.setHeader("Content-Type", "text/csv");
      res.setHeader("Content-Disposition", "attachment; filename=driver_location_history_report.csv");
      res.send(report);
    } else {
      res.json(report);
    }
  })
);

reportRoutes.get(
  "/vehicle-km",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { startDate, endDate, page, limit, sort, order, exportFormat } = req.query;

    const filters: { 
      startDate?: Date; 
      endDate?: Date; 
      page?: number; 
      limit?: number; 
      sort?: "totalKm" | "totalLocacoes" | "placa"; 
      order?: "asc" | "desc"; 
      exportFormat?: "csv" | "pdf"; // Adicionado "csv" e "pdf"
    } = {};

    if (startDate && typeof startDate === "string") {
      filters.startDate = new Date(startDate);
      if (isNaN(filters.startDate.getTime())) {
        res.status(400).json({ error: "Data de início inválida" });
        return;
      }
    }
    if (endDate && typeof endDate === "string") {
      filters.endDate = new Date(endDate);
      if (isNaN(filters.endDate.getTime())) {
        res.status(400).json({ error: "Data de fim inválida" });
        return;
      }
    }
    if (page && typeof page === "string") {
      filters.page = parseInt(page, 10);
      if (isNaN(filters.page) || filters.page <= 0) {
        res.status(400).json({ error: "Página inválida" });
        return;
      }
    }
    if (limit && typeof limit === "string") {
      filters.limit = parseInt(limit, 10);
      if (isNaN(filters.limit) || filters.limit <= 0) {
        res.status(400).json({ error: "Limite inválido" });
        return;
      }
    }
    if (sort && typeof sort === "string") {
      if (!["totalKm", "totalLocacoes", "placa"].includes(sort)) {
        res.status(400).json({ error: "Campo de ordenação inválido" });
        return;
      }
      filters.sort = sort as "totalKm" | "totalLocacoes" | "placa";
    }
    if (order && typeof order === "string") {
      if (!["asc", "desc"].includes(order)) {
        res.status(400).json({ error: "Direção de ordenação inválida" });
        return;
      }
      filters.order = order as "asc" | "desc";
    }
    if (exportFormat && typeof exportFormat === "string") {
      if (!["csv", "pdf"].includes(exportFormat)) {
        res.status(400).json({ error: "Formato de exportação inválido. Use 'csv' ou 'pdf'." });
        return;
      }
      filters.exportFormat = exportFormat as "csv" | "pdf";
    }

    const report = await generateVehicleKmReport.execute(filters);

    if (filters.exportFormat === "csv") {
      res.setHeader("Content-Type", "text/csv");
      res.setHeader("Content-Disposition", "attachment; filename=vehicle_km_report.csv");
      res.send(report);
    } else if (filters.exportFormat === "pdf") {
      res.setHeader("Content-Type", "application/pdf");
      res.setHeader("Content-Disposition", "attachment; filename=vehicle_km_report.pdf");
      res.send(report);
    } else {
      res.json(report);
    }
  })
);

reportRoutes.get(
  "/driver-fines",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { startDate, endDate, page, limit, sort, order, exportFormat } = req.query;

    const filters: { 
      startDate?: Date; 
      endDate?: Date; 
      page?: number; 
      limit?: number; 
      sort?: "totalMultas" | "totalValor" | "nome"; 
      order?: "asc" | "desc"; 
      exportFormat?: "csv" | "pdf"; // Adicionado "pdf"
    } = {};

    if (startDate && typeof startDate === "string") {
      filters.startDate = new Date(startDate);
      if (isNaN(filters.startDate.getTime())) {
        res.status(400).json({ error: "Data de início inválida" });
        return;
      }
    }
    if (endDate && typeof endDate === "string") {
      filters.endDate = new Date(endDate);
      if (isNaN(filters.endDate.getTime())) {
        res.status(400).json({ error: "Data de fim inválida" });
        return;
      }
    }
    if (page && typeof page === "string") {
      filters.page = parseInt(page, 10);
      if (isNaN(filters.page) || filters.page <= 0) {
        res.status(400).json({ error: "Página inválida" });
        return;
      }
    }
    if (limit && typeof limit === "string") {
      filters.limit = parseInt(limit, 10);
      if (isNaN(filters.limit) || filters.limit <= 0) {
        res.status(400).json({ error: "Limite inválido" });
        return;
      }
    }
    if (sort && typeof sort === "string") {
      if (!["totalMultas", "totalValor", "nome"].includes(sort)) {
        res.status(400).json({ error: "Campo de ordenação inválido" });
        return;
      }
      filters.sort = sort as "totalMultas" | "totalValor" | "nome";
    }
    if (order && typeof order === "string") {
      if (!["asc", "desc"].includes(order)) {
        res.status(400).json({ error: "Direção de ordenação inválida" });
        return;
      }
      filters.order = order as "asc" | "desc";
    }
    if (exportFormat && typeof exportFormat === "string") {
      if (!["csv", "pdf"].includes(exportFormat)) {
        res.status(400).json({ error: "Formato de exportação inválido. Use 'csv' ou 'pdf'." });
        return;
      }
      filters.exportFormat = exportFormat as "csv" | "pdf";
    }

    const report = await generateDriverFinesReport.execute(filters);

    if (filters.exportFormat === "csv") {
      res.setHeader("Content-Type", "text/csv");
      res.setHeader("Content-Disposition", "attachment; filename=driver_fines_report.csv");
      res.send(report);
    } else if (filters.exportFormat === "pdf") {
      res.setHeader("Content-Type", "application/pdf");
      res.setHeader("Content-Disposition", "attachment; filename=driver_fines_report.pdf");
      res.send(report);
    } else {
      res.json(report);
    }
  })
);

reportRoutes.get(
  "/summary",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { startDate, endDate, export: exportParam } = req.query;

    const filters: { startDate?: Date; endDate?: Date; exportFormat?: "csv" | "pdf" } = {};

    if (startDate && typeof startDate === "string") {
      filters.startDate = new Date(startDate);
      if (isNaN(filters.startDate.getTime())) {
        res.status(400).json({ error: "Data de início inválida" });
        return;
      }
    }
    if (endDate && typeof endDate === "string") {
      filters.endDate = new Date(endDate);
      if (isNaN(filters.endDate.getTime())) {
        res.status(400).json({ error: "Data de fim inválida" });
        return;
      }
    }
    if (exportParam && typeof exportParam === "string") {
      if (!["csv", "pdf"].includes(exportParam)) {
        res.status(400).json({ error: "Formato de exportação inválido. Use 'csv' ou 'pdf'." });
        return;
      }
      filters.exportFormat = exportParam as "csv" | "pdf";
    }

    const report = await generateSummaryReport.execute(filters);

    if (filters.exportFormat === "csv") {
      res.setHeader("Content-Type", "text/csv");
      res.setHeader("Content-Disposition", "attachment; filename=summary_report.csv");
      res.send(report);
    } else if (filters.exportFormat === "pdf") {
      res.setHeader("Content-Type", "application/pdf");
      res.setHeader("Content-Disposition", "attachment; filename=summary_report.pdf");
      res.send(report);
    } else {
      res.json(report);
    }
  })
);

export default reportRoutes;