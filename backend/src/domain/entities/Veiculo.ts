export class Veiculo {
    id?: number;
    placa: string;
    tipo: string;
    secretariaId: number;
  
    constructor(placa: string, tipo: string, secretariaId: number) {
      this.placa = placa;
      this.tipo = tipo;
      this.secretariaId = secretariaId;
    }
  }