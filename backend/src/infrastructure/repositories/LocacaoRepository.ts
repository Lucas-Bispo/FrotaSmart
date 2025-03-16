import { PrismaClient } from "@prisma/client";
import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";

const prisma = new PrismaClient();

function mapPrismaLocacaoToEntity(prismaLocacao: any): Locacao {
  const locacao = new Locacao(
    prismaLocacao.veiculoId,
    prismaLocacao.motoristaId,
    prismaLocacao.dataInicio,
    prismaLocacao.destino,
    prismaLocacao.dataFim ?? undefined,
    prismaLocacao.km ?? undefined
  );
  locacao.id = prismaLocacao.id; // Atribuir o id após a criação
  return locacao;
}

export class LocacaoRepository implements ILocacaoRepository {
  async create(locacao: Locacao): Promise<Locacao> {
    const created = await prisma.locacao.create({
      data: {
        veiculoId: locacao.veiculoId,
        motoristaId: locacao.motoristaId,
        dataInicio: locacao.dataInicio,
        dataFim: locacao.dataFim ?? null,
        destino: locacao.destino,
        km: locacao.km ?? null,
      },
    });
    return mapPrismaLocacaoToEntity(created);
  }

  async findById(id: number): Promise<Locacao | null> {
    const found = await prisma.locacao.findUnique({ where: { id } });
    return found ? mapPrismaLocacaoToEntity(found) : null;
  }

  async list(): Promise<Locacao[]> {
    const locacoes = await prisma.locacao.findMany();
    return locacoes.map(mapPrismaLocacaoToEntity);
  }

  async update(id: number, locacao: Partial<Locacao>): Promise<Locacao> {
    const updated = await prisma.locacao.update({
      where: { id },
      data: {
        veiculoId: locacao.veiculoId,
        motoristaId: locacao.motoristaId,
        dataInicio: locacao.dataInicio,
        dataFim: locacao.dataFim ?? null,
        destino: locacao.destino,
        km: locacao.km ?? null,
      },
    });
    return mapPrismaLocacaoToEntity(updated);
  }

  async delete(id: number): Promise<void> {
    await prisma.locacao.delete({ where: { id } });
  }

  async findByVeiculoId(veiculoId: number): Promise<Locacao[]> {
    const locacoes = await prisma.locacao.findMany({ where: { veiculoId } });
    return locacoes.map(mapPrismaLocacaoToEntity);
  }
}