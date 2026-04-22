# 🐾 PET STORE - TECHNICAL SYNOPSIS

## Project Overview

A full-featured PHP-based e-commerce platform for selling pets with advanced features including user authentication, shopping cart, order management, admin dashboard, and customer reviews system.

---

## 📊 SYSTEM ARCHITECTURE

### Technology Stack

| Component              | Technology              | Version      |
| ---------------------- | ----------------------- | ------------ |
| **Backend**            | PHP                     | 7.4+         |
| **Database**           | MySQL                   | 5.7+         |
| **Frontend**           | HTML5, CSS3, JavaScript | ES6+         |
| **Protocol**           | HTTP/HTTPS              | RESTful      |
| **Session Management** | PHP Sessions            | Cookie-based |

---

## 🗂️ PROJECT STRUCTURE & KEY FILES

### **Core Configuration Files**

#### `config.php` - Global Configuration Constants

```php
/* Security & Authentication */
OTP_TIMEOUT = 600                    // OTP valid for 10 minutes
OTP_MAX_ATTEMPTS = 6                 // Max failed OTP attempts
SESSION_TIMEOUT = 1800               // 30 minutes session timeout
PASSWORD_MIN_LENGTH = 8              // Minimum password requirement

/* Pagination & Caching */
ITEMS_PER_PAGE = 12                  // Products per page
CACHE_DURATION = 300                 // 5 minutes cache

/* Rate Limiting */
MAX_OTP_REQUESTS_PER_HOUR = 6       // Prevent OTP spam
OTP_REQUEST_COOLDOWN = 60            // 60 seconds between requests

/* Session Security */
session.cookie_httponly = 1          // Prevent XSS attacks
session.use_strict_mode = 1          // Strict SID handling
```

#### `db.php` - Database Connection Handler

```php
/* Connection Type */ PDO (PHP Data Objects)
/* Database */ MySQL with UTF-8 charset (utf8mb4)
/* Error Reporting */ Enabled with file logging (php-error.log)

Connection Example:
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",
               $username, $password);
```

---

## 🔐 SECURITY IMPLEMENTATION

### Files Handling Security

#### `helpers/csrf_token.php` - CSRF Protection

- Generates unique tokens for each session
- Validates tokens on form submission
- Prevents Cross-Site Request Forgery attacks

#### `helpers/validator.php` - Input Validation

- Email validation
- Password strength checking
- Data sanitization
- XSS prevention

#### `auth/` Directory - Authentication System

```
├── login.php           - User login with email & password
├── register.php        - New user registration
├── logout.php          - Session termination
├── verify_otp.php      - OTP verification (2-factor auth)
├── forgot_password.php - Password recovery initiation
├── verify_forgot_password_otp.php - Reset token validation
└── set_new_password.php - New password setting
```

**Security Features:**

- OTP (One-Time Password) verification
- Password hashing (bcrypt/argon2)
- Session token validation
- Rate limiting on OTP requests
- Email verification

---

## 📂 DATABASE SCHEMA

### Core Tables

#### `users` Table

```sql
Stores user account information
- id (PK)
- email (UNIQUE, INDEXED)
- password (HASHED)
- first_name, last_name
- phone, address
- account_status (active/inactive/suspended)
- created_at, updated_at
```

#### `pets` Table

```sql
Pet catalog and inventory
- id (PK)
- name, species, breed
- price, discount_price
- description, features
- stock_quantity
- images (JSON array)
- category_id (FK)
- created_at, updated_at
```

#### `pet_reviews` Table

```sql
Customer reviews and ratings
- id (PK)
- pet_id (FK)
- user_id (FK)
- username, email
- rating (1-5 stars)
- review_text
- status (pending/approved/rejected)
- created_at
```

#### `orders` Table

```sql
Purchase orders and tracking
- id (PK)
- user_id (FK)
- order_number (UNIQUE)
- total_amount, discount, tax
- status (pending/confirmed/shipped/delivered)
- shipping_address, billing_address
- created_at, updated_at
```

#### `cart` Table

```sql
Shopping cart items
- id (PK)
- user_id (FK)
- pet_id (FK)
- quantity
- added_at
- session_id (for guest users)
```

#### `testimonials` Table

```sql
Customer testimonials and feedback
- id (PK)
- customer_name, email
- rating (1-5 stars)
- message
- status (pending/approved)
- created_at
```

---

## 🎯 KEY FEATURES & IMPLEMENTATION

### 1. **User Management System**

**Files:** `auth/` directory, `profile.php`, `admin_login.php`

```php
/* Features */
- User registration with email verification
- Login with CSRF protection
- OTP-based 2-factor authentication
- Password recovery with email link
- Profile management
- Admin authentication (separate)
```

### 2. **Product Catalog**

**Files:** `index.php`, `category.php`, `pet_details.php`, `search.php`

```php
/* Features */
- Browse all pets by category
- Advanced search with filters
- Filter by: keyword, category, price range, location
- Sorting: by price, name, newest
- Detailed pet information with images
- Stock status display
- Similar products suggestion
```

### 3. **Shopping Cart & Checkout**

**Files:** `cart.php`, `cart_action.php`, `payment.php`, `delivery_address.php`

```php
/* Features */
- Add/remove items from cart
- Update quantities
- Real-time price calculation
- Delivery address input
- Payment gateway integration
- Invoice generation
```

### 4. **Order Management**

**Files:** `order_history.php`, `track_order.php`, `invoice.php`

```php
/* Features */
- View all user orders
- Real-time order tracking
- Order status history
- Invoice download (PDF)
- Delivery address display
- Payment confirmation
```

### 5. **Reviews & Ratings**

**Files:** `pet_details.php`, `helpers/review_handler.php` (if exists)

```php
/* Features */
- 1-5 star ratings
- Text reviews with validation
- Email verification
- Admin approval workflow
- Average rating calculation
- Review count display
```

### 6. **Admin Dashboard**

**Files:** `admin.php`, `admin_login.php`, `admin_logout.php`

```php
/* Features */
- Dashboard statistics
- Pet management (add/edit/delete)
- Order management
- User management
- Review approval/rejection
- Analytics and reporting
```

### 7. **Testimonials System**

**Files:** `testimonials.php`, `handlers/testimonial_handler.php`

```php
/* Features */
- Display approved testimonials
- Submit new testimonials
- Star rating system
- Admin approval workflow
- Professional layout
```

---

## 🛠️ HELPER FUNCTIONS & UTILITIES

### `helpers/` Directory

| File                     | Purpose              | Key Functions                                                 |
| ------------------------ | -------------------- | ------------------------------------------------------------- |
| **csrf_token.php**       | CSRF Protection      | `generate_csrf_token()`, `validate_csrf_token()`              |
| **validator.php**        | Input Validation     | `validate_email()`, `validate_password()`, `sanitize_input()` |
| **email_helper.php**     | Email Sending        | `send_verification_email()`, `send_otp_email()`               |
| **analytics_helper.php** | Tracking & Analytics | Analytics event logging, page tracking                        |

### `handlers/` Directory

| File                        | Purpose                 | Handles                                              |
| --------------------------- | ----------------------- | ---------------------------------------------------- |
| **testimonial_handler.php** | Testimonial Submissions | Form validation, database insert, email notification |
| **cart_action.php**         | Cart Operations         | Add to cart, update quantity, remove item            |
| **payment_success.php**     | Payment Processing      | Order confirmation, receipt generation               |

---

## 📁 ASSET MANAGEMENT

### `Assets/` Structure

```
Assets/
├── Birds/          → Bird product images
├── Cat/            → Cat product images
├── Dog/            → Dog product images
├── Fish/           → Fish product images
├── Category/       → Category thumbnail images
└── services/       → Service images
```

### `uploads/` Directory

- User invoices (PDF)
- Order receipts
- Testimonial attachments (if applicable)

---

## 🌐 FRONTEND PAGES

### Public Pages

| Page                     | Route                            | Purpose                          |
| ------------------------ | -------------------------------- | -------------------------------- |
| **index.php**            | `/`                              | Homepage with featured pets      |
| **category.php**         | `/category.php?id={id}`          | Category browse page             |
| **pet_details.php**      | `/pet_details.php?id={id}`       | Individual pet details + reviews |
| **search.php**           | `/search.php?q={query}`          | Advanced search results          |
| **cart.php**             | `/cart.php`                      | Shopping cart display            |
| **delivery_address.php** | `/delivery_address.php`          | Shipping address form            |
| **payment.php**          | `/payment.php`                   | Payment page                     |
| **payment_success.php**  | `/payment_success.php`           | Order confirmation               |
| **order_history.php**    | `/order_history.php`             | User order history               |
| **track_order.php**      | `/track_order.php?order_id={id}` | Order tracking page              |
| **profile.php**          | `/profile.php`                   | User profile management          |
| **wishlist.php**         | `/wishlist.php`                  | Saved items                      |
| **testimonials.php**     | `/testimonials.php`              | Customer testimonials            |
| **contact.php**          | `/contact.php`                   | Contact form                     |
| **FAQ.php**              | `/FAQ.php`                       | Frequently asked questions       |
| **privacy-policy.php**   | `/privacy-policy.php`            | Privacy policy                   |
| **terms-conditions.php** | `/terms-conditions.php`          | Terms & conditions               |
| **refund-policy.php**    | `/refund-policy.php`             | Refund policy                    |
| **404.php**              | `/404`                           | Error page                       |

### Authentication Pages

| Page                                    | Purpose                   |
| --------------------------------------- | ------------------------- |
| **auth/login.php**                      | User login                |
| **auth/register.php**                   | New user registration     |
| **auth/verify_otp.php**                 | OTP verification          |
| **auth/forgot_password.php**            | Password recovery request |
| **auth/verify_forgot_password_otp.php** | Reset token verification  |
| **auth/set_new_password.php**           | New password setting      |
| **auth/logout.php**                     | User session logout       |

### Admin Pages

| Page                 | Purpose              |
| -------------------- | -------------------- |
| **admin_login.php**  | Admin authentication |
| **admin.php**        | Admin dashboard      |
| **admin_logout.php** | Admin logout         |

---

## 🔄 DATA FLOW DIAGRAMS

### User Registration Flow

```
1. User fills register.php form
2. Validator.php validates input
3. CSRF token verification
4. Email check (unique)
5. Password hashing
6. Insert into users table
7. Send verification email
8. Redirect to verify_otp.php
```

### Purchase Flow

```
1. Browse pets (index/category/search)
2. Add to cart (cart_action.php)
3. Review cart (cart.php)
4. Enter delivery address
5. Proceed to payment.php
6. Payment processing
7. Order creation in database
8. payment_success.php confirmation
9. Invoice generation
10. Email receipt to user
```

### Admin Review Approval

```
1. User submits review (pet_details.php)
2. Review saved with status: 'pending'
3. Admin reviews in dashboard (admin.php)
4. Admin approves/rejects
5. Status updated to 'approved'/'rejected'
6. Display on pet_details.php
```

---

## 🔧 API ENDPOINTS (Form-based)

### Cart Operations

- `POST /cart_action.php` → Add/Update/Remove items
- `GET /cart.php` → Display cart

### Order Processing

- `POST /delivery_address.php` → Save shipping address
- `POST /payment.php` → Process payment
- `POST /process_payment.php` → Payment handler

### Authentication

- `POST /auth/login.php` → User login
- `POST /auth/register.php` → New registration
- `POST /auth/verify_otp.php` → OTP verification
- `POST /auth/forgot_password.php` → Reset initiation

### Reviews & Testimonials

- `POST /handlers/testimonial_handler.php` → Submit testimonial
- `POST /pet_details.php` → Submit pet review

---

## 📊 IMPORTANT CONSTANTS & CONFIGURATION

### Session & Security

```php
ITEMS_PER_PAGE = 12              // Pagination size
OTP_TIMEOUT = 600 seconds        // OTP validity
SESSION_TIMEOUT = 1800 seconds   // 30-min timeout
PASSWORD_MIN_LENGTH = 8          // Minimum password
OTP_MAX_ATTEMPTS = 6             // Failed attempts limit
```

### Environment Variables (.env file)

```
DB_HOST=localhost
DB_NAME=pet_store
DB_USER=root
DB_PASS=
SYSTEM_EMAIL=noreply@paws-store.com
```

---

## 🚀 DEPLOYMENT CHECKLIST

- [ ] Set `session.cookie_secure = 1` in production
- [ ] Use HTTPS for all connections
- [ ] Configure .env with production database
- [ ] Set appropriate file permissions
- [ ] Enable error logging to file
- [ ] Disable debug mode
- [ ] Set up automated backups
- [ ] Configure email service
- [ ] Test payment gateway integration
- [ ] Set up SSL certificate

---

## 📈 PERFORMANCE OPTIMIZATIONS

- Database indexing on frequently queried columns
- Cache mechanism for product listings (5-minute cache)
- Image optimization in Assets/ folders
- Pagination to reduce memory load
- PDO prepared statements (prevents SQL injection)

---

## 🔍 DEBUGGING & LOGGING

### Error Logs Location

- `php-error.log` - PHP errors and warnings
- Browser Console - JavaScript errors
- Database Logs - Query errors

### Debug Output

- Enable in development: `error_reporting(E_ALL)`
- Disable in production: Set to `0`

---

## 📞 SUPPORT & DOCUMENTATION

For detailed implementation guides:

- See `FEATURES_DOCUMENTATION.md` - Complete feature list
- See `SETUP_INSTRUCTIONS.md` - Installation guide
- Check individual file headers for code comments

---

**Last Updated:** April 2026
**Status:** Production Ready ✅
**Version:** 1.0
