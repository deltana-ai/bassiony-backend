# LNF Updated Api Project

## Main Menu
Home, About, Services, Membership Guidelines, Events, News, Contact, Application Form

## Pages Structure
### 1. Home Page
- Hero Section
- About LNF (Short) Section
- Latest Events Grid (Same as WSA with user_id -> nullable)
- Latest News Grid (Same as WSA with user_id -> nullable)

### 2. About
- Who we are
- The core idea
- mission and vision
- Governance
- Board Team
- Presidency
- Headquarters Team

### 3. Services
- Side Menu (Categories with Sub Categories)
- Service Details ``(id, name, slug, icon, short_description, description, parent_id, type, position)`` -> has media image
- Services are
  1. Increasing Membership
  2. Catalyzing Network Profitability
  3. Solidifying Trust through Financial Protection
  4. Revolutionizing Conferences for Mutual Benefit
  5. Unified Payment Gateway
  6. Blacklist Management and Shared Resources

### 4. Membership Guidelines
- Eligibility
- Rights and Responsibilities
- Termination of Membership
- Permitted Uses for Members
- Policy Overview
- Use of LNF Brand

### 5. Application Form 
#### Table Schema
```php
$table->id();
$table->string('name');
$table->string('address_line_one')->nullable();
$table->string('address_line_two')->nullable();
$table->string('city');
$table->string('state')->nullable();
$table->string('postal_code')->nullable();
$table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
$table->string('website');
$table->string('phone')->nullable();
$table->integer('members_count');
$table->integer('business_est');
$table->longText('profile')->nullable();
$table->enum('fpp', ['yes', 'no'])->default('no');
// Representative Details
$table->enum('title', ['mr', 'mrs', 'ms'])->default('mr');
$table->string('first_name');
$table->string('last_name');
$table->string('job_title');
$table->string('phone_number');
$table->string('cell_number');
$table->string('email');
$table->date('birth_date')->nullable();
$table->timestamps();
```
