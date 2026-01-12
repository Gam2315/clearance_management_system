# Production Deployment Checklist

## ✅ Code Cleanup Completed
- [x] Removed all debug view files (debug-clearance.blade.php, test-detection.blade.php, admin/debug/, admin/test-lock-enforcement.blade.php)
- [x] Removed all debug routes from web.php (/debug-detection, /debug-clearance, /test-detection, /debug-auth, /quick-student-login, /debug-nfc-data, /test-activity-log, /test-lock-enforcement)
- [x] Removed temporary files (read-uid.py, cookies.txt)
- [x] Cleaned up console.log statements from JavaScript files
- [x] Removed debug API test functions from view files

## 🔧 Environment Configuration

### Required Environment Variables (.env)
```bash
APP_NAME="Clearance Management System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-db-username
DB_PASSWORD=your-secure-password

# Session Security
SESSION_DRIVER=database
SESSION_LIFETIME=60
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# Security Settings
BCRYPT_ROUNDS=12
SECURITY_LOG_ENABLED=true
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=30
FORCE_HTTPS=true

# File Upload Security
MAX_FILE_SIZE=5120
ALLOWED_FILE_TYPES="jpeg,png,jpg,gif"
```

## 🚀 Pre-Deployment Steps

### 1. Security Configuration
- [ ] Ensure `APP_DEBUG=false` in production .env
- [ ] Set `APP_ENV=production` in .env
- [ ] Generate secure `APP_KEY` using `php artisan key:generate`
- [ ] Configure HTTPS enforcement (`FORCE_HTTPS=true`)
- [ ] Set secure session cookies (`SESSION_SECURE_COOKIE=true`)

### 2. Database Setup
- [ ] Run database migrations: `php artisan migrate --force`
- [ ] Seed initial data if needed: `php artisan db:seed --force`
- [ ] Verify database connection and tables

### 3. Cache and Optimization
- [ ] Clear all caches: `php artisan cache:clear`
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Clear view cache: `php artisan view:clear`
- [ ] Optimize for production: `php artisan optimize`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`

### 4. File Permissions
- [ ] Set proper permissions on storage directory: `chmod -R 775 storage`
- [ ] Set proper permissions on bootstrap/cache: `chmod -R 775 bootstrap/cache`
- [ ] Ensure web server can write to storage and cache directories

### 5. Asset Compilation
- [ ] Install production dependencies: `npm ci --production`
- [ ] Build assets for production: `npm run build`
- [ ] Verify compiled assets exist in public/build

## 🔒 Security Verification

### 1. Debug Mode
- [ ] Verify `APP_DEBUG=false` is set
- [ ] Test error pages show generic messages (not stack traces)
- [ ] Confirm no debug routes are accessible

### 2. HTTPS Configuration
- [ ] SSL certificate is properly installed
- [ ] HTTP requests redirect to HTTPS
- [ ] Security headers are properly set

### 3. File Upload Security
- [ ] File upload size limits are enforced
- [ ] Only allowed file types can be uploaded
- [ ] Uploaded files are properly validated

### 4. Authentication Security
- [ ] Login attempt limits are working
- [ ] IP lockout functionality is active
- [ ] Session security is properly configured

## 📊 Monitoring Setup

### 1. Logging
- [ ] Security logs are being written to storage/logs/security.log
- [ ] Application logs are being written to storage/logs/laravel.log
- [ ] Log rotation is configured to prevent disk space issues

### 2. Activity Logging
- [ ] User activities are being logged to database
- [ ] Activity log cleanup is scheduled (365 days retention)

## 🧪 Post-Deployment Testing

### 1. Basic Functionality
- [ ] Login system works correctly
- [ ] User roles and permissions are enforced
- [ ] NFC card detection functions properly
- [ ] Clearance management works as expected

### 2. Security Testing
- [ ] Debug routes return 404 errors
- [ ] Unauthorized access attempts are blocked
- [ ] File upload restrictions are enforced
- [ ] Session security is working

### 3. Performance Testing
- [ ] Page load times are acceptable
- [ ] Database queries are optimized
- [ ] Caching is working effectively

## 📝 Maintenance Tasks

### Daily
- [ ] Monitor error logs for issues
- [ ] Check security logs for suspicious activity

### Weekly
- [ ] Review activity logs
- [ ] Check disk space usage
- [ ] Verify backup integrity

### Monthly
- [ ] Update dependencies if security patches available
- [ ] Review and clean old log files
- [ ] Performance optimization review

## 🆘 Rollback Plan

### If Issues Occur
1. Keep previous version backup ready
2. Database backup before deployment
3. Quick rollback procedure documented
4. Emergency contact information available

---

**Note**: This checklist should be completed before deploying to production. Each item should be verified and checked off during the deployment process.
