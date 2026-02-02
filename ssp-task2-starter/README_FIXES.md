# Quick Fixes for SSP Task 2 (PHP + MySQL + Tailwind)

## 1) Install Tailwind CLI and build
```bash
cd ssp-task2-starter
npm install -D tailwindcss@latest
npx tailwindcss -i ./assets/css/input.css -o ./public/css/tailwind.css --watch
```
Make sure your pages include: `<link rel="stylesheet" href="/css/tailwind.css">`.

## 2) Fix PHP include path
Replace `app/Views/helpers.php` with the one in this zip. It now correctly requires `app/Database.php`.

## 3) MySQL
Import `ssp_task2` from your provided `.sql` file, then set your DB creds in `app/config.php`.

## 4) Run PHP
```bash
php -S localhost:8000 -t public
# open http://localhost:8000
```
If your project is served from a subfolder, update `app/config.php -> base_url` accordingly.

## 5) MVC + Auth + CRUD Checklist
- Models: `app/Models/*` (Admin, Customer, Product)
- Controllers: `app/Controllers/*` (AuthController, ProductController, HomeController)
- Views: `app/Views/*` (login/register/dashboard/products/*)
- Auth routes: /login, /register, /logout (role-based: admin/customer)
- Secure routes: /dashboard, /admin/products/*
- CRUD: /admin/products (index, create, edit, delete)

You’re good to go 🚀
