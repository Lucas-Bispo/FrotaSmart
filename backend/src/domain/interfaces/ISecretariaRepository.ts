import { Secretaria } from "../entities/Secretaria";

export interface ISecretariaRepository {
  create(secretaria: Secretaria): Promise<Secretaria>;
  findById(id: number): Promise<Secretaria | null>;
}