import { Router, Request, Response, NextFunction } from "express";
import { CreateMotorista } from "../../../application/useCases/CreateMotorista";
import { ListMotoristas } from "../../../application/useCases/ListMotoristas";
import { UpdateMotorista } from "../../../application/useCases/UpdateMotorista";
import { DeleteMotorista } from "../../../application/useCases/DeleteMotorista";
import { MotoristaRepository } from "../../repositories/MotoristaRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const motoristaRoutes = Router();
const motoristaRepository = new MotoristaRepository();
const createMotorista = new CreateMotorista(motoristaRepository);
const listMotoristas = new ListMotoristas(motoristaRepository);
const updateMotorista = new UpdateMotorista(motoristaRepository);
const deleteMotorista = new DeleteMotorista(motoristaRepository);

const asyncHandler = (fn: (req: Request, res: Response, next: NextFunction) => Promise<any>) =>
  (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };

motoristaRoutes.use(ensureAuthenticated);

motoristaRoutes.post(
  "/",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { cpf, nome, cnh, secretariaId } = req.body;
    const motorista = await createMotorista.execute({ cpf, nome, cnh, secretariaId });
    return res.status(201).json(motorista);
  })
);

motoristaRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const motoristas = await listMotoristas.execute();
    return res.json(motoristas);
  })
);

motoristaRoutes.put(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const { nome, cnh, secretariaId } = req.body;
    const motorista = await updateMotorista.execute(Number(id), { nome, cnh, secretariaId });
    return res.json(motorista);
  })
);

motoristaRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    await deleteMotorista.execute(Number(id));
    return res.status(204).send();
  })
);

export default motoristaRoutes;