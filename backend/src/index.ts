import express from "express";
import authRoutes from "./infrastructure/http/routes/auth.routes";
import motoristaRoutes from "./infrastructure/http/routes/motorista.routes";
import veiculoRoutes from "./infrastructure/http/routes/veiculo.routes";

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());
app.use("/auth", authRoutes);
app.use("/motoristas", motoristaRoutes);
app.use("/veiculos", veiculoRoutes);

app.get("/", (req, res) => {
  res.send("FrotaSmart Backend está rodando!");
});

app.listen(PORT, () => {
  console.log(`Servidor rodando na porta ${PORT}`);
});