export interface IUser {
  id: number;
  cpf: string;
  senha: string;
  isAdmin: boolean;
}

export interface IUserRepository {
  create(user: { cpf: string; senha: string; isAdmin: boolean }): Promise<IUser>;
  findByCpf(cpf: string): Promise<IUser | null>;
}