import { Router } from "express";
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

veiculoRoutes.use(ensureAuthenticated);

veiculoRoutes.post("/", ensureAdmin, async (req, res) => {
  try {
    const { placa, tipo, secretariaId } = req.body;
    const veiculo = await createVeiculo.execute({ placa, tipo, secretariaId });
    return res.status(201).json(veiculo);
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

veiculoRoutes.get("/", async (req, res) => {
  try {
    const veiculos = await listVeiculos.execute();
    return res.json(veiculos);
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

veiculoRoutes.put("/:id", ensureAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    const { placa, tipo, secretariaId } = req.body;
    const veiculo = await updateVeiculo.execute(Number(id), { placa, tipo, secretariaId });
    return res.json(veiculo);
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

veiculoRoutes.delete("/:id", ensureAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    await deleteVeiculo.execute(Number(id));
    return res.status(204).send();
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

export default veiculoRoutes;