import { Secretaria } from "../../domain/entities/Secretaria";
import { ISecretariaRepository } from "../../domain/interfaces/ISecretariaRepository";
import prisma from "../../prisma";


export class SecretariaRepository implements ISecretariaRepository {
  async create(secretaria: Secretaria): Promise<Secretaria> {
    const created = await prisma.secretaria.create({
      data: {
        nome: secretaria.nome,
      },
    });
    return new Secretaria(created.nome, created.id);
  }

  async findById(id: number): Promise<Secretaria | null> {
    const found = await prisma.secretaria.findUnique({
      where: { id },
    });
    return found ? new Secretaria(found.nome, found.id) : null;
  }

  async findAll(): Promise<Secretaria[]> {
    const secretarias = await prisma.secretaria.findMany();
    return secretarias.map((s) => new Secretaria(s.nome, s.id));
  }

  async update(id: number, data: Partial<Secretaria>): Promise<Secretaria | null> {
    const updated = await prisma.secretaria.update({
      where: { id },
      data: { nome: data.nome },
    });
    return updated ? new Secretaria(updated.nome, updated.id) : null;
  }

  async delete(id: number): Promise<void> {
    await prisma.secretaria.delete({
      where: { id },
    });
  }
}