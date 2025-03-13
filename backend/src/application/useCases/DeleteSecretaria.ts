import { ISecretariaRepository } from "../../domain/interfaces/ISecretariaRepository";

export class DeleteSecretaria {
  constructor(private secretariaRepository: ISecretariaRepository) {}

  async execute(id: number): Promise<void> {
    const secretaria = await this.secretariaRepository.findById(id);
    if (!secretaria) {
      throw new Error("Secretaria não encontrada");
    }
    await this.secretariaRepository.delete(id);
  }
}