import prisma from "../../prisma";
import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Multa } from "../../domain/entities/Multa";

export class MultaRepository implements IMultaRepository {
  async create(multa: Multa): Promise<Multa> {
    const created = await prisma.multa.create({
      data: {
        veiculoId: multa.veiculoId,
        motoristaId: multa.motoristaId,
        data: multa.data,
        tipo: multa.tipo,
        valor: multa.valor,
        descricao: multa.descricao,
      },
    });
    return new Multa(
      created.veiculoId,
      created.motoristaId,
      created.data,
      created.tipo,
      created.valor,
      created.descricao,
      created.id
    );
  }

  async findById(id: number): Promise<Multa | null> {
    const found = await prisma.multa.findUnique({ where: { id } });
    return found
      ? new Multa(
          found.veiculoId,
          found.motoristaId,
          found.data,
          found.tipo,
          found.valor,
          found.descricao,
          found.id
        )
      : null;
  }

  async list(): Promise<Multa[]> {
    const multas = await prisma.multa.findMany();
    return multas.map((m) =>
      new Multa(
        m.veiculoId,
        m.motoristaId,
        m.data,
        m.tipo,
        m.valor,
        m.descricao,
        m.id
      )
    );
  }

  async update(id: number, data: Partial<Multa>): Promise<Multa> {
    const updated = await prisma.multa.update({
      where: { id },
      data,
    });
    return new Multa(
      updated.veiculoId,
      updated.motoristaId,
      updated.data,
      updated.tipo,
      updated.valor,
      updated.descricao,
      updated.id
    );
  }

  async delete(id: number): Promise<void> {
    await prisma.multa.delete({ where: { id } });
  }
}