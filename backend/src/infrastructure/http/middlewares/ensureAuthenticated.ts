import { Request, Response, NextFunction } from "express";
import { verify } from "jsonwebtoken";

interface IPayload {
  id: number;
  cpf: string;
  isAdmin: boolean;
}

export function ensureAuthenticated(req: Request, res: Response, next: NextFunction): void {
  const authHeader = req.headers.authorization;
  if (!authHeader) {
    res.status(401).json({ error: "Token não fornecido" });
    return;
  }

  const [, token] = authHeader.split(" ");
  try {
    const decoded = verify(token, "secret_key") as IPayload;
    req.user = decoded;
    next();
  } catch {
    res.status(401).json({ error: "Token inválido" });
    return;
  }
}