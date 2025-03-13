import { Secretaria } from "../entities/Secretaria";

export interface ISecretariaRepository {
  create(secretaria: Secretaria): Promise<Secretaria>;
  findById(id: number): Promise<Secretaria | null>;
  findAll(): Promise<Secretaria[]>;
  update(id: number, secretaria: Partial<Secretaria>): Promise<Secretaria | null>;
  delete(id: number): Promise<void>;
}