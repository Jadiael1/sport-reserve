# Sistema de Reservas de Esportes

Este é um sistema de reservas de esportes desenvolvido com Laravel 10, incluindo autenticação de usuário, reservas de campos, pagamentos e muito mais. O projeto possui testes automatizados para garantir a qualidade e a confiabilidade do código.

## Contato

Para mais informações, entre em contato:

- Email: [jadiael@hotmail.com.br](mailto:jadiael@hotmail.com.br)

## URL do Sistema

O sistema pode ser acessado online através da URL: [https://api-sport-reserve.juvhost.com](https://api-sport-reserve.juvhost.com)

## Executando Testes

O projeto inclui testes automatizados para garantir a qualidade do código. Para executar os testes, utilize o seguinte comando:

```sh
php artisan test
```

## Funcionalidades

- Autenticação de Usuários
  - Registro de novos usuários
  - Login de usuários existentes
  - Verificação de e-mail para novos registros
  - Logout
  - Redefinição de senha

- Gerenciamento de Campos
  - Listar campos disponíveis
  - Visualizar detalhes de um campo específico
  - Administradores podem adicionar, editar e remover campos

- Reservas
  - Usuários podem reservar campos
  - Listar reservas do usuário
  - Visualizar detalhes de uma reserva específica

- Pagamentos
  - Processamento de pagamentos para reservas
  - Notificações de pagamento

## Estrutura do Projeto

```plaintext
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── Api
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── FieldController.php
│   │   │   │   ├── PaymentController.php
│   │   │   │   ├── ReservationController.php
│   │   │   │   └── UserController.php
│   │   └── Requests
│   │       ├── StoreUserRequest.php
│   │       └── ...
│   ├── Models
│   │   ├── Field.php
│   │   ├── Reservation.php
│   │   └── User.php
│   └── Notifications
│       ├── CustomVerifyEmail.php
│       └── CustomResetPassword.php
├── database
│   ├── factories
│   │   ├── FieldFactory.php
│   │   ├── ReservationFactory.php
│   │   └── UserFactory.php
│   ├── migrations
│   │   ├── create_fields_table.php
│   │   ├── create_reservations_table.php
│   │   └── create_users_table.php
│   └── seeders
│       └── DatabaseSeeder.php
├── routes
│   └── api.php
└── tests
    ├── Feature
    │   ├── Api
    │   │   ├── AuthControllerTest.php
    │   │   ├── FieldControllerTest.php
    │   │   ├── PaymentControllerTest.php
    │   │   ├── ReservationControllerTest.php
    │   │   └── UserControllerTest.php
    └── Unit
        └── ExampleTest.php
```

## Tecnologias Utilizadas

- **Laravel 10**: Framework PHP para desenvolvimento web.
- **Sanctum**: Autenticação simples para APIs.
- **FakerPHP**: Geração de dados fictícios para testes.
- **PHPUnit**: Framework para testes automatizados.
- **MySQL/MariaDB**: Banco de dados relacional.

## Instalação e Configuração

1. Clone o repositório:

    ```sh
    git clone https://github.com/Jadiael1/sport-reserve.git
    ```

2. Instale as dependências:

    ```sh
    cd sport-reserve
    composer install
    ```

3. Configure o arquivo `.env`:

    ```sh
    cp .env.example .env
    ```

    Atualize as variáveis de ambiente conforme necessário.

4. Gere a chave da aplicação:

    ```sh
    php artisan key:generate
    ```

5. Execute as migrações e seeders:

    ```sh
    php artisan migrate --seed
    ```

6. Inicie o servidor:

    ```sh
    php artisan serve
    ```

Os testes de funcionalidade incluem:

- Autenticação de Usuários (AuthControllerTest)
- Gerenciamento de Campos (FieldControllerTest)
- Pagamentos (PaymentControllerTest)
- Reservas (ReservationControllerTest)
- Usuários (UserControllerTest)

## Documentação da API

A API possui documentação Swagger. Para acessá-la, inicie o servidor e acesse a URL `/`.

## Contribuição

Se você deseja contribuir com o projeto, siga os passos abaixo:

1. Faça um fork do repositório.
2. Crie uma nova branch:

    ```sh
    git checkout -b minha-branch
    ```

3. Faça as alterações desejadas e commit:

    ```sh
    git commit -m "Minhas alterações"
    ```

4. Envie para o seu repositório fork:

    ```sh
    git push origin minha-branch
    ```

5. Abra um Pull Request para o repositório principal.

## Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.
