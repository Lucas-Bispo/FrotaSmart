import { Secretaria } from "../../domain/entities/Secretaria";
import { ISecretariaRepository } from "../../domain/interfaces/ISecretariaRepository";

interface IUpdateSecretariaDTO {
  nome?: string;
}

export class UpdateSecretaria {
  constructor(private secretariaRepository: ISecretariaRepository) {}

  async execute(id: number, data: IUpdateSecretariaDTO): Promise<Secretaria> {
    const secretaria = await this.secretariaRepository.findById(id);
    if (!secretaria) {
      throw new Error("Secretaria não encontrada");
    }
    const updatedSecretaria = await this.secretariaRepository.update(id, data);
    if (!updatedSecretaria) {
      throw new Error("Erro ao atualizar secretaria");
    }
    return updatedSecretaria;
  }
}