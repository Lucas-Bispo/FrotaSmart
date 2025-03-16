import { Locacao } from "../entities/Locacao";

export interface ILocacaoRepository {
  create(locacao: Locacao): Promise<Locacao>;
  findById(id: number): Promise<Locacao | null>;
  list(): Promise<Locacao[]>;
  update(id: number, locacao: Partial<Locacao>): Promise<Locacao>;
  delete(id: number): Promise<void>;
  findByVeiculoId(veiculoId: number): Promise<Locacao[]>; // Novo método
}