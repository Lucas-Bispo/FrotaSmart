import { Secretaria } from "../../domain/entities/Secretaria";
import { ISecretariaRepository } from "../../domain/interfaces/ISecretariaRepository";

export class SecretariaRepository implements ISecretariaRepository {
  private secretarias: Secretaria[] = [];

  async create(secretaria: Secretaria): Promise<Secretaria> {
    this.secretarias.push(secretaria);
    return secretaria;
  }

  async findById(id: number): Promise<Secretaria | null> {
    const secretaria = this.secretarias.find((s) => s.id === id);
    return secretaria || null;
  }

  async findAll(): Promise<Secretaria[]> {
    return this.secretarias;
  }

  async update(id: number, secretaria: Partial<Secretaria>): Promise<Secretaria | null> {
    const index = this.secretarias.findIndex((s) => s.id === id);
    if (index === -1) return null;
    this.secretarias[index] = { ...this.secretarias[index], ...secretaria };
    return this.secretarias[index];
  }

  async delete(id: number): Promise<void> {
    this.secretarias = this.secretarias.filter((s) => s.id !== id);
  }
}