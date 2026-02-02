# Render.com Deployment Guide - Traxtar Application

## Overview

This guide provides step-by-step instructions for deploying the Traxtar Laravel 12 application to Render.com, a modern cloud hosting platform.

---

## 📋 Prerequisites

1. **Render.com Account**
   - Sign up at https://render.com
   - Free tier available

2. **Git Repository**
   - Application code in Git (GitHub, GitLab, or Bitbucket)
   - Repository must be accessible to Render

3. **Database**
   - MySQL database (Render provides managed databases)

---

## 🚀 Deployment Steps

### Step 1: Prepare Repository

1. **Ensure all code is committed and pushed**:
   ```bash
   git add .
   git commit -m "Prepare for Render deployment"
   git push origin main
   ```

2. **Verify .gitignore includes**:
   - `.env` (not committed)
   - `vendor/` (installed on Render)
   - `node_modules/` (installed on Render)
   - `storage/logs/*` (logs not committed)

---

### Step 2: Create Database on Render

1. **Go to Render Dashboard** → **New** → **PostgreSQL** (or MySQL)
2. **Configure Database**:
   - **Name**: `traxtar-db`
   - **Database**: `traxtar`
   - **User**: `traxtar`
   - **Plan**: Free (or paid)
3. **Note the connection string** (will be used in environment variables)

---

### Step 3: Create Web Service

1. **Go to Render Dashboard** → **New** → **Web Service**
2. **Connect Repository**:
   - Connect your Git repository
   - Select the repository and branch (usually `main` or `master`)

3. **Configure Service**:
   - **Name**: `traxtar`
   - **Environment**: `PHP`
   - **Region**: Choose closest to your users
   - **Branch**: `main` (or your default branch)

4. **Build Settings**:
   - **Build Command**: 
     ```bash
     composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan key:generate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
     ```
   - **Start Command**: 
     ```bash
     php artisan serve --host=0.0.0.0 --port=$PORT
     ```

---

### Step 4: Configure Environment Variables

Add the following environment variables in Render dashboard:

#### Required Variables

```env
APP_NAME=Traxtar
APP_ENV=production
APP_KEY=base64:... (will be generated)
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=your-db-host.render.com
DB_PORT=3306
DB_DATABASE=traxtar
DB_USERNAME=traxtar
DB_PASSWORD=your-db-password

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync

STRIPE_KEY=pk_live_your_publishable_key
STRIPE_SECRET=sk_live_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
STRIPE_CURRENCY=usd

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@traxtar.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Optional Variables

```env
REDIS_URL= (if using Redis)
BROADCAST_DRIVER=log
FILESYSTEM_DISK=local
```

---

### Step 5: Database Setup

1. **Run Migrations**:
   - After first deployment, go to **Shell** in Render dashboard
   - Run: `php artisan migrate --force`
   - Or add to build command: `php artisan migrate --force`

2. **Create Storage Link**:
   - In Shell: `php artisan storage:link`

3. **Seed Database** (optional):
   - In Shell: `php artisan db:seed`

---

### Step 6: Configure Webhook (Stripe)

1. **Get Render URL**: `https://your-app-name.onrender.com`
2. **In Stripe Dashboard**:
   - Go to **Developers** → **Webhooks**
   - Add endpoint: `https://your-app-name.onrender.com/stripe/webhook`
   - Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `payment_intent.canceled`
   - Copy webhook signing secret
   - Add to Render: `STRIPE_WEBHOOK_SECRET=whsec_...`

---

## 🔧 Configuration Files

### render.yaml (Optional)

If using `render.yaml`, Render will auto-configure:
- Web service
- Database
- Build commands
- Environment variables

**Note**: You can also configure manually in Render dashboard.

---

## 📝 Build Process

### Automatic Build Steps

1. **Install Dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   ```

2. **Build Assets**:
   ```bash
   npm run build
   ```

3. **Cache Configuration**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Generate Key** (if not set):
   ```bash
   php artisan key:generate --force
   ```

5. **Run Migrations** (optional):
   ```bash
   php artisan migrate --force
   ```

---

## 🗄️ Database Configuration

### MySQL on Render

1. **Create Database**:
   - Render Dashboard → New → PostgreSQL/MySQL
   - Note connection details

2. **Update Environment Variables**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=your-db-host.render.com
   DB_PORT=3306
   DB_DATABASE=traxtar
   DB_USERNAME=traxtar
   DB_PASSWORD=your-password
   ```

3. **Run Migrations**:
   ```bash
   php artisan migrate --force
   ```

---

## 🔒 Security Configuration

### Production Settings

1. **Set `APP_DEBUG=false`**:
   - Prevents exposing sensitive information
   - Shows generic error pages

2. **Set `APP_ENV=production`**:
   - Enables production optimizations
   - Disables debug features

3. **Use Strong `APP_KEY`**:
   - Generated automatically or manually
   - Never commit to repository

4. **Secure Database Credentials**:
   - Use environment variables
   - Never commit credentials

5. **HTTPS**:
   - Render provides free HTTPS
   - Automatically configured

---

## 📦 Storage Configuration

### File Storage

1. **Storage Link**:
   ```bash
   php artisan storage:link
   ```
   - Creates symbolic link from `storage/app/public` to `public/storage`
   - Required for uploaded images

2. **Storage Permissions**:
   - Render handles permissions automatically
   - No manual configuration needed

---

## 🧪 Post-Deployment Checklist

- [ ] Application accessible at Render URL
- [ ] Database connected and migrations run
- [ ] Storage link created
- [ ] Environment variables configured
- [ ] HTTPS working (automatic on Render)
- [ ] Stripe webhook configured
- [ ] Email configuration (if using)
- [ ] Test login/registration
- [ ] Test product creation
- [ ] Test cart functionality
- [ ] Test payment flow
- [ ] Test API endpoints

---

## 🔍 Troubleshooting

### Common Issues

1. **Build Fails**:
   - Check build logs in Render dashboard
   - Verify Node.js and PHP versions
   - Ensure all dependencies in `composer.json` and `package.json`

2. **Database Connection Error**:
   - Verify database credentials
   - Check database is running
   - Verify network access

3. **500 Error**:
   - Check `APP_DEBUG=true` temporarily
   - Check logs: `storage/logs/laravel.log`
   - Verify `APP_KEY` is set

4. **Storage Not Working**:
   - Run `php artisan storage:link`
   - Check file permissions
   - Verify storage directory exists

5. **Assets Not Loading**:
   - Verify `npm run build` completed
   - Check `public/build` directory
   - Clear cache: `php artisan cache:clear`

---

## 📊 Render Service Configuration

### Recommended Settings

- **Instance Type**: Free tier (or paid for production)
- **Auto-Deploy**: Yes (deploy on git push)
- **Health Check**: `/up` (Laravel health check route)
- **Environment**: Production

### Scaling (Paid Plans)

- **Auto-scaling**: Configure based on traffic
- **Multiple instances**: For high availability
- **Load balancing**: Automatic on Render

---

## 🔗 Useful Links

- [Render Documentation](https://render.com/docs)
- [Laravel on Render](https://render.com/docs/deploy-laravel)
- [Render Pricing](https://render.com/pricing)

---

## 📝 Environment Variables Reference

### Complete .env for Production

```env
# Application
APP_NAME=Traxtar
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host.render.com
DB_PORT=3306
DB_DATABASE=traxtar
DB_USERNAME=traxtar
DB_PASSWORD=your-password

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@traxtar.com
MAIL_FROM_NAME="Traxtar"

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=usd

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

---

## ✅ Deployment Checklist

### Pre-Deployment
- [ ] Code committed and pushed to Git
- [ ] All tests passing (if applicable)
- [ ] Environment variables documented
- [ ] Database schema ready
- [ ] Stripe account configured

### Deployment
- [ ] Render account created
- [ ] Database created on Render
- [ ] Web service created
- [ ] Environment variables set
- [ ] Build successful
- [ ] Application accessible

### Post-Deployment
- [ ] Migrations run
- [ ] Storage link created
- [ ] Database seeded (if needed)
- [ ] Stripe webhook configured
- [ ] Email configured
- [ ] All features tested
- [ ] HTTPS verified
- [ ] Performance checked

---

**Document Version**: 1.0  
**Last Updated**: 2026-01-31
