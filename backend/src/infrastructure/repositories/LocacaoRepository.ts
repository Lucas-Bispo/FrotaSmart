import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";
import { Locacao } from "../../domain/entities/Locacao";

export class LocacaoRepository implements ILocacaoRepository {
  // Implementação com seu ORM ou banco de dados
  async create(locacao: Locacao): Promise<Locacao> {
    // Exemplo: return prisma.locacao.create({ data: locacao });
    throw new Error("Método não implementado");
  }

  async findById(id: number): Promise<Locacao | null> {
    // Exemplo: return prisma.locacao.findUnique({ where: { id } });
    throw new Error("Método não implementado");
  }

  async list(): Promise<Locacao[]> {
    // Exemplo: return prisma.locacao.findMany();
    throw new Error("Método não implementado");
  }

  async update(id: number, locacao: Partial<Locacao>): Promise<Locacao> {
    // Exemplo: return prisma.locacao.update({ where: { id }, data: locacao });
    throw new Error("Método não implementado");
  }

  async delete(id: number): Promise<void> {
    // Exemplo: await prisma.locacao.delete({ where: { id } });
    throw new Error("Método não implementado");
  }

  async findByVeiculoId(veiculoId: number): Promise<Locacao[]> {
    // Exemplo: return prisma.locacao.findMany({ where: { veiculoId } });
    throw new Error("Método não implementado");
  }
}