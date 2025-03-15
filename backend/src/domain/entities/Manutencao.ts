export class Manutencao {
    id?: number;
    veiculoId: number;
    data: Date;
    tipo: string;
    descricao?: string;
    custo?: number;
  
    constructor(veiculoId: number, data: Date, tipo: string, descricao?: string, custo?: number) {
      this.veiculoId = veiculoId;
      this.data = data;
      this.tipo = tipo;
      this.descricao = descricao;
      this.custo = custo;
    }
  }