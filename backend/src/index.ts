import express from "express";
import authRoutes from "./infrastructure/http/routes/auth.routes";
import secretariaRoutes from "./infrastructure/http/routes/secretaria.routes";
import motoristaRoutes from "./infrastructure/http/routes/motorista.routes";
import veiculoRoutes from "./infrastructure/http/routes/veiculo.routes";
import locacaoRoutes from "./infrastructure/http/routes/locacao.routes";

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());
app.use("/auth", authRoutes);
app.use("/secretarias", secretariaRoutes);
app.use("/motoristas", motoristaRoutes);
app.use("/veiculos", veiculoRoutes);
app.use("/locacoes", locacaoRoutes);

app.get("/", (req, res) => {
  res.send("FrotaSmart Backend está rodando!");
});

app.listen(PORT, () => {
  console.log(`Servidor rodando na porta ${PORT}`);
});