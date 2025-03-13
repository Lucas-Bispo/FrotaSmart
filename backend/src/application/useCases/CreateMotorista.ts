import { IMotoristaRepository } from "../../domain/interfaces/IMotoristaRepository";
import { Motorista } from "../../domain/entities/Motorista";
import { ICreateMotoristaDTO } from "../dtos/ICreateMotoristaDTO";

export class CreateMotorista {
  constructor(private motoristaRepository: IMotoristaRepository) {}

  async execute({ cpf, nome, cnh, secretariaId }: ICreateMotoristaDTO): Promise<Motorista> {
    const motoristaExists = await this.motoristaRepository.findByCpf(cpf);
    if (motoristaExists) {
      throw new Error("Motorista já existe");
    }
    const motorista = new Motorista(cpf, nome, cnh, secretariaId);
    return this.motoristaRepository.create(motorista);
  }
}