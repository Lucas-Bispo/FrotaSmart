import { Router, Request, Response, NextFunction, RequestHandler } from "express";
import { CreateManutencao } from "../../../application/useCases/CreateManutencao";
import { ListManutencoes } from "../../../application/useCases/ListManutencoes";
import { UpdateManutencao } from "../../../application/useCases/UpdateManutencao";
import { DeleteManutencao } from "../../../application/useCases/DeleteManutencao";
import { ManutencaoRepository } from "../../repositories/ManutencaoRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const manutencaoRoutes = Router();
const manutencaoRepository = new ManutencaoRepository();
const createManutencao = new CreateManutencao(manutencaoRepository);
const listManutencoes = new ListManutencoes(manutencaoRepository);
const updateManutencao = new UpdateManutencao(manutencaoRepository);
const deleteManutencao = new DeleteManutencao(manutencaoRepository);

const asyncHandler = (
  fn: (req: Request, res: Response, next: NextFunction) => Promise<void>
): RequestHandler => {
  return (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
};

manutencaoRoutes.use(ensureAuthenticated);

manutencaoRoutes.post(
  "/",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { veiculoId, data, tipo, descricao, custo } = req.body;
    const manutencao = await createManutencao.execute({
      veiculoId,
      data: new Date(data),
      tipo,
      descricao,
      custo,
    });
    res.status(201).json(manutencao);
  })
);

manutencaoRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const manutencoes = await listManutencoes.execute();
    res.json(manutencoes);
  })
);

manutencaoRoutes.put(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const { veiculoId, data, tipo, descricao, custo } = req.body;
    const manutencao = await updateManutencao.execute(Number(id), {
      veiculoId,
      data: data ? new Date(data) : undefined,
      tipo,
      descricao,
      custo,
    });
    res.json(manutencao);
  })
);

manutencaoRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    await deleteManutencao.execute(Number(id));
    res.status(204).send();
  })
);

export default manutencaoRoutes;