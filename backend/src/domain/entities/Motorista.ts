export class Motorista {
    id?: number;
    cpf: string;
    nome: string;
    cnh: string;
    secretariaId: number;
  
    constructor(cpf: string, nome: string, cnh: string, secretariaId: number) {
      this.cpf = cpf;
      this.nome = nome;
      this.cnh = cnh;
      this.secretariaId = secretariaId;
    }
  }