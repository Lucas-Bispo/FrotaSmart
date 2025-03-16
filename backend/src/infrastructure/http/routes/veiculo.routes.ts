import { Router, Request, Response, NextFunction } from "express";
import { VeiculoRepository } from "../../repositories/VeiculoRepository";
import { CreateVeiculo } from "../../../application/useCases/CreateVeiculo";
import { ListVeiculos } from "../../../application/useCases/ListVeiculos";
import { UpdateVeiculo } from "../../../application/useCases/UpdateVeiculo";
import { DeleteVeiculo } from "../../../application/useCases/DeleteVeiculo";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";
import { validateRequest } from "../middlewares/validateRequest";
import { object, string, number } from "yup";
import asyncHandler from "express-async-handler";

const veiculoRoutes = Router();
const veiculoRepository = new VeiculoRepository();
const createVeiculo = new CreateVeiculo(veiculoRepository);
const listVeiculos = new ListVeiculos(veiculoRepository);
const updateVeiculo = new UpdateVeiculo(veiculoRepository);
const deleteVeiculo = new DeleteVeiculo(veiculoRepository);

// Schema de validação para criação de veículo
const createVeiculoSchema = object({
  placa: string()
    .required("Placa é obrigatória")
    .matches(/^[A-Z]{3}-?\d{4}$|^[A-Z]{3}\d[A-Z]\d{2}$/, "Placa deve seguir o padrão ABC-1234 ou ABC1D23")
    .test("unique-placa", "Placa já está em uso", async (value) => {
      const veiculo = await veiculoRepository.findByPlaca(value);
      return !veiculo;
    }),
  tipo: string().required("Tipo é obrigatório").min(2, "Tipo deve ter pelo menos 2 caracteres"), // Substituído 'modelo' por 'tipo'
  secretariaId: number().required("Secretaria ID é obrigatório").integer("Secretaria ID deve ser um número inteiro"),
  // 'ano' foi removido
});

// Schema de validação para atualização de veículo
const updateVeiculoSchema = object({
  placa: string()
    .matches(/^[A-Z]{3}-?\d{4}$|^[A-Z]{3}\d[A-Z]\d{2}$/, "Placa deve seguir o padrão ABC-1234 ou ABC1D23")
    .optional()
    .test("unique-placa", "Placa já está em uso por outro veículo", async (value, context) => {
      if (!value) return true;
      const veiculo = await veiculoRepository.findByPlaca(value);
      const id = parseInt(context.options.context?.req?.params?.id || "0", 10);
      return !veiculo || veiculo.id === id;
    }),
  tipo: string().min(2, "Tipo deve ter pelo menos 2 caracteres").optional(), // Substituído 'modelo' por 'tipo'
  secretariaId: number().integer("Secretaria ID deve ser um número inteiro").optional(),
  // 'ano' foi removido
});

veiculoRoutes.use(ensureAuthenticated);

// Listar veículos
veiculoRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const veiculos = await listVeiculos.execute();
    res.json(veiculos);
  })
);

// Criar veículo
veiculoRoutes.post(
  "/",
  ensureAdmin,
  validateRequest(createVeiculoSchema),
  asyncHandler(async (req: Request<{}, {}, { placa: string; tipo: string; secretariaId: number }>, res: Response) => {
    const { placa, tipo, secretariaId } = req.body;
    const veiculo = await createVeiculo.execute({ placa, tipo, secretariaId });
    res.status(201).json(veiculo);
  })
);

// Atualizar veículo
veiculoRoutes.put(
  "/:id",
  ensureAdmin,
  validateRequest(updateVeiculoSchema),
  asyncHandler(async (req: Request<{ id: string }, {}, { placa?: string; tipo?: string; secretariaId?: number }>, res: Response) => {
    const id = parseInt(req.params.id, 10);
    const { placa, tipo, secretariaId } = req.body;
    const veiculo = await updateVeiculo.execute(id, { placa, tipo, secretariaId }); // Linha 81
    res.json(veiculo);
  })
);

// Deletar veículo
veiculoRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request<{ id: string }>, res: Response) => {
    const id = parseInt(req.params.id, 10);
    await deleteVeiculo.execute(id);
    res.status(204).send();
  })
);

export default veiculoRoutes;