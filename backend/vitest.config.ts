import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    environment: "node",
    coverage: {
      provider: "v8",
      reporter: ["text", "json", "html"],
    },
    globals: true,
    ui: true,
  },
  server: {
    port: 51205, // Configura a porta do servidor UI aqui
  },
});