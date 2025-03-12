import { Router } from "express";
import { AuthenticateUser } from "../../../application/useCases/AuthenticateUser";
import { CreateUser } from "../../../application/useCases/CreateUser";
import { UserRepository } from "../../repositories/UserRepository";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const authRoutes = Router();
const userRepository = new UserRepository();
const authenticateUser = new AuthenticateUser(userRepository);
const createUser = new CreateUser(userRepository);

authRoutes.post("/login", async (req, res) => {
  try {
    const { cpf, senha } = req.body;
    const token = await authenticateUser.execute({ cpf, senha });
    return res.json({ token });
  } catch (error) {
    return res.status(401).json({ error: (error as Error).message });
  }
});

authRoutes.post("/users", ensureAdmin, async (req, res) => {
  try {
    const { cpf, senha, isAdmin } = req.body;
    const user = await createUser.execute({ cpf, senha, isAdmin });
    return res.status(201).json(user);
  } catch (error) {
    return res.status(400).json({ error: (error as Error).message });
  }
});

export default authRoutes;