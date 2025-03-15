import { IMultaRepository } from "../../domain/interfaces/IMultaRepository";
import { Multa } from "../../domain/entities/Multa";

export class ListMultas {
  constructor(private multaRepository: IMultaRepository) {}

  async execute(): Promise<Multa[]> {
    return this.multaRepository.list();
  }
}