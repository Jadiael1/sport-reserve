# Sport Reserve

Sport Reserve é um sistema de marcação de reserva de quadras esportivas, permitindo que usuários registrem e reservem quadras de futebol. O sistema é construído com Laravel e oferece uma API RESTful com autenticação utilizando Laravel Sanctum e documentação da API com Swagger.

## Funcionalidades

- Registro de usuários
- Autenticação de usuários
- Reserva de quadras esportivas
- Gestão de usuários e reservas por administradores
- Documentação da API com Swagger

## Tecnologias Utilizadas

- [Laravel 10.3.3](https://laravel.com)
- [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum)
- [Swagger](https://swagger.io) via [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger)
- [MySQL](https://www.mysql.com)
- [Nginx](https://www.nginx.com) ou [Apache](https://httpd.apache.org)

## Requisitos

- PHP 8.1 ou superior
- Composer
- MySQL
- Servidor web (Nginx ou Apache)

## Instalação

1. Clone o repositório:
    ```sh
    git clone https://github.com/seu-usuario/sport-reserve.git
    cd sport-reserve
    ```

2. Instale as dependências do Composer:
    ```sh
    composer install
    ```

3. Copie o arquivo `.env.example` para `.env` e configure as variáveis de ambiente:
    ```sh
    cp .env.example .env
    ```

4. Gere a chave da aplicação:
    ```sh
    php artisan key:generate
    ```

5. Configure o banco de dados no arquivo `.env`:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=sport_reserve
    DB_USERNAME=seu_usuario
    DB_PASSWORD=sua_senha
    ```

6. Execute as migrações e seeders:
    ```sh
    php artisan migrate --seed
    ```

7. Inicie o servidor de desenvolvimento:
    ```sh
    php artisan serve
    ```

## Uso

### Autenticação

- **Registro de Usuário**: `POST /api/register`
- **Login de Usuário**: `POST /api/login`
- **Logout de Usuário**: `POST /api/logout`
- **Dados do Usuário Autenticado**: `GET /api/user`

### Rotas Protegidas para Administradores

- **Gerenciamento de Usuários**: `GET /api/users`, `POST /api/users`, `GET /api/users/{id}`, `PATCH /api/users/{id}`, `DELETE /api/users/{id}`
- **Gerenciamento de Campos**: `GET /api/fields`, `POST /api/fields`, `GET /api/fields/{id}`, `PATCH /api/fields/{id}`, `DELETE /api/fields/{id}`

### Rotas Protegidas para Todos os Usuários Autenticados

- **Gerenciamento de Reservas**: `GET /api/reservations`, `POST /api/reservations`, `GET /api/reservations/{id}`, `PATCH /api/reservations/{id}`, `DELETE /api/reservations/{id}`

## Documentação da API

A documentação da API está disponível em `/`. Para acessá-la, certifique-se de que o servidor está em execução e visite:
```
https://api-sport-reserve.juvhost.com/
```

## Arquivo `.htaccess` para Apache

```apache
# Disable index view
Options -Indexes

# Hide a specific file
<Files .env>
    Order allow,deny
    Deny from all
</Files>

RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

## Licença
Este projeto está licenciado sob a MIT License.

### Considerações Finais

Esse `README.md` inclui uma visão geral do projeto, tecnologias utilizadas, instruções de instalação, uso das principais funcionalidades e documentação da API. Adicione qualquer outra informação relevante ou ajuste conforme necessário para o seu projeto específico. Se precisar de mais assistência, estarei à disposição!
