import { IUserRepository } from "../../domain/interfaces/IUserRepository";
import { AuthService } from "../../domain/services/AuthService";

interface ICreateUserDTO {
  cpf: string;
  senha: string;
  isAdmin?: boolean;
}

export class CreateUser {
  constructor(
    private userRepository: IUserRepository,
    private authService: AuthService
  ) {}

  async execute({ cpf, senha, isAdmin = false }: ICreateUserDTO): Promise<{ user: { cpf: string; isAdmin: boolean }; token: string }> {
    const userExists = await this.userRepository.findByCpf(cpf);
    if (userExists) {
      throw new Error("Usuário já existe");
    }
    const hashedPassword = await this.authService.hashPassword(senha);
    const user = await this.userRepository.create({ cpf, senha: hashedPassword, isAdmin });
    const token = this.authService.generateToken(user.id, user.isAdmin);
    return { user: { cpf: user.cpf, isAdmin: user.isAdmin }, token };
  }
}