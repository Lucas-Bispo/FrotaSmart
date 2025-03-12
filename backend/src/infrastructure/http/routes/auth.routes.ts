import { Router, RequestHandler } from "express";
import { AuthenticateUser } from "../../../application/useCases/AuthenticateUser";
import { CreateUser } from "../../../application/useCases/CreateUser";
import { UserRepository } from "../../repositories/UserRepository";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const authRoutes = Router();
const userRepository = new UserRepository();
const authenticateUser = new AuthenticateUser(userRepository);
const createUser = new CreateUser(userRepository);

// Handler para login
const loginHandler: RequestHandler = async (req, res) => {
  try {
    const { cpf, senha } = req.body;
    const token = await authenticateUser.execute({ cpf, senha });
    res.json({ token });
  } catch (error) {
    res.status(401).json({ error: (error as Error).message });
  }
};

// Handler para criar usuário
const createUserHandler: RequestHandler = async (req, res) => {
  try {
    const { cpf, senha, isAdmin } = req.body;
    const user = await createUser.execute({ cpf, senha, isAdmin });
    res.status(201).json(user);
  } catch (error) {
    res.status(400).json({ error: (error as Error).message });
  }
};

// Rotas
authRoutes.post("/login", loginHandler);
authRoutes.post("/users", [ensureAdmin, createUserHandler]);

export default authRoutes;