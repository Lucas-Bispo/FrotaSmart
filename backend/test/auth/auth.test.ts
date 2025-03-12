import { describe, it, expect, beforeAll, afterAll } from "vitest";
import { UserRepository } from "../../src/infrastructure/repositories/UserRepository";
import { AuthenticateUser } from "../../src/application/useCases/AuthenticateUser";
import { CreateUser } from "../../src/application/useCases/CreateUser";
import prisma from "../../src/prisma";

describe("Autenticação", () => {
  let userRepository: UserRepository;
  let authenticateUser: AuthenticateUser;
  let createUser: CreateUser;

  beforeAll(async () => {
    userRepository = new UserRepository();
    authenticateUser = new AuthenticateUser(userRepository);
    createUser = new CreateUser(userRepository);

    await createUser.execute({
      cpf: "12345678901",
      senha: "test123",
      isAdmin: true,
    });
  });

  afterAll(async () => {
    await prisma.user.deleteMany({ where: { cpf: "12345678901" } });
    await prisma.$disconnect();
  });

  it("deve autenticar um usuário com CPF e senha corretos", async () => {
    const token = await authenticateUser.execute({
      cpf: "12345678901",
      senha: "test123",
    });
    expect(token).toBeDefined();
    expect(typeof token).toBe("string");
  });

  it("deve falhar ao autenticar com senha incorreta", async () => {
    await expect(
      authenticateUser.execute({
        cpf: "12345678901",
        senha: "wrongpass",
      })
    ).rejects.toThrow("CPF ou senha inválidos");
  });
});