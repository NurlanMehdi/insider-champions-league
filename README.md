Insider Champions League – Setup

Run with Docker (Recommended)

Requirements:
 Docker
 Docker Compose
 Git

Steps:

1. Clone the repository
   `git clone <https://github.com/NurlanMehdi/insider-champions-league.git>`
   `cd insider-champions-league`

2. Start the containers
   `docker compose up -d`

Services:
 Laravel App: [http://localhost:8000]
 Vite Dev Server: [http://localhost:5173] 
 PhpMyAdmin: [http://localhost:8080]
 MySQL DB Port: 3307
 Redis Port: 6380

Database Credentials:
 Username: `premier_league_user`
 Password: `premier_league_password`
 Database: `premier_league`

---

Run Locally (Without Docker)

Requirements:
 PHP 8.2+
 Composer
 Node.js 18+
 MySQL 8.0+ or SQLite

Steps:

1. Install dependencies
   `composer install`
   `npm install`

2. Setup environment
   `cp .env.example .env`
   `php artisan key:generate`

3. Setup database
   `php artisan migrate:fresh --seed`

4. Build frontend
   `npm run build`

5. Start servers
   `php artisan serve`
   `npm run dev`

