import prisma from "../../prisma";
import { IUser, IUserRepository } from "../../domain/interfaces/IUserRepository";

export class UserRepository implements IUserRepository {
  async create(user: { cpf: string; senha: string; isAdmin: boolean }): Promise<IUser> {
    const created = await prisma.user.create({
      data: {
        cpf: user.cpf,
        senha: user.senha,
        isAdmin: user.isAdmin,
      },
    });
    return { id: created.id, cpf: created.cpf, senha: created.senha, isAdmin: created.isAdmin };
  }

  async findByCpf(cpf: string): Promise<IUser | null> {
    const found = await prisma.user.findUnique({ where: { cpf } });
    return found ? { id: found.id, cpf: found.cpf, senha: found.senha, isAdmin: found.isAdmin } : null;
  }
}