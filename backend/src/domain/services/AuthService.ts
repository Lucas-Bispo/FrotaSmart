import jwt from "jsonwebtoken";
import bcrypt from "bcryptjs";
import { SignOptions } from "jsonwebtoken"; // Importar SignOptions para tipagem

export class AuthService {
  private secret: string = process.env.JWT_SECRET || "seu-segredo-aqui";

  async hashPassword(password: string): Promise<string> {
    return bcrypt.hash(password, 10);
  }

  async comparePassword(password: string, hash: string): Promise<boolean> {
    return bcrypt.compare(password, hash);
  }

  generateToken(userId: number, isAdmin: boolean): string {
    const options: SignOptions = { expiresIn: "1h" }; // Tipagem explícita
    return jwt.sign({ id: userId, isAdmin }, this.secret, options);
  }

  verifyToken(token: string): { id: number; isAdmin: boolean } {
    return jwt.verify(token, this.secret) as { id: number; isAdmin: boolean };
  }
}