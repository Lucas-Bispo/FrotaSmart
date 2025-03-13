import { Motorista } from "../entities/Motorista";

export interface IMotoristaRepository {
  create(motorista: Motorista): Promise<Motorista>;
  findById(id: number): Promise<Motorista | null>;
  findByCpf(cpf: string): Promise<Motorista | null>;
  list(): Promise<Motorista[]>;
  update(id: number, motorista: Partial<Motorista>): Promise<Motorista>;
  delete(id: number): Promise<void>;
}