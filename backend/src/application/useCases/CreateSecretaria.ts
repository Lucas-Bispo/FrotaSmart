import { Secretaria } from "../../domain/entities/Secretaria";
import { ISecretariaRepository } from "../../domain/interfaces/ISecretariaRepository";

interface ICreateSecretariaDTO {
  nome: string;
}

export class CreateSecretaria {
  constructor(private secretariaRepository: ISecretariaRepository) {}

  async execute({ nome }: ICreateSecretariaDTO): Promise<Secretaria> {
    const secretaria = new Secretaria(nome);
    return this.secretariaRepository.create(secretaria);
  }
}