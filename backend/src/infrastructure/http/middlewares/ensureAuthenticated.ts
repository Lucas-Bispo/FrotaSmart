import { Request, Response, NextFunction } from "express";
import { AuthService } from "../../../domain/services/AuthService";

export function ensureAuthenticated(req: Request, res: Response, next: NextFunction) {
  const authHeader = req.headers.authorization;
  if (!authHeader) {
    return res.status(401).json({ error: "Token não fornecido" });
  }

  const [, token] = authHeader.split("Bearer ");
  try {
    const authService = new AuthService();
    const decoded = authService.verifyToken(token);
    req.user = { id: decoded.id, isAdmin: decoded.isAdmin };
    next();
  } catch (err) {
    return res.status(401).json({ error: "Token inválido" });
  }
}

declare module "express" {
  interface Request {
    user?: { id: number; isAdmin: boolean };
  }
}