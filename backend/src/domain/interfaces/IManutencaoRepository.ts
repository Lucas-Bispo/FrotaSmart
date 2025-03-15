import { Manutencao } from "../entities/Manutencao";

export interface IManutencaoRepository {
  create(manutencao: Manutencao): Promise<Manutencao>;
  findById(id: number): Promise<Manutencao | null>;
  list(): Promise<Manutencao[]>;
  update(id: number, manutencao: Partial<Manutencao>): Promise<Manutencao>;
  delete(id: number): Promise<void>;
}