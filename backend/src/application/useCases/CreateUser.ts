import { hash } from "bcryptjs";
import { IUserRepository } from "../../domain/interfaces/IUserRepository";
import { ICreateUserDTO } from "../dtos/ICreateUserDTO";
import { User } from "../../domain/entities/User";

export class CreateUser {
  constructor(private userRepository: IUserRepository) {}

  async execute({ cpf, senha, isAdmin = false }: ICreateUserDTO): Promise<User> {
    const userExists = await this.userRepository.findByCpf(cpf);
    if (userExists) {
      throw new Error("Usuário já existe");
    }

    const hashedSenha = await hash(senha, 10);
    const user = new User(cpf, hashedSenha, isAdmin);
    return this.userRepository.create(user);
  }
}