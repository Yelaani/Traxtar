# Gmail SMTP Setup - Step by Step Guide

## Step 1: Enable 2-Factor Authentication

1. Go to your Google Account: https://myaccount.google.com
2. Click on **Security** (left sidebar)
3. Under "How you sign in to Google", find **2-Step Verification**
4. Click **Get Started** and follow the prompts
5. You'll need to verify your phone number

## Step 2: Generate App Password

1. After 2FA is enabled, go to: https://myaccount.google.com/apppasswords
   - Or: Google Account → Security → 2-Step Verification → App passwords
2. Select **Mail** from the dropdown
3. Select **Other (Custom name)** from device dropdown
4. Type: **Traxtar** (or any name you prefer)
5. Click **Generate**
6. **Copy the 16-character password** (it will look like: `abcd efgh ijkl mnop`)
   - ⚠️ **Important**: Remove spaces when pasting into .env file
   - Example: `abcdefghijklmnop`

## Step 3: Update .env File

I'll help you update the .env file with your Gmail credentials.

## Step 4: Test

After updating, we'll test sending an email.
