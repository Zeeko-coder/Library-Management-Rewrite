# System Update Walkthrough - LibroTech

This document summarizes the major technical and visual updates implemented for the LibroTech Library Management System.

---

## 1. UniSMS API Integration (MFA/OTP)
The system now supports real-time SMS delivery for Multi-Factor Authentication.

- **Configuration:** [`config/sms_config.php`](file:///c:/xampp/htdocs/Library-Management-Rewrite/config/sms_config.php) stores the API Secret Key (`sk_daf45...`).
- **Core Engine:** [`helpers/sms_helper.php`](file:///c:/xampp/htdocs/Library-Management-Rewrite/helpers/sms_helper.php) handles the cURL communication with UniSMS.
- **Login Integration:** Both [Student](file:///c:/xampp/htdocs/Library-Management-Rewrite/auth/process_student_login.php) and [Librarian](file:///c:/xampp/htdocs/Library-Management-Rewrite/auth/process_librarian_login.php) login processes now trigger SMS delivery when selected.

## 2. Database Restoration & Stability
Fixed a critical issue where the system was crashing due to a missing settings table.

- **Missing Table:** Re-created the `system_settings` table.
- **Data Initialization:** Added default values for `library_name`, `library_address`, `library_phone`, and `library_email` to ensure the landing page loads correctly.
- **Result:** The [Landing Page](file:///c:/xampp/htdocs/Library-Management-Rewrite/index.php) and [Role Selection](file:///c:/xampp/htdocs/Library-Management-Rewrite/public/loginAs.php) are now fully operational.

## 3. Premium UI Enhancement (About Us)
Upgraded the visual identity of the "About Us" section.

- **Graphic Upgrade:** Replaced the generic gradient placeholder with a custom-generated **Floating Laptop Mockup** (`img/about-laptop.png`).
- **Live Preview:** The laptop screen displays a high-quality capture of the actual LibroTech landing page.
- **Modern Effects:**
    - **Floating Animation:** Added a smooth CSS keyframe animation in [`styles.css`](file:///c:/xampp/htdocs/Library-Management-Rewrite/src/css/styles.css) that makes the laptop gently hover.
    - **Blending:** Used `mix-blend-mode: multiply` and CSS filters (`brightness`/`contrast`) to ensure the image blends perfectly with the white section background.

---
**Status:** All modules are currently deployed and tested.
