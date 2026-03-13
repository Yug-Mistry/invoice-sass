# Multi-Tenant SaaS Invoice Platform - Phase 1 & 2 Implementation Plan

Complete backend foundation with multi-tenant authentication, RBAC scaffolding, and core database structure.

---

## 📋 Implementation Checklist

### **STEP 1: Project Setup & Core Packages**

- [x] **1.1** Create Laravel 12 project: `laravel new saas-invoice-api`
- [x] **1.2** Install core packages:
  ```bash
  composer require laravel/sanctum
  composer require barryvdh/laravel-dompdf
  composer require intervention/image
  ```
- [x] **1.3** Run API installation: `php artisan install:api`
- [x] **1.4** Configure environment variables (`.env`):
  - Database connection
  - APP_URL
  - SANCTUM_STATEFUL_DOMAINS
- [x] **1.5** Test initial setup: `php artisan serve`

---

### **STEP 2: Multi-Tenant Core Tables**

#### 2.1 Tenants Table
- [x] **2.1.1** Create migration: `php artisan make:migration create_tenants_table`
- [x] **2.1.2** Define schema:
  - `id` (primary key)
  - `name` (string)
  - `gst_no` (string, nullable)
  - `timestamps`
- [x] **2.1.3** Create Tenant model with fillable fields

#### 2.2 Groups Table (per-tenant roles)
- [x] **2.2.1** Create migration: `php artisan make:migration create_groups_table`
- [x] **2.2.2** Define schema:
  - `id` (primary key)
  - `tenant_id` (foreign key → tenants)
  - `name` (string)
  - `description` (text, nullable)
  - `timestamps`
  - Index on `tenant_id`
- [x] **2.2.3** Create Group model with:
  - Fillable: `tenant_id`, `name`, `description`
  - Relationships: `belongsTo(Tenant)`, `hasMany(User)`, `belongsToMany(Permission)`
  - TenantScope
  - Boot method for auto tenant_id injection

#### 2.3 Permissions Table (global definitions)
- [x] **2.3.1** Create migration: `php artisan make:migration create_permissions_table`
- [x] **2.3.2** Define schema:
  - `id` (primary key)
  - `name` (string) — e.g., "Create Invoices"
  - `slug` (string, unique) — e.g., "invoices.create"
  - `description` (text, nullable)
  - `timestamps`
- [x] **2.3.3** Create Permission model with:
  - Fillable: `name`, `slug`, `description`
  - Relationships: `belongsToMany(Group)`
  - NO TenantScope (global)

#### 2.4 Group-Permission Pivot Table
- [x] **2.4.1** Create migration: `php artisan make:migration create_group_permission_table`
- [x] **2.4.2** Define schema:
  - `id` (primary key)
  - `group_id` (foreign key → groups)
  - `permission_id` (foreign key → permissions)
  - `timestamps`

#### 2.5 Users Table
- [x] **2.5.1** Modify default users migration to include:
  - `tenant_id` (foreign key → tenants)
  - `group_id` (foreign key → groups, nullable)
  - `first_name` (string)
  - `middle_name` (string, nullable)
  - `last_name` (string)
  - `email` (string, unique)
  - `phone` (string, nullable, unique)
  - `password` (string)
  - `status` (enum: active, inactive, suspended, default: active)
  - `timestamps`
- [x] **2.5.2** Update User model:
  - Add `HasApiTokens` trait (from Laravel\Sanctum)
  - Fillable: `tenant_id`, `group_id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `password`, `status`
  - Relationships: `belongsTo(Tenant)`, `belongsTo(Group)`
  - TenantScope
  - Boot method for auto tenant_id injection (authenticated users only)

---

### **STEP 3: Multi-Tenant Scope Implementation**

- [x] **3.1** Create `app/Models/Scopes/TenantScope.php`
- [x] **3.2** Implement scope logic:
  ```php
  public function apply(Builder $builder, Model $model)
  {
      if (auth()->check()) {
          $builder->where($model->getTable() . '.tenant_id', auth()->user()->tenant_id);
      }
  }
  ```
- [x] **3.3** Apply TenantScope to models (User, Group)
- [ ] **3.4** Test scope isolation with multiple tenants

---

### **STEP 4: Authentication System**

#### 4.1 Auth Routes
- [x] **4.1.1** Define routes in `routes/api.php`:
  - `POST /auth/register`
  - `POST /auth/login`
  - `POST /auth/logout` (protected)
  - `GET /auth/me` (protected)

#### 4.2 AuthController
- [x] **4.2.1** Create controller: `php artisan make:controller AuthController`
- [x] **4.2.2** Implement `register()` method:
  - Validate: `company_name`, `first_name`, `last_name`, `email`, `password`
  - Create Tenant
  - Create Admin Group for tenant
  - Attach all permissions to Admin group
  - Create User with tenant_id and group_id
  - Generate Sanctum token
  - Return user + token
- [x] **4.2.3** Implement `login()` method:
  - Validate: `email`, `password`
  - Find user and verify password
  - Generate Sanctum token
  - Return user + token
- [x] **4.2.4** Implement `logout()` method:
  - Delete current access token
  - Return success message
- [x] **4.2.5** Implement `me()` method:
  - Return authenticated user with relationships (tenant, group)

---

### **STEP 5: RBAC - Permissions Seeder**

- [x] **5.1** Create seeder: `php artisan make:seeder PermissionSeeder`
- [x] **5.2** Define all permissions:
  - **Clients**: `clients.create`, `clients.view`, `clients.update`, `clients.delete`
  - **Products**: `products.create`, `products.view`, `products.update`, `products.delete`
  - **Invoices**: `invoices.create`, `invoices.view`, `invoices.update`, `invoices.delete`
  - **Challans**: `challans.create`, `challans.view`, `challans.update`, `challans.delete`
  - **Reports**: `reports.view`, `reports.export`
  - **Admin**: `users.manage`, `groups.manage`, `settings.manage`
- [x] **5.3** Register seeder in `DatabaseSeeder.php`
- [x] **5.4** Run seeder: `php artisan db:seed --class=PermissionSeeder`

---

### **STEP 6: Core Business Tables - Migrations**

#### 6.1 Clients & Addresses
- [ ] **6.1.1** Create clients migration:
  - `tenant_id`, `name`, `email`, `phone`, `gst_no`, `pan`, `status`, `timestamps`
- [ ] **6.1.2** Create addresses migration (polymorphic):
  - `tenant_id`, `addressable_id`, `addressable_type`, `type` (billing/shipping), `street`, `city`, `state`, `pincode`, `country`, `timestamps`
- [ ] **6.1.3** Create Client model
- [ ] **6.1.4** Create Address model

#### 6.2 Products Module
- [ ] **6.2.1** Create product_categories migration:
  - `tenant_id`, `name`, `description`, `timestamps`
- [ ] **6.2.2** Create products migration:
  - `tenant_id`, `category_id`, `name`, `hsn_code`, `description`, `unit`, `price`, `tax_percent`, `status`, `timestamps`
- [ ] **6.2.3** Create ProductCategory model
- [ ] **6.2.4** Create Product model

#### 6.3 Factories/Warehouses
- [ ] **6.3.1** Create factories migration:
  - `tenant_id`, `name`, `code`, `address`, `city`, `state`, `pincode`, `gst_no`, `timestamps`
- [ ] **6.3.2** Create Factory model

#### 6.4 Invoices Module
- [ ] **6.4.1** Create invoices migration:
  - `tenant_id`, `client_id`, `factory_id`, `invoice_number`, `invoice_date`, `due_date`, `subtotal`, `tax_amount`, `total`, `status`, `notes`, `timestamps`
- [ ] **6.4.2** Create invoice_items migration:
  - `invoice_id`, `product_id`, `description`, `hsn_code`, `quantity`, `unit_price`, `tax_percent`, `tax_amount`, `total`, `timestamps`
- [ ] **6.4.3** Create Invoice model
- [ ] **6.4.4** Create InvoiceItem model

#### 6.5 Challans Module
- [ ] **6.5.1** Create challans migration:
  - `tenant_id`, `client_id`, `factory_id`, `invoice_id` (nullable), `challan_number`, `challan_date`, `vehicle_no`, `lr_no`, `status`, `timestamps`
- [ ] **6.5.2** Create challan_items migration:
  - `challan_id`, `product_id`, `description`, `quantity`, `unit`, `timestamps`
- [ ] **6.5.3** Create Challan model
- [ ] **6.5.4** Create ChallanItem model

#### 6.6 Audit Logs
- [ ] **6.6.1** Create audit_logs migration:
  - `tenant_id`, `user_id`, `action`, `model_type`, `model_id`, `old_data` (json), `new_data` (json), `ip_address`, `user_agent`, `timestamps`
  - Indexes on: `tenant_id`, `user_id`, `model_type`, `created_at`
- [ ] **6.6.2** Create AuditLog model

---

### **STEP 7: Models Implementation**

For each model created above, implement:
- [ ] **7.1** Fillable attributes
- [ ] **7.2** Relationships (belongsTo, hasMany, belongsToMany)
- [ ] **7.3** TenantScope (where applicable)
- [ ] **7.4** Boot method for auto tenant_id injection (where applicable)
- [ ] **7.5** Casts (dates, json, enums)

**Models requiring TenantScope:**
- Client, ProductCategory, Product, Factory, Invoice, Challan, AuditLog

**Models NOT requiring TenantScope:**
- Permission (global), InvoiceItem, ChallanItem, Address (accessed via parent)

---

### **STEP 8: Run Migrations**

- [x] **8.1** Run fresh migrations: `php artisan migrate:fresh`
- [x] **8.2** Run seeders: `php artisan db:seed`
- [x] **8.3** Verify all tables created in database
- [x] **8.4** Verify permissions seeded

---

### **STEP 9: Testing & Verification**

#### 9.1 API Testing (Postman/Insomnia/Thunder Client)
- [x] **9.1.1** Test Registration:
  - POST `/auth/register`
  - Payload: `{"company_name": "Test Corp", "first_name": "John", "last_name": "Doe", "email": "john@test.com", "password": "secret123"}`
  - Verify response contains `user` object and `token`
  - Check database: tenant created, user created, Admin group created, user.group_id set

- [x] **9.1.2** Test Login:
  - POST `/auth/login`
  - Payload: `{"email": "john@test.com", "password": "secret123"}`
  - Verify token returned

- [x] **9.1.3** Test Protected Route:
  - GET `/auth/me`
  - Header: `Authorization: Bearer {token}`
  - Verify user data with relationships (tenant, group)

- [x] **9.1.4** Test Logout:
  - POST `/auth/logout` with Bearer token
  - Verify token deleted
  - Verify subsequent `/auth/me` returns 401

#### 9.2 Tenant Isolation Testing
- [ ] **9.2.1** Register Tenant A with User A
- [ ] **9.2.2** Register Tenant B with User B
- [ ] **9.2.3** Login as User A, verify `auth()->user()->tenant_id` matches Tenant A
- [ ] **9.2.4** Create a record (future: client/product)
- [ ] **9.2.5** Login as User B, verify User A's data is NOT visible
- [ ] **9.2.6** Verify TenantScope filters queries correctly

#### 9.3 Auto tenant_id Injection Testing
- [ ] **9.3.1** Login as a user
- [ ] **9.3.2** Create an entity (Group, future: Client) without passing `tenant_id`
- [ ] **9.3.3** Verify `tenant_id` is auto-set to authenticated user's tenant_id
- [ ] **9.3.4** Verify manual override is NOT possible (security check)

#### 9.4 Database Verification
- [ ] **9.4.1** Check all tables exist
- [ ] **9.4.2** Check foreign keys and constraints
- [ ] **9.4.3** Check indexes on tenant_id columns
- [ ] **9.4.4** Verify permissions table has all seeded permissions
- [ ] **9.4.5** Verify group_permission pivot properly links groups to permissions

---

## 📚 File Structure Reference

### Models to Create (10 total)
```
app/Models/
├── Tenant.php ✓ (already exists)
├── User.php ✓ (needs updates)
├── Group.php ✓ (needs completion)
├── Permission.php ✓ (needs completion)
├── Client.php
├── Address.php
├── ProductCategory.php
├── Product.php
├── Factory.php
├── Invoice.php
├── InvoiceItem.php
├── Challan.php
├── ChallanItem.php
└── AuditLog.php
```

### Migrations to Create (11 total)
```
database/migrations/
├── XXXX_create_tenants_table.php ✓
├── XXXX_create_groups_table.php ✓
├── XXXX_create_permissions_table.php ✓
├── XXXX_create_users_table.php ✓
├── XXXX_create_group_permission_table.php ✓
├── XXXX_create_clients_table.php
├── XXXX_create_addresses_table.php
├── XXXX_create_product_categories_table.php
├── XXXX_create_products_table.php
├── XXXX_create_factories_table.php
├── XXXX_create_invoices_table.php
├── XXXX_create_invoice_items_table.php
├── XXXX_create_challans_table.php
├── XXXX_create_challan_items_table.php
└── XXXX_create_audit_logs_table.php
```

### Seeders to Create (1 total)
```
database/seeders/
├── DatabaseSeeder.php ✓
└── PermissionSeeder.php ✓
```

### Controllers
```
app/Http/Controllers/
└── AuthController.php ✓ (needs register() enhancement)
```

### Scopes
```
app/Models/Scopes/
└── TenantScope.php ✓
```

---

## 🎯 Success Criteria

## 🎯 Success Criteria

Phase 1 & 2 are complete when:

- ✅ All migrations run without errors
- ✅ All models created with proper relationships
- ✅ TenantScope isolates data correctly
- ✅ Auto tenant_id injection works
- ✅ Registration creates: Tenant → Admin Group → User (with token)
- ✅ Login returns valid Sanctum token
- ✅ Protected routes require Bearer token
- ✅ Logout invalidates token
- ✅ Two tenants cannot see each other's data
- ✅ Permissions seeded and linked to Admin group
- ✅ All database tables created as per schema

---

## 🔑 Key Architectural Decisions

### 1. **Custom RBAC vs Spatie Permission**
**Decision:** Custom RBAC (Group → Permission relationship)
- **Reason:** Simpler for this use case, full control, already started
- **Trade-off:** No caching, wildcard permissions, or role inheritance out-of-the-box
- **Alternative:** Install Spatie later if advanced features needed

### 2. **Permission Scoping**
**Decision:** Permissions are global (no tenant_id), Groups are per-tenant
- **Reason:** Permission definitions are universal ("invoices.create"), but group membership is tenant-specific
- **Implementation:** PermissionSeeder runs once; AuthController creates groups per-tenant on registration

### 3. **Auto tenant_id Injection Strategy**
**Decision:** Auto-inject only for authenticated users via model boot
- **Reason:** During registration, no user is authenticated; manual assignment is necessary
- **Implementation:** 
  ```php
  static::creating(function ($model) {
      if (auth()->check() && !$model->tenant_id) {
          $model->tenant_id = auth()->user()->tenant_id;
      }
  });
  ```

### 4. **Group Assignment During Registration**
**Decision:** Auto-create "Admin" group per tenant on first user registration
- **Reason:** Every tenant needs at least one admin; simplifies onboarding
- **Implementation:** AuthController register() creates group, attaches all permissions, assigns to user

### 5. **Address Data Model**
**Decision:** Polymorphic relationship (`addressable_type`, `addressable_id`)
- **Reason:** Clients, Factories, Users, Tenants all need addresses; DRY principle
- **Alternative:** Separate address columns per table (client_address, factory_address, etc.)

### 6. **Invoice/Challan Numbering**
**Decision:** Add columns now, implement auto-generation in Phase 6
- **Reason:** Numbering strategy needs careful thought (scope: per-tenant, per-year, format)
- **Format:** `INV-2026-0001` (prefix-year-sequential)

---

## 📊 Database Schema Summary

### Core Multi-Tenant Tables
```
tenants (no tenant_id - root level)
├── groups (tenant_id → tenants)
│   └── group_permission (pivot)
│       └── permissions (global, no tenant_id)
└── users (tenant_id → tenants, group_id → groups)
```

### Business Tables (all have tenant_id)
```
clients (tenant_id)
├── addresses (polymorphic)
│
product_categories (tenant_id)
└── products (tenant_id, category_id)
│
factories (tenant_id)
│
invoices (tenant_id, client_id, factory_id)
├── invoice_items (invoice_id, product_id)
│
challans (tenant_id, client_id, factory_id, invoice_id?)
├── challan_items (challan_id, product_id)
│
audit_logs (tenant_id, user_id)
```

---

## 🚫 Out of Scope (Deferred to Later Phases)

This plan does **NOT** include:

- ❌ CRUD API endpoints for business entities (Phase 4-6)
- ❌ Permission checking middleware (Phase 3)
- ❌ PDF generation (Phase 7)
- ❌ Audit logging middleware (Phase 9)
- ❌ Redis caching (Phase 10)
- ❌ Frontend development (Phase 12+)
- ❌ Subscription/billing system (Phase 13-14)
- ❌ Advanced analytics (Phase 15)
- ❌ Testing suite (add incrementally)

---

## 💡 Implementation Tips

### Parallel Execution Strategy
Steps that can be executed simultaneously:
- **Group 1:** Steps 6.1, 6.2, 6.3 (Clients, Products, Factories migrations)
- **Group 2:** Steps 6.4, 6.5 (Invoices, Challans migrations - after Group 1)
- **Group 3:** All model creations in Step 7

### Common Pitfalls to Avoid
1. **Forgetting HasApiTokens trait** → `createToken()` method won't exist
2. **Not adding tenant_id to fillable** → Mass assignment errors
3. **Applying TenantScope to child models** → InvoiceItem should access via Invoice, not directly
4. **Manual tenant_id override** → Could breach tenant isolation; validate in boot
5. **Not indexing tenant_id** → Query performance issues at scale

### Artisan Commands Reference
```bash
# Create migration
php artisan make:migration create_table_name

# Create model
php artisan make:model ModelName

# Create controller
php artisan make:controller ControllerName

# Create seeder
php artisan make:seeder SeederName

# Run migrations (fresh)
php artisan migrate:fresh

# Run seeders
php artisan db:seed
php artisan db:seed --class=PermissionSeeder

# Run migrations + seeders
php artisan migrate:fresh --seed
```

### Testing Endpoints with cURL
```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"company_name":"Test Corp","first_name":"John","last_name":"Doe","email":"john@test.com","password":"secret123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@test.com","password":"secret123"}'

# Get authenticated user
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📈 Progress Tracking

Use the main project checklist ([project-checklist.md]) to track overall progress across all 25 phases.

**This plan completes:**
- ✅ Phase 0 (Architecture) - already done
- ✅ Phase 1 (Backend Foundation) - Steps 1-3, 5
- ✅ Phase 2 (Authentication System) - Complete

**After completion, move to:**
- 🔜 Phase 3 (RBAC Middleware)
- 🔜 Phase 4 (Clients Module with CRUD APIs)
- 🔜 Phase 5 (Products Module with CRUD APIs)
- 🔜 Phase 6 (Invoices & Challans with business logic)

---

## 🎓 Learning Resources

If you get stuck, refer to:
- **Laravel Sanctum Docs:** https://laravel.com/docs/11.x/sanctum
- **Laravel Models:** https://laravel.com/docs/11.x/eloquent
- **Laravel Migrations:** https://laravel.com/docs/11.x/migrations
- **Global Scopes:** https://laravel.com/docs/11.x/eloquent#global-scopes
- **Polymorphic Relationships:** https://laravel.com/docs/11.x/eloquent-relationships#polymorphic-relationships

---

## ✅ Completion Checklist Summary

- [ ] **18** migrations created and run successfully
- [ ] **14** models created with proper implementation
- [ ] **1** seeder created and executed
- [ ] **1** controller (AuthController) complete with 4 endpoints
- [ ] **1** scope (TenantScope) applied to all tenant-specific models
- [ ] **4** API endpoints tested and working
- [ ] **2** tenants registered to verify isolation
- [ ] **Zero** errors in `php artisan migrate:fresh --seed`

**Estimated Time:** 4-6 hours for experienced developer, 8-12 hours for learning while building

---

**Next Action:** Start with Step 1 (Project Setup) or jump to specific steps if project already initialized.
