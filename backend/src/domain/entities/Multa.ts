export class Multa {
    id?: number;
    veiculoId: number;
    motoristaId: number | null; // Aqui está como number | null
    data: Date;
    tipo: string;
    valor: number;
    descricao: string | null;
  
    constructor(
      veiculoId: number,
      motoristaId: number | null,
      data: Date,
      tipo: string,
      valor: number,
      descricao: string | null,
      id?: number
    ) {
      this.veiculoId = veiculoId;
      this.motoristaId = motoristaId;
      this.data = data;
      this.tipo = tipo;
      this.valor = valor;
      this.descricao = descricao;
      this.id = id;
    }
  }