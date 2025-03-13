export class Secretaria {
  id: number;
  nome: string;

  constructor(nome: string, id?: number) {
    this.id = id || Math.floor(Math.random() * 1000); // ID temporário para repositório em memória
    this.nome = nome;
  }
}