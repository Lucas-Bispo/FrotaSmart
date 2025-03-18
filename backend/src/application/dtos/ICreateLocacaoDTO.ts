export interface ICreateLocacaoDTO {
  motoristaId: number;
  veiculoId: number;
  dataInicio: Date;
  destino: string;
  dataFim: Date | undefined; // Aqui está o tipo esperado
  km: number;
}