-- CreateTable
CREATE TABLE "Multa" (
    "id" SERIAL NOT NULL,
    "veiculoId" INTEGER NOT NULL,
    "motoristaId" INTEGER,
    "data" TIMESTAMP(3) NOT NULL,
    "tipo" TEXT NOT NULL,
    "valor" DOUBLE PRECISION NOT NULL,
    "descricao" TEXT,

    CONSTRAINT "Multa_pkey" PRIMARY KEY ("id")
);

-- AddForeignKey
ALTER TABLE "Multa" ADD CONSTRAINT "Multa_veiculoId_fkey" FOREIGN KEY ("veiculoId") REFERENCES "Veiculo"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "Multa" ADD CONSTRAINT "Multa_motoristaId_fkey" FOREIGN KEY ("motoristaId") REFERENCES "Motorista"("id") ON DELETE SET NULL ON UPDATE CASCADE;
