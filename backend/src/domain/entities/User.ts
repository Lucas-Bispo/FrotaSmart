export class User {
    id?: number;
    cpf: string;
    senha: string;
    isAdmin: boolean;
    createdAt?: Date;
    updatedAt?: Date;
  
    constructor(cpf: string, senha: string, isAdmin: boolean) {
      this.cpf = cpf;
      this.senha = senha;
      this.isAdmin = isAdmin;
    }
  }