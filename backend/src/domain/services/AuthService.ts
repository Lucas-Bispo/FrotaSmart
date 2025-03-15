import jwt from "jsonwebtoken";
import bcrypt from "bcryptjs";

export class AuthService {
  private secret: string = process.env.JWT_SECRET || "seu-segredo-aqui"; // Use .env em produção
  private expiresIn: string = "1h";

  async hashPassword(password: string): Promise<string> {
    return bcrypt.hash(password, 10);
  }

  async comparePassword(password: string, hash: string): Promise<boolean> {
    return bcrypt.compare(password, hash);
  }

  generateToken(userId: number, isAdmin: boolean): string {
    return jwt.sign({ id: userId, isAdmin }, this.secret, { expiresIn: this.expiresIn });
  }

  verifyToken(token: string): { id: number; isAdmin: boolean } {
    return jwt.verify(token, this.secret) as { id: number; isAdmin: boolean };
  }
}