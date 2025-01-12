# Sport Reserve

Um sistema completo de API baseado em Laravel para gerenciamento de reservas de quadras esportivas.

## Demo

Você pode testar o sistema em:
- URL da API: [https://api-sport-reserve.juvhost.com](https://api-sport-reserve.juvhost.com)
- Credenciais do Admin Demo:
  - **Email:** `test@test.com`
  - **Senha:** `password`

## Vídeos Tutoriais

Para um guia visual sobre como configurar e usar a aplicação:
- **Canal do YouTube:** [Sport Reserve Channel](https://www.youtube.com/@Sport-Reserve?sub_confirmation=1)
- **Playlist Tutorial:** [Guia de Configuração](https://www.youtube.com/watch?v=SIDX8NHD8TY&list=PLwVHkMYZnB-8RHZ6mB79b9FHNYkCYXPxp)

## Contato

Para suporte ou dúvidas, entre em contato:
- **Email:** [jadiael@hotmail.com.br](mailto:jadiael@hotmail.com.br)

---

## Funcionalidades

- **Gerenciamento de Usuários**  
  - Registro com verificação de email.  
  - Controle de acesso baseado em funções (Admin/Usuário).  
  - Redefinição de senha e gerenciamento de perfil.  

- **Gerenciamento de Quadras**  
  - Cadastro de quadras com upload de múltiplas imagens.  
  - Definição de horários de disponibilidade.  
  - Gerenciamento de status (ativa/inativa).  

- **Sistema de Reservas**  
  - Reservar quadras para horários específicos.  
  - Verificação de disponibilidade em tempo real.  
  - Cancelamento automático de reservas não pagas.  
  - Acompanhamento do status das reservas.  

- **Integração de Pagamentos**  
  - Suporte a diversos métodos de pagamento via PagSeguro.  
  - Processamento de reembolsos.  
  - Notificações automáticas sobre o status de pagamento.  

- **Relatórios**  
  - Relatórios de desempenho.  
  - Relatórios financeiros.  
  - Estatísticas de ocupação das quadras.  

- **Documentação da API**  
  - Documentação Swagger/OpenAPI acessível na URL raiz (`/`).  
  - Exemplos detalhados de requisição e resposta.  

---

## Estrutura de Diretórios

```txt
Estrutura de diretórios:
└── sport-reserve/
    ├── artisan
    ├── composer.json
    ├── composer.lock
    ├── package.json
    ├── phpunit.xml
    ├── vite.config.js
    ├── .editorconfig
    ├── .env.example
    ├── .htaccess
    ├── app/
    │   ├── Console/
    │   │   └── Kernel.php
    │   ├── Exceptions/
    │   │   └── Handler.php
    │   ├── Http/
    │   │   ├── Kernel.php
    │   │   ├── Controllers/
    │   │   │   ├── Controller.php
    │   │   │   ├── Api/
    │   │   │   │   ├── AuthController.php
    │   │   │   │   ├── FieldAvailabilityController.php
    │   │   │   │   ├── FieldController.php
    │   │   │   │   ├── PaymentController.php
    │   │   │   │   ├── ReportController.php
    │   │   │   │   ├── ReservationController.php
    │   │   │   │   └── UserController.php
    │   │   │   └── Documentation/
    │   │   │       ├── AuthDocumentation.php
    │   │   │       ├── FieldAvailabilityDocumentation.php
    │   │   │       ├── FieldDocumentation.php
    │   │   │       ├── PaymentDocumentation.php
    │   │   │       ├── ReportDocumentation.php
    │   │   │       ├── ReservationDocumentation.php
    │   │   │       └── UserDocumentation.php
    │   │   ├── Middleware/
    │   │   │   ├── AdminMiddleware.php
    │   │   │   ├── Authenticate.php
    │   │   │   ├── CheckIfVerified.php
    │   │   │   ├── EncryptCookies.php
    │   │   │   ├── PreventRequestsDuringMaintenance.php
    │   │   │   ├── RedirectIfAuthenticated.php
    │   │   │   ├── TrimStrings.php
    │   │   │   ├── TrustHosts.php
    │   │   │   ├── TrustProxies.php
    │   │   │   ├── ValidateSignature.php
    │   │   │   └── VerifyCsrfToken.php
    │   │   ├── Requests/
    │   │   │   ├── ReportRequest.php
    │   │   │   ├── StoreFieldAvailabilityRequest.php
    │   │   │   ├── StoreFieldRequest.php
    │   │   │   ├── StoreReservationRequest.php
    │   │   │   ├── StoreUserRequest.php
    │   │   │   ├── UpdateFieldAvailabilityRequest.php
    │   │   │   ├── UpdateFieldRequest.php
    │   │   │   ├── UpdatePaymentRequest.php
    │   │   │   ├── UpdateReservationRequest.php
    │   │   │   └── UpdateUserRequest.php
    │   │   └── Resources/
    │   │       └── ReportResource.php
    │   ├── Models/
    │   │   ├── Field.php
    │   │   ├── FieldAvailability.php
    │   │   ├── FieldImage.php
    │   │   ├── Payment.php
    │   │   ├── Reservation.php
    │   │   └── User.php
    │   ├── Notifications/
    │   │   ├── CustomResetPassword.php
    │   │   └── CustomVerifyEmail.php
    │   ├── Providers/
    │   │   ├── AppServiceProvider.php
    │   │   ├── AuthServiceProvider.php
    │   │   ├── BroadcastServiceProvider.php
    │   │   ├── EventServiceProvider.php
    │   │   └── RouteServiceProvider.php
    │   └── Rules/
    │       ├── ValidCpf.php
    │       └── ValidPhone.php
    ├── bootstrap/
    │   ├── app.php
    │   └── cache/
    │       └── .gitignore
    ├── config/
    │   ├── app.php
    │   ├── auth.php
    │   ├── broadcasting.php
    │   ├── cache.php
    │   ├── cors.php
    │   ├── database.php
    │   ├── filesystems.php
    │   ├── hashing.php
    │   ├── l5-swagger.php
    │   ├── logging.php
    │   ├── mail.php
    │   ├── pagseguro.php
    │   ├── queue.php
    │   ├── sanctum.php
    │   ├── services.php
    │   ├── session.php
    │   └── view.php
    ├── database/
    │   ├── .gitignore
    │   ├── factories/
    │   │   ├── FieldAvailabilityFactory.php
    │   │   ├── FieldFactory.php
    │   │   ├── PaymentFactory.php
    │   │   ├── ReservationFactory.php
    │   │   └── UserFactory.php
    │   ├── migrations/
    │   │   ├── 2014_10_12_000000_create_users_table.php
    │   │   ├── 2014_10_12_100000_create_password_reset_tokens_table.php
    │   │   ├── 2019_08_19_000000_create_failed_jobs_table.php
    │   │   ├── 2019_12_14_000001_create_personal_access_tokens_table.php
    │   │   ├── 2024_06_20_193256_create_fields_table.php
    │   │   ├── 2024_06_20_193257_create_reservations_table.php
    │   │   ├── 2024_06_20_194400_create_payments_table.php
    │   │   ├── 2024_07_15_220030_create_field_images_table.php
    │   │   └── 2024_07_27_234950_create_field_availabilities_table.php
    │   └── seeders/
    │       └── DatabaseSeeder.php
    ├── public/
    │   ├── index.php
    │   ├── robots.txt
    │   └── .htaccess
    ├── resources/
    │   ├── css/
    │   │   └── app.css
    │   ├── js/
    │   │   ├── app.js
    │   │   └── bootstrap.js
    │   └── views/
    │       └── vendor/
    │           └── l5-swagger/
    │               ├── index.blade.php
    │               └── .gitkeep
    ├── routes/
    │   ├── api.php
    │   ├── channels.php
    │   ├── console.php
    │   └── web.php
    ├── storage/
    │   ├── app/
    │   │   ├── .gitignore
    │   │   └── public/
    │   │       └── .gitignore
    │   ├── framework/
    │   │   ├── .gitignore
    │   │   ├── cache/
    │   │   │   ├── .gitignore
    │   │   │   └── data/
    │   │   │       └── .gitignore
    │   │   ├── sessions/
    │   │   │   └── .gitignore
    │   │   ├── testing/
    │   │   │   └── .gitignore
    │   │   └── views/
    │   │       └── .gitignore
    │   └── logs/
    │       └── .gitignore
    ├── tests/
    │   ├── CreatesApplication.php
    │   ├── TestCase.php
    │   ├── Feature/
    │   │   ├── ExampleTest.php
    │   │   └── Api/
    │   │       ├── AuthControllerTest.php
    │   │       ├── FieldControllerTest.php
    │   │       ├── PaymentControllerTest.php
    │   │       ├── ReservationControllerTest.php
    │   │       └── UserControllerTest.php
    │   └── Unit/
    │       └── ExampleTest.php
    └── .github/
        └── workflows/
            └── deploy.yml
```

---

## Tecnologias Utilizadas

- **PHP 8.1**: Linguagem principal para backend.
- **Laravel 10**: Framework robusto para desenvolvimento web.
- **MySQL ou MariaDB**: Banco de dados relacional.
- **PagSeguro**: Gateway para pagamentos.
- **Swagger/OpenAPI**: Documentação da API.
- **Sanctum**: Autenticação via tokens.

---

## Requisitos do Sistema

- **PHP 8.1** ou superior.
- **Composer** para gerenciamento de dependências.
- **MySQL 5.7+**.

---

## Instalação e Configuração

1. Clone o repositório:
    ```bash
    git clone https://github.com/Jadiael1/sport-reserve.git
    cd sport-reserve
    ```

2. Instale as dependências:
    ```bash
    composer install
    ```

3. Configure o arquivo de ambiente:
    ```bash
    cp .env.example .env
    ```
    Atualize o `.env` com:
    - URL da aplicação `APP_URL`
    - Credenciais do banco de dados
    - Configurações do servidor de email
    - URL do Front-End `SAP_URL`
    - Credenciais da API do PagSeguro
    - Chaves do Google reCAPTCHA

4. Gere a chave da aplicação:
    ```bash
    php artisan key:generate
    ```

5. Execute as migrações e seeders:
    ```bash
    php artisan migrate --seed
    ```

6. Gere a documentação da API:
    ```bash
    php artisan l5-swagger:generate
    ```

7. Crie o link simbólico do storage:
    ```bash
    php artisan storage:link
    ```
---

## Testes Automatizados

Execute os testes automatizados para garantir o funcionamento correto do sistema:
```bash
php artisan test
```

O conjunto de testes inclui:
- Testes de funcionalidade para todos os endpoints da API.  
- Testes unitários para funcionalidades principais.  
- Testes de autenticação e autorização.  
- Testes de integração de pagamento.  

---

## Contribuindo

Este é um projeto proprietário e não está aberto para contribuições públicas.

## Segurança

Se você descobrir alguma vulnerabilidade de segurança, por favor, envie um email para [jadiael@hotmail.com.br](mailto:jadiael@hotmail.com.br) em vez de usar o rastreador de problemas.

## Licença

Este projeto é protegido por uma **Licença Proprietária**. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.
