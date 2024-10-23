# Basic Installation and Config

1. Download dependency manager tools:
   - [composer](https://getcomposer.org/download/)
   - [nodejs](https://nodejs.org/en/download/prebuilt-installer)

2. Clone repository;

3. Include **composer** **nodejs** and **php** in the system environment variables;

4. Install project depencies inside laravel directory:
   - `npm install`
   - `composer install`

5. Copy .env.example file to .env and:
   - fill database information;
   - on the laravel dir, run `php artisan key:generate` to generate aplication key (APP_KEY);

6. Run database migrations with `php artisan migrate`;

7. Run servers:
   - backend: php artisan serve
   - frontend: npm run dev

[Windows Task Scheduler](https://gist.github.com/Splode/94bfa9071625e38f7fd76ae210520d94)


# Updated Entity Relationship Diagram
![Diagrama Relações](https://github.com/user-attachments/assets/ce139e38-8614-4a6f-a0e8-44670f6f3e79)

