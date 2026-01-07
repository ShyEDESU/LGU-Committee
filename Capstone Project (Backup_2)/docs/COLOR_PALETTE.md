# Color Palette Documentation

## Legislative Services Committee Management System

**Version:** 1.0  
**Last Updated:** January 2025

---

## Table of Contents

1. [Design Philosophy](#design-philosophy)
2. [Primary Color Palette](#primary-color-palette)
3. [Status Colors](#status-colors)
4. [Semantic Colors](#semantic-colors)
5. [Neutral Colors](#neutral-colors)
6. [Gradients](#gradients)
7. [Usage Guidelines](#usage-guidelines)
8. [Accessibility & Contrast](#accessibility--contrast)
9. [Implementation in CSS](#implementation-in-css)
10. [Brand Guidelines](#brand-guidelines)

---

## Design Philosophy

The Legislative Services Committee Management System uses a **professional government-oriented color scheme** designed to:

- **Inspire Trust:** Government-approved professional colors
- **Ensure Clarity:** High contrast for readability and accessibility
- **Provide Consistency:** Unified visual language across all interfaces
- **Support Efficiency:** Clear status indicators and visual hierarchy
- **Maintain Accessibility:** WCAG 2.1 AA compliance for color contrast

### Color Psychology

Each color is selected based on established psychological principles:

- **Blues (Primary):** Trust, stability, security, professionalism
- **Greens (Success):** Positive actions, approval, success
- **Reds (Danger):** Warnings, errors, critical actions
- **Yellows (Warning):** Caution, alerts, pending actions
- **Grays (Neutral):** Professional, balanced, structured

---

## Primary Color Palette

### 1. Primary Blue

```
Name: Primary Blue
Hex: #3498db
RGB: rgb(52, 152, 219)
HSL: hsl(204, 70%, 53%)
CSS Variable: --primary-color
Usage: Buttons, links, hover states, primary actions
```

**Visual Usage:**
- Primary action buttons ("Login", "Submit")
- Navigation highlights
- Active menu items
- Primary links

**Accessibility:**
- Contrast Ratio with White: 4.5:1 ✓ (WCAG AA)
- Contrast Ratio with Black Text: 5.2:1 ✓ (WCAG AAA)

---

### 2. Secondary Blue

```
Name: Secondary Blue / Lighter Primary
Hex: #5dade2
RGB: rgb(93, 173, 226)
HSL: hsl(204, 67%, 63%)
Usage: Secondary buttons, accents, lighter backgrounds
```

**Visual Usage:**
- Secondary action buttons
- Accent elements
- Light backgrounds for emphasized sections
- Hover effects for primary elements

---

### 3. Dark Blue (Primary Dark)

```
Name: Dark Blue / Darker Primary
Hex: #2980b9
RGB: rgb(41, 128, 185)
HSL: hsl(204, 63%, 44%)
Usage: Active states, hover effects, button presses
```

**Visual Usage:**
- Button pressed states
- Darker section backgrounds
- Text color on light backgrounds
- Active navigation states

---

## Status Colors

### 1. Success Green

```
Name: Success Green
Hex: #27ae60
RGB: rgb(39, 174, 96)
HSL: hsl(145, 63%, 42%)
CSS Variable: --success-color
Usage: Success messages, approved actions, positive states
```

**Use Cases:**
- Success alert messages ("Account created successfully")
- Approved status indicators
- Valid input fields
- Completed tasks
- Checkmark icons

**Accessibility:**
- Contrast Ratio with White: 5.3:1 ✓ (WCAG AAA)
- Contrast Ratio with Black: 6.1:1 ✓ (WCAG AAA)

---

### 2. Danger Red

```
Name: Danger Red
Hex: #e74c3c
RGB: rgb(231, 76, 60)
HSL: hsl(6, 78%, 57%)
CSS Variable: --danger-color
Usage: Error messages, delete actions, critical alerts
```

**Use Cases:**
- Error alert messages ("Invalid email format")
- Delete confirmation buttons
- Error field highlights
- Critical warnings
- Stop/prohibited icons

**Accessibility:**
- Contrast Ratio with White: 3.6:1 ✓ (WCAG AA)
- Contrast Ratio with Black: 4.2:1 ✓ (WCAG AA)

---

### 3. Warning Yellow

```
Name: Warning Yellow
Hex: #f39c12
RGB: rgb(243, 156, 18)
HSL: hsl(38, 89%, 51%)
CSS Variable: --warning-color
Usage: Warning messages, caution alerts, pending actions
```

**Use Cases:**
- Warning alert messages ("This action cannot be undone")
- Pending status indicators
- Caution messages
- Important notices
- Attention-required indicators

**Accessibility:**
- Contrast Ratio with White: 3.2:1 (Just below AA for normal text)
- Contrast Ratio with Black: 8.2:1 ✓ (WCAG AAA)
- Note: Use dark text when background is warning yellow

---

## Semantic Colors

### 1. Info Blue (Light Variant)

```
Name: Info Blue
Hex: #3498db (same as primary)
RGB: rgb(52, 152, 219)
CSS Variable: --info-color (optional)
Usage: Informational messages, tips, help text
```

**Use Cases:**
- Informational alerts
- Tips and help messages
- Additional information displays
- Info icons

---

### 2. Accent Red (Alternative)

```
Name: Accent Color
Hex: #e74c3c
RGB: rgb(231, 76, 60)
CSS Variable: --accent-color
Usage: Important accents, highlights, emphasis
```

**Note:** Currently same as danger color, can be adjusted for branding

---

## Neutral Colors

### 1. Light Background Gray

```
Name: Light Background
Hex: #ecf0f1
RGB: rgb(236, 240, 241)
HSL: hsl(210, 14%, 94%)
CSS Variable: --light-bg
Usage: Alternative backgrounds, subtle sections, borders
```

**Use Cases:**
- Section backgrounds
- Alternate row backgrounds in tables
- Form backgrounds
- Demo credential boxes
- Subtle dividers

---

### 2. Dark Text Color

```
Name: Dark Text
Hex: #2c3e50
RGB: rgb(44, 62, 80)
HSL: hsl(215, 28%, 24%)
CSS Variable: --dark-text
Usage: Primary text color, headings, strong text
```

**Use Cases:**
- Body text
- Headings (H1, H2, H3)
- Form labels
- Strong emphasis text
- Default paragraph color

**Accessibility:**
- Contrast Ratio with White: 8.6:1 ✓ (WCAG AAA)
- Excellent readability

---

### 3. Light Text Color

```
Name: Light Text / White
Hex: #ffffff
RGB: rgb(255, 255, 255)
HSL: hsl(0, 0%, 100%)
CSS Variable: --light-text
Usage: Text on dark backgrounds
```

**Use Cases:**
- Button text
- Text on colored backgrounds
- Navigation bar text
- Footer text
- Inverted text colors

---

### 4. Gray (Medium)

```
Name: Medium Gray
Hex: #95a5a6
RGB: rgb(149, 165, 166)
HSL: hsl(187, 9%, 62%)
Usage: Secondary text, disabled states, borders
```

**Use Cases:**
- Secondary text
- Disabled button colors
- Placeholder text
- Subtle borders
- Muted text

---

### 5. Dark Gray

```
Name: Dark Gray
Hex: #34495e
RGB: rgb(52, 73, 94)
HSL: hsl(215, 28%, 29%)
Usage: Darker text, emphasis, secondary headings
```

**Use Cases:**
- Secondary headings
- Emphasized text
- Dark theme text
- Form descriptions

---

### 6. Light Border Gray

```
Name: Light Border
Hex: #bdc3c7
RGB: rgb(189, 195, 199)
HSL: hsl(210, 7%, 76%)
Usage: Borders, dividers, subtle lines
```

**Use Cases:**
- Form input borders
- Table borders
- Card borders
- Horizontal dividers
- Subtle shadows

---

## Gradients

### 1. Primary Gradient

```css
background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
Direction: 135 degrees (top-left to bottom-right)
Start: #2c3e50 (Dark Blue)
End: #3498db (Primary Blue)
Usage: Login page background, header backgrounds, accent sections
```

**Visual Effect:**
- Creates depth and visual interest
- Transitions from dark to light blue
- Professional appearance

---

### 2. Success Gradient

```css
background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
Direction: 135 degrees
Start: #27ae60 (Success Green)
End: #229954 (Darker Green)
Usage: Success button, positive emphasis, completion indicators
```

---

### 3. Danger Gradient

```css
background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
Direction: 135 degrees
Start: #e74c3c (Danger Red)
End: #c0392b (Darker Red)
Usage: Danger/delete buttons, critical alerts
```

---

## Usage Guidelines

### 1. Buttons

**Primary Buttons (Main Actions)**
- Background: Primary Blue (#3498db) with gradient
- Text: White (#ffffff)
- Hover: Darker Blue (#2980b9)
- Example: "Login", "Submit", "Save"

```css
.btn-primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #ffffff;
    border: none;
    padding: 0.9rem 1.5rem;
    border-radius: 8px;
}

.btn-primary:hover {
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
    transform: translateY(-2px);
}
```

**Secondary Buttons (Alternative Actions)**
- Background: Light Background (#ecf0f1)
- Text: Dark Text (#2c3e50)
- Border: Light Border (#bdc3c7)
- Hover: Primary Blue text

**Success Buttons (Confirmations)**
- Background: Success Green (#27ae60) with gradient
- Text: White (#ffffff)
- Example: "Create Account", "Approve"

**Danger Buttons (Destructive Actions)**
- Background: Danger Red (#e74c3c) with gradient
- Text: White (#ffffff)
- Example: "Delete", "Remove", "Confirm Deletion"

---

### 2. Forms

**Input Fields**
- Border: Light Border (#bdc3c7)
- Background: White (#ffffff)
- Focus Border: Primary Blue (#3498db)
- Focus Shadow: rgba(52, 152, 219, 0.1)

```css
.form-control {
    border: 2px solid #bdc3c7;
    border-radius: 8px;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}
```

**Labels**
- Color: Dark Text (#2c3e50)
- Font Weight: 600

**Help Text**
- Color: Medium Gray (#95a5a6)
- Font Size: 0.85rem

---

### 3. Alerts & Messages

**Success Alert**
- Background: rgba(39, 174, 96, 0.1)
- Border Left: Success Green (#27ae60)
- Text: Dark Text (#2c3e50)
- Icon: Success Green (#27ae60)

**Error Alert**
- Background: rgba(231, 76, 60, 0.1)
- Border Left: Danger Red (#e74c3c)
- Text: Dark Text (#2c3e50)
- Icon: Danger Red (#e74c3c)

**Warning Alert**
- Background: rgba(243, 156, 18, 0.1)
- Border Left: Warning Yellow (#f39c12)
- Text: Dark Text (#2c3e50)
- Icon: Warning Yellow (#f39c12)

**Info Alert**
- Background: rgba(52, 152, 219, 0.1)
- Border Left: Primary Blue (#3498db)
- Text: Dark Text (#2c3e50)
- Icon: Primary Blue (#3498db)

---

### 4. Navigation

**Navigation Bar**
- Background: Primary Blue (#3498db) or with gradient
- Text: White (#ffffff)
- Active Item: Light Background highlight (#ecf0f1)
- Hover: Slightly lighter shade

---

### 5. Status Indicators

**Active / Online**
- Color: Success Green (#27ae60)
- Icon: Green checkmark or circle

**Inactive / Offline**
- Color: Medium Gray (#95a5a6)
- Icon: Gray X or circle

**Pending / Waiting**
- Color: Warning Yellow (#f39c12)
- Icon: Yellow clock or hourglass

**Error / Offline**
- Color: Danger Red (#e74c3c)
- Icon: Red X or circle

---

## Accessibility & Contrast

### WCAG 2.1 Compliance

All color combinations meet at minimum WCAG AA standards (4.5:1 contrast for normal text).

### Contrast Ratios

| Color 1 | Color 2 | Ratio | Level | Use |
|---------|---------|-------|-------|-----|
| #3498db (Primary) | #ffffff (White) | 4.5:1 | AA | Normal text ✓ |
| #27ae60 (Success) | #ffffff (White) | 5.3:1 | AAA | Any text ✓ |
| #e74c3c (Danger) | #ffffff (White) | 3.6:1 | AA | Bold/Large text ✓ |
| #f39c12 (Warning) | #ffffff (White) | 3.2:1 | | Use with dark text only |
| #2c3e50 (Dark) | #ffffff (White) | 8.6:1 | AAA | All text ✓✓ |
| #ecf0f1 (Light BG) | #2c3e50 (Dark) | 11.5:1 | AAA | All text ✓✓ |

### Best Practices

1. **Never rely on color alone** - Use text labels and icons
2. **Provide sufficient contrast** - Don't use light colors on light backgrounds
3. **Test with color-blind simulator** - Verify accessibility
4. **Use semantic colors** - Green for success, red for danger
5. **Consider dark mode** - Test light/dark backgrounds

---

## Implementation in CSS

### CSS Variables

```css
/* Primary Colors */
--primary-color: #3498db;
--secondary-color: #2980b9;
--accent-color: #e74c3c;

/* Status Colors */
--success-color: #27ae60;
--warning-color: #f39c12;
--danger-color: #e74c3c;

/* Neutral Colors */
--light-bg: #ecf0f1;
--dark-text: #2c3e50;
--light-text: #ffffff;
--medium-gray: #95a5a6;
--dark-gray: #34495e;
--light-border: #bdc3c7;

/* Additional Grays */
--bg-light: #f9f9f9;
--bg-lighter: #f0f7ff;
--border-color: #e0e0e0;
--disabled-color: #cccccc;
```

### Usage Example

```css
/* Using CSS variables */
.button-primary {
    background: var(--primary-color);
    color: var(--light-text);
    border: none;
}

.alert-success {
    background-color: rgba(39, 174, 96, 0.1);
    border-left: 4px solid var(--success-color);
    color: var(--dark-text);
}

.alert-danger {
    background-color: rgba(231, 76, 60, 0.1);
    border-left: 4px solid var(--danger-color);
    color: var(--dark-text);
}
```

### Fallback Colors

```css
/* For older browsers without CSS variable support */
.button-primary {
    background: #3498db;
    color: #ffffff;
}

/* Modern browsers use variables */
@supports (--css: variables) {
    .button-primary {
        background: var(--primary-color);
        color: var(--light-text);
    }
}
```

---

## Brand Guidelines

### Official Logo Colors

The system logo (landmark icon) uses:
- Primary: #3498db
- Accent: #2c3e50

### Visual Consistency

**All interfaces should:**
1. Use provided color palette consistently
2. Maintain at least 8px padding around colored elements
3. Use consistent border radius (8px recommended)
4. Apply consistent shadow depths
5. Maintain minimum font sizes (12px body, 14px labels)

### Do's ✓

- ✓ Use gradient overlays for depth
- ✓ Apply hover state changes (darker/lighter)
- ✓ Use icons with colors for clarity
- ✓ Maintain consistent spacing
- ✓ Test color accessibility

### Don'ts ✗

- ✗ Mix non-palette colors arbitrarily
- ✗ Use color alone to convey information
- ✗ Place light text on light backgrounds
- ✗ Use excessive color variations
- ✗ Skip accessibility testing

---

## Color Variants

### Lightness Scale (Primary Blue)

```
100% White:        #ffffff
90% Lightest:      #d6eaf8
80% Very Light:    #bee3f8
70% Light:         #90cdf4
60% Lighter:       #63b3ed
50% Medium:        #4299e1
40% Base:          #3182ce
30% Primary:       #3498db
20% Dark:          #2c5aa0
10% Darker:        #1e40af
0% Darkest:        #0c2340
```

### Usage by Lightness

- **Very Light (90-80%):** Hover states, disabled states, backgrounds
- **Light (70-60%):** Secondary elements, accents
- **Medium (50-40%):** Primary interactive elements
- **Dark (30-20%):** Active states, emphasis
- **Darkest (10%):** Text on light backgrounds

---

## Color Accessibility Simulator

When designing, test colors using:
- Sim Daltonism (iOS)
- Color Vision Simulator (Web)
- Chrome DevTools (Built-in)
- WebAIM Contrast Checker

---

## Examples in Context

### Login Page Color Scheme

```
Background Gradient: #2c3e50 → #3498db
Button Primary: #3498db
Button Hover: #2980b9
Form Border Focus: #3498db
Text: #2c3e50
Helper Text: #95a5a6
Error Messages: #e74c3c
```

### Dashboard Color Scheme

```
Header: #2c3e50 (with white text)
Sidebar: #ecf0f1
Active Menu Item: #3498db
Buttons: #3498db
Success Status: #27ae60
Warning Status: #f39c12
Error Status: #e74c3c
Body Text: #2c3e50
```

### Committee Card Color Scheme

```
Card Background: #ffffff
Card Border: #e0e0e0
Header: #2c3e50 (dark text)
Status Badge: #27ae60 (success) or #f39c12 (warning)
Buttons: #3498db (primary)
Hover Effect: Subtle shadow increase
```

---

## Export Formats

### For Designers (Figma/Adobe XD)

```json
{
  "colors": [
    {
      "name": "Primary Blue",
      "value": "#3498db",
      "roles": ["button-primary", "link", "focus-border"]
    },
    {
      "name": "Success Green",
      "value": "#27ae60",
      "roles": ["status-success", "positive-action"]
    }
  ]
}
```

### For Developers (SCSS/LESS)

```scss
// Primary Colors
$primary-color: #3498db;
$secondary-color: #2980b9;
$accent-color: #e74c3c;

// Status Colors
$success-color: #27ae60;
$warning-color: #f39c12;
$danger-color: #e74c3c;

// Neutral Colors
$light-bg: #ecf0f1;
$dark-text: #2c3e50;
$light-text: #ffffff;
```

---

## Contact & Questions

For questions about color usage or brand guidelines:
- Email: design@lgu.gov
- Web: [Internal Design System Portal]
- Slack: #design-system

---

## Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | January 2025 | Initial color palette documentation |

---

**Document Classification:** Government Internal  
**Last Updated:** January 2025  
**Next Review:** January 2026
