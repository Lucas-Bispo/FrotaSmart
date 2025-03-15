import prisma from "../../prisma";
import { IManutencaoRepository } from "../../domain/interfaces/IManutencaoRepository";
import { Manutencao } from "../../domain/entities/Manutencao";

export class ManutencaoRepository implements IManutencaoRepository {
  async create(manutencao: Manutencao): Promise<Manutencao> {
    const created = await prisma.manutencao.create({
      data: {
        veiculoId: manutencao.veiculoId,
        data: manutencao.data,
        tipo: manutencao.tipo,
        descricao: manutencao.descricao,
        custo: manutencao.custo,
      },
    });
    return new Manutencao(created.veiculoId, created.data, created.tipo, created.descricao, created.custo, created.id);
  }

  async findById(id: number): Promise<Manutencao | null> {
    const found = await prisma.manutencao.findUnique({ where: { id } });
    return found
      ? new Manutencao(found.veiculoId, found.data, found.tipo, found.descricao, found.custo, found.id)
      : null;
  }

  async list(): Promise<Manutencao[]> {
    const manutencoes = await prisma.manutencao.findMany();
    return manutencoes.map(
      (m: { id: number; veiculoId: number; data: Date; tipo: string; descricao?: string; custo?: number }) =>
        new Manutencao(m.veiculoId, m.data, m.tipo, m.descricao, m.custo, m.id)
    );
  }

  async update(id: number, data: Partial<Manutencao>): Promise<Manutencao> {
    const updated = await prisma.manutencao.update({
      where: { id },
      data,
    });
    return new Manutencao(updated.veiculoId, updated.data, updated.tipo, updated.descricao, updated.custo, updated.id);
  }

  async delete(id: number): Promise<void> {
    await prisma.manutencao.delete({ where: { id } });
  }
}