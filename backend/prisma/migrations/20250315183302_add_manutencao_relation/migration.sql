-- CreateTable
CREATE TABLE "Manutencao" (
    "id" SERIAL NOT NULL,
    "veiculoId" INTEGER NOT NULL,
    "data" TIMESTAMP(3) NOT NULL,
    "tipo" TEXT NOT NULL,
    "descricao" TEXT,
    "custo" DOUBLE PRECISION,

    CONSTRAINT "Manutencao_pkey" PRIMARY KEY ("id")
);

-- AddForeignKey
ALTER TABLE "Manutencao" ADD CONSTRAINT "Manutencao_veiculoId_fkey" FOREIGN KEY ("veiculoId") REFERENCES "Veiculo"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
