import { Router, Request, Response, NextFunction } from "express";
import { CreateLocacao } from "../../../application/useCases/CreateLocacao";
import { ListLocacoes } from "../../../application/useCases/ListLocacoes";
import { UpdateLocacao } from "../../../application/useCases/UpdateLocacao";
import { DeleteLocacao } from "../../../application/useCases/DeleteLocacao";
import { LocacaoRepository } from "../../repositories/LocacaoRepository";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const locacaoRoutes = Router();
const locacaoRepository = new LocacaoRepository();
const createLocacao = new CreateLocacao(locacaoRepository);
const listLocacoes = new ListLocacoes(locacaoRepository);
const updateLocacao = new UpdateLocacao(locacaoRepository);
const deleteLocacao = new DeleteLocacao(locacaoRepository);

const asyncHandler = (fn: (req: Request, res: Response, next: NextFunction) => Promise<any>) =>
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      await fn(req, res, next);
    } catch (error) {
      next(error);
    }
  };

locacaoRoutes.use(ensureAuthenticated);

locacaoRoutes.post(
  "/",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { veiculoId, motoristaId, dataInicio, destino, dataFim, km } = req.body;
    const locacao = await createLocacao.execute({
      veiculoId,
      motoristaId,
      dataInicio: new Date(dataInicio),
      destino,
      dataFim: dataFim ? new Date(dataFim) : undefined,
      km,
    });
    res.status(201).json(locacao);
  })
);

locacaoRoutes.get(
  "/",
  asyncHandler(async (req: Request, res: Response) => {
    const locacoes = await listLocacoes.execute();
    res.json(locacoes);
  })
);

locacaoRoutes.put(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    const { veiculoId, motoristaId, dataInicio, dataFim, destino, km } = req.body;
    const locacao = await updateLocacao.execute(Number(id), {
      veiculoId,
      motoristaId,
      dataInicio: dataInicio ? new Date(dataInicio) : undefined,
      dataFim: dataFim ? new Date(dataFim) : undefined,
      destino,
      km,
    });
    res.json(locacao);
  })
);

locacaoRoutes.delete(
  "/:id",
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { id } = req.params;
    await deleteLocacao.execute(Number(id));
    res.status(204).send();
  })
);

export default locacaoRoutes;