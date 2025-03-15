import { Request, Response, NextFunction } from "express";

export function ensureAdmin(req: Request, res: Response, next: NextFunction): void {
  if (!req.user || !req.user.isAdmin) {
    res.status(403).json({ error: "Acesso negado: apenas administradores" });
    return;
  }
  next();
}