import { Request, Response, NextFunction } from "express";
import { verify } from "jsonwebtoken";

interface IPayload {
  id: number;
  cpf: string;
  isAdmin: boolean;
}

export function ensureAdmin(req: Request, res: Response, next: NextFunction) {
  const authHeader = req.headers.authorization;
  if (!authHeader) {
    return res.status(401).json({ error: "Token não fornecido" });
  }

  const [, token] = authHeader.split(" ");
  try {
    const { isAdmin } = verify(token, "secret_key") as IPayload;
    if (!isAdmin) {
      return res.status(403).json({ error: "Acesso negado: apenas administradores" });
    }
    next();
  } catch {
    return res.status(401).json({ error: "Token inválido" });
  }
}