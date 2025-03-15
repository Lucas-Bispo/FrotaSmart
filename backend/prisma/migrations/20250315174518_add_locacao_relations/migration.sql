-- CreateTable
CREATE TABLE "Locacao" (
    "id" SERIAL NOT NULL,
    "veiculoId" INTEGER NOT NULL,
    "motoristaId" INTEGER NOT NULL,
    "dataInicio" TIMESTAMP(3) NOT NULL,
    "dataFim" TIMESTAMP(3),
    "destino" TEXT NOT NULL,
    "km" DOUBLE PRECISION,

    CONSTRAINT "Locacao_pkey" PRIMARY KEY ("id")
);

-- AddForeignKey
ALTER TABLE "Locacao" ADD CONSTRAINT "Locacao_veiculoId_fkey" FOREIGN KEY ("veiculoId") REFERENCES "Veiculo"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "Locacao" ADD CONSTRAINT "Locacao_motoristaId_fkey" FOREIGN KEY ("motoristaId") REFERENCES "Motorista"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
