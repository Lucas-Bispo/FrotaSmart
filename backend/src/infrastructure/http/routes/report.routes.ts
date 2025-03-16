import { Router, Request, Response, NextFunction, RequestHandler } from "express";
import { GenerateVehicleCostReport } from "../../../application/useCases/GenerateVehicleCostReport";
import { GenerateDriverLocationHistory } from "../../../application/useCases/GenerateDriverLocationHistory";
import { GenerateVehicleKmReport } from "../../../application/useCases/GenerateVehicleKmReport";
import { VeiculoRepository } from "../../repositories/VeiculoRepository";
import { ManutencaoRepository } from "../../repositories/ManutencaoRepository";
import { MultaRepository } from "../../repositories/MultaRepository";
import { MotoristaRepository } from "../../repositories/MotoristaRepository";
import { LocacaoRepository } from "../../repositories/LocacaoRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const reportRoutes = Router();
const veiculoRepository = new VeiculoRepository();
const manutencaoRepository = new ManutencaoRepository();
const multaRepository = new MultaRepository();
const motoristaRepository = new MotoristaRepository();
const locacaoRepository = new LocacaoRepository();

const generateVehicleCostReport = new GenerateVehicleCostReport(veiculoRepository, manutencaoRepository, multaRepository);
const generateDriverLocationHistory = new GenerateDriverLocationHistory(motoristaRepository, locacaoRepository);
const generateVehicleKmReport = new GenerateVehicleKmReport(veiculoRepository, locacaoRepository);

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
    const report = await generateVehicleCostReport.execute();
    res.json(report);
  })
);

reportRoutes.get(
  "/driver-location-history",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { startDate, endDate, page, limit } = req.query;

    const filters: { startDate?: Date; endDate?: Date; page?: number; limit?: number } = {};
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

    const report = await generateDriverLocationHistory.execute(filters);
    res.json(report);
  })
);

reportRoutes.get(
  "/vehicle-km",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { startDate, endDate } = req.query;
    const filters: { startDate?: Date; endDate?: Date } = {};
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
    const report = await generateVehicleKmReport.execute(filters);
    res.json(report);
  })
);

export default reportRoutes;