import { Secretaria } from "../../domain/entities/Secretaria";
import { ISecretariaRepository } from "../../domain/interfaces/ISecretariaRepository";

export class ListSecretarias {
  constructor(private secretariaRepository: ISecretariaRepository) {}

  async execute(): Promise<Secretaria[]> {
    return this.secretariaRepository.findAll();
  }
}