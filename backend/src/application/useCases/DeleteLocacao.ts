import { ILocacaoRepository } from "../../domain/interfaces/ILocacaoRepository";

export class DeleteLocacao {
  constructor(private locacaoRepository: ILocacaoRepository) {}

  async execute(id: number): Promise<void> {
    const locacao = await this.locacaoRepository.findById(id);
    if (!locacao) {
      throw new Error("Locação não encontrada");
    }
    await this.locacaoRepository.delete(id);
  }
}