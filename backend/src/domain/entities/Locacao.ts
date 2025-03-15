export class Locacao {
    id?: number;
    veiculoId: number;
    motoristaId: number;
    dataInicio: Date;
    dataFim?: Date;
    destino: string;
    km?: number;
  
    constructor(veiculoId: number, motoristaId: number, dataInicio: Date, destino: string, dataFim?: Date, km?: number) {
      this.veiculoId = veiculoId;
      this.motoristaId = motoristaId;
      this.dataInicio = dataInicio;
      this.dataFim = dataFim;
      this.destino = destino;
      this.km = km;
    }
  }