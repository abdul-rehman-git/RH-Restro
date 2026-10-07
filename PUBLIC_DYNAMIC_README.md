# Backend + Admin Requirements for Making the Public Site Dynamic

## Objective

The main goal of this project is to move the public website away from a static React bundle and make it backend-driven / dynamic. The purpose of this file is only to make these points clear:

1. What is currently static in the public frontend
2. Which backend flows must be built first
3. What needs to be added or updated on the admin side
4. Which existing flows are already correct and can be reused
5. In what order the work should ideally be done

Important point:
Completing the backend and admin flows first is the right approach. Once that is done, connecting the public UI later will become much easier.

---

## Current Repo Reality Summary

### Current state of the public side

The public app currently exists as a standalone React application inside `resources/js/public` and is served from the Blade view `resources/views/public-app.blade.php`. At the moment, the public routes only render that same bundle. Right now:

- the Home page runs on static arrays
- the Shop page runs on static products and static filters
- the Product Detail page runs on a static single object
- the About page is fully hardcoded
- the Reviews page is based on hardcoded reviews
- the Contact page contains hardcoded contact information and a non-submitting form
- the Custom Order page contains a UI-only form with no backend submission flow
- Cart and Checkout both run on local/static demo state
- the public login sign-in flow is partially connected, but signup and social login are fake/static

### Current state of the admin side

At the moment, only these real modules are properly available in the admin panel:

- Categories CRUD
- Products CRUD
- Admin business settings
- Auth / profile basic flows

### Existing backend pieces that are already useful

The current codebase already includes these useful building blocks:

- `categories` table with `name`, `slug`, `description`, `image`, `sort_order`, `is_active`
- `products` table with `category_id`, `title`, `slug`, `sku`, `short_description`, `description`, `image`, `gallery_images`, `price`, `compare_price`, `stock_quantity`, `sort_order`, `is_featured`, `is_active`
- category/product admin listing filters, search, sorting, and pagination
- image upload handling for categories/products
- admin-only business settings sharing through Inertia

In other words, the basic catalog backend already exists. It can become the foundation for a dynamic public catalog.

---

## Existing Flows That Can Already Be Reused

### 1. Categories flow

Already available:

- category create / update / delete
- slug support
- single image
- active/inactive status
- sort order

Reuse for public:

- active categories can be used in the public category listing
- sort order can be used for public ordering
- image and description can be used for public cards/detail sections

Still missing for public:

- a featured-on-home flag or homepage visibility logic
- SEO/meta fields
- an optional banner/cover image if category landing pages will be more detailed

### 2. Products flow

Already available:

- product create / update / delete
- category relation
- slug
- SKU
- short description + long description
- gallery images
- price + compare price
- stock quantity
- featured flag
- active flag
- sort order

Reuse for public:

- shop listing
- featured/trending products
- product detail base content
- sale price display using `compare_price`
- stock state based on `stock_quantity`

Still missing for public:

- public-ready product specifications
- attributes such as dimensions/material/frame
- product badges/labels
- SEO/meta fields
- reviews relation
- related product strategy

### 3. Public sign-in

Existing flow:

- the `/sign-in` public page sends a form POST
- a backend auth request already exists
- existing users can sign in

Missing:

- final public registration flow
- real social auth integration
- clearly defined post-login public behavior

### 4. Business settings

The existing settings are for the admin panel, not for the public site.

Important:

- the current business settings are explicitly being used for admin branding
- treating those settings directly as public site settings would not be a clean approach

Recommendation:

- create a separate settings structure for the public site
- or keep separate `public_site` keys inside the same `setting.data`
- but admin branding and public branding should remain logically separate

---

## Public Page Gap Analysis

## 1. Home Page

Currently static:

- hero heading/subheading
- hero image
- stats counters
- categories section
- trending products
- testimonials
- final CTA

Backend requirements:

- homepage content endpoint / service
- featured categories source
- featured/trending products source
- testimonial source
- stats source
- hero CTA / section content source

Admin requirements:

- Home page content editor
- Hero title, subtitle, badge text, CTA labels/links
- Hero image upload
- Stats management
- Section visibility toggles
- Featured categories selection
- Featured/trending products selection rule
- Testimonials selection/order
- CTA block content

## 2. Shop Page

Currently static:

- product list
- category filters
- price filter
- sorting
- result count
- pagination UI

Backend requirements:

- public product listing endpoint
- filters: category, min price, max price, featured, in-stock, search
- sorting: featured, newest, price asc/desc, popularity if available
- pagination
- category list endpoint

Admin requirements:

- existing products and categories are already enough for the basic version
- optional: visibility rules, stock behavior, badges

## 3. Product Detail Page

Currently static:

- product title, price, description
- image gallery
- rating + review count
- features list
- dimensions/material/frame info
- reviews list
- related products

Backend requirements:

- public single product detail endpoint, preferably by slug
- gallery images
- category relation
- stock availability
- related products query
- review summary
- approved reviews list

Admin requirements:

- product specification fields
- product features repeater
- product materials / dimensions / frame fields
- optional delivery info / care instructions
- SEO fields

## 4. About Page

Currently static:

- story hero
- stats
- artist section
- process section
- values section

Backend requirements:

- page content CMS flow

Admin requirements:

- About page editor
- artist profile content
- hero image
- process steps
- values list
- stats list

## 5. Reviews Page

Currently static:

- total rating
- rating breakdown
- reviews listing
- helpful counts
- load more button

Backend requirements:

- reviews/testimonials table
- approval/moderation flow
- average rating aggregation
- pagination/load more
- optional distinction between product-linked and general reviews

Admin requirements:

- reviews moderation panel
- approve/reject/feature review
- verified badge control
- optional helpful count/manual override

## 6. Contact Page

Currently static:

- contact form
- phone/email/address
- WhatsApp CTA
- business hours
- map placeholder

Backend requirements:

- contact form submission endpoint
- contact submission storage
- optional notification email
- public contact settings source

Admin requirements:

- contact details editor
- social links
- WhatsApp number/link
- business hours editor
- map embed / coordinates
- inquiry management list

## 7. Custom Order Page

Currently static:

- custom order form
- budget choice
- art type dropdown
- timeline dropdown
- reference upload UI

Backend requirements:

- custom order submission endpoint
- file upload support
- request status workflow
- optional email notification

Admin requirements:

- custom order requests module
- status management: new, contacted, quoted, in-progress, closed
- attachments view/download
- internal notes
- optional follow-up dates

## 8. Cart Page

Currently static/local state:

- cart items
- quantity update
- subtotal/shipping/tax

Backend requirements if real ecommerce is needed:

- cart storage strategy
- guest cart + user cart merge
- cart item validation against stock
- shipping/tax rule source

Admin requirements:

- none for the minimum dynamic content phase
- later, shipping rules / tax rules / coupon management can be added

## 9. Checkout Page

Currently static:

- shipping form
- payment selection
- order summary
- place order button

Backend requirements if real ordering is needed:

- orders table
- order items table
- billing/shipping address storage
- payment gateway integration
- order confirmation flow
- inventory reduction logic

Admin requirements:

- orders management
- order status tracking
- payment status
- fulfillment/shipping updates

Important:

It is better to keep Checkout/Cart separate from the dynamic catalog phase. If the immediate goal is only to make the public site dynamic, then catalog + CMS + inquiry flows should come first. Ecommerce transactions should remain in phase two.

---

## Backend Flows That Must Be Built First

This is the most important section. If we want to make the public site dynamic, then at minimum these backend flows must be completed first.

## Phase 1: Public Catalog + CMS Foundation

### 1. Public API / data delivery layer

Based on the current architecture, this appears to be the best option:

- keep the public React bundle as it is
- create dedicated public endpoints for it

Recommended examples:

- `GET /api/public/home`
- `GET /api/public/categories`
- `GET /api/public/products`
- `GET /api/public/products/{slug}`
- `GET /api/public/reviews`
- `GET /api/public/site-settings`
- `GET /api/public/pages/about`
- `POST /api/public/contact`
- `POST /api/public/custom-orders`

Reason:

- the current public app is already a separate BrowserRouter-based bundle
- the public routes currently render the same app through Blade
- in this architecture, adding JSON endpoints is the safest extension

### 2. Public site settings flow

This should include global items such as:

- public site name
- logo
- favicon
- footer text
- contact email
- phone
- WhatsApp
- address
- business hours
- social links
- copyright text
- SEO defaults

### 3. Homepage CMS flow

Fields/modules:

- hero section
- stats section
- featured categories section
- featured products section
- testimonial section
- CTA section

### 4. Public catalog flow

Use the existing categories + products as the base.

Required behaviors:

- only active categories/products should appear publicly
- sort order should be respected
- featured products should appear on the home page
- product detail should resolve by slug
- images should be returned with public URLs

### 5. Product extended content flow

The existing product table is not enough for a full product detail page. The following will need to be added:

- dimensions
- materials
- `frame_included`
- `feature_points` JSON
- `care_instructions` optional
- `shipping_note` optional
- SEO title/meta description optional

### 6. Testimonials / reviews flow

Decision required:

- do we only need general site testimonials
- or do we also need product reviews

Best structure:

- one `reviews` table
- optional `product_id`
- `is_approved`, `is_featured`, `is_verified_purchase`
- reviewer name, role, rating, comment, helpful_count

### 7. Contact inquiry flow

Store at least:

- name
- email
- phone optional
- subject
- message
- source page
- status

### 8. Custom order request flow

Store at least:

- customer name
- email
- phone optional
- artwork type
- dimensions
- budget range
- description
- preferred timeline
- attachments
- status
- admin notes

---

## What Must Be Added on the Admin Side

The admin requirements below are split into two parts:

1. Items that will directly appear on public pages
2. Items needed for support/admin workflow

## A. Admin Additions That Directly Feed Public Pages

### 1. Public Site Settings module

Required fields:

- public brand name
- public logo
- footer logo optional
- primary phone
- primary email
- WhatsApp
- address
- map embed code / map URL
- social media links
- business hours
- default SEO/meta
- copyright/footer text

### 2. Home Page CMS module

Required fields:

- hero badge
- hero title
- hero subtitle
- hero image
- hero CTA 1 label/link
- hero CTA 2 label/link
- homepage stats
- featured categories selection
- featured products selection rule or manual picker
- testimonials selection
- CTA section content

### 3. About Page CMS module

Required fields:

- hero title/subtitle
- story content
- artist name
- artist bio
- artist image
- stats
- process steps
- values list

### 4. Contact Page CMS module

Required fields:

- heading/subheading
- contact cards data
- WhatsApp CTA text/link
- business hours rows
- map embed

### 5. Reviews/Testimonial module

Required fields:

- reviewer name
- role
- rating
- comment
- date
- verified flag
- featured flag
- approval status
- related product optional

### 6. Product module extension

The current product form will need these additions:

- dimensions
- materials
- frame included yes/no
- feature bullets repeater
- optional badge text
- SEO title
- SEO meta description
- public visibility notes if needed

### 7. Category module extension

The current category form will need these additions:

- featured on home flag
- optional banner/cover image
- SEO title/meta optional

## B. Additions Needed for Admin Workflow

### 1. Contact inquiries management

- submissions list
- status change
- notes
- replied marker

### 2. Custom orders management

- submissions list
- attachment preview/download
- status pipeline
- notes
- quote/status tracking

### 3. Reviews moderation

- pending queue
- approved/rejected
- featured toggle

### 4. Optional orders module

This will only be necessary when Checkout becomes real:

- order list
- payment status
- fulfillment status
- shipment tracking

---

## Recommended Data Model / Tables

Recommended structure while extending the current base tables:

- `categories`
- `products`
- `setting` or a better-named settings storage
- `site_sections` or dedicated page content storage
- `reviews`
- `contact_inquiries`
- `custom_orders`
- `custom_order_attachments`

Optional second-phase ecommerce:

- `carts`
- `cart_items`
- `orders`
- `order_items`
- `shipping_methods`
- `payment_transactions`

### Recommended new fields in the product table

- `dimensions`
- `materials`
- `frame_included`
- `feature_points` JSON
- `seo_title`
- `seo_description`
- `badge_label`

### Recommended new fields in the category table

- `is_featured`
- `banner_image`
- `seo_title`
- `seo_description`

If the CMS needs to remain more flexible, page-specific structured JSON can also be used. However, for critical public data related to products/categories, dedicated columns or a controlled JSON structure would be the better approach.

---

## Existing Flow Compatibility Check

## 1. Categories flow

Status: compatible with extension

Notes:

- the base is fully usable for the public catalog
- only a few public-specific extra fields need to be added
- active/sort order are already helpful

## 2. Products flow

Status: compatible with extension

Notes:

- product CRUD already provides a strong base
- gallery, price, stock, and featured flag are already useful
- extra content fields are needed for the product detail page
- moving the public route from id to slug would be more future-friendly

## 3. Admin settings flow

Status: partial reuse only

Notes:

- the current settings are for admin branding
- public site settings can be added into the same system, but the structure must remain clean
- recommendation: keep admin branding and public branding logically separate

## 4. Public auth flow

Status: partially working

Notes:

- sign-in is usable for existing users
- signup is not connected
- the social buttons are UI-only

## 5. Public app architecture

Status: compatible with an API-first approach

Notes:

- the current standalone React public bundle does not need to be rewritten
- adding public JSON endpoints underneath it is a practical path
- later, the pages can be converted from static to dynamic step by step

## 6. Dashboard flow

Status: not yet real-data based

Notes:

- the dashboard currently uses demo stats/charts
- this is not a blocker for the public dynamic phase
- later it can be aligned with real admin analytics

---

## Implementation Priority Recommendation

The best order should be:

### Step 1

Finalize the public data architecture:

- public API endpoint pattern
- response shape
- slug vs id strategy
- image URL strategy

### Step 2

Build the global public site settings and CMS structure:

- site settings
- home page sections
- about page content
- contact page content

### Step 3

Make the existing catalog public-ready:

- products/public queries
- categories/public queries
- active/featured/sort rules
- product detail extended fields

### Step 4

Add inquiry flows:

- contact inquiries
- custom orders
- admin management screens

### Step 5

Add the reviews/testimonials system

### Step 6

Move the public frontend from static to dynamic section by section:

- home
- shop
- product detail
- about
- reviews
- contact
- custom order

### Step 7

Keep cart/checkout/orders for phase two if the goal is to build real ecommerce

---

## Minimum Backend Scope That Should Be Completed First

If the goal is only to make the public site dynamic, then the minimum backend/admin scope is:

- public site settings module
- homepage CMS module
- about page CMS module
- contact page CMS module
- public category listing endpoint
- public product listing endpoint
- public product detail endpoint
- extra product detail fields
- category featured/home flag
- reviews/testimonials module
- contact inquiry submission + admin listing
- custom order submission + admin listing

Once this minimum scope is complete, a very large part of the public site will become dynamic.

Cart, checkout, orders, and payment gateway can remain outside this scope.

---

## Clear Action List for the Next Prompt

In the next implementation prompt, we should ideally start with:

1. finalizing the database schema for the public dynamic backend
2. creating the required migrations
3. adding/extending admin modules
4. creating public API endpoints
5. keeping existing admin product/category flows compatible with the new fields

Recommended immediate build order:

1. Public site settings + home/about/contact CMS
2. Product/category public endpoints
3. Product/category admin form extensions
4. Reviews + contact inquiries + custom orders
5. Public frontend integration

---

## Final Conclusion

The current codebase already contains the basic foundation needed to build a dynamic public catalog, especially in the form of products and categories. The biggest gap is that almost all public app content is still hardcoded, and there are no CMS-style admin modules to manage that content.

Because of that, the best path is:

- reuse the existing product/category/admin foundation
- add public settings + page content CMS
- add inquiry/review flows
- create public JSON endpoints
- then convert the public UI to dynamic behavior step by step

This is the safest approach with the least rework.
