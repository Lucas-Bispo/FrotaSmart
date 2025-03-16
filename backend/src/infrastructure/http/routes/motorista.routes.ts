import { Router } from "express";
import { MotoristaRepository } from "../../infra/repositories/MotoristaRepository";
import { CreateMotoristaService } from "../../application/services/CreateMotoristaService";
import { UpdateMotoristaService } from "../../application/services/UpdateMotoristaService";
import { DeleteMotoristaService } from "../../application/services/DeleteMotoristaService";
import { ListMotoristaService } from "../../application/services/ListMotoristaService";
import { GetMotoristaService } from "../../application/services/GetMotoristaService";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";
import { validateRequest } from "../middlewares/validateRequest";
import { object, string } from "yup";
import asyncHandler from "express-async-handler";

const motoristaRoutes = Router();
const motoristaRepository = new MotoristaRepository();
const createMotoristaService = new CreateMotoristaService(motoristaRepository);
const updateMotoristaService = new UpdateMotoristaService(motoristaRepository);
const deleteMotoristaService = new DeleteMotoristaService(motoristaRepository);
const listMotoristaService = new ListMotoristaService(motoristaRepository);
const getMotoristaService = new GetMotoristaService(motoristaRepository);

// Schema de validação para criação de motorista
const createMotoristaSchema = object({
  nome: string().required("Nome é obrigatório").min(2, "Nome deve ter pelo menos 2 caracteres"),
  cpf: string()
    .required("CPF é obrigatório")
    .matches(/^\d{11}$/, "CPF deve ter exatamente 11 dígitos numéricos")
    .test("unique-cpf", "CPF já está em uso", async (value) => {
      const motorista = await motoristaRepository.findByCpf(value);
      return !motorista; // Retorna true se CPF não existir
    }),
});

// Schema de validação para atualização de motorista
const updateMotoristaSchema = object({
  nome: string().min(2, "Nome deve ter pelo menos 2 caracteres").optional(),
  cpf: string()
    .matches(/^\d{11}$/, "CPF deve ter exatamente 11 dígitos numéricos")
    .test("unique-cpf", "CPF já está em uso por outro motorista", async (value, context) => {
      if (!value) return true; // Se CPF não for fornecido, pula a validação
      const motorista = await motoristaRepository.findByCpf(value);
      return !motorista || motorista.id === Number(context.parent.id); // Permite o mesmo CPF se for o mesmo motorista
    })
    .optional(),
});

// Listar motoristas
motoristaRoutes.get(
  "/",
  ensureAuthenticated,
  asyncHandler(async (req: Request, res: Response) => {
    const motoristas = await listMotoristaService.execute();
    res.json(motoristas);
  })
);

// Obter motorista por ID
motoristaRoutes.get(
  "/:id",
  ensureAuthenticated,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const motorista = await getMotoristaService.execute(Number(id));
    res.json(motorista);
  })
);

// Criar motorista
motoristaRoutes.post(
  "/",
  ensureAuthenticated,
  ensureAdmin,
  validateRequest(createMotoristaSchema),
  asyncHandler(async (req: Request, res: Response) => {
    const { nome, cpf } = req.body;
    const motorista = await createMotoristaService.execute({ nome, cpf });
    res.status(201).json(motorista);
  })
);

// Atualizar motorista
motoristaRoutes.put(
  "/:id",
  ensureAuthenticated,
  ensureAdmin,
  validateRequest(updateMotoristaSchema),
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const { nome, cpf } = req.body;
    const motorista = await updateMotoristaService.execute({
      id: Number(id),
      nome,
      cpf,
    });
    res.json(motorista);
  })
);

// Deletar motorista
motoristaRoutes.delete(
  "/:id",
  ensureAuthenticated,
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    await deleteMotoristaService.execute(Number(id));
    res.status(204).send();
  })
);

export { motoristaRoutes };