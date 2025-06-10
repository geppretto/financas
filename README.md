<div align="center">
	<h1>Finanças</h1>
</div>

## Requisitos

- **PHP** 8.2 ou superior  
- **Composer**  
- **Node.js** e **npm**  
- Banco de dados MySQL/MariaDB (ou conforme configurado no `.env`)

---

## Primeiros Passos

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/geppretto/financas
   cd financas

2. **Configuração do ambiente:**
- Duplique o arquivo .env.example e renomeie para .env.
- Preencha as informações do banco de dados e outras variáveis conforme necessário.
    ```bash
    cp .env.example .env

3. **Instale as dependências do PHP:**
    ```bash
    composer install

4. **Instale as dependências do Node.js:**
    ```bash
    npm install

5. **Gere a chave da aplicação:**
    ```bash
    php artisan key:generate

6. **Execute as migrações do banco de dados:**
    ```bash
    php artisan migrate

## Compilação de CSS e JavaScript
O projeto utiliza ferramentas modernas para processar os arquivos front-end.

### Ambiente de Desenvolvimento
- Para iniciar a compilação em tempo real (hot reload):
```sh
npm run dev
```

As alterações feitas em arquivos CSS/JS serão aplicadas automaticamente.

### Ambiente de Homologação/Produção
- Para gerar os arquivos otimizados e prontos para publicação:
```sh
npm run build
```

## Observações
**Servidor de desenvolvimento:**
- Rode localmente com:
```sh
php artisan serve
```

Ou use Valet, XAMPP, Laragon, Docker, etc.


## Suporte
Dúvidas ou problemas?
Abra uma [issue](https://github.com/piloti/financas/issues) ou entre em contato com a equipe de desenvolvimento.
