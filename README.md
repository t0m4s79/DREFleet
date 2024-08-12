# Basic Installation and Config

Download dependency manager tools:
  -> composer: https://getcomposer.org/download/
  -> nodejs: https://nodejs.org/en/download/prebuilt-installer

Clone repo;

Install project depencies inside laravel directory:
  -> npm install
  -> composer install

Include composer, php and nodejs in the system environment variables

Copy .env.example file to .env and:
  -> fill database information
  -> on the laravel dir, run *php artisan key:generate* to generate aplication key (APP_KEY) 

Run database migrations with *php artisan migrate*

Run servers:
  -> backend: php artisan serve
  -> frontend: npm run dev
  
