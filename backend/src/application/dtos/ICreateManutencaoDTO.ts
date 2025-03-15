export interface ICreateManutencaoDTO {
    veiculoId: number;
    data: Date;
    tipo: string;
    descricao?: string;
    custo?: number;
  }