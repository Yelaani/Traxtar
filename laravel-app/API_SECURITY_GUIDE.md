# API Security Guide - Traxtar Application

## Overview

This document provides detailed security guidelines and best practices for using the Traxtar API. It covers authentication, authorization, data protection, and security considerations for API consumers.

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Authorization](#2-authorization)
3. [Request Security](#3-request-security)
4. [Response Security](#4-response-security)
5. [Error Handling](#5-error-handling)
6. [Rate Limiting](#6-rate-limiting)
7. [Best Practices](#7-best-practices)
8. [Security Checklist](#8-security-checklist)

---

## 1. Authentication

### 1.1 Laravel Sanctum

The Traxtar API uses **Laravel Sanctum** for token-based authentication.

#### Token Generation

**Registration**:
```bash
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "role": "customer"
}
```

**Response**:
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "customer"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
  }
}
```

**Login**:
```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePassword123!"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "customer"
    },
    "token": "2|abcdefghijklmnopqrstuvwxyz1234567890"
  }
}
```

### 1.2 Token Usage

Include the token in the `Authorization` header:

```bash
GET /api/user
Authorization: Bearer 2|abcdefghijklmnopqrstuvwxyz1234567890
```

### 1.3 Token Security

#### ✅ DO:
- Store tokens securely (encrypted storage)
- Use HTTPS for all API requests
- Include token in `Authorization` header
- Revoke tokens on logout
- Regenerate tokens periodically

#### ❌ DON'T:
- Store tokens in localStorage (XSS risk)
- Include tokens in URLs
- Share tokens between users
- Commit tokens to version control
- Use tokens over HTTP (use HTTPS)

### 1.4 Token Revocation

**Logout**:
```bash
POST /api/auth/logout
Authorization: Bearer 2|abcdefghijklmnopqrstuvwxyz1234567890
```

**Response**:
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

After logout, the token is invalidated and cannot be used.

---

## 2. Authorization

### 2.1 Role-Based Access

The API enforces role-based access control:

- **Admin**: Full access to all endpoints
- **Customer**: Limited access to customer endpoints

### 2.2 Protected Endpoints

#### Admin-Only Endpoints

Require admin role and valid token:

- `POST /api/products` - Create product
- `PUT /api/products/{id}` - Update product
- `PATCH /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

**Example**:
```bash
POST /api/products
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "New Product",
  "price": 29.99,
  "stock": 100
}
```

**Unauthorized Response** (403):
```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

#### Customer-Only Endpoints

Require customer role and valid token:

- `GET /api/cart` - Get cart
- `POST /api/cart` - Add to cart
- `PUT /api/cart/{id}` - Update cart item
- `DELETE /api/cart/{id}` - Remove from cart
- `GET /api/orders` - Get orders
- `POST /api/orders` - Create order
- `GET /api/orders/{id}` - Get order details

**Example**:
```bash
POST /api/cart
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "product_id": 1,
  "qty": 2
}
```

**Unauthorized Response** (403):
```json
{
  "success": false,
  "message": "Unauthorized. Customer access required."
}
```

### 2.3 Public Endpoints

No authentication required:

- `POST /api/auth/register` - Register user
- `POST /api/auth/login` - Login
- `GET /api/products` - List products
- `GET /api/products/{id}` - Get product details

---

## 3. Request Security

### 3.1 HTTPS

**Always use HTTPS** for API requests in production:

```bash
# ✅ Good
https://api.traxtar.com/api/products

# ❌ Bad
http://api.traxtar.com/api/products
```

### 3.2 Content-Type

Always include `Content-Type: application/json` for POST/PUT requests:

```bash
POST /api/products
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Product",
  "price": 10.00
}
```

### 3.3 Input Validation

The API validates all input. Invalid input returns validation errors:

**Request**:
```bash
POST /api/products
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "name": "",
  "price": -10
}
```

**Response** (422):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be at least 0."]
  }
}
```

### 3.4 Request Size Limits

- **Maximum request size**: Configured by server
- **File uploads**: 2MB maximum for images
- **Array limits**: Reasonable limits enforced

---

## 4. Response Security

### 4.1 Sensitive Data

Sensitive data is automatically hidden in responses:

**User Response** (`/api/user`):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "customer"
    // password, tokens, etc. are hidden
  }
}
```

### 4.2 Error Messages

**Production**:
- Generic error messages
- No stack traces
- No sensitive information

**Development**:
- Detailed error messages
- Stack traces (if `APP_DEBUG=true`)

### 4.3 Response Format

All API responses follow a consistent format:

**Success**:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error**:
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... } // Optional, for validation errors
}
```

---

## 5. Error Handling

### 5.1 HTTP Status Codes

| Status Code | Meaning |
|------------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (invalid/missing token) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests (rate limited) |
| 500 | Server Error |

### 5.2 Error Response Examples

**401 Unauthorized** (Invalid Token):
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

**403 Forbidden** (Insufficient Permissions):
```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

**404 Not Found**:
```json
{
  "success": false,
  "message": "Product not found"
}
```

**422 Validation Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## 6. Rate Limiting

### 6.1 Current Implementation

- **Login**: 5 attempts per minute per email/IP
- **2FA**: 5 attempts per minute per session
- **API**: Can be configured (recommended for production)

### 6.2 Rate Limit Headers

When rate limited, response includes:

```
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
Retry-After: 60
```

### 6.3 Best Practices

- Implement exponential backoff
- Cache responses when possible
- Batch requests when appropriate
- Respect rate limit headers

---

## 7. Best Practices

### 7.1 Client Security

#### ✅ DO:
- Use HTTPS for all requests
- Store tokens securely (encrypted)
- Validate server certificates
- Implement token refresh
- Handle errors gracefully
- Log security events

#### ❌ DON'T:
- Store tokens in localStorage
- Include tokens in URLs
- Share tokens between users
- Ignore SSL certificate errors
- Log sensitive data
- Expose tokens in error messages

### 7.2 Request Security

#### ✅ DO:
- Validate input before sending
- Use parameterized queries (if building queries)
- Sanitize user input
- Check response integrity
- Verify SSL certificates

#### ❌ DON'T:
- Send sensitive data in URLs
- Trust client-side validation only
- Ignore validation errors
- Process untrusted data
- Expose internal errors

### 7.3 Token Management

#### ✅ DO:
- Store tokens securely
- Refresh tokens periodically
- Revoke tokens on logout
- Handle token expiration
- Use different tokens per environment

#### ❌ DON'T:
- Store tokens in plain text
- Reuse tokens across environments
- Share tokens between users
- Ignore token expiration
- Use tokens indefinitely

---

## 8. Security Checklist

### For API Consumers

- [ ] Use HTTPS for all API requests
- [ ] Store tokens securely (encrypted)
- [ ] Include tokens in `Authorization` header
- [ ] Validate all input before sending
- [ ] Handle errors gracefully
- [ ] Implement rate limiting on client side
- [ ] Log security events
- [ ] Revoke tokens on logout
- [ ] Verify SSL certificates
- [ ] Keep API client updated

### For API Developers

- [ ] All endpoints require authentication (except public)
- [ ] Role-based access enforced
- [ ] Input validation on all endpoints
- [ ] Sensitive data hidden in responses
- [ ] Error messages generic in production
- [ ] Rate limiting implemented
- [ ] Security headers present
- [ ] HTTPS enforced in production
- [ ] Tokens can be revoked
- [ ] Security testing performed

---

## Security Testing

### Test Authentication

```bash
# Test without token (should fail)
curl http://localhost:8000/api/user

# Test with invalid token (should fail)
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer invalid_token"

# Test with valid token (should succeed)
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer {valid_token}"
```

### Test Authorization

```bash
# Test admin endpoint as customer (should fail)
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","price":10}'

# Test admin endpoint as admin (should succeed)
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","price":10}'
```

### Test Input Validation

```bash
# Test invalid input (should fail)
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"name":"","price":-10}'
```

---

## Incident Response

### If Token Compromised

1. **Immediately revoke token**:
   ```bash
   POST /api/auth/logout
   Authorization: Bearer {compromised_token}
   ```

2. **Generate new token**:
   ```bash
   POST /api/auth/login
   ```

3. **Review access logs** for suspicious activity

4. **Notify users** if necessary

### If Unauthorized Access Detected

1. **Review access logs**
2. **Revoke affected tokens**
3. **Investigate source**
4. **Update security measures**
5. **Document incident**

---

## References

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [OWASP API Security](https://owasp.org/www-project-api-security/)
- [REST API Security Best Practices](https://restfulapi.net/security-essentials/)

---

**Document Version**: 1.0  
**Last Updated**: 2026-01-29
