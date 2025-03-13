import { Router, Request, Response, NextFunction } from "express";
import { CreateSecretaria } from "../../../application/useCases/CreateSecretaria";
import { ListSecretarias } from "../../../application/useCases/ListSecretarias";
import { UpdateSecretaria } from "../../../application/useCases/UpdateSecretaria";
import { DeleteSecretaria } from "../../../application/useCases/DeleteSecretaria";
import { SecretariaRepository } from "../../repositories/SecretariaRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const secretariaRoutes = Router();
const secretariaRepository = new SecretariaRepository();
const createSecretaria = new CreateSecretaria(secretariaRepository);
const listSecretarias = new ListSecretarias(secretariaRepository);
const updateSecretaria = new UpdateSecretaria(secretariaRepository);
const deleteSecretaria = new DeleteSecretaria(secretariaRepository);

const asyncHandler = (fn: (req: Request, res: Response, next: NextFunction) => Promise<any>) =>
  (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };

// Middleware de autenticação global
secretariaRoutes.use(ensureAuthenticated);

// Rotas
secretariaRoutes.post(
  "/",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { nome } = req.body;
    const secretaria = await createSecretaria.execute({ nome });
    return res.status(201).json(secretaria);
  })
);

secretariaRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const secretarias = await listSecretarias.execute();
    return res.json(secretarias);
  })
);

secretariaRoutes.put(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const { nome } = req.body;
    const secretaria = await updateSecretaria.execute(Number(id), { nome });
    return res.json(secretaria);
  })
);

secretariaRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    await deleteSecretaria.execute(Number(id));
    return res.status(204).send();
  })
);

export default secretariaRoutes;