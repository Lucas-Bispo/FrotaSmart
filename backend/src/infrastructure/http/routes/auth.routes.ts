import { Router, Request, Response, NextFunction } from "express";
import { AuthenticateUser } from "../../../application/useCases/AuthenticateUser";
import { CreateUser } from "../../../application/useCases/CreateUser";
import { UserRepository } from "../../repositories/UserRepository";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const authRoutes = Router();
const userRepository = new UserRepository();
const authenticateUser = new AuthenticateUser(userRepository);
const createUser = new CreateUser(userRepository);

// Login
authRoutes.post("/login", async (req: Request, res: Response) => {
  try {
    const { cpf, senha } = req.body;
    const token = await authenticateUser.execute({ cpf, senha });
    return res.json({ token });
  } catch (error) {
    return res.status(401).json({ error: (error as Error).message });
  }
});

// Criar usuário (restrito a admin)
authRoutes.post(
  "/users",
  ensureAdmin,
  async (req: Request, res: Response) => {
    try {
      const { cpf, senha, isAdmin } = req.body;
      const user = await createUser.execute({ cpf, senha, isAdmin });
      return res.status(201).json(user);
    } catch (error) {
      return res.status(400).json({ error: (error as Error).message });
    }
  }
);

export default authRoutes;