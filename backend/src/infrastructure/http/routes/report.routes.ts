import { Router, Request, Response, NextFunction, RequestHandler } from "express";
import { GenerateVehicleCostReport } from "../../../application/useCases/GenerateVehicleCostReport";
import { VeiculoRepository } from "../../repositories/VeiculoRepository";
import { ManutencaoRepository } from "../../repositories/ManutencaoRepository";
import { MultaRepository } from "../../repositories/MultaRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const reportRoutes = Router();
const veiculoRepository = new VeiculoRepository();
const manutencaoRepository = new ManutencaoRepository();
const multaRepository = new MultaRepository();
const generateVehicleCostReport = new GenerateVehicleCostReport(veiculoRepository, manutencaoRepository, multaRepository);

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

export default reportRoutes;