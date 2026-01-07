# Sub-Modules Planning Document

## Current Status

✅ **Completed:**
- Created shared `header.php` and `footer.php` include files
- Fixed dashboard logo to be circular
- All 6 core modules have index pages with hardcoded data

⚠️ **Pending:**
- Integrate shared header/sidebar to all 6 module pages
- Add sub-modules to each core module
- Fix favicon display on all pages

---

## Proposed Sub-Modules for Each Core Module

### 1. Committee Profiles
**Sub-modules:**
- **Members Management** - Add/remove/edit committee members
- **Committee Documents** - Upload and manage committee documents
- **Meeting History** - View past meetings for this committee
- **Jurisdiction & Mandate** - Edit committee scope and responsibilities

### 2. Meetings
**Sub-modules:**
- **Schedule Meeting** - Create new meeting
- **Attendance Tracking** - Mark attendance, track quorum
- **Minutes Management** - Upload/view meeting minutes
- **Meeting Calendar** - Calendar view of all meetings

### 3. Agendas & Deliberation
**Sub-modules:**
- **Agenda Items** - Add/edit/reorder agenda items
- **Deliberation Notes** - Record discussion points
- **Voting Interface** - Record votes on agenda items
- **Agenda Templates** - Save and reuse agenda templates

### 4. Referrals
**Sub-modules:**
- **Referral Tracking** - Timeline view of referral progress
- **Committee Assignment** - Assign referrals to committees
- **Status Updates** - Update referral status and notes
- **Deadline Management** - Set and track deadlines

### 5. Action Items
**Sub-modules:**
- **Task Assignment** - Assign tasks to members
- **Progress Tracking** - Update task progress
- **Deadline Alerts** - View overdue and upcoming deadlines
- **Completion Verification** - Mark tasks as complete with notes

### 6. Reports & Recommendations
**Sub-modules:**
- **Report Drafting** - Create new reports
- **Recommendation Formulation** - Approve/Amend/Reject recommendations
- **Approval Workflow** - Submit for approval, track status
- **Report Archive** - View historical reports

---

## Implementation Approach

### Option 1: Full Integration (Recommended)
1. Update all 6 module index pages to use shared header/sidebar
2. Create sub-module pages for each core module (24 pages total)
3. Add navigation tabs/buttons within each module
4. Implement functionality for each sub-module

### Option 2: Phased Approach
1. Update all 6 modules with shared header first
2. Create sub-modules for one module at a time
3. Test each module before moving to next

---

## Questions for User

1. **Sub-modules**: Do the proposed sub-modules above match your vision?
2. **Approach**: Should I do full integration or phased?
3. **Priority**: Which module's sub-modules are most important to create first?
4. **Functionality**: How detailed should the sub-module functionality be? (Basic CRUD or advanced features?)

---

## Technical Notes

- All pages will use shared `includes/header.php` and `includes/footer.php`
- Circular logo will be consistent across all pages
- Favicon will be properly set on all pages
- Navigation will highlight active module
- All pages will support dark mode
- Responsive design for mobile/tablet/desktop
