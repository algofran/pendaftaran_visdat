# Export to Excel - Troubleshooting Guide

This guide helps diagnose and fix the "500 Internal Server Error" when exporting to Excel.

## Quick Fix Summary

The most likely cause of the 500 error is **MySQL version compatibility**. The original code used `ROW_NUMBER()` which requires MySQL 8.0+, but many production servers run older versions.

### ✅ Solution Applied
1. **Updated `export-data.php`** with MySQL version detection
2. **Added comprehensive error logging** 
3. **Created fallback queries** for older MySQL versions
4. **Added database connection testing**

## Testing the Fix

### Step 1: Test Database Connection
Visit: `https://registrasi.visdatteknik.co.id/admin/test-db-connection.php`

This will show:
- ✅ Config file status
- ✅ Database connection status  
- ✅ MySQL version
- ✅ Table structure
- ❌ Any issues found

### Step 2: Check Error Logs
After attempting export, check: `admin/export_error.log`

Common log entries:
```
Export attempt started at 2024-01-XX XX:XX:XX
Config loaded successfully
User authenticated successfully
Database connection confirmed
Applications table exists
MySQL version: 5.7.XX
Using compatible query for MySQL < 8.0
Query executed successfully. Found X applications
Export completed successfully
```

### Step 3: Test Export Function
Try the export again from the admin panel.

## Common Issues & Solutions

### Issue 1: MySQL Version < 8.0
**Symptoms:** 500 error, log shows "ROW_NUMBER() function not supported"
**Solution:** ✅ Fixed - code now detects MySQL version automatically

### Issue 2: Database Connection Failed
**Symptoms:** 500 error, log shows "Database connection failed"
**Solution:** Update `config.php` with correct production credentials

### Issue 3: Wrong Database Credentials
**Symptoms:** 500 error, log shows "Access denied for user"
**Solution:** Check production database settings in `config.php`:
```php
define('DB_HOST', 'your_production_host');
define('DB_NAME', 'your_production_database');
define('DB_USER', 'your_production_user');
define('DB_PASS', 'your_production_password');
```

### Issue 4: Applications Table Missing
**Symptoms:** 500 error, log shows "Applications table does not exist"
**Solution:** Run `setup_db.php` or manually create the table

### Issue 5: PHP Memory Limit
**Symptoms:** 500 error with large datasets
**Solution:** Increase PHP memory limit in `.htaccess`:
```
php_value memory_limit 256M
```

### Issue 6: Session Not Started
**Symptoms:** "Unauthorized" error
**Solution:** Ensure admin is logged in first

## Production Configuration

### Update config.php for Production
Replace development settings with production values:

```php
// Development (current)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DEBUG', true);

// Production (recommended)
define('DB_HOST', 'your_db_host');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_secure_password');
define('DEBUG', false);
```

### Alternative Test Files
If main export still fails, try:
- `export-data-compatible.php` - MySQL 5.7 specific version
- `test-export.php` - Client-side only test

## File Structure After Fix

```
admin/
├── export-data.php              ← Updated with MySQL compatibility
├── export-data-compatible.php   ← Backup MySQL 5.7 version
├── test-db-connection.php       ← Database diagnostic tool
├── test-export.php              ← Client-side test
├── export_error.log             ← Error log file (auto-created)
├── EXPORT_TROUBLESHOOTING.md    ← This guide
└── admin-script.js              ← Frontend JavaScript (unchanged)
```

## Debug Process

1. **Check error logs first**: `admin/export_error.log`
2. **Test database**: Visit `test-db-connection.php`
3. **Verify config**: Check database credentials
4. **Test minimal export**: Use `export-data-compatible.php`
5. **Check server logs**: Look at PHP error logs

## Contact Support

If issues persist after following this guide:
1. Check `export_error.log` for specific errors
2. Run `test-db-connection.php` and note any failures
3. Provide the error log output and test results

**Last Updated:** January 2024  
**Version:** 2.0 (MySQL Compatibility Update)