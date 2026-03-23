# Apache Hardening para o FrotaSmart

## Objetivo

Padronizar a publicacao do FrotaSmart em Linux com Apache 2.4+, reduzindo risco de exposicao da aplicacao PHP, dos diretórios internos e dos arquivos sensiveis do repositorio.

## Checklist minimo

1. Configure o `DocumentRoot` para `public/`.
2. Mantenha `backend/`, `src/`, `scripts/`, `AI/` e `.env` fora da area publica.
3. Habilite apenas os modulos necessarios (`headers`, `rewrite`, `ssl`).
4. Desabilite listagem de diretorios (`Options -Indexes`).
5. Force HTTPS e HSTS quando a aplicacao estiver atras de certificado valido.
6. Restrinja metodos HTTP para `GET`, `POST` e `HEAD`.
7. Garanta permissoes minimas de leitura para o usuario do Apache.
8. Direcione logs do Apache e do PHP para rotacao centralizada.

## VirtualHost de referencia

```apache
<VirtualHost *:80>
    ServerName frota.exemplo.gov.br
    DocumentRoot /var/www/frotasmart/public

    <Directory /var/www/frotasmart/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/frotasmart-error.log
    CustomLog ${APACHE_LOG_DIR}/frotasmart-access.log combined
</VirtualHost>
```

## Recomendacoes adicionais

- Execute `a2enmod headers rewrite ssl` antes de publicar o sistema.
- Se houver proxy reverso, configure `X-Forwarded-Proto` corretamente para manter cookies seguros.
- Mantenha atualizacoes de seguranca do PHP, Apache e OpenSSL em dia.
- Adote backups, monitoramento e varredura recorrente de dependencias e segredos.
