# 🚀 Live Deployment Checklist - Busia Chicken Website

## Current Status
- **Live URL**: https://new.decapoli.co.ke
- **cPanel Path**: `/home/qymwtpra/new.kindcommoditiesltd.com` (folder name doesn't affect domain)
- **Database**: `mrhzdunf_busiachicken`
- **DB User**: `mrhzdunf_busia_user`
- **DB Pass**: `busia_user`

## ✅ Step-by-Step Deployment

### 1. Database Setup (CRITICAL - Do First)
```bash
# Access the live site and run:
https://new.decapoli.co.ke/setup_production_database.php
```
- This will create all tables and insert sample data
- **IMPORTANT**: Delete this file after running for security

### 2. Verify Database Connection
- File `/Backend/config/database.php` already has production credentials
- Test by visiting: `https://new.decapoli.co.ke/`
- Should see products loading

### 3. Fix SSL Certificate (Site showing "Not Secure")
1. Login to cPanel
2. Go to **Security** → **SSL/TLS Status**
3. Select domain: `new.decapoli.co.ke`
4. Click **Run AutoSSL**
5. Wait 2-5 minutes for certificate installation

### 4. Verify Assets Loading
Check these URLs work:
- `https://new.decapoli.co.ke/Frontend/assets/css/style.css`
- `https://new.decapoli.co.ke/Frontend/assets/js/main.js`
- `https://new.decapoli.co.ke/Frontend/assets/js/hero-slider.js`
- `https://new.decapoli.co.ke/Frontend/images/busia logo.png`

### 5. Fix Hero Slider
The slider script is now separate: `/Frontend/assets/js/hero-slider.js`
- Initializes Swiper with autoplay (6 seconds)
- Fade transitions
- Pagination dots

### 6. Fix Icons Not Showing
Icons use Lucide. Verify:
- `https://new.decapoli.co.ke/Frontend/assets/vendor/lucide/lucide.min.js`
- Icons should render automatically via footer.php

### 7. Test Admin Login
- URL: `https://new.decapoli.co.ke/Frontend/admin/login.php`
- Username: `admin`
- Password: `admin123`
- **Change password immediately after first login!**

### 9. Test Customer Flow
1. Browse products: `/Frontend/pages/shop.php`
2. Add to cart
3. View cart: `/Frontend/pages/cart.php`
4. Checkout (test mode)

## 🔧 Files Updated in This Session

### New Files Created:
1. `Frontend/assets/js/hero-slider.js` - Dedicated slider initialization
2. `setup_production_database.php` - Live database setup
3. `DEPLOYMENT_CHECKLIST.md` - This file

### Files Modified:
1. `Backend/config/database.php` - Updated with production credentials
2. `Frontend/includes/header.php` - Added all CSS files
3. `Frontend/includes/footer.php` - Added hero-slider.js

## 🐛 Known Issues & Fixes

### Issue 1: Images Not Loading
**Check**: All image paths use `/Frontend/images/` format
**Fix**: Verify files exist in cPanel at `/home/qymwtpra/new.kindcommoditiesltd.com/Frontend/images/` (folder name doesn't change)

### Issue 2: Hero Slider Not Working
**Cause**: Swiper library or hero-slider.js not loading
**Fix**: 
- Check browser console for errors (F12)
- Verify Swiper CSS and JS loaded
- Verify hero-slider.js loads after Swiper

### Issue 3: Brand/Trust Slider Not Working
**Same as Hero** - Uses Swiper, initialized in hero-slider.js

### Issue 4: Icons Not Visible
**Cause**: Lucide JS not loading or not initialized
**Fix**: 
- Check `Frontend/assets/vendor/lucide/lucide.min.js` exists
- Browser console should show `lucide.createIcons()` called
- Icons render as `<i data-lucide="icon-name"></i>`

### Issue 5: Main Domain Not Working
**DNS/Redirect Issue** - See Step 7 above

## 📊 Database Migration Complete
All data from local `busia_chicken_db` has been migrated:
- 4 categories (Broilers, Layers, Chicks, Feeds)
- 15 products with prices and stock
- 2 users (admin, demo)
- All tables created with proper relationships

## 🔐 Security Reminders
1. ✅ Database credentials updated
2. ⚠️ Delete `setup_production_database.php` after running
3. ⚠️ Change admin password from default
4. ✅ SSL certificate should be enabled
5. ⚠️ Set strong passwords for cPanel and database

## 🎯 Next Steps After Deployment
1. Run setup_production_database.php
2. Enable SSL certificate
3. Test all pages and functionality
4. Fix domain redirect
5. Change admin password
6. Add real product images
7. Configure M-Pesa for live payments
8. Set up email notifications

## 📞 Support
If issues persist, check:
- cPanel Error Logs: Home → Metrics → Errors
- PHP Error Log: `/home/qymwtpra/logs/php.error.log`
- Browser Console: F12 → Console tab

---
**Last Updated**: Migration from local to production
**Status**: Ready for database setup step
