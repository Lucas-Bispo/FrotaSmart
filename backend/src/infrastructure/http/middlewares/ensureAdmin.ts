import { Request, Response, NextFunction, RequestHandler } from "express";
import { verify } from "jsonwebtoken";

interface IPayload {
  id: number;
  cpf: string;
  isAdmin: boolean;
}

export const ensureAdmin: RequestHandler = (
  req: Request,
  res: Response,
  next: NextFunction
) => {
  const authHeader = req.headers.authorization;
  if (!authHeader) {
    res.status(401).json({ error: "Token não fornecido" });
    return;
  }

  const [, token] = authHeader.split(" ");
  try {
    const { isAdmin } = verify(token, "secret_key") as IPayload;
    if (!isAdmin) {
      res.status(403).json({ error: "Acesso negado: apenas administradores" });
      return;
    }
    next();
  } catch {
    res.status(401).json({ error: "Token inválido" });
    return;
  }
};