export interface ICreateVeiculoDTO {
  placa: string;         // A placa do veículo (ex.: "ABC-1234")
  tipo: string;          // O tipo do veículo (ex.: "Carro", "Caminhão")
  secretariaId: number;  // O ID da secretaria à qual o veículo pertence
}