import prisma from "../../prisma";
import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { Motorista } from "../../domain/entities/Motorista";

export class MotoristaRepository implements IMotoristaRepository {
  async create(motorista: Motorista): Promise<Motorista> {
    return prisma.motorista.create({
      data: {
        cpf: motorista.cpf,
        nome: motorista.nome,
        cnh: motorista.cnh,
        secretariaId: motorista.secretariaId,
      },
    });
  }

  async findById(id: number): Promise<Motorista | null> {
    return prisma.motorista.findUnique({ where: { id } });
  }

  async findByCpf(cpf: string): Promise<Motorista | null> {
    return prisma.motorista.findUnique({ where: { cpf } });
  }

  async list(): Promise<Motorista[]> {
    return prisma.motorista.findMany();
  }

  async update(id: number, data: Partial<Motorista>): Promise<Motorista> {
    return prisma.motorista.update({
      where: { id },
      data,
    });
  }

  async delete(id: number): Promise<void> {
    await prisma.motorista.delete({ where: { id } });
  }
}