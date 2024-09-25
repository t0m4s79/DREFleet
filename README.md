# Basic Installation and Config

1. Download dependency manager tools:
   - [composer](https://getcomposer.org/download/)
   - [nodejs](https://nodejs.org/en/download/prebuilt-installer)

2. Clone repository;

3. Install project depencies inside laravel directory:
   - `npm install`
   - `composer install`

4. Include **composer** **nodejs** and **php** in the system environment variables;

5. Copy .env.example file to .env and:
   - fill database information;
   - on the laravel dir, run `php artisan key:generate` to generate aplication key (APP_KEY);

6. Run database migrations with `php artisan migrate`;

7. Run servers:
   - backend: php artisan serve
   - frontend: npm run dev
  


# Entity Relationship Diagram

![Diagrama Relações](https://github.com/user-attachments/assets/c6c15152-f48e-4fbd-943c-e86f6a96cb76)

# Updated Entity Relationship Diagram

![Diagrama Relações Atualizado drawio](https://github.com/user-attachments/assets/001af184-edb1-4a3e-8cb2-efa4bf11a093)

