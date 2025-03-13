import { Router } from "express";
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

motoristaRoutes.use(ensureAuthenticated);

motoristaRoutes.post("/", ensureAdmin, async (req, res) => {
  try {
    const { cpf, nome, cnh, secretariaId } = req.body;
    const motorista = await createMotorista.execute({ cpf, nome, cnh, secretariaId });
    return res.status(201).json(motorista);
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

motoristaRoutes.get("/", async (req, res) => {
  try {
    const motoristas = await listMotoristas.execute();
    return res.json(motoristas);
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

motoristaRoutes.put("/:id", ensureAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    const { nome, cnh, secretariaId } = req.body;
    const motorista = await updateMotorista.execute(Number(id), { nome, cnh, secretariaId });
    return res.json(motorista);
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

motoristaRoutes.delete("/:id", ensureAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    await deleteMotorista.execute(Number(id));
    return res.status(204).send();
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

export default motoristaRoutes;