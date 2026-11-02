# Fork & Find - local setup (Variant 1)

## 1. Install (WSL Ubuntu)
    sudo apt update
    sudo apt install -y php php-mysql php-mbstring

## 2. Create the database and a DB user (MySQL root via sudo)
    sudo mysql < database/schema.sql
    sudo mysql < database/sample_data.sql
    sudo mysql -e "CREATE USER 'fork_user'@'localhost' IDENTIFIED BY 'choose_a_password'; GRANT ALL ON fork_and_find.* TO 'fork_user'@'localhost'; FLUSH PRIVILEGES;"

## 3. Local config (never committed)
    cp config/database.example.php config/database.php
    # edit config/database.php and set $dbPass to the password you chose

## 4. Run
    php -S localhost:8000 -t public
Open http://localhost:8000 (document root is the `public/` folder; `config/` and `includes/` stay outside it).

## Test accounts (sample data only)
| Role    | Email                  | Password     |
|---------|------------------------|--------------|
| Admin   | admin@forkfind.test    | Admin@1234   |
| Owner   | bilal@forkfind.test    | Owner@1234   |
| Owner (pending approval) | pending@forkfind.test | Owner@1234 |
| Student | ayesha@forkfind.test   | Student@1234 |

Regenerate sample data with fresh hashes: `php database/sample_data.php`
