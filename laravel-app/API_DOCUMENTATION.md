# API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

Tokens are obtained through registration or login endpoints.

---

## Public Endpoints

### 1. Register User
**POST** `/api/auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer"
}
```

**Response (201):**
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
    "token": "1|abc123..."
  }
}
```

---

### 2. Login
**POST** `/api/auth/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
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
    "token": "1|abc123..."
  }
}
```

---

### 3. List Products
**GET** `/api/products`

**Query Parameters:**
- `search` (optional) - Search term
- `in_stock` (optional) - Filter in-stock products (true/false)
- `per_page` (optional) - Items per page (default: 15)

**Example:**
```
GET /api/products?search=shirt&in_stock=true&per_page=20
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "sku": "SKU123",
      "description": "Product description",
      "price": "99.99",
      "stock": 10,
      "image": "uploads/image.jpg"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### 4. Get Product
**GET** `/api/products/{id}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product Name",
    "sku": "SKU123",
    "description": "Product description",
    "price": "99.99",
    "stock": 10,
    "image": "uploads/image.jpg"
  }
}
```

---

## Protected Endpoints (Require Authentication)

### 5. Get Authenticated User
**GET** `/api/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "customer",
    "orders": [...]
  }
}
```

---

### 6. Logout
**POST** `/api/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## Customer Endpoints (Require Customer Role)

### 7. Get Cart
**GET** `/api/cart`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Product Name",
        "price": "99.99",
        "qty": 2,
        "image": "uploads/image.jpg",
        "stock": 10,
        "subtotal": "199.98"
      }
    ],
    "total": 199.98
  }
}
```

---

### 8. Add to Cart
**POST** `/api/cart`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "product_id": 1,
  "qty": 2
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Item added to cart",
  "data": {
    "items": [...],
    "total": 199.98
  }
}
```

---

### 9. Update Cart Item
**PUT** `/api/cart/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "qty": 3
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Cart updated",
  "data": {
    "items": [...],
    "total": 299.97
  }
}
```

---

### 10. Remove from Cart
**DELETE** `/api/cart/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Item removed from cart",
  "data": {
    "items": [...],
    "total": 0
  }
}
```

---

### 11. List Orders
**GET** `/api/orders`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `per_page` (optional) - Items per page (default: 15)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "shipping_name": "John Doe",
      "shipping_phone": "1234567890",
      "shipping_address": "123 Main St",
      "total": "199.98",
      "status": "pending",
      "items": [...]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### 12. Create Order
**POST** `/api/orders`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "shipping_name": "John Doe",
  "shipping_phone": "1234567890",
  "shipping_address": "123 Main St",
  "items": [
    {
      "product_id": 1,
      "qty": 2
    },
    {
      "product_id": 2,
      "qty": 1
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "shipping_name": "John Doe",
    "shipping_phone": "1234567890",
    "shipping_address": "123 Main St",
    "total": "299.97",
    "status": "pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Product 1",
        "price": "99.99",
        "qty": 2
      }
    ]
  }
}
```

---

### 13. Get Order
**GET** `/api/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "shipping_name": "John Doe",
    "shipping_phone": "1234567890",
    "shipping_address": "123 Main St",
    "total": "199.98",
    "status": "pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Product Name",
        "price": "99.99",
        "qty": 2,
        "product": {...}
      }
    ]
  }
}
```

---

## Admin Endpoints (Require Admin Role)

### 14. Create Product
**POST** `/api/products`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "New Product",
  "sku": "SKU123",
  "description": "Product description",
  "price": 99.99,
  "stock": 10,
  "category_id": null,
  "image": "uploads/image.jpg"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "New Product",
    ...
  }
}
```

---

### 15. Update Product
**PUT/PATCH** `/api/products/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "Updated Product",
  "price": 89.99,
  "stock": 15
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Product",
    ...
  }
}
```

---

### 16. Delete Product
**DELETE** `/api/products/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Product not found"
}
```

### Bad Request (400)
```json
{
  "success": false,
  "message": "Insufficient stock (available: 5)"
}
```

---

## Response Format

All API responses follow this format:

**Success:**
```json
{
  "success": true,
  "message": "Optional message",
  "data": {...}
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message"
}
```

---

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Testing with cURL

### Register
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password123","password_confirmation":"password123","role":"customer"}'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'
```

### Get Products
```bash
curl -X GET http://localhost:8000/api/products
```

### Get Authenticated User
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {token}"
```

---

## Notes

- Tokens do not expire by default (configurable in `config/sanctum.php`)
- Cart uses session storage (same as web)
- All prices are in LKR (Sri Lankan Rupees)
- Stock is automatically decremented when orders are created
- Orders are created in database transactions for data integrity
