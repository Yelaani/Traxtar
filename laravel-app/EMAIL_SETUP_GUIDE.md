# Email Setup Guide

## ✅ What I've Done

1. **Made notifications send immediately** - Removed queue requirement so emails send right away
2. **Updated `.env` file** - Changed from `log` to `smtp` driver

## 📧 Setup Instructions

### Option 1: Mailtrap (Recommended for Testing)

**Mailtrap is perfect for testing** - it captures all emails without actually sending them.

1. **Sign up for free**: https://mailtrap.io (free account available)
2. **Get your credentials**:
   - Go to Mailtrap dashboard
   - Select "Inboxes" → "SMTP Settings"
   - Choose "Laravel" from the dropdown
3. **Update your `.env` file** with your Mailtrap credentials:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username_here
MAIL_PASSWORD=your_mailtrap_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@traxtar.com"
MAIL_FROM_NAME="${APP_NAME}"
```

4. **Clear config cache**:
```bash
php artisan config:clear
```

5. **Test it**: Send an admin invitation and check your Mailtrap inbox!

---

### Option 2: Gmail (For Production)

**For actual email delivery**, use Gmail SMTP:

1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Enter "Traxtar" as the name
   - Copy the 16-character password
3. **Update your `.env` file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_16_character_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_email@gmail.com"
MAIL_FROM_NAME="Traxtar"
```

4. **Clear config cache**:
```bash
php artisan config:clear
```

---

## 🧪 Testing

After setting up your email:

1. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

2. **Send a test invitation**:
   - Go to `/admin/admins/invite`
   - Enter an email address
   - Submit the form

3. **Check your email** (or Mailtrap inbox)

---

## 📝 Current Status

- ✅ Notification sends immediately (no queue needed)
- ✅ SMTP driver configured
- ⚠️ **You need to add your Mailtrap or Gmail credentials to `.env`**

---

## 🔍 Troubleshooting

**If emails still don't send:**

1. **Check `.env` file** - Make sure credentials are correct (no quotes around passwords)
2. **Clear cache**: `php artisan config:clear`
3. **Check logs**: `storage/logs/laravel.log` for error messages
4. **Test connection**: Try sending a test email from Laravel Tinker

**For Gmail:**
- Make sure you're using an **App Password**, not your regular password
- App passwords are 16 characters with spaces (remove spaces when pasting)

**For Mailtrap:**
- Double-check your username and password from the Mailtrap dashboard
- Make sure you're using the correct inbox credentials
