import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    globals: true,         // Permite usar describe, it, expect sem importações
    environment: "node",  // Ambiente Node.js para backend
    ui: true,             // Habilita a UI do Vitest
    dir: "tests",         // Diretório onde os testes будут armazenados
  },
});