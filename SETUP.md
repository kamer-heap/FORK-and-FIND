# Fork & Find - local setup (Variant 1)

Works on Ubuntu or WSL Ubuntu. Replace `/var/www/forkfind` if you keep the project somewhere else.

## 1. Install
    sudo apt update
    sudo apt install -y git php php-mysql php-mbstring mysql-server
    sudo service mysql start

## 2. Get the code
    git clone https://github.com/kamer-heap/FORK-and-FIND.git /var/www/forkfind
    cd /var/www/forkfind

Already have it? Update it with `git pull origin main`.

## 3. Create the database and a DB user (MySQL root via sudo)
    sudo mysql < database/schema.sql
    sudo mysql < database/sample_data.sql
    sudo mysql -e "CREATE USER 'fork_user'@'localhost' IDENTIFIED BY 'choose_a_password'; GRANT SELECT, INSERT, UPDATE, DELETE ON fork_and_find.* TO 'fork_user'@'localhost'; FLUSH PRIVILEGES;"

`schema.sql` drops and recreates the four tables, so running it again resets all data (accounts, menus, reviews). It is also the only file you need for the table structure. There is no separate migration.
Always run `schema.sql` first, then `sample_data.sql`.

## 4. Local config (never committed)
    cp config/database.example.php config/database.php
    # edit config/database.php and set $dbPass to the password you chose

## 5. Run

### Option A: PHP built-in server (simplest)
    cd /var/www/forkfind
    php -S localhost:8000 -t public
Open http://localhost:8000. The document root is the `public/` folder; `config/` and `includes/` stay outside it. PHP errors are printed in the terminal where this command runs.

### Option B: Apache virtual host
    sudo apt install -y apache2 libapache2-mod-php
    sudo cp docs/forkfind.local.conf /etc/apache2/sites-available/forkfind.conf
    sudo a2ensite forkfind
    echo "127.0.0.1 forkfind.local" | sudo tee -a /etc/hosts
    sudo service apache2 restart
Open http://forkfind.local. The virtual host points to `/var/www/forkfind/public`; edit `docs/forkfind.local.conf` if your folder is different. PHP errors are written to `/var/log/apache2/forkfind_error.log`.

## 6. Photo uploads
Owners upload photos into `public/uploads/`. The user that runs PHP must be able to write there. With `php -S` that is you. With Apache it is `www-data`:

    sudo chown $USER:www-data public/uploads
    sudo chmod 775 public/uploads

Good to know:
- Uploads accept JPG, PNG or WebP, up to 2 MB.
- Replacing a meal photo deletes the old file. If you replace a demo photo while testing, restore it with `git checkout -- public/uploads/<file name>`.
- Test uploads show up in `git status` as untracked files. Do not commit them. Add files explicitly (for example `git add database public includes`), not with `git add -A`.

## Test accounts (sample data only)
| Role    | Email                  | Password     |
|---------|------------------------|--------------|
| Admin   | admin@forkfind.test    | Admin@1234   |
| Owner   | bilal@forkfind.test    | Owner@1234   |
| Owner   | sara@forkfind.test     | Owner@1234   |
| Owner   | usman@forkfind.test    | Owner@1234   |
| Owner (pending approval) | pending@forkfind.test | Owner@1234 |
| Student | ayesha@forkfind.test   | Student@1234 |
| Student | hamza@forkfind.test    | Student@1234 |

Regenerate sample data with fresh hashes: `php database/sample_data.php`

The demo photos are committed under `public/uploads/` and their file names are
seeded in `database/sample_data.sql`. Keep that folder when copying or
deploying the project; otherwise the database will contain image names whose
files are missing.

## Troubleshooting
| Problem | What to do |
|---|---|
| `git pull` says "Already up to date" but pictures or data look different | The database is local and is not part of Git. Re-import it: `sudo mysql < database/schema.sql` then `sudo mysql < database/sample_data.sql`. |
| A meal picture is broken | The file is missing from `public/uploads/`. Check `git status`, then restore it with `git checkout -- public/uploads/<file name>`. |
| "The uploads folder is missing or not writable" | Fix the permissions in step 6. |
| "Something went wrong" or "Database unavailable" | Check `config/database.php` (user, password, database name) and that MySQL is running. Read the error in the `php -S` terminal or in the Apache log. |
| The site does not load | Start MySQL (`sudo service mysql start`) and the server (`php -S localhost:8000 -t public` from the project root). |
