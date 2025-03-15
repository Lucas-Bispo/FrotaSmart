import { IUserRepository } from "../../domain/interfaces/IUserRepository";
import { AuthService } from "../../domain/services/AuthService";

interface ILoginUserDTO {
  cpf: string;
  senha: string;
}

export class LoginUser {
  constructor(
    private userRepository: IUserRepository,
    private authService: AuthService
  ) {}

  async execute({ cpf, senha }: ILoginUserDTO): Promise<{ user: { cpf: string; isAdmin: boolean }; token: string }> {
    const user = await this.userRepository.findByCpf(cpf);
    if (!user || !(await this.authService.comparePassword(senha, user.senha))) {
      throw new Error("CPF ou senha inválidos");
    }
    const token = this.authService.generateToken(user.id, user.isAdmin);
    return { user: { cpf: user.cpf, isAdmin: user.isAdmin }, token };
  }
}