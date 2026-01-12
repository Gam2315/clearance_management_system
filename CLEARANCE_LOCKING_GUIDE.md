# 🔒 Clearance Locking System - Complete User Guide

## Overview
The Clearance Locking System is designed to prevent students from accessing their clearance when they have incomplete requirements from previous academic years or have violations. This ensures accountability and proper completion of clearance requirements.

## 🎯 Key Features

### 1. **Automatic Locking**
- Students with incomplete clearances from previous academic years are automatically locked
- Students with active violations are automatically locked
- Prevents access to current clearance until issues are resolved

### 2. **Manual Locking**
- Administrators can manually lock individual clearances
- Bulk locking for current academic year incomplete clearances
- Custom lock reasons can be specified

### 3. **Unlocking System**
- Role-based unlock permissions (Admin, Registrar)
- Individual and bulk unlock capabilities
- Audit trail for all lock/unlock actions

### 4. **Student Experience**
- Clear lock notifications with reasons
- Contact information for resolution
- Prevents confusion about clearance status

## 🚀 How to Use the Locking System

### For Administrators

#### **1. View Locked Clearances**
```
Navigation: Admin Dashboard → Clearance Management → Locked Clearances
URL: /admin/clearance/locked
```

**Features:**
- View all locked clearances in a table format
- Filter by academic year and department
- See lock reasons and dates
- Statistics dashboard showing total locked clearances

#### **2. Lock Current Year Clearances (Bulk)**
```
Location: Locked Clearances page → "Lock Current Year" button
```

**Steps:**
1. Click "Lock Current Year" button
2. Confirm the action in the dialog
3. System will:
   - Find all students with incomplete clearances in current academic year
   - Lock their clearances automatically
   - Show success message with count of locked clearances
   - Refresh the page to show newly locked clearances

**What gets locked:**
- Students with `overall_status != 'cleared'`
- Only unlocked clearances
- Current active academic year only

#### **3. Unlock Individual Clearances**
```
Location: Locked Clearances table → "Unlock" button
```

**Steps:**
1. Click "Unlock" button for specific clearance
2. Enter unlock reason when prompted
3. Confirm the action
4. Clearance is immediately unlocked

#### **4. Bulk Unlock Clearances**
```
Location: Locked Clearances page → "Bulk Unlock" button
```

**Steps:**
1. Select clearances using checkboxes
2. Click "Bulk Unlock" button
3. Enter reason for unlocking in the modal
4. Click "Unlock Selected"
5. All selected clearances are unlocked

#### **5. View Detailed Clearance Information**
```
Location: Locked Clearances table → "View" button
```

**Information shown:**
- Student details
- Lock reason and date
- Who locked the clearance
- Current clearance status
- Unlock form

### For Students

#### **When Clearance is Locked**
Students will see:
- 🔒 **Red warning banner** on their clearance page
- **Lock reason** explaining why it's locked
- **Lock date** showing when it was locked
- **Contact information** (Registrar's Office) for resolution

**Student cannot:**
- Submit new clearance requirements
- Access clearance processing
- View detailed clearance status

**Student can:**
- See the lock notification
- Contact appropriate office for resolution
- View basic account information

## 🛠️ Command Line Tools

### **Lock Incomplete Clearances Command**
```bash
# Lock incomplete clearances for specific academic year
php artisan clearance:lock-incomplete --academic-year=5

# Dry run to see what would be locked
php artisan clearance:lock-incomplete --academic-year=5 --dry-run

# Force lock without confirmation
php artisan clearance:lock-incomplete --academic-year=5 --force

# Lock previous academic year (automatic detection)
php artisan clearance:lock-incomplete
```

**Command Options:**
- `--academic-year=ID`: Specific academic year to process
- `--dry-run`: Preview what would be locked without making changes
- `--force`: Skip confirmation prompts

**What the command does:**
1. Finds students with incomplete clearances
2. Shows a table of students to be locked
3. Asks for confirmation (unless --force is used)
4. Locks the clearances with detailed reasons
5. Updates student records
6. Logs all actions

### **Update Overall Status Command**
```bash
# Fix any mismatched overall statuses
php artisan clearance:update-overall-status
```

## 🔧 Technical Implementation

### **Database Fields**
```sql
-- Clearances table
is_locked BOOLEAN DEFAULT FALSE
lock_reason TEXT
locked_at TIMESTAMP
locked_by INTEGER (user_id)
can_unlock_roles JSON

-- Students table  
has_locked_clearance BOOLEAN DEFAULT FALSE
locked_academic_years JSON
```

### **Lock Enforcement Points**
1. **Student clearance access** - Prevents viewing/submitting
2. **Department processing** - Blocks clearance processing
3. **NFC tapping** - Shows lock message
4. **API endpoints** - Returns lock status

### **Permission System**
- **Lock permissions**: Admin, Dean, Employee (department-specific)
- **Unlock permissions**: Admin, Registrar (configurable per lock)
- **View permissions**: Admin, Registrar, Dean

## 📋 Best Practices

### **When to Lock Clearances**
1. **End of academic year** - Lock all incomplete clearances
2. **Student violations** - Lock until violations are resolved
3. **Missing requirements** - Lock until documents are submitted
4. **Administrative holds** - Lock for disciplinary reasons

### **Unlock Guidelines**
1. **Verify resolution** - Ensure issues are actually resolved
2. **Document reasons** - Always provide clear unlock reasons
3. **Notify students** - Inform students when unlocked
4. **Monitor compliance** - Check if students complete requirements

### **Maintenance Tasks**
1. **Regular cleanup** - Remove old lock records
2. **Audit reviews** - Check lock/unlock patterns
3. **System updates** - Keep lock logic current
4. **Training staff** - Ensure proper usage

## 🚨 Troubleshooting

### **Common Issues**

#### **"No locked clearances found"**
- Check if academic year is set correctly
- Verify students have clearance records
- Ensure clearances are actually incomplete

#### **"Permission denied to unlock"**
- Check user role (must be admin or registrar)
- Verify can_unlock_roles field in clearance
- Contact system administrator

#### **"Lock button not working"**
- Check browser console for JavaScript errors
- Verify CSRF token is present
- Refresh page and try again

#### **"Student still sees locked message"**
- Clear browser cache
- Check if clearance was actually unlocked
- Verify student is viewing correct academic year

### **Emergency Procedures**

#### **Unlock All Clearances (Emergency)**
```sql
-- Use with extreme caution!
UPDATE clearances SET 
    is_locked = FALSE,
    lock_reason = NULL,
    locked_at = NULL,
    locked_by = NULL,
    can_unlock_roles = NULL
WHERE is_locked = TRUE;

UPDATE students SET 
    has_locked_clearance = FALSE,
    locked_academic_years = NULL
WHERE has_locked_clearance = TRUE;
```

#### **Check Lock Status**
```sql
-- View all locked clearances
SELECT 
    c.id,
    s.student_number,
    u.firstname,
    u.lastname,
    c.lock_reason,
    c.locked_at
FROM clearances c
JOIN students s ON c.student_id = s.id
JOIN users u ON s.users_id = u.id
WHERE c.is_locked = TRUE;
```

## 📞 Support Contacts

- **Technical Issues**: System Administrator
- **Clearance Questions**: Registrar's Office  
- **Student Support**: Student Affairs Office
- **Emergency Access**: IT Department

---

**Last Updated**: December 2024
**Version**: 1.0
**System**: SPUP Clearance Management System
