import { Router, Request, Response, NextFunction, RequestHandler } from "express";

import { ListMultas } from "../../../application/useCases/ListMultas";
import { UpdateMulta } from "../../../application/useCases/UpdateMulta";
import { DeleteMulta } from "../../../application/useCases/DeleteMulta";
import { MultaRepository } from "../../repositories/MultaRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";
import { CreateMulta } from "../../../application/useCases/CreateMulta";

const multaRoutes = Router();
const multaRepository = new MultaRepository();
const createMulta = new CreateMulta(multaRepository);
const listMultas = new ListMultas(multaRepository);
const updateMulta = new UpdateMulta(multaRepository);
const deleteMulta = new DeleteMulta(multaRepository);

const asyncHandler = (
  fn: (req: Request, res: Response, next: NextFunction) => Promise<void>
): RequestHandler => {
  return (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
};

multaRoutes.use(ensureAuthenticated);

multaRoutes.post(
  "/",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { veiculoId, motoristaId, data, tipo, valor, descricao } = req.body;
    const multa = await createMulta.execute({
      veiculoId,
      motoristaId,
      data: new Date(data),
      tipo,
      valor,
      descricao,
    });
    res.status(201).json(multa);
  })
);

multaRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const multas = await listMultas.execute();
    res.json(multas);
  })
);

multaRoutes.put(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const { veiculoId, motoristaId, data, tipo, valor, descricao } = req.body;
    const multa = await updateMulta.execute(Number(id), {
      veiculoId,
      motoristaId,
      data: data ? new Date(data) : undefined,
      tipo,
      valor,
      descricao,
    });
    res.json(multa);
  })
);

multaRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    await deleteMulta.execute(Number(id));
    res.status(204).send();
  })
);

export default multaRoutes;