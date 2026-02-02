# Traxtar Application - Complete Deployment Guide to Render.com

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Step-by-Step Deployment](#step-by-step-deployment)
4. [Post-Deployment Configuration](#post-deployment-configuration)
5. [Verification & Testing](#verification--testing)
6. [Troubleshooting](#troubleshooting)
7. [Maintenance & Updates](#maintenance--updates)

---

## Prerequisites

### Required Accounts & Services

1. **Render.com Account**
   - Sign up at https://render.com
   - Free tier available (with limitations)
   - Email verification required

2. **Git Repository**
   - Code hosted on GitHub, GitLab, or Bitbucket
   - Repository must be accessible to Render
   - All code committed and pushed

3. **Stripe Account** (for payments)
   - Account at https://stripe.com
   - API keys (test or live)
   - Webhook endpoint configured

4. **Email Service** (optional)
   - SMTP service (Mailtrap, SendGrid, etc.)
   - For email verification and notifications

---

## Pre-Deployment Checklist

### Code Preparation

- [ ] All code committed to Git
- [ ] All changes pushed to repository
- [ ] `.env` file NOT committed (in `.gitignore`)
- [ ] `vendor/` directory NOT committed
- [ ] `node_modules/` NOT committed
- [ ] Application tested locally
- [ ] All migrations ready
- [ ] Database schema finalized

### Configuration Files

- [ ] `render.yaml` exists (optional)
- [ ] `.render-build.sh` exists (optional)
- [ ] `.render-start.sh` exists (optional)
- [ ] `composer.json` has all dependencies
- [ ] `package.json` has all dependencies

### Documentation

- [ ] Environment variables documented
- [ ] Database credentials ready
- [ ] Stripe keys ready
- [ ] Email service credentials ready (if using)

---

## Step-by-Step Deployment

### Step 1: Create Render Account

1. **Visit Render.com**
   - Go to https://render.com
   - Click "Get Started" or "Sign Up"

2. **Sign Up**
   - Use GitHub, GitLab, or email
   - Verify email address
   - Complete profile setup

3. **Dashboard Access**
   - You'll be redirected to the dashboard
   - Familiarize yourself with the interface

---

### Step 2: Create Database

1. **Navigate to Database Creation**
   - Click "New +" button
   - Select "PostgreSQL" or "MySQL"
   - **Note**: Free tier available for PostgreSQL

2. **Configure Database**
   ```
   Name: traxtar-db
   Database: traxtar
   User: traxtar
   Region: Choose closest to your users
   PostgreSQL Version: Latest (or MySQL 8.0)
   Plan: Free (or paid for production)
   ```

3. **Create Database**
   - Click "Create Database"
   - Wait for provisioning (1-2 minutes)
   - **Important**: Note the connection details

4. **Save Connection Details**
   - Internal Database URL (for Render services)
   - External Database URL (for local access)
   - Database name, username, password
   - **Keep these secure!**

---

### Step 3: Connect Git Repository

1. **Navigate to Web Service**
   - Click "New +" button
   - Select "Web Service"

2. **Connect Repository**
   - Choose your Git provider (GitHub, GitLab, Bitbucket)
   - Authorize Render to access your repositories
   - Select the repository containing Traxtar
   - Select the branch (usually `main` or `master`)

3. **Repository Settings**
   - Repository: `your-username/traxtar` (or your repo name)
   - Branch: `main`
   - Root Directory: Leave empty (or `laravel-app` if code is in subdirectory)

---

### Step 4: Configure Web Service

#### Basic Settings

```
Name: traxtar
Environment: PHP
Region: Choose closest to your users
Branch: main (or your default branch)
```

#### Build Settings

**Build Command**:
```bash
composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan key:generate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

**Or use build script** (if `.render-build.sh` exists):
```bash
bash .render-build.sh
```

**Start Command**:
```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

**Or use start script** (if `.render-start.sh` exists):
```bash
bash .render-start.sh
```

#### Advanced Settings

- **Auto-Deploy**: Yes (deploy on every push)
- **Health Check Path**: `/up`
- **Instance Type**: Free (or paid for production)

---

### Step 5: Configure Environment Variables

Click "Environment" tab and add the following variables:

#### Application Variables

```env
APP_NAME=Traxtar
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
LOG_LEVEL=error
```

**Note**: `APP_KEY` will be generated automatically, or you can generate it manually:
```bash
php artisan key:generate --show
```

#### Database Variables

```env
DB_CONNECTION=mysql
DB_HOST=your-db-host.render.com
DB_PORT=3306
DB_DATABASE=traxtar
DB_USERNAME=traxtar
DB_PASSWORD=your-database-password
```

**For PostgreSQL**:
```env
DB_CONNECTION=pgsql
DB_HOST=your-db-host.render.com
DB_PORT=5432
DB_DATABASE=traxtar
DB_USERNAME=traxtar
DB_PASSWORD=your-database-password
```

**How to get database credentials**:
1. Go to your database service in Render
2. Click on "Connections"
3. Copy the "Internal Database URL" or individual values

#### Session & Cache Variables

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

#### Stripe Variables

```env
STRIPE_KEY=pk_live_your_publishable_key
STRIPE_SECRET=sk_live_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
STRIPE_CURRENCY=usd
```

**For Testing** (use test keys):
```env
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_test_webhook_secret
```

#### Mail Variables (Optional)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@traxtar.com
MAIL_FROM_NAME="Traxtar"
```

#### Complete Environment Variables List

See `RENDER_DEPLOYMENT.md` for the complete list.

---

### Step 6: Deploy Application

1. **Review Configuration**
   - Double-check all settings
   - Verify environment variables
   - Ensure build/start commands are correct

2. **Create Service**
   - Click "Create Web Service"
   - Render will start building your application

3. **Monitor Build Process**
   - Watch the build logs
   - Look for any errors
   - Build typically takes 3-5 minutes

4. **Build Success Indicators**
   - ✅ "Build successful" message
   - ✅ "Deploying..." status
   - ✅ "Live" status with green indicator

5. **Note Your Application URL**
   - Format: `https://your-app-name.onrender.com`
   - This is your production URL
   - Update `APP_URL` if different

---

## Post-Deployment Configuration

### Step 1: Run Database Migrations

1. **Access Shell**
   - Go to your web service in Render
   - Click "Shell" tab
   - This opens a terminal

2. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

3. **Verify Migrations**
   ```bash
   php artisan migrate:status
   ```

4. **Seed Database (Optional)**
   ```bash
   php artisan db:seed
   ```

### Step 2: Create Storage Link

1. **In Shell**
   ```bash
   php artisan storage:link
   ```

2. **Verify**
   - Check that `public/storage` exists
   - This enables image uploads to work

### Step 3: Clear and Cache Configuration

1. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

2. **Re-cache (for production)**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Step 4: Configure Stripe Webhook

1. **Get Your Application URL**
   - Format: `https://your-app-name.onrender.com`
   - Webhook URL: `https://your-app-name.onrender.com/stripe/webhook`

2. **In Stripe Dashboard**
   - Go to Developers → Webhooks
   - Click "Add endpoint"
   - Enter webhook URL
   - Select events:
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `payment_intent.canceled`

3. **Get Webhook Secret**
   - After creating webhook, click on it
   - Copy "Signing secret"
   - Format: `whsec_...`

4. **Update Environment Variable**
   - In Render dashboard
   - Go to Environment variables
   - Update `STRIPE_WEBHOOK_SECRET`
   - Redeploy if needed

### Step 5: Test Email Configuration (Optional)

1. **Test Email Sending**
   - Use Laravel Tinker in Shell:
   ```bash
   php artisan tinker
   ```
   ```php
   Mail::raw('Test email', function($message) {
       $message->to('your-email@example.com')
                ->subject('Test Email');
   });
   ```

2. **Verify Email Delivery**
   - Check your inbox
   - Check spam folder
   - Verify SMTP settings if not received

---

## Verification & Testing

### Step 1: Basic Application Check

1. **Visit Application URL**
   - `https://your-app-name.onrender.com`
   - Should see homepage

2. **Check Health Endpoint**
   - `https://your-app-name.onrender.com/up`
   - Should return 200 OK

3. **Verify HTTPS**
   - URL should start with `https://`
   - Browser should show secure connection
   - No mixed content warnings

### Step 2: Authentication Testing

1. **Test Registration**
   - Go to `/register`
   - Create a test account
   - Verify email (if enabled)

2. **Test Login**
   - Go to `/login`
   - Login with test account
   - Verify redirect to dashboard

3. **Test Logout**
   - Click logout
   - Verify redirect to homepage

### Step 3: Feature Testing

1. **Product Management (Admin)**
   - Login as admin
   - Go to `/admin/products`
   - Create a product
   - Edit a product
   - Delete a product
   - Verify images upload

2. **Product Shop (Public)**
   - Go to `/products`
   - Browse products
   - Test search
   - Test sorting
   - Test pagination

3. **Shopping Cart**
   - Add products to cart
   - Update quantities
   - Remove items
   - Verify cart persists

4. **Checkout & Orders**
   - Go to checkout
   - Fill shipping details
   - Place order
   - Verify order created

5. **Payment Flow**
   - Go to order details
   - Click "Pay Now"
   - Complete Stripe payment (use test card)
   - Verify payment success
   - Check order status updated

6. **API Testing**
   - Test API authentication
   - Test product endpoints
   - Test cart endpoints
   - Test order endpoints
   - Use Postman or similar tool

### Step 4: Performance Check

1. **Page Load Times**
   - Check homepage load time
   - Check product pages
   - Should be < 3 seconds

2. **Database Performance**
   - Check query times
   - Verify indexes are used
   - Monitor slow queries

3. **Asset Loading**
   - Verify CSS loads
   - Verify JavaScript loads
   - Verify images load
   - Check browser console for errors

---

## Troubleshooting

### Build Failures

**Problem**: Build fails during deployment

**Solutions**:
1. **Check Build Logs**
   - Go to service → Logs
   - Look for error messages
   - Common issues:
     - Missing dependencies
     - PHP version mismatch
     - Node version mismatch

2. **Verify Dependencies**
   - Check `composer.json`
   - Check `package.json`
   - Ensure all required packages listed

3. **Check PHP Version**
   - Render uses PHP 8.2+ by default
   - Verify compatibility in `composer.json`

4. **Check Node Version**
   - Render uses Node 18+ by default
   - Verify compatibility in `package.json`

### Application Not Starting

**Problem**: Application shows "Application Error"

**Solutions**:
1. **Check Start Command**
   - Verify start command is correct
   - Should be: `php artisan serve --host=0.0.0.0 --port=$PORT`

2. **Check Environment Variables**
   - Verify `APP_KEY` is set
   - Verify database credentials
   - Check all required variables

3. **Check Logs**
   - Go to service → Logs
   - Look for runtime errors
   - Check `storage/logs/laravel.log` in Shell

4. **Enable Debug Temporarily**
   - Set `APP_DEBUG=true`
   - Redeploy
   - Check error messages
   - **Remember to set back to `false`**

### Database Connection Errors

**Problem**: "SQLSTATE[HY000] [2002] Connection refused"

**Solutions**:
1. **Verify Database Credentials**
   - Check `DB_HOST`
   - Check `DB_PORT`
   - Check `DB_DATABASE`
   - Check `DB_USERNAME`
   - Check `DB_PASSWORD`

2. **Check Database Status**
   - Go to database service
   - Verify it's running
   - Check connection details

3. **Use Internal URL**
   - For Render services, use internal database URL
   - External URL is for local access only

4. **Check Network Access**
   - Verify database allows connections
   - Check firewall settings

### 500 Internal Server Error

**Problem**: Application returns 500 error

**Solutions**:
1. **Check Application Logs**
   ```bash
   # In Shell
   tail -f storage/logs/laravel.log
   ```

2. **Verify File Permissions**
   - Storage directory writable
   - Cache directory writable
   - Logs directory writable

3. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Check Environment Variables**
   - Verify all required variables set
   - Check for typos
   - Verify values are correct

### Assets Not Loading

**Problem**: CSS/JavaScript not loading

**Solutions**:
1. **Verify Build Completed**
   - Check build logs
   - Ensure `npm run build` succeeded

2. **Check Public Directory**
   - Verify `public/build` exists
   - Check for `manifest.json`

3. **Clear Cache**
   ```bash
   php artisan view:clear
   ```

4. **Rebuild Assets**
   - In Shell:
   ```bash
   npm run build
   ```

### Storage Not Working

**Problem**: Images not uploading or displaying

**Solutions**:
1. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

2. **Check Permissions**
   - Verify `storage/app/public` is writable
   - Verify `public/storage` exists

3. **Check File System**
   - Verify `FILESYSTEM_DISK=local` in env
   - Check disk configuration

### Stripe Webhook Not Working

**Problem**: Payments not updating via webhook

**Solutions**:
1. **Verify Webhook URL**
   - Check webhook endpoint is correct
   - Format: `https://your-app.onrender.com/stripe/webhook`

2. **Check Webhook Secret**
   - Verify `STRIPE_WEBHOOK_SECRET` is set
   - Should start with `whsec_`

3. **Check Webhook Logs**
   - In Stripe Dashboard → Webhooks
   - Check event delivery
   - Look for errors

4. **Test Webhook**
   - Use Stripe CLI for testing
   - Or use test mode

---

## Maintenance & Updates

### Updating Application

1. **Make Changes Locally**
   - Make code changes
   - Test locally
   - Commit changes

2. **Push to Git**
   ```bash
   git add .
   git commit -m "Update description"
   git push origin main
   ```

3. **Auto-Deploy**
   - Render automatically detects push
   - Starts new build
   - Deploys when ready

4. **Manual Deploy**
   - Go to service → Manual Deploy
   - Select branch/commit
   - Click "Deploy"

### Database Migrations

1. **Create Migration**
   ```bash
   php artisan make:migration migration_name
   ```

2. **Test Locally**
   ```bash
   php artisan migrate
   ```

3. **Deploy Migration**
   - Push code to Git
   - After deployment, run in Shell:
   ```bash
   php artisan migrate --force
   ```

### Monitoring

1. **Check Logs**
   - Service → Logs
   - Real-time log streaming
   - Filter by level

2. **Check Metrics**
   - Service → Metrics
   - CPU usage
   - Memory usage
   - Request rate

3. **Check Health**
   - Service → Health
   - Uptime status
   - Response times

### Backup

1. **Database Backup**
   - Render provides automatic backups
   - Or export manually:
   ```bash
   # In Shell
   mysqldump -h host -u user -p database > backup.sql
   ```

2. **Code Backup**
   - Code is in Git (already backed up)
   - Regular commits recommended

---

## Security Best Practices

1. **Environment Variables**
   - Never commit `.env`
   - Use Render environment variables
   - Rotate secrets regularly

2. **Application Settings**
   - `APP_DEBUG=false` in production
   - `APP_ENV=production`
   - Strong `APP_KEY`

3. **Database**
   - Use strong passwords
   - Restrict network access
   - Regular backups

4. **HTTPS**
   - Automatic on Render
   - Verify SSL certificate
   - Use secure cookies

5. **Updates**
   - Keep dependencies updated
   - Monitor security advisories
   - Apply patches promptly

---

## Support & Resources

### Render Resources
- [Render Documentation](https://render.com/docs)
- [Render Support](https://render.com/support)
- [Render Status](https://status.render.com)

### Laravel Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Forums](https://laravel.io/forum)

### Application Resources
- See `RENDER_DEPLOYMENT.md` for detailed reference
- See `PHASE_9_1_RENDER_SETUP.md` for setup details

---

**Document Version**: 1.0  
**Last Updated**: 2026-01-31
