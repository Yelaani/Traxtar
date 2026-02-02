# Fixing UI Issues on Render

## Common UI Problems

If your UI looks broken (no styles, messed up layout), check these:

### 1. APP_URL Not Set Correctly

**Problem:** CSS and JavaScript files not loading, page looks unstyled.

**Solution:**
1. Go to Render Dashboard → Your Web Service → Environment tab
2. Find `APP_URL` environment variable
3. Set it to your exact Render URL:
   ```
   APP_URL=https://traxtar-1.onrender.com
   ```
   (Replace `traxtar-1` with your actual service name)
4. **Important:** 
   - Must include `https://`
   - No trailing slash
   - Must match your Render service URL exactly
5. Save and redeploy

### 2. Vite Assets Not Built

**Problem:** `manifest.json` missing, assets return 404 errors.

**Check:**
- Go to Render Dashboard → Your Web Service → Logs
- Look for "✓ Vite assets built successfully" in build logs
- If you see "ERROR: Vite build failed", check Node.js version

**Solution:**
- Assets should build automatically during Docker build
- If build fails, check `package.json` and `vite.config.js`

### 3. Storage Link Missing

**Problem:** Product images not showing.

**Check:**
- In Render logs, look for "✓ Storage symlink created successfully"
- If missing, the entrypoint script will try to create it automatically

**Solution:**
- Storage link is created automatically at container startup
- If it fails, check file permissions in logs

### 4. Browser Console Errors

**To Debug:**
1. Open your site in browser
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Look for 404 errors on CSS/JS files
5. Check Network tab for failed asset requests

**Common Errors:**
- `GET https://your-app.onrender.com/build/app-xxx.css 404` → Assets not built or APP_URL wrong
- `GET https://your-app.onrender.com/storage/... 404` → Storage link not created

## Quick Checklist

- [ ] `APP_URL` is set to your exact Render URL (with https://)
- [ ] Build logs show "✓ Vite assets built successfully"
- [ ] Startup logs show "✓ Storage symlink created successfully"
- [ ] Browser console has no 404 errors for CSS/JS files
- [ ] Images load correctly

## Testing After Fix

1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check browser console for errors
4. Verify styles are applied (Tailwind CSS classes work)
5. Check that images display correctly

## Still Not Working?

1. Check Render build logs for errors
2. Check Render runtime logs for warnings
3. Verify all environment variables are set correctly
4. Try redeploying the service
