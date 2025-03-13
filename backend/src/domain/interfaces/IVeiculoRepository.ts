import { Veiculo } from "../entities/Veiculo";

export interface IVeiculoRepository {
  create(veiculo: Veiculo): Promise<Veiculo>;
  findById(id: number): Promise<Veiculo | null>;
  findByPlaca(placa: string): Promise<Veiculo | null>;
  list(): Promise<Veiculo[]>;
  update(id: number, veiculo: Partial<Veiculo>): Promise<Veiculo>;
  delete(id: number): Promise<void>;
}