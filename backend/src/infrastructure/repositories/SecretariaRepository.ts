import prisma from "../../prisma";
import { ISecretariaRepository } from "../../domain/interfaces/ISecretariaRepository";
import { Secretaria } from "../../domain/entities/Secretaria";

export class SecretariaRepository implements ISecretariaRepository {
  async create(secretaria: Secretaria): Promise<Secretaria> {
    return prisma.secretaria.create({
      data: { nome: secretaria.nome },
    });
  }

  async findById(id: number): Promise<Secretaria | null> {
    return prisma.secretaria.findUnique({ where: { id } });
  }
}