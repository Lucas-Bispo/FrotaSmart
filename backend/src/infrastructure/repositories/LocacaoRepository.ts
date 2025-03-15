import prisma from "../../prisma";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";

export class LocacaoRepository implements ILocacaoRepository {
  async create(locacao: Locacao): Promise<Locacao> {
    const created = await prisma.locacao.create({
      data: {
        veiculoId: locacao.veiculoId,
        motoristaId: locacao.motoristaId,
        dataInicio: locacao.dataInicio,
        dataFim: locacao.dataFim,
        destino: locacao.destino,
        km: locacao.km,
      },
    });
    return new Locacao(
      created.veiculoId,
      created.motoristaId,
      created.dataInicio,
      created.destino,
      created.dataFim,
      created.km,
      created.id
    );
  }

  async findById(id: number): Promise<Locacao | null> {
    const found = await prisma.locacao.findUnique({ where: { id } });
    return found
      ? new Locacao(found.veiculoId, found.motoristaId, found.dataInicio, found.destino, found.dataFim, found.km, found.id)
      : null;
  }

  async list(): Promise<Locacao[]> {
    const locacoes = await prisma.locacao.findMany();
    return locacoes.map(
      (l: { id: number; veiculoId: number; motoristaId: number; dataInicio: Date; dataFim?: Date; destino: string; km?: number }) =>
        new Locacao(l.veiculoId, l.motoristaId, l.dataInicio, l.destino, l.dataFim, l.km, l.id)
    );
  }

  async update(id: number, data: Partial<Locacao>): Promise<Locacao> {
    const updated = await prisma.locacao.update({
      where: { id },
      data,
    });
    return new Locacao(updated.veiculoId, updated.motoristaId, updated.dataInicio, updated.destino, updated.dataFim, updated.km, updated.id);
  }

  async delete(id: number): Promise<void> {
    await prisma.locacao.delete({ where: { id } });
  }
}