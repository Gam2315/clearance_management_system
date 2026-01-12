# Academic Year Management Features

## 🎯 Overview

This document covers two key features:
1. **Auto Assignment**: Automatically assigns students when creating active academic years
2. **Selective Archiving**: Archives only clearances (not students) when academic year becomes inactive

## 🔧 Feature 1: Auto Assignment on Creation

### **Creating Academic Year with "Active" Status**

When you create a new academic year and set the status to "active":

1. **Academic Year Created**: New academic year record is saved
2. **Other Years Deactivated**: All other academic years are set to "inactive"
3. **Students Auto-Assigned**: All active (non-archived) students get assigned to the new academic year
4. **Clearances Created**: New clearance records are automatically created for all students
5. **Success Message**: Detailed feedback about the assignment process

### **Creating Academic Year with "Inactive" Status**

When you create a new academic year and set the status to "inactive":

1. **Academic Year Created**: New academic year record is saved
2. **No Auto-Assignment**: Students remain with their current academic year
3. **No Clearances**: No clearance records are created
4. **Manual Activation**: You can activate it later using the edit/update function

## 🗂️ Feature 2: Selective Archiving

### **Setting Academic Year to "Inactive"**

When you change an academic year status from "active" to "inactive":

1. **Clearances Archived**: All clearances for that academic year are archived (`is_archived = true`)
2. **Students Preserved**: Student records remain unarchived and accessible
3. **Data Integrity**: All historical data is preserved for reporting
4. **Selective Hiding**: Only clearances are hidden from active operations

### **Reactivating Academic Year**

When you change an academic year status from "inactive" to "active":

1. **Clearances Unarchived**: Previously archived clearances are restored (`is_archived = false`)
2. **Students Remain Active**: Student records were never archived, so they remain accessible
3. **Full Restoration**: All clearance data becomes available again

## 📋 Step-by-Step Process

### **Automatic Assignment Process:**

```
1. User creates academic year with status = "active"
   ↓
2. System creates academic year record
   ↓
3. System sets all other academic years to "inactive"
   ↓
4. System updates all active students: academic_id = new_academic_year_id
   ↓
5. System runs clearance creation command
   ↓
6. System creates clearance records for all students
   ↓
7. System creates clearance status records for all departments
   ↓
8. Success message with assignment details
```

## 🎓 Usage Examples

### **Example 1: Start New School Year (Auto Assignment)**
```
Action: Create "2024-2025 1st Semester" with status "active"
Result:
- All 500 students automatically assigned to 2024-2025
- 500 new clearance records created
- All departments get pending clearance statuses for each student
- Previous academic year (2023-2024) becomes inactive
- Previous clearances get archived
```

### **Example 2: Prepare Future Academic Year**
```
Action: Create "2025-2026 1st Semester" with status "inactive"
Result:
- Academic year created but not activated
- Students remain in current academic year
- No clearances created yet
- Can be activated later when ready
```

### **Example 3: End Current Academic Year (Selective Archiving)**
```
Action: Set "2023-2024 2nd Semester" status to "inactive"
Result:
- All clearances for 2023-2024 are archived
- Students remain unarchived and accessible
- Historical clearance data preserved but hidden from active operations
- Students can be assigned to new academic year when ready
```

### **Example 4: Reactivate Previous Academic Year**
```
Action: Set "2023-2024 2nd Semester" status back to "active"
Result:
- All previously archived clearances are unarchived
- Students were never archived, so they remain accessible
- Full clearance functionality restored for that academic year
```

## ✅ Benefits

### **Auto Assignment Benefits:**
1. **Time Saving**: No need to manually activate academic years after creation
2. **Consistency**: Ensures only one academic year is active at a time
3. **Automation**: Automatically creates all necessary clearance records
4. **Error Prevention**: Reduces manual steps and potential mistakes
5. **Immediate Readiness**: Students can start clearance process immediately

### **Selective Archiving Benefits:**
1. **Data Preservation**: Student records are never lost or hidden
2. **Selective Management**: Only clearances are archived, not students
3. **Historical Access**: Student data remains available for reporting across all years
4. **Flexible Reactivation**: Can easily restore previous academic years
5. **Clean Operations**: Inactive clearances don't interfere with current operations

## 🔍 What Gets Updated

### **Students Table**
- `academic_id` field updated for all active students
- Archived students remain unchanged

### **Academic Years Table**
- New academic year created with specified status
- Other academic years set to "inactive" if new one is "active"

### **Clearances Table**
- New clearance records created for each student (when creating active academic year)
- `is_archived` field updated when academic year status changes
- Linked to the academic year

### **Clearance Statuses Table**
- Pending status records created for all relevant departments
- Each student gets clearance requirements for all departments

## 🚨 Important Notes

### **Auto Assignment Notes:**
1. **Only Active Students**: Archived students are not assigned to the new academic year
2. **One Active Year**: Only one academic year can be active at a time
3. **Automatic Deactivation**: Creating an active academic year deactivates all others
4. **Clearance Creation**: Uses the existing `clearance:create-for-new-year` command
5. **Error Handling**: If clearance creation fails, academic year is still created but with warning message

### **Selective Archiving Notes:**
1. **Students Never Archived**: Student records are always preserved regardless of academic year status
2. **Only Clearances Archived**: When academic year becomes inactive, only clearances are archived
3. **Reversible Process**: Reactivating an academic year unarchives its clearances
4. **Historical Reporting**: All student data remains available for cross-year reporting
5. **Clean Separation**: Archived clearances don't appear in active operations but remain in database

## 🧪 Testing

Run the test suite to verify functionality:

```bash
php artisan test tests/Feature/AcademicYearAutoAssignmentTest.php
```

Tests cover:
- Automatic student assignment for active academic years
- No assignment for inactive academic years  
- Deactivation of other academic years
- Exclusion of archived students
- Clearance creation verification
