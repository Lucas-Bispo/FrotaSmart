import { Router, Request, Response, NextFunction } from "express";
import { LocacaoRepository } from "../../repositories/LocacaoRepository";
import { MotoristaRepository } from "../../repositories/MotoristaRepository";
import { VeiculoRepository } from "../../repositories/VeiculoRepository";
import { CreateLocacao } from "../../../application/useCases/CreateLocacao";
import { ListLocacoes } from "../../../application/useCases/ListLocacoes";
import { UpdateLocacao } from "../../../application/useCases/UpdateLocacao";
import { DeleteLocacao } from "../../../application/useCases/DeleteLocacao";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";
import { validateRequest } from "../middlewares/validateRequest";
import { object, number, string } from "yup";
import asyncHandler from "express-async-handler";

const locacaoRoutes = Router();
const locacaoRepository = new LocacaoRepository();
const motoristaRepository = new MotoristaRepository();
const veiculoRepository = new VeiculoRepository();
const createLocacao = new CreateLocacao(locacaoRepository);
const listLocacoes = new ListLocacoes(locacaoRepository);
const updateLocacao = new UpdateLocacao(locacaoRepository);
const deleteLocacao = new DeleteLocacao(locacaoRepository);

// Schema de validação para criação de locação
const createLocacaoSchema = object({
  motoristaId: number()
    .required("Motorista ID é obrigatório")
    .integer("Motorista ID deve ser um número inteiro")
    .positive("Motorista ID deve ser positivo")
    .test("motorista-exists", "Motorista não encontrado", async (value) => {
      const motorista = await motoristaRepository.findById(value);
      return !!motorista;
    }),
  veiculoId: number()
    .required("Veículo ID é obrigatório")
    .integer("Veículo ID deve ser um número inteiro")
    .positive("Veículo ID deve ser positivo")
    .test("veiculo-exists", "Veículo não encontrado", async (value) => {
      const veiculo = await veiculoRepository.findById(value);
      return !!veiculo;
    }),
  dataInicio: string()
    .required("Data de início é obrigatória")
    .matches(/^\d{4}-\d{2}-\d{2}$/, "Data de início deve estar no formato YYYY-MM-DD"),
  destino: string()
    .required("Destino é obrigatório")
    .min(2, "Destino deve ter pelo menos 2 caracteres"),
  dataFim: string()
    .optional()
    .matches(/^\d{4}-\d{2}-\d{2}$/, {
      message: "Data de fim deve estar no formato YYYY-MM-DD",
      excludeEmptyString: true,
    }),
  km: number()
    .optional()
    .integer("KM deve ser um número inteiro")
    .positive("KM deve ser positivo"),
});

// Schema de validação para atualização de locação
const updateLocacaoSchema = object({
  motoristaId: number()
    .integer("Motorista ID deve ser um número inteiro")
    .positive("Motorista ID deve ser positivo")
    .optional()
    .test("motorista-exists", "Motorista não encontrado", async (value) => {
      if (!value) return true;
      const motorista = await motoristaRepository.findById(value);
      return !!motorista;
    }),
  veiculoId: number()
    .integer("Veículo ID deve ser um número inteiro")
    .positive("Veículo ID deve ser positivo")
    .optional()
    .test("veiculo-exists", "Veículo não encontrado", async (value) => {
      if (!value) return true;
      const veiculo = await veiculoRepository.findById(value);
      return !!veiculo;
    }),
  dataInicio: string()
    .matches(/^\d{4}-\d{2}-\d{2}$/, "Data de início deve estar no formato YYYY-MM-DD")
    .optional(),
  destino: string()
    .min(2, "Destino deve ter pelo menos 2 caracteres")
    .optional(),
  dataFim: string()
    .optional()
    .matches(/^\d{4}-\d{2}-\d{2}$/, {
      message: "Data de fim deve estar no formato YYYY-MM-DD",
      excludeEmptyString: true,
    }),
  km: number()
    .integer("KM deve ser um número inteiro")
    .positive("KM deve ser positivo")
    .optional(),
});

locacaoRoutes.use(ensureAuthenticated);

// Listar locações
locacaoRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const locacoes = await listLocacoes.execute();
    res.json(locacoes);
  })
);

// Criar locação
locacaoRoutes.post(
  "/",
  ensureAdmin,
  validateRequest(createLocacaoSchema),
  asyncHandler(async (req: Request<{}, {}, { motoristaId: number; veiculoId: number; dataInicio: string; destino: string; dataFim?: string; km?: number }>, res: Response) => {
    const { motoristaId, veiculoId, dataInicio, destino, dataFim, km } = req.body;
    const locacao = await createLocacao.execute({
      motoristaId,
      veiculoId,
      dataInicio: new Date(dataInicio),
      destino,
      dataFim: dataFim ? new Date(dataFim) : undefined, // Alterado de null para undefined
      km,
    });
    res.status(201).json(locacao);
  })
);

// Atualizar locação
locacaoRoutes.put(
  "/:id",
  ensureAdmin,
  validateRequest(updateLocacaoSchema),
  asyncHandler(async (req: Request<{ id: string }, {}, { motoristaId?: number; veiculoId?: number; dataInicio?: string; destino?: string; dataFim?: string; km?: number }>, res: Response) => {
    const id = parseInt(req.params.id, 10);
    const { motoristaId, veiculoId, dataInicio, destino, dataFim, km } = req.body;
    const locacao = await updateLocacao.execute(id, {
      motoristaId,
      veiculoId,
      dataInicio: dataInicio ? new Date(dataInicio) : undefined,
      destino,
      dataFim: dataFim ? new Date(dataFim) : undefined, // Alterado para evitar null
      km,
    });
    res.json(locacao);
  })
);

// Deletar locação
locacaoRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request<{ id: string }>, res: Response) => {
    const id = parseInt(req.params.id, 10);
    await deleteLocacao.execute(id);
    res.status(204).send();
  })
);

export default locacaoRoutes;