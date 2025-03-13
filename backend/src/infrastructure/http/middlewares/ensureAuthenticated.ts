import { Request, Response, NextFunction } from "express";
import { verify } from "jsonwebtoken";

interface IPayload {
  id: number;
  cpf: string;
  isAdmin: boolean;
}

export function ensureAuthenticated(req: Request, res: Response, next: NextFunction) {
  const authHeader = req.headers.authorization;
  if (!authHeader) {
    return res.status(401).json({ error: "Token não fornecido" });
  }

  const [, token] = authHeader.split(" ");
  try {
    const decoded = verify(token, "secret_key") as IPayload;
    req.user = decoded; // Adiciona o usuário ao request para uso posterior
    next();
  } catch {
    return res.status(401).json({ error: "Token inválido" });
  }
}