# Project Onboarding Guide

This file has been written to help you understand this project from a beginner's perspective. If you do not yet have a strong understanding of Laravel, Vue, Inertia, API flow, or the folder structure, read this guide from top to bottom.

## 1. What Is This Project?

This project is a full-stack web application in which:

- the backend is built with `Laravel 13`
- the frontend is built with `Vue 3`
- `Inertia.js` is used to connect the backend and frontend
- `Tailwind CSS` is used for styling
- `Vite` is the build tool

This project is used for:

- a public website
- an admin dashboard
- product and category management
- public content management
- reviews
- contact inquiries

Now the important part:

- the project now runs on a `single build`
- both admin and public are part of the same `Laravel + Inertia + Vue` application
- the separate React public bundle is no longer used

## 2. High-Level Architecture

In simple terms, the project flow works like this:

1. The browser hits a URL
2. Laravel routes determine which controller method should run
3. The controller retrieves data from the database or settings
4. The controller sends props to a Vue page through `Inertia::render()`
5. `resources/js/app.js` loads the correct Vue page
6. The Vue page receives the props and renders the UI

There are two main form-handling patterns:

- admin side: mostly `Inertia useForm` with normal Laravel web routes
- public side: mostly `window.axios` posting to `/api/public/*` endpoints

## 3. Languages and Frameworks

### Backend

- language: `PHP 8.3`
- framework: `Laravel 13`
- auth: `Laravel Breeze`
- session/security: `Laravel Sanctum` is installed

### Frontend

- language: `JavaScript`
- framework: `Vue 3`
- app bridge: `@inertiajs/vue3`
- icons: `lucide-vue-next`
- toast notifications: `vue-sonner`
- tables: `@tanstack/vue-table`
- file upload UI: `filepond`, `vue-filepond`

## 4. Important Packages

### PHP packages

Main packages in `composer.json`:

- `laravel/framework`
- `inertiajs/inertia-laravel`
- `tightenco/ziggy`
- `laravel/breeze`

### JavaScript packages

Main packages in `package.json`:

- `vue`
- `@inertiajs/vue3`
- `axios`
- `vite`
- `@vitejs/plugin-vue`
- `tailwindcss`
- `@tanstack/vue-table`
- `lucide-vue-next`
- `vue-sonner`
- `filepond`
- `vue-filepond`

## 5. How Is the Project Running on a Single Build?

This project now runs as a single application:

- root Blade view: `resources/views/app.blade.php`
- main frontend entry: `resources/js/app.js`
- the same Vite build is used for both admin and public

This means:

- `npm run dev` serves one frontend application
- `npm run build` creates one production build
- there is no need for a separate React build for the public side

## 6. Understand the Folder Structure

### Backend folders

- `app/Http/Controllers`
  - HTTP requests are handled here
- `app/Http/Requests`
  - validation classes live here
- `app/Models`
  - Eloquent models for database tables
- `app/Actions`
  - reusable business logic, such as save/delete actions
- `app/Services`
  - helper services, such as file uploads
- `app/Support`
  - support utilities, such as the settings store

### Frontend folders

- `resources/js/Pages`
  - page-level Vue components
- `resources/js/Layouts`
  - admin/public layouts
- `resources/js/Components`
  - reusable shared components
- `resources/js/public/components`
  - reusable components specific to the public site

### Routes and views

- `routes/web.php`
  - main application routes
- `routes/auth.php`
  - auth routes
- `resources/views/app.blade.php`
  - Inertia root view

### Database

- `database/migrations`
  - table structure
- `database/seeders`
  - sample/default data

## 7. Main Entry Files

### Frontend app start

File: `resources/js/app.js`

Here:

- CSS is imported
- bootstrap is loaded
- the Inertia app is created
- `./Pages/**/*.vue` files are resolved dynamically

This means:

- if a controller calls `Inertia::render('Public/Home')`
- then the Vue file `resources/js/Pages/Public/Home.vue` will be loaded

### Axios setup

File: `resources/js/bootstrap.js`

Here:

- `window.axios` is registered
- `X-Requested-With` is set
- `X-CSRF-TOKEN` is pulled from the meta tag

The benefit:

- public POST requests remain CSRF-safe

### Root Blade

File: `resources/views/app.blade.php`

Here:

- `@routes` is used
- `@vite(...)` is used
- `@inertiaHead`
- `@inertia`

This is the root HTML bridge between Laravel and Vue.

## 8. Where Do Shared Inertia Props Come From?

File: `app/Http/Middleware/HandleInertiaRequests.php`

This provides some shared props to every Inertia page:

- `auth.user`
- `flash.message`
- `flash.type`
- `businessSettings`

`businessSettings` is used in the admin layout where the business name/logo is displayed.

## 9. Route Map

File: `routes/web.php`

### Public page routes

- `/`
- `/shop`
- `/product/{slug}`
- `/cart`
- `/checkout`
- `/custom-order`
- `/reviews`
- `/about`
- `/contact`
- `/sign-in`

All of these are handled by `PublicController`.

### Public API routes

Prefix: `/api/public`

Important endpoints:

- `GET /api/public/site-settings`
- `GET /api/public/home`
- `GET /api/public/pages/about`
- `GET /api/public/pages/contact`
- `GET /api/public/reviews`
- `POST /api/public/reviews`
- `GET /api/public/products`
- `GET /api/public/products/{slug}`
- `POST /api/public/contact`

### Admin routes

After authentication:

- `/dashboard`
- `/categories`
- `/products`
- `/public-content`
- `/admin-reviews`
- `/contact-inquiries`
- `/profile`
- `/settings`

## 10. Public Page Render Flow

This is the most important concept for understanding the project.

Example: a user opens `/shop`.

Flow:

1. The browser hits `/shop`
2. The route is found in `routes/web.php`
3. The route calls `PublicController@shop`
4. The controller fetches products/categories from the database
5. The controller returns `Inertia::render('Public/Shop', [...props])`
6. `resources/js/app.js` resolves `Public/Shop.vue`
7. `Shop.vue` receives the props and displays the UI

This means the public pages run on the Inertia pattern with server-rendered JSON props.

## 11. Public API Flow

Now understand the form flow.

Example: contact form submission.

Flow:

1. The user fills out the form in `resources/js/Pages/Public/Contact.vue`
2. A Vue method calls `window.axios.post('/api/public/contact', form)`
3. The request goes to the Laravel route `/api/public/contact`
4. `PublicApiController@storeContactInquiry` runs
5. Validation happens through a request class
6. The `ContactInquiry` model saves the record in the database
7. A JSON response is returned
8. Vue shows a success message

The same pattern is also used for review submission.

## 12. Admin Flow

The main admin-side pattern is slightly different from the public side.

Example: an admin creates a product.

Flow:

1. The admin opens the `/products/create` page
2. `ProductController@create` sends category props
3. The Vue page `resources/js/Pages/Products/Create.vue` is loaded
4. The form is managed with `useForm()`
5. On submit, `form.post(route('products.store'))` runs
6. The request reaches `ProductController@store`
7. The controller calls `SaveProductAction`
8. The action stores files and creates the `Product`
9. Laravel redirects
10. A flash message is stored in the session
11. `AppToaster.vue` shows that flash message as a toast

So on the admin side:

- the flow is mostly full-page Inertia form handling
- on the public side, the flow is mostly axios-based JSON API handling

## 13. Role of Controllers

### `PublicController`

Renders public pages:

- home
- shop
- product detail
- about
- contact
- reviews
- custom order
- login

It mostly uses `Inertia::render(...)`.

### `PublicApiController`

Provides JSON APIs for:

- public site settings
- home/about/contact data
- products/reviews data
- contact form submission
- review submission

### `ProductController`

Admin product CRUD:

- list
- create
- edit
- save
- update
- delete

### `PublicContentController`

Lets admin manage public site content:

- site branding
- home page content
- about page content
- contact page content

## 14. Where Is Settings Data Stored?

File: `app/Support/SettingStore.php`

This is a very important file.

In this project, much of the public site content is stored in JSON form inside the database `setting` table.

Example keys:

- `public_site`
- `public_pages.home`
- `public_pages.about`
- `public_pages.contact`

This means:

- the website logo
- footer text
- hero title
- about page content
- contact page text

are not hardcoded; they are driven by the database.

## 15. How Do File Uploads Work?

File: `app/Services/FileService.php`

This service:

- receives the uploaded file
- ensures the folder exists
- generates a unique filename
- moves the file inside `public/`
- returns the path

Important upload folders:

- `public/admin-catalog/products`
- `public/public-site/branding`
- `public/public-site/home`
- `public/public-site/about`
- `public/public-site/reviews`

## 16. Database Tables and Models

### `users`

Model: `App\Models\User`

Used for:

- admin login
- dashboard access

### `setting`

Model: `App\Models\Setting`

Used for:

- public site content as JSON

### `categories`

Model: `App\Models\Category`

Used for:

- product grouping

Relation:

- one category has many products

### `products`

Model: `App\Models\Product`

Used for:

- catalog items

Important fields include:

- title
- slug
- price
- images
- category_id
- featured/active flags

Relation:

- product belongs to a category

### `reviews`

Model: `App\Models\Review`

Used for:

- product reviews

Relation:

- review belongs to a product

### `contact_inquiries`

Model: `App\Models\ContactInquiry`

Used for:

- public contact form submissions

## 17. Useful Model Concepts

### Query scopes

Custom scopes are used in the models.

Examples:

- `active()`
- `featured()`
- `search()`
- `priceBetween()`
- `categorySlug()`

These scopes keep controller code clean.

### Casting

Some fields are cast to arrays/booleans.

Examples:

- product `gallery_images`
- product `feature_points`
- review `gallery_images`
- setting `data`

This means the value may be stored as JSON in the database, but in PHP it is available like an array.

## 18. Frontend Layouts

### Public layout

File: `resources/js/Layouts/PublicLayout.vue`

This handles:

- navbar
- active nav state
- theme switcher
- footer
- public site branding

The `site` prop comes from `usePage().props.site`.

### Admin layout

File: `resources/js/Layouts/AuthenticatedLayout.vue`

This handles:

- sidebar
- top header
- user dropdown
- theme switcher
- `AppToaster`

`AppToaster.vue` converts flash session messages into toast notifications.

## 19. Actual Calling Style of Public Forms

### Contact form

File: `resources/js/Pages/Public/Contact.vue`

Pattern:

- `reactive()` form object
- `window.axios.post('/api/public/contact', form)`
- `formErrors` from the error response
- `successMessage` from the success response

### Custom order page

File: `resources/js/Pages/Public/CustomOrder.vue`

Pattern:

- it is an informational public page
- direct conversation starts through a WhatsApp CTA
- no submit CRUD flow is running here

### Product review form

File: `resources/js/Pages/Public/ProductDetail.vue`

Pattern:

- `FormData`
- `product_id`, reviewer info, rating, comment
- images `images[]`
- POST `/api/public/reviews`

## 20. Actual Calling Style of Admin Product Save

Files:

- `resources/js/Pages/Products/Create.vue`
- `resources/js/Pages/Products/Partials/ProductForm.vue`
- `app/Http/Controllers/ProductController.php`
- `app/Actions/Products/SaveProductAction.php`

Flow:

1. Vue creates `useForm()`
2. On submit, `form.post(route('products.store'))` runs
3. The Laravel request is validated
4. `SaveProductAction` extracts the payload
5. Uploaded images are saved through `FileService`
6. The `Product` record is created or updated
7. A redirect happens
8. A toast is shown

## 21. How Does the Auth System Work?

File: `routes/auth.php`

Authentication follows the Laravel Breeze style.

Important routes:

- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `POST /logout`
- password reset routes
- email verification routes

Extra public-facing sign-in submit route:

- `POST /sign-in`

This hits `AuthenticatedSessionController@store`.

## 22. What Is the Role of Seeders?

In `database/seeders`, you will find sample/default data.

Important seeders:

- `CategorySeeder`
- `ProductSeeder`
- `ReviewSeeder`
- `ContactInquirySeeder`
- `PublicContentSeeder`
- `UserSeeder`

If you want demo data, the seeders will be helpful.

## 23. Commands You Should Know

### Development

```bash
composer run dev
```

This runs multiple things:

- Laravel server
- queue listener
- log tailing
- Vite dev server

### Frontend dev only

```bash
npm run dev
```

### Production build

```bash
npm run build
```

### Tests

```bash
php artisan test
```

### Migrations

```bash
php artisan migrate
```

## 24. How Are the API and Frontend Working Together in This Project?

This is a very common point of confusion.

Short answer:

- not everything is an API-based SPA
- not everything is classic Blade either
- this is a hybrid Inertia architecture

This means:

- page rendering is mostly `Laravel route -> controller -> Inertia -> Vue page`
- data submission sometimes happens through `Inertia form POST`
- and sometimes through `axios -> JSON API`

## 25. If You Want to Understand a New Feature, Follow This Sequence

For any feature, follow this order:

1. Check the route in `routes/web.php`
2. Open the controller method
3. See which model/action/service the controller is using
4. If there is an `Inertia::render`, open the corresponding Vue page
5. If there is an `axios.post`, check its API route and API controller
6. If there is a file upload, check `FileService`
7. If it involves public text/branding, check `SettingStore`

## 26. Which Files Should You Read First?

Recommended order:

1. `routes/web.php`
2. `resources/js/app.js`
3. `resources/views/app.blade.php`
4. `app/Http/Middleware/HandleInertiaRequests.php`
5. `app/Http/Controllers/PublicController.php`
6. `app/Http/Controllers/PublicApiController.php`
7. `app/Http/Controllers/ProductController.php`
8. `app/Http/Controllers/PublicContentController.php`
9. `app/Support/SettingStore.php`
10. `app/Services/FileService.php`
11. `resources/js/Layouts/PublicLayout.vue`
12. `resources/js/Layouts/AuthenticatedLayout.vue`

## 27. Beginner Mental Model

Remember this project like this:

- Laravel routes decide where the request should go
- controllers prepare the data
- models communicate with the database
- actions handle reusable business logic
- services handle helper work
- Inertia glues Laravel and Vue together
- Vue pages display the UI
- axios sends public form submissions
- Vite builds the frontend

## 28. Final Short Summary

This project:

- is a Laravel backend application
- is a Vue frontend application
- uses Inertia as the bridge
- runs on a single build
- includes both admin and public inside the same application
- supports database-driven public content
- manages products, reviews, and inquiries

---

If you want, in the next step I can create another file based on this guide:

- `REQUEST_FLOW_EXAMPLES.md`

In that file, I can document 4 real flows line by line:

- home page load
- product creation
- contact form submission
- product review submission
