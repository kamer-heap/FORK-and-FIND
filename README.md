# Fork & Find

A campus cafeteria guide for PUCIT departments. Students search meals, compare
prices and ratings, and leave reviews. Cafeteria owners manage their own menu.
Admins approve owners and keep the content clean.

Course: Web Technologies, PUCIT
Team: Hijab Ahmad, Khadija Amer

## Features
- **Student:** sign up, search meals by name, max price and rating, browse cafeterias, add / update / delete own review
- **Owner:** edit own cafeteria info, add / edit / delete menu items, mark items sold out, read reviews
- **Admin:** approve owners, add and manage cafeterias, assign owners, delete inappropriate reviews

## Tech
PHP (PDO), MySQL, Apache, HTML, CSS, JavaScript

## Folder structure
    config/     database settings (the real config file is git-ignored)
    database/   schema.sql, sample_data.sql
    docs/       ER diagram, Apache virtual host file
    includes/   bootstrap, auth, helpers, header, footer
    public/     web root: student pages, owner/, admin/, css/

## Security
- Passwords stored as hashes (`password_hash` / `password_verify`)
- Prepared statements for every query (PDO)
- Server-side role checks (`requireRole`) and ownership checks in SQL
- Session ID regenerated on login; HttpOnly and SameSite cookies
- CSRF token on every POST form, checked in `bootstrap.php`
- Output escaped with `htmlspecialchars`
- App database user has only SELECT, INSERT, UPDATE, DELETE on its own database
- Real credentials are never committed (`config/database.php` is in .gitignore)

## Database
4 tables: Users, Cafeterias, Menu_Items, Reviews. See [docs/erd.md](docs/erd.md).

## Run it
See [SETUP.md](SETUP.md). After setup, open http://forkfind.local
