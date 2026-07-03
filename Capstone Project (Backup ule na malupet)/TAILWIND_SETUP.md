# Tailwind CSS Implementation Guide

## Overview
Full migration to Tailwind CSS for Committee Management System with professional design matching template folder.

## Installation Steps

### Option 1: Using Tailwind CDN (Quickest - Production Ready)
Add this to the `<head>` of all PHP files:
```html
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          'cms-red': '#dc2626',
          'cms-dark': '#b91c1c',
        }
      }
    }
  }
</script>
```

### Option 2: Using npm + Build Process (Recommended for Production)

```bash
# Initialize npm project
npm init -y

# Install Tailwind and dependencies
npm install -D tailwindcss postcss autoprefixer

# Initialize Tailwind config
npx tailwindcss init -p

# Add to tailwind.config.js
module.exports = {
  content: [
    "./public/**/*.{php,html,js}",
    "./auth/**/*.{php,html,js}",
    "./app/**/*.{php,html,js}",
    "./resources/**/*.{php,html,js}",
  ],
  theme: {
    extend: {
      colors: {
        'cms-red': '#dc2626',
        'cms-dark': '#b91c1c',
        'cms-light': '#fef2f2',
      },
      animation: {
        'fade-in': 'fadeIn 0.3s ease-in',
        'slide-in': 'slideIn 0.3s ease-in',
      },
    },
  },
  plugins: [],
}

# Create CSS file: public/assets/css/tailwind-input.css
@tailwind base;
@tailwind components;
@tailwind utilities;

# Build command in package.json
"scripts": {
  "build": "tailwindcss -i ./public/assets/css/tailwind-input.css -o ./public/assets/css/tailwind.css",
  "dev": "tailwindcss -i ./public/assets/css/tailwind-input.css -o ./public/assets/css/tailwind.css --watch"
}

# Run build
npm run build
```

## Color Scheme (Matching Template)
- Primary Red: `#dc2626` (class: `text-red-600` or `bg-red-600`)
- Dark Red: `#b91c1c` (class: `text-red-700` or `bg-red-700`)
- Light Gray: `#f5f7fa` (class: `bg-gray-50`)
- Dark Gray: `#1f2937` (class: `bg-gray-800`)

## Implementation Plan

### Phase 1: Login Page (auth/login.php)
- Convert inline styles to Tailwind classes
- Keep security features intact
- Test lockout timer functionality

### Phase 2: Dashboard (public/dashboard.php)
- Implement Tailwind sidebar
- Create header with logo
- Add all 10 Committee Management modules
- Responsive hamburger menu

### Phase 3: Module Pages
- Create public/pages/committee-structure/
- Create public/pages/member-assignment/
- Create public/pages/referral-management/
- Create public/pages/meeting-scheduler/
- Create public/pages/agenda-builder/
- Create public/pages/deliberation-tools/
- Create public/pages/action-items/
- Create public/pages/report-generation/
- Create public/pages/inter-committee/
- Create public/pages/research-support/

### Phase 4: Testing & Optimization
- Mobile responsiveness (< 768px)
- Tablet view (768px - 1024px)
- Desktop view (> 1024px)
- Dark mode support
- Performance optimization

## Quick Reference

### Common Tailwind Classes Used
- Layout: `flex`, `grid`, `block`, `hidden`
- Spacing: `p-4`, `m-2`, `gap-4`
- Colors: `bg-red-600`, `text-gray-700`, `border-gray-200`
- Responsive: `md:`, `lg:`, `sm:` prefixes
- Animations: `hover:`, `focus:`, `transition`

## File Structure
```
Capstone Project/
├── public/
│   ├── dashboard.php (New: Tailwind-based)
│   ├── assets/
│   │   ├── css/
│   │   │   ├── tailwind.css (Generated)
│   │   │   └── tailwind-input.css (Source)
│   │   └── js/
│   ├── pages/
│   │   ├── committee-structure/
│   │   ├── member-assignment/
│   │   ├── referral-management/
│   │   ├── meeting-scheduler/
│   │   ├── agenda-builder/
│   │   ├── deliberation-tools/
│   │   ├── action-items/
│   │   ├── report-generation/
│   │   ├── inter-committee/
│   │   └── research-support/
├── auth/
│   └── login.php (Updated: Tailwind)
├── package.json (New)
├── tailwind.config.js (New)
└── postcss.config.js (New)
```

## Next Steps
1. Choose implementation option (CDN or npm)
2. Update login.php with Tailwind
3. Create new dashboard.php
4. Create module pages
5. Test all functionality
6. Deploy

---
**Status**: Ready for implementation
**Date**: December 3, 2025
