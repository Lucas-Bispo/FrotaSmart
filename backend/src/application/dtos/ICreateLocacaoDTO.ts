export interface ICreateLocacaoDTO {
    veiculoId: number;
    motoristaId: number;
    dataInicio: Date;
    destino: string;
    dataFim?: Date;
    km?: number;
  }