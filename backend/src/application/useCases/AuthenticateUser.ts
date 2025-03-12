import { compare } from "bcryptjs";
import { sign } from "jsonwebtoken";
import { IUserRepository } from "../../domain/interfaces/IUserRepository";
import { IAuthenticateUserDTO } from "../dtos/IAuthenticateUserDTO";

export class AuthenticateUser {
  constructor(private userRepository: IUserRepository) {}

  async execute({ cpf, senha }: IAuthenticateUserDTO): Promise<string> {
    const user = await this.userRepository.findByCpf(cpf);
    if (!user || !(await compare(senha, user.senha))) {
      throw new Error("CPF ou senha inválidos");
    }

    const token = sign({ id: user.id, cpf: user.cpf, isAdmin: user.isAdmin }, "secret_key", {
      expiresIn: "1h",
    });
    return token;
  }
}