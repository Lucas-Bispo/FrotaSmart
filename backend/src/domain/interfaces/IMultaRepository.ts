import { Multa } from "../entities/Multa";

export interface IMultaRepository {
  create(multa: Multa): Promise<Multa>;
  findById(id: number): Promise<Multa | null>;
  list(): Promise<Multa[]>;
  update(id: number, multa: Partial<Multa>): Promise<Multa>;
  delete(id: number): Promise<void>;
}