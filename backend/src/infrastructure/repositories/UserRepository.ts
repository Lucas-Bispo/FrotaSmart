import prisma from "../../prisma";
import { IUserRepository } from "../../domain/interfaces/IUserRepository";
import { User } from "../../domain/entities/User";

export class UserRepository implements IUserRepository {
  async findByCpf(cpf: string): Promise<User | null> {
    return prisma.user.findUnique({ where: { cpf } });
  }

  async create(user: User): Promise<User> {
    return prisma.user.create({
      data: {
        cpf: user.cpf,
        senha: user.senha,
        isAdmin: user.isAdmin,
      },
    });
  }
}