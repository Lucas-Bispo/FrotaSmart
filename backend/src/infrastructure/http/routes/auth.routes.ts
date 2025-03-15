import { Router, Request, Response, NextFunction } from "express";
import { CreateUser } from "../../../application/useCases/CreateUser";
import { LoginUser } from "../../../application/useCases/LoginUser";
import { UserRepository } from "../../repositories/UserRepository";
import { AuthService } from "../../../domain/services/AuthService";
import { ensureAuthenticated } from "../middlewares/ensureAuthenticated";
import { ensureAdmin } from "../middlewares/ensureAdmin";

const authRoutes = Router();
const userRepository = new UserRepository();
const authService = new AuthService();
const createUser = new CreateUser(userRepository, authService);
const loginUser = new LoginUser(userRepository, authService);

const asyncHandler = (fn: (req: Request, res: Response, next: NextFunction) => Promise<any>) =>
  (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };

// Criar usuário (apenas admin)
authRoutes.post(
  "/users",
  ensureAuthenticated,
  ensureAdmin,
  asyncHandler(async (req: Request, res: Response) => {
    const { cpf, senha, isAdmin } = req.body;
    const result = await createUser.execute({ cpf, senha, isAdmin });
    return res.status(201).json(result);
  })
);

// Login
authRoutes.post(
  "/login",
  asyncHandler(async (req: Request, res: Response) => {
    const { cpf, senha } = req.body;
    const result = await loginUser.execute({ cpf, senha });
    return res.json(result);
  })
);

export default authRoutes;