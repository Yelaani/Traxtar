# Security Testing Guide - Traxtar Application

## Overview

This document provides a comprehensive guide for testing the security measures implemented in the Traxtar application. It includes test cases, procedures, and expected results for all security features.

---

## Table of Contents

1. [Authentication Security Testing](#1-authentication-security-testing)
2. [Authorization Testing](#2-authorization-testing)
3. [Input Validation Testing](#3-input-validation-testing)
4. [XSS Prevention Testing](#4-xss-prevention-testing)
5. [SQL Injection Testing](#5-sql-injection-testing)
6. [CSRF Protection Testing](#6-csrf-protection-testing)
7. [API Security Testing](#7-api-security-testing)
8. [Rate Limiting Testing](#8-rate-limiting-testing)
9. [File Upload Security Testing](#9-file-upload-security-testing)
10. [Session Security Testing](#10-session-security-testing)
11. [Security Headers Testing](#11-security-headers-testing)

---

## 1. Authentication Security Testing

### Test 1.1: Password Hashing

**Objective**: Verify passwords are hashed, not stored in plain text.

**Procedure**:
1. Register a new user with password `TestPassword123!`
2. Check database `users` table
3. Verify password field contains bcrypt hash (starts with `$2y$`)

**Expected Result**: ✅ Password is hashed, not plain text

**SQL Query**:
```sql
SELECT id, email, password FROM users WHERE email = 'test@example.com';
```

---

### Test 1.2: Email Verification

**Objective**: Verify users must verify email before accessing protected routes.

**Procedure**:
1. Register new user
2. Try to access `/customer/dashboard` without verifying email
3. Should be redirected to email verification page

**Expected Result**: ✅ Unverified users cannot access protected routes

---

### Test 1.3: Login Rate Limiting

**Objective**: Verify brute force protection via rate limiting.

**Procedure**:
1. Attempt to login with wrong password 6 times in 1 minute
2. 6th attempt should be blocked
3. Wait 1 minute
4. Should be able to attempt login again

**Expected Result**: ✅ Maximum 5 login attempts per minute per email/IP

**Test Command**:
```bash
# Use curl or Postman to test
for i in {1..6}; do
  curl -X POST http://localhost:8000/login \
    -d "email=test@example.com&password=wrong"
done
```

---

### Test 1.4: Two-Factor Authentication

**Objective**: Verify 2FA functionality.

**Procedure**:
1. Enable 2FA in user profile
2. Scan QR code with authenticator app
3. Logout and login
4. Enter 2FA code when prompted

**Expected Result**: ✅ 2FA code required for login

---

## 2. Authorization Testing

### Test 2.1: Admin Access Control

**Objective**: Verify only admins can access admin routes.

**Procedure**:
1. Login as customer user
2. Try to access `/admin/dashboard`
3. Try to access `/admin/products`
4. Should be denied access

**Expected Result**: ✅ Customer users cannot access admin routes

**Test URLs**:
- `GET /admin/dashboard` → Should redirect to login or show 403
- `GET /admin/products` → Should redirect to login or show 403

---

### Test 2.2: Customer Access Control

**Objective**: Verify only customers can access customer routes.

**Procedure**:
1. Login as admin user
2. Try to access `/customer/dashboard`
3. Try to access `/cart`
4. Should be denied access

**Expected Result**: ✅ Admin users cannot access customer routes

**Test URLs**:
- `GET /customer/dashboard` → Should redirect to login or show 403
- `GET /cart` → Should redirect to login or show 403

---

### Test 2.3: API Authorization

**Objective**: Verify API endpoints require proper authorization.

**Procedure**:
1. Try to access `/api/products` (POST) without token → Should fail
2. Try to access `/api/products` (POST) with customer token → Should fail (admin only)
3. Try to access `/api/products` (POST) with admin token → Should succeed

**Expected Result**: ✅ API endpoints enforce role-based access

**Test Commands**:
```bash
# Without token
curl -X POST http://localhost:8000/api/products

# With customer token (should fail)
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {customer_token}"

# With admin token (should succeed)
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Product","price":10.00,"stock":100}'
```

---

## 3. Input Validation Testing

### Test 3.1: Product Creation Validation

**Objective**: Verify product creation validates all inputs.

**Procedure**:
1. Try to create product with empty name → Should fail
2. Try to create product with negative price → Should fail
3. Try to create product with negative stock → Should fail
4. Try to create product with invalid image type → Should fail
5. Try to create product with image > 2MB → Should fail

**Expected Result**: ✅ All invalid inputs are rejected

**Test Cases**:
```php
// Empty name
POST /admin/products
{ "name": "", "price": 10, "stock": 100 }

// Negative price
POST /admin/products
{ "name": "Test", "price": -10, "stock": 100 }

// Invalid image
POST /admin/products
{ "name": "Test", "price": 10, "stock": 100, "image": "malicious.exe" }
```

---

### Test 3.2: Order Creation Validation

**Objective**: Verify order creation validates shipping details.

**Procedure**:
1. Try to create order with empty shipping name → Should fail
2. Try to create order with empty shipping address → Should fail
3. Try to create order with invalid phone format → Should fail
4. Try to create order with empty cart → Should fail

**Expected Result**: ✅ All required fields validated

---

### Test 3.3: User Registration Validation

**Objective**: Verify registration validates user input.

**Procedure**:
1. Try to register with invalid email → Should fail
2. Try to register with password < 8 characters → Should fail
3. Try to register with mismatched passwords → Should fail
4. Try to register with existing email → Should fail

**Expected Result**: ✅ All validation rules enforced

---

## 4. XSS Prevention Testing

### Test 4.1: Product Name XSS

**Objective**: Verify XSS attempts in product names are escaped.

**Procedure**:
1. As admin, create product with name: `<script>alert('XSS')</script>`
2. View product in shop page
3. Check page source

**Expected Result**: ✅ Script tags are escaped, not executed

**Test Input**:
```
Product Name: <script>alert('XSS')</script>
```

**Expected Output in HTML**:
```html
&lt;script&gt;alert('XSS')&lt;/script&gt;
```

---

### Test 4.2: Product Description XSS

**Objective**: Verify XSS attempts in descriptions are escaped.

**Procedure**:
1. Create product with description containing: `<img src=x onerror=alert('XSS')>`
2. View product page
3. Verify image tag is escaped

**Expected Result**: ✅ XSS payloads are escaped

---

### Test 4.3: Search Input XSS

**Objective**: Verify search input is safe.

**Procedure**:
1. Enter `<script>alert('XSS')</script>` in search box
2. Submit search
3. Verify script is not executed

**Expected Result**: ✅ Search input is escaped

---

## 5. SQL Injection Testing

### Test 5.1: Product ID Injection

**Objective**: Verify SQL injection attempts are prevented.

**Procedure**:
1. Try to access product with ID: `1' OR '1'='1`
2. Try to access product with ID: `1; DROP TABLE products;--`
3. Verify no SQL errors and no data loss

**Expected Result**: ✅ SQL injection attempts fail safely

**Test URLs**:
```
GET /products/1' OR '1'='1
GET /products/1; DROP TABLE products;--
```

---

### Test 5.2: Search SQL Injection

**Objective**: Verify search input prevents SQL injection.

**Procedure**:
1. Search for: `' OR '1'='1`
2. Search for: `'; DROP TABLE products;--`
3. Verify no SQL errors

**Expected Result**: ✅ Search uses parameterized queries

---

### Test 5.3: Order ID Injection

**Objective**: Verify order access prevents SQL injection.

**Procedure**:
1. Try to access order with ID: `1' UNION SELECT * FROM users--`
2. Verify no unauthorized data access

**Expected Result**: ✅ SQL injection prevented

---

## 6. CSRF Protection Testing

### Test 6.1: Form Without CSRF Token

**Objective**: Verify forms require CSRF tokens.

**Procedure**:
1. Create HTML form without CSRF token
2. Submit form to `/admin/products` (POST)
3. Should receive 419 error

**Expected Result**: ✅ Forms without CSRF token are rejected

**Test HTML**:
```html
<form method="POST" action="http://localhost:8000/admin/products">
  <input name="name" value="Test">
  <button type="submit">Submit</button>
</form>
```

---

### Test 6.2: API CSRF Exemption

**Objective**: Verify API routes are exempt from CSRF.

**Procedure**:
1. Make API request without CSRF token
2. Should succeed (if authenticated with token)

**Expected Result**: ✅ API routes don't require CSRF token

---

## 7. API Security Testing

### Test 7.1: API Authentication

**Objective**: Verify API requires authentication.

**Procedure**:
1. Try to access `/api/user` without token → Should fail (401)
2. Login via API to get token
3. Access `/api/user` with token → Should succeed

**Expected Result**: ✅ API endpoints require valid token

**Test Commands**:
```bash
# Without token (should fail)
curl http://localhost:8000/api/user

# With token (should succeed)
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer {token}"
```

---

### Test 7.2: API Token Revocation

**Objective**: Verify tokens can be revoked.

**Procedure**:
1. Login via API to get token
2. Use token to access protected endpoint → Should succeed
3. Logout via API
4. Try to use same token → Should fail (401)

**Expected Result**: ✅ Revoked tokens are invalid

---

### Test 7.3: API Role-Based Access

**Objective**: Verify API enforces role-based access.

**Procedure**:
1. Login as customer, get token
2. Try to POST to `/api/products` → Should fail (403)
3. Login as admin, get token
4. Try to POST to `/api/products` → Should succeed

**Expected Result**: ✅ API enforces role-based access

---

## 8. Rate Limiting Testing

### Test 8.1: Login Rate Limiting

**Objective**: Verify login rate limiting works.

**Procedure**:
1. Attempt login with wrong password 5 times → Should succeed (but fail auth)
2. Attempt 6th time → Should be blocked (429 Too Many Requests)
3. Wait 1 minute
4. Attempt login again → Should succeed

**Expected Result**: ✅ Maximum 5 attempts per minute

---

### Test 8.2: 2FA Rate Limiting

**Objective**: Verify 2FA rate limiting works.

**Procedure**:
1. Enable 2FA
2. Enter wrong 2FA code 5 times → Should succeed (but fail auth)
3. Enter 6th time → Should be blocked

**Expected Result**: ✅ Maximum 5 2FA attempts per minute

---

## 9. File Upload Security Testing

### Test 9.1: File Type Validation

**Objective**: Verify only images are accepted.

**Procedure**:
1. Try to upload `.exe` file → Should fail
2. Try to upload `.php` file → Should fail
3. Try to upload `.jpg` file → Should succeed
4. Try to upload `.png` file → Should succeed

**Expected Result**: ✅ Only image types accepted

---

### Test 9.2: File Size Validation

**Objective**: Verify file size limit enforced.

**Procedure**:
1. Try to upload image > 2MB → Should fail
2. Try to upload image < 2MB → Should succeed

**Expected Result**: ✅ Maximum 2MB file size

---

### Test 9.3: Malicious File Name

**Objective**: Verify malicious file names are handled.

**Procedure**:
1. Try to upload file with name: `../../../etc/passwd`
2. Verify file is stored safely, not in system directory

**Expected Result**: ✅ Directory traversal prevented

---

## 10. Session Security Testing

### Test 10.1: Session Hijacking Prevention

**Objective**: Verify session security.

**Procedure**:
1. Login and note session cookie
2. Logout
3. Try to use old session cookie → Should fail

**Expected Result**: ✅ Sessions are invalidated on logout

---

### Test 10.2: Session Timeout

**Objective**: Verify sessions expire after timeout.

**Procedure**:
1. Login
2. Wait for session lifetime (120 minutes default)
3. Try to access protected route → Should require re-login

**Expected Result**: ✅ Sessions expire after timeout

---

## 11. Security Headers Testing

### Test 11.1: Security Headers Presence

**Objective**: Verify security headers are present.

**Procedure**:
1. Make request to any page
2. Check response headers

**Expected Result**: ✅ All security headers present

**Test Command**:
```bash
curl -I http://localhost:8000
```

**Expected Headers**:
```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; ...
```

---

### Test 11.2: HSTS Header (HTTPS)

**Objective**: Verify HSTS header on HTTPS.

**Procedure**:
1. Access site via HTTPS
2. Check for `Strict-Transport-Security` header

**Expected Result**: ✅ HSTS header present on HTTPS

---

## Test Execution Checklist

### Pre-Testing Setup
- [ ] Application running in test environment
- [ ] Test database configured
- [ ] Test users created (admin, customer)
- [ ] API testing tool ready (Postman/curl)

### Authentication Tests
- [ ] Password hashing verified
- [ ] Email verification tested
- [ ] Login rate limiting tested
- [ ] 2FA tested (if applicable)

### Authorization Tests
- [ ] Admin access control tested
- [ ] Customer access control tested
- [ ] API authorization tested

### Input Validation Tests
- [ ] Product validation tested
- [ ] Order validation tested
- [ ] User registration tested

### Security Tests
- [ ] XSS prevention tested
- [ ] SQL injection prevention tested
- [ ] CSRF protection tested
- [ ] File upload security tested
- [ ] Security headers verified

### Documentation
- [ ] Test results documented
- [ ] Issues logged (if any)
- [ ] Recommendations provided

---

## Automated Testing

### PHPUnit Security Tests

Create test cases in `tests/Feature/SecurityTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_hashed()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertStringStartsWith('$2y$', $user->password);
    }

    public function test_sql_injection_prevented()
    {
        $response = $this->get('/products/1\' OR \'1\'=\'1');
        $response->assertStatus(404); // Should not find product
    }

    public function test_xss_prevented()
    {
        $admin = User::factory()->admin()->create();
        
        $this->actingAs($admin)->post('/admin/products', [
            'name' => '<script>alert("XSS")</script>',
            'price' => 10,
            'stock' => 100,
        ]);

        $response = $this->get('/products');
        $response->assertDontSee('<script>', false);
    }
}
```

---

## Security Testing Tools

### Recommended Tools

1. **OWASP ZAP** - Web application security scanner
2. **Burp Suite** - Web vulnerability scanner
3. **SQLMap** - SQL injection testing
4. **Postman** - API testing
5. **curl** - Command-line testing

### OWASP ZAP Scan

```bash
# Run OWASP ZAP scan
docker run -t owasp/zap2docker-stable zap-baseline.py \
  -t http://localhost:8000
```

---

## Reporting

### Test Report Template

**Test Date**: [Date]  
**Tester**: [Name]  
**Environment**: [Development/Staging/Production]

**Results Summary**:
- Total Tests: [Number]
- Passed: [Number]
- Failed: [Number]
- Issues Found: [List]

**Critical Issues**: [List critical security issues]

**Recommendations**: [List recommendations]

---

**Document Version**: 1.0  
**Last Updated**: 2026-01-29
