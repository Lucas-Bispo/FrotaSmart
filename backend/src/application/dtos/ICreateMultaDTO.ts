export interface ICreateMultaDTO {
    veiculoId: number;
    motoristaId: number | null; // Alterado de ? (undefined) para null
    data: Date;
    tipo: string;
    valor: number;
    descricao: string | null; // Alinhado com a entidade
  }