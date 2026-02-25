# LastGrade API Documentation

## Base URL
```
http://localhost/Projek%20Website/api/
```

## Endpoints

### 1. Authentication

#### POST /auth.php?action=login
Login user

**Request Body:**
```
json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```
json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "murid"
    },
    "token": "eyJ1c2VyX2lkIjoxLCJleHAiOjE3MD..."
  }
}
```

#### POST /auth.php?action=register
Register new user

**Request Body:**
```
json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "role": "murid"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "murid"
    },
    "token": "eyJ1c2VyX2lkIjoxLCJleHAiOjE3MD..."
  }
}
```

### 2. Grades

#### POST /grades.php
Calculate final grade

**Request Body:**
```
json
{
  "tugas": 85,
  "bobot_tugas": 30,
  "uts": 90,
  "bobot_uts": 30,
  "uas": 88,
  "bobot_uas": 40,
  "user_id": 1
}
```

**Response:**
```
json
{
  "success": true,
  "data": {
    "nilai_akhir": 87.8,
    "grade": "B",
    "grade_info": "Baik",
    "details": {
      "tugas": { "nilai": 85, "bobot": 30, "hasil": 25.5 },
      "uts": { "nilai": 90, "bobot": 30, "hasil": 27 },
      "uas": { "nilai": 88, "bobot": 40, "hasil": 35.2 }
    }
  },
  "saved": true
}
```

#### GET /grades.php?user_id=1
Get grade history

**Response:**
```
json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "tugas": 85,
      "bobot_tugas": 30,
      "uts": 90,
      "bobot_uts": 30,
      "uas": 88,
      "bobot_uas": 40,
      "nilai_akhir": 87.8,
      "grade": "B",
      "created_at": "2026-01-15 10:30:00"
    }
  ],
  "count": 1
}
```

## Error Responses

```
json
{
  "success": false,
  "message": "Error message here"
}
```

## HTTP Status Codes

- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 404: Not Found
- 405: Method Not Allowed
- 500: Internal Server Error
