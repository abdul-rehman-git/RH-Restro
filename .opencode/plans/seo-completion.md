# SEO Completion — Remaining Steps

## Overview
Wrap up the SEO implementation: production config, cleanup, optional features.

---

## 1. Remove static `public/robots.txt`
The file overrides the dynamic Blade route in most web servers.

## 2. Set `APP_URL` in `.env`
Required for canonical URLs and sitemap paths to be correct.

## 3. Replace placeholder icon assets
`public/favicon.svg`, `apple-touch-icon.png`, `icon-192.png`, `icon-512.png` currently have gold "FA" placeholder images. Replace with actual brand logo before production.

## 4. Submit sitemap to Google Search Console
Once deployed, submit `https://domain.com/sitemap.xml` in Google Search Console.

## 5. Admin SEO Settings
Admin → Public Content → SEO Settings:
- Upload default OG image (1200×630px)
- Enter GA4 Measurement ID
- Enter Google Search Console verification code

## 6. (Optional) Re-add `seo_title` / `seo_description` DB columns
If per-product/per-category custom SEO titles/descriptions are needed later.

## 7. (Optional) Delete static robots.txt in production
Only if the web server is confirmed to serve the dynamic route first.
