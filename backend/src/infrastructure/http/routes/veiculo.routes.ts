import { Router, Request, Response, NextFunction, RequestHandler } from "express";
import { CreateVeiculo } from "../../../application/useCases/CreateVeiculo";
import { ListVeiculos } from "../../../application/useCases/ListVeiculos";
import { UpdateVeiculo } from "../../../application/useCases/UpdateVeiculo";
import { DeleteVeiculo } from "../../../application/useCases/DeleteVeiculo";
import { VeiculoRepository } from "../../repositories/VeiculoRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const veiculoRoutes = Router();
const veiculoRepository = new VeiculoRepository();
const createVeiculo = new CreateVeiculo(veiculoRepository);
const listVeiculos = new ListVeiculos(veiculoRepository);
const updateVeiculo = new UpdateVeiculo(veiculoRepository);
const deleteVeiculo = new DeleteVeiculo(veiculoRepository);

// Função auxiliar para encapsular handlers assíncronos
const asyncHandler = (fn: (req: Request, res: Response, next: NextFunction) => Promise<any>) =>
  (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };

// Aplica o middleware de autenticação globalmente com tipagem explícita
veiculoRoutes.use(ensureAuthenticated as RequestHandler);

// Rotas
veiculoRoutes.post(
  "/",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { placa, tipo, secretariaId } = req.body;
    const veiculo = await createVeiculo.execute({ placa, tipo, secretariaId });
    return res.status(201).json(veiculo);
  })
);

veiculoRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const veiculos = await listVeiculos.execute();
    return res.json(veiculos);
  })
);

veiculoRoutes.put(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const { placa, tipo, secretariaId } = req.body;
    const veiculo = await updateVeiculo.execute(Number(id), { placa, tipo, secretariaId });
    return res.json(veiculo);
  })
);

veiculoRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    await deleteVeiculo.execute(Number(id));
    return res.status(204).send();
  })
);

export default veiculoRoutes;