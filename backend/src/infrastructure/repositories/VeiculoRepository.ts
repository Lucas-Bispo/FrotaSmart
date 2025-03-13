import prisma from "../../prisma";
import { IVeiculoRepository } from "../../domain/interfaces/IVeiculoRepository";
import { Veiculo } from "../../domain/entities/Veiculo";

export class VeiculoRepository implements IVeiculoRepository {
  async create(veiculo: Veiculo): Promise<Veiculo> {
    return prisma.veiculo.create({
      data: {
        placa: veiculo.placa,
        tipo: veiculo.tipo,
        secretariaId: veiculo.secretariaId,
      },
    });
  }

  async findById(id: number): Promise<Veiculo | null> {
    return prisma.veiculo.findUnique({ where: { id } });
  }

  async findByPlaca(placa: string): Promise<Veiculo | null> {
    return prisma.veiculo.findUnique({ where: { placa } });
  }

  async list(): Promise<Veiculo[]> {
    return prisma.veiculo.findMany();
  }

  async update(id: number, data: Partial<Veiculo>): Promise<Veiculo> {
    return prisma.veiculo.update({
      where: { id },
      data,
    });
  }

  async delete(id: number): Promise<void> {
    await prisma.veiculo.delete({ where: { id } });
  }
}