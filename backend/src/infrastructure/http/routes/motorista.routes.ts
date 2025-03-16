import { Router, Request, Response, NextFunction } from "express";
import { MotoristaRepository } from "../../repositories/MotoristaRepository";
import { CreateMotorista } from "../../../application/useCases/CreateMotorista";
import { ListMotoristas } from "../../../application/useCases/ListMotoristas";
import { UpdateMotorista } from "../../../application/useCases/UpdateMotorista";
import { DeleteMotorista } from "../../../application/useCases/DeleteMotorista";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";
import { validateRequest } from "../middlewares/validateRequest";
import { object, string, number } from "yup";
import asyncHandler from "express-async-handler";

const motoristaRoutes = Router();
const motoristaRepository = new MotoristaRepository();
const createMotorista = new CreateMotorista(motoristaRepository);
const listMotoristas = new ListMotoristas(motoristaRepository);
const updateMotorista = new UpdateMotorista(motoristaRepository);
const deleteMotorista = new DeleteMotorista(motoristaRepository);

// Schema de validação para criação de motorista
const createMotoristaSchema = object({
  nome: string().required("Nome é obrigatório").min(2, "Nome deve ter pelo menos 2 caracteres"),
  cpf: string()
    .required("CPF é obrigatório")
    .matches(/^\d{11}$/, "CPF deve ter exatamente 11 dígitos numéricos")
    .test("unique-cpf", "CPF já está em uso", async (value) => {
      const motorista = await motoristaRepository.findByCpf(value);
      return !motorista;
    }),
  cnh: string().required("CNH é obrigatória"),
  secretariaId: number().required("Secretaria ID é obrigatório").integer("Secretaria ID deve ser um número inteiro"),
});

// Schema de validação para atualização de motorista
const updateMotoristaSchema = object({
  nome: string().min(2, "Nome deve ter pelo menos 2 caracteres").optional(),
  cnh: string().optional(),
  secretariaId: number().integer("Secretaria ID deve ser um número inteiro").optional(),
});

motoristaRoutes.use(ensureAuthenticated);

// Listar motoristas
motoristaRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const motoristas = await listMotoristas.execute();
    res.json(motoristas);
  })
);

// Criar motorista
motoristaRoutes.post(
  "/",
  ensureAdmin,
  validateRequest(createMotoristaSchema),
  asyncHandler(async (req: Request<{}, {}, { nome: string; cpf: string; cnh: string; secretariaId: number }>, res: Response) => {
    const { nome, cpf, cnh, secretariaId } = req.body;
    const motorista = await createMotorista.execute({ nome, cpf, cnh, secretariaId });
    res.status(201).json(motorista);
  })
);

// Atualizar motorista
motoristaRoutes.put(
  "/:id",
  ensureAdmin,
  validateRequest(updateMotoristaSchema),
  asyncHandler(async (req: Request<{ id: string }, {}, { nome?: string; cnh?: string; secretariaId?: number }>, res: Response) => {
    const id = parseInt(req.params.id, 10);
    const { nome, cnh, secretariaId } = req.body;
    const motorista = await updateMotorista.execute(id, { nome, cnh, secretariaId });
    res.json(motorista);
  })
);

// Deletar motorista
motoristaRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request<{ id: string }>, res: Response) => {
    const id = parseInt(req.params.id, 10);
    await deleteMotorista.execute(id);
    res.status(204).send();
  })
);

export default motoristaRoutes;