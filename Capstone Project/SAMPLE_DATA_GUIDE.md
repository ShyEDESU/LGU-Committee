# Sample Data Initialization Guide

## 🎯 Purpose
This script creates realistic, interconnected sample data for the entire Committee Management System, allowing you to test all features and understand the complete workflow.

## 📊 What Gets Created

### 1. **3 Committees**
- Finance and Budget Committee
- Education and Culture Committee  
- Infrastructure and Public Works Committee

### 2. **10 Committee Members**
- 5 members in Finance Committee
- 3 members in Education Committee
- 2 members in Infrastructure Committee

### 3. **4 Meetings**
- 1 Completed meeting (with minutes and attendance)
- 3 Scheduled meetings (upcoming)

### 4. **11 Agenda Items**
- Linked to respective meetings
- Various types: Call to Order, Presentations, Discussions, Voting

### 5. **5 Attendance Records**
- All members marked present for completed meeting
- Quorum achieved

### 6. **1 Meeting Minutes**
- Approved minutes for completed meeting
- Includes decisions and action items

### 7. **3 Action Items**
- Linked to meetings and committees
- Various statuses and priorities

### 8. **3 Referrals**
- Assigned to different committees
- Different priority levels

---

## 🚀 How to Run

### **Option 1: Via Browser**
1. Open your browser
2. Navigate to: `http://localhost/[your-project-folder]/init-sample-data.php`
3. Wait for completion message
4. Click "Go to Dashboard"

### **Option 2: Direct Access**
1. Place `init-sample-data.php` in your project root
2. Access via: `http://localhost/Capstone%20Project/init-sample-data.php`

---

## ✅ What You Can Test After Running

### **Committee Profiles Module:**
- ✅ View all 3 committees
- ✅ See committee members
- ✅ Access Reports tab (shows statistics)
- ✅ Access Meetings tab (shows committee meetings)
- ✅ Access Referrals tab (shows assigned referrals)

### **Committee Meetings Module:**
- ✅ View all 4 meetings
- ✅ Click on completed meeting → See Details
- ✅ Access Attendance tab → See marked attendance
- ✅ Access Minutes tab → See approved minutes
- ✅ Access Documents tab

### **Agenda Builder Module:**
- ✅ View all agendas (4 meetings with items)
- ✅ Click on any agenda → See agenda items
- ✅ Access Comments tab
- ✅ Access Distribution tab
- ✅ Access Archive (from main list)

### **Action Items Module:**
- ✅ See 3 action items linked to meetings
- ✅ Track progress
- ✅ View by status

### **Referral Management Module:**
- ✅ See 3 referrals assigned to committees
- ✅ Track status
- ✅ View by priority

---

## 🔗 Data Interconnections

The sample data is fully interconnected:

```
Committees
    ↓
Members ← → Meetings
    ↓           ↓
Attendance  Agenda Items
    ↓           ↓
Minutes ← → Action Items
    ↓
Referrals
```

**Example Flow:**
1. Finance Committee has 5 members
2. Finance Committee has 2 meetings
3. First meeting (Jan 8) has 6 agenda items
4. All 5 members attended (marked present)
5. Minutes were recorded and approved
6. 2 action items were created from the meeting
7. 1 referral is assigned to the committee

---

## 🗑️ How to Reset Data

The script **clears existing data** before creating new data. To reset:

1. Simply run the script again
2. All old data will be cleared
3. Fresh sample data will be created

---

## 🎓 For Database Migration

This sample data structure matches what you'll need for database tables:

### **Tables You'll Need:**
1. `committees` - Committee information
2. `committee_members` - Members and their roles
3. `meetings` - Meeting schedules
4. `agenda_items` - Agenda items for meetings
5. `attendance` - Attendance records
6. `minutes` - Meeting minutes
7. `action_items` - Tasks from meetings
8. `referrals` - Referrals to committees

### **Key Relationships:**
- `committee_members.committee_id` → `committees.id`
- `meetings.committee_id` → `committees.id`
- `agenda_items.meeting_id` → `meetings.id`
- `attendance.meeting_id` → `meetings.id`
- `attendance.member_id` → `committee_members.id`
- `minutes.meeting_id` → `meetings.id`
- `action_items.committee_id` → `committees.id`
- `action_items.meeting_id` → `meetings.id`
- `referrals.committee_id` → `committees.id`

---

## 📝 Sample Data Details

### **Finance Committee Meeting (Completed)**
- **Date:** January 8, 2026
- **Topic:** Q1 2026 Budget Review
- **Attendance:** 5/5 members (100%)
- **Agenda Items:** 6 items
- **Duration:** 3 hours (9 AM - 12 PM)
- **Minutes:** Approved
- **Action Items:** 2 created

### **Upcoming Meetings**
1. **Finance:** Revenue Enhancement (Jan 20)
2. **Education:** School Infrastructure (Jan 15)
3. **Infrastructure:** Road Repair Program (Jan 18)

---

## ⚠️ Important Notes

1. **Session-Based:** Data is stored in `$_SESSION`, so it will be lost when you close the browser or session expires
2. **For Testing Only:** This is for development and testing
3. **Database Ready:** Structure is designed for easy database migration
4. **Realistic Data:** Uses actual committee workflows and terminology

---

## 🆘 Troubleshooting

**Problem:** "Cannot access committees/meetings"
- **Solution:** Make sure you ran `init-sample-data.php` first

**Problem:** "Data disappeared"
- **Solution:** Session expired, run init script again

**Problem:** "Some pages show no data"
- **Solution:** Refresh the page or run init script again

---

## 📞 Next Steps

After running the sample data:

1. **Explore the System:**
   - Go to Dashboard
   - Navigate through all modules
   - Test all features

2. **Follow the Workflow:**
   - View committees → See members
   - View meetings → See agendas
   - Mark attendance → Record minutes
   - Create action items → Track progress

3. **Prepare for Database:**
   - Note the data structure
   - Plan your database schema
   - Use this as reference for migration

---

**Ready to test!** Run the script and explore the fully functional system! 🚀
