---
name: Nhà Sách Mọt Sách Design System
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#43474e'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#74777f'
  outline-variant: '#c4c6cf'
  surface-tint: '#455f87'
  primary: '#022448'
  on-primary: '#ffffff'
  primary-container: '#1e3a5f'
  on-primary-container: '#8aa4cf'
  inverse-primary: '#adc8f5'
  secondary: '#9d4300'
  on-secondary: '#ffffff'
  secondary-container: '#fd761a'
  on-secondary-container: '#5c2400'
  tertiary: '#002a0d'
  on-tertiary: '#ffffff'
  tertiary-container: '#004319'
  on-tertiary-container: '#38b95d'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d5e3ff'
  primary-fixed-dim: '#adc8f5'
  on-primary-fixed: '#001c3b'
  on-primary-fixed-variant: '#2d486d'
  secondary-fixed: '#ffdbca'
  secondary-fixed-dim: '#ffb690'
  on-secondary-fixed: '#341100'
  on-secondary-fixed-variant: '#783200'
  tertiary-fixed: '#7ffc97'
  tertiary-fixed-dim: '#62df7d'
  on-tertiary-fixed: '#002109'
  on-tertiary-fixed-variant: '#005320'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  headline-xl:
    fontFamily: Be Vietnam Pro
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-xl-mobile:
    fontFamily: Be Vietnam Pro
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Be Vietnam Pro
    fontSize: 20px
    fontWeight: '700'
    lineHeight: 28px
  headline-md:
    fontFamily: Be Vietnam Pro
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 26px
  headline-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-md:
    fontFamily: Be Vietnam Pro
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
  label-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
  price-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
    letterSpacing: -0.01em
  price-md:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '700'
    lineHeight: 20px
  price-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 13px
    fontWeight: '700'
    lineHeight: 16px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-mobile: 0.5rem
  margin: 1rem
  margin-desktop: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

This design system establishes an e-commerce experience tailored for a high-volume, trusted Vietnamese retail bookstore. It balances literary authority with dense, efficient commercial merchandising, reflecting the familiarity and credibility of established modern retailers like Fahasa, Tiki, and Phương Nam Book.

### Visual Aesthetic & Principles
- **Corporate & Commercial Utility:** Utilitarian, functional, and organized. Every square pixel works to highlight literature, editions, pricing, and publisher credibility.
- **Architectural Crispness:** Strict reliance on subtle, controlled borders (1px solid `#E5E7EB`) and disciplined 2px–4px radius corners. Playful, overly rounded pills, floating glassmorphism, and neon gradients are explicitly avoided.
- **Vietnamese Typographic Fidelity:** Native support for Vietnamese tone markers and diacritics with generous baseline clearance, ensuring complex tone marks (dấu hỏi, ngã, nặng, sắc, huyền) never clash with preceding descenders or line boxes.
- **Clarity over Abstraction:** Clear transactional cues, authentic stock statuses, structured book specifications (publisher, author, cover type), and unmistakable pricing conventions.

## Colors

The palette grounds transactional confidence through deep ocean-navy tones, driven by high-visibility orange accents reserved strictly for conversions and value incentives.

### Palette Hierarchy & Tokens
- **Primary Navy (`#1E3A5F`):** Anchors brand authority, global headers, primary categorization menus, and key titles.
- **Secondary Orange (`#F97316`):** The dedicated action driver. Reserved for primary purchase triggers ("Chọn Mua", "Thêm vào giỏ"), flash sales, and active discount stickers.
- **Success Green (`#16A34A`):** In-stock notifications, authentic badge labels ("Chính hãng 100%"), and verified buyer stamps.
- **Danger Red (`#DC2626`):** Price drops, urgent stock levels ("Chỉ còn 2 cuốn"), and critical validation errors.
- **Surface Canvas (`#FFFFFF` & `#F8FAFC`):** Pure white cards set against an ultra-light cool slate container tier for subtle section separation.
- **Text Tiers:**
  - Main Body & Titles: `#111827` (Rich Slate/Charcoal) delivers razor-sharp legibility without absolute black glare.
  - Subdued / Metadata: `#64748B` (Cool Slate) for author names, review tallies, and original pre-discount prices.
  - Border Substrate: `#E5E7EB` (Subtle Gray) provides high-definition structural boundaries.

## Typography

The type system is powered entirely by **Be Vietnam Pro**, explicitly engineered for flawless rendering of Vietnamese diacritics. It guarantees proportional harmony across stacked tone accents without vertical text clipping.

### Rules & Formatting
- **Vietnamese Currency (VND):** Always format final prices with period thousand separators followed by a non-breaking space and the currency symbol (e.g., `125.000 ₫`). Never use commas for thousands.
- **Original / Crossed-out Pricing:** Render strikethroughs with `body-sm` or `price-sm` in `#64748B` immediately adjacent to or below the current price.
- **Line Heights:** Body and label line heights maintain at least a 1.4× to 1.5× ratio to accommodate high diacritic peaks (e.g., ể, ẩ, ỗ) without overlapping borders.

## Layout & Spacing

A disciplined 8px base rhythm governs all spatial calculations, with a secondary 4px sub-step for compact item groups like chip paddings and icon offsets.

### Grid & Breakpoints
- **Desktop (≥1200px):** 12-column fixed-max layout centered at `1240px`. Column gutter is `16px` (`space-md`). Outer section margins are `32px` (`space-xl`).
- **Tablet (768px – 1199px):** 8-column responsive fluid layout with `16px` gutters and `16px` margin.
- **Mobile (<768px):** 4-column fluid layout with `8px` (`gutter-mobile`) gutters for merchandising cards and `12px` to `16px` canvas margins.

### Catalog Density
- Product listings adapt from 5–6 books per row on desktop (200px–220px width per card) to 2 books per row on mobile screens to maintain readable cover art and full price clarity.

## Elevation & Depth

This system avoids floating, fuzzy, or dramatic dropshadows. Depth is established through **flat surface layering**, **crisp 1px borders**, and **low-diffusion elevation**.

### Elevation Hierarchy
- **Level 0 (Flat Canvas):** `#F8FAFC` background. Pure flat baseline with no shadow.
- **Level 1 (Card & Content Blocks):** `#FFFFFF` fill with a `1px solid #E5E7EB` perimeter border. Shadow: `0 1px 2px 0 rgba(0, 0, 0, 0.05)`.
- **Level 2 (Hover State / Interactive Cards):** `1px solid #CBD5E1` border with a crisp elevation step: `0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05)`. The card translates vertically by `-2px`.
- **Level 3 (Dropdowns, Overlays & Sticky Nav):** Contextual search auto-suggest, Mega-menu flyouts, and fixed sticky headers use: `0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04)` enclosed in a `1px solid #E2E8F0` frame.

## Shapes

The geometric personality of this design system is structural and restrained. Roundedness is strictly constrained to Level 1 (`roundedness: 1`), keeping radii between 0px and 4px.

- **Base Radius (`rounded-sm` / 2px):** Small badges, discount tags, rating chips, and status flags.
- **Component Standard (`rounded` / 4px):** Product cards, input fields, action buttons, category thumbnails, and book cover wrappers.
- **Pill Exceptions:** Rounded pills (`rounded-full`) are prohibited for primary buttons and cards; they are limited solely to compact numeric notification count bubbles on the shopping cart icon.

## Components

### 1. High-Density Book Card
- **Structure:** Vertical orientation. 1px solid `#E5E7EB` border, `#FFFFFF` background, 8px padding (`space-sm`) on mobile, 12px padding on desktop.
- **Book Cover Container:** Aspect ratio `1:1.4` (standard book proportion). Subtle inner edge highlight (`inset 0 0 0 1px rgba(0,0,0,0.06)`) simulating realistic book spine and paper depth.
- **Discount Badge:** Absolute top-left position over cover. High-contrast tag: `#DC2626` background, `#FFFFFF` bold text (`label-sm`), 2px radius, formatted as `-20%`.
- **Title:** Truncated strictly at 2 lines, `#111827`, `headline-sm`, font-weight 600.
- **Author & Publisher:** Single line truncated, `#64748B`, `body-sm`.
- **Review Rating:** 5-star cluster rendered in `#FBBF24` (12px icons) accompanied by total sold count (ví dụ: `★ 4.8 (Đã bán 1.2k)`).
- **Pricing Stack:**
  - Current Price: `price-md` in `#DC2626` or `#1E3A5F` (e.g., `125.000 ₫`).
  - Strikethrough Price: `price-sm` in `#94A3B8`, line-through (e.g., `156.000 ₫`).

### 2. Search Bar with Auto-Suggest
- **Main Bar:** Input height 40px, enclosed inside a `#1E3A5F` border frame. Incorporates a category selection dropdown prefix, a clear input zone with placeholder `Tìm kiếm tựa sách, tác giả, ISBN...`, and an attached `#F97316` or `#1E3A5F` submit button containing a search icon and label.
- **Auto-suggest Popover:** Direct 0px top-offset drop, Level 3 elevation, `#FFFFFF` background. Grouped sections: "Từ khóa phổ biến" (chips), "Sách gợi ý" (micro-book rows with 32x45px covers, title, and current VND price), and "Tác giả liên quan".

### 3. Buttons & Action Drivers
- **Primary CTA ("Chọn Mua" / "Thanh toán"):** Height 40px or 44px, background `#F97316`, hover `#EA580C`, text `#FFFFFF`, font-weight 600 (`label-lg`), 4px border radius. No drop shadow.
- **Secondary CTA ("Thêm vào giỏ"):** Height 40px, background `#FFF7ED`, border `1px solid #F97316`, text `#EA580C`.
- **Tertiary / Utility Button:** Height 36px, background `#F8FAFC`, border `1px solid #E2E8F0`, text `#1E3A5F`.

### 4. Category & Filter Chips
- **Dimensions:** 32px height, 4px border radius, horizontal padding 12px.
- **Default State:** Background `#FFFFFF`, border `1px solid #E5E7EB`, text `#475569`, `label-md`.
- **Active / Selected State:** Background `#EFF6FF`, border `1px solid #1E3A5F`, text `#1E3A5F`, bold font weight.

### 5. Form Inputs, Checkboxes & Radios
- **Inputs:** Height 40px, 4px radius, `1px solid #CBD5E1` border, `#FFFFFF` fill, 12px inner horizontal padding. Active focus ring: `1px solid #1E3A5F` with zero blur offset.
- **Checkboxes & Radios:** Sharp 2px radius for checkboxes, standard circular for radios. Active check color is `#1E3A5F`.

### 6. Breadcrumbs & Announcement Bar
- **Announcement Bar:** Compact 32px high band at the absolute top of viewport. Background `#1E3A5F`, text `#F8FAFC` (`label-sm`), featuring rotating delivery updates ("Freeship toàn quốc từ 250.000 ₫") and hotline support.
- **Breadcrumbs:** Neutral path indicators: `Trang chủ / Văn học / Tiểu thuyết / Tác phẩm`, linked elements in `#64748B`, terminal element in `#111827`, separated by `/` in 12px type.