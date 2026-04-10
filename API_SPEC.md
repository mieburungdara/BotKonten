# API Specification for BotKonten

## Authentication Endpoints

### POST /api/register
Creates a new anonymous user with a unique anonymous_id.

**Payload:**
```json
{
  // No payload required - anonymous_id generated automatically
}
```

**Response (201 Created):**
```json
{
  "anonymous_id": "string (UUID)",
  "token": "string (JWT token for future requests)",
  "created_at": "string (ISO 8601 datetime)"
}
```

**Error Codes:**
- 400 Bad Request: Invalid request data
  ```json
  {
    "error": "BAD_REQUEST",
    "message": "Missing required field: draft_id",
    "details": {
      "field": "draft_id",
      "issue": "Field is required but was not provided"
    }
  }
  ```
- 500 Internal Server Error: Server error during registration

### POST /api/login
Authenticates an existing user with their anonymous_id.

**Payload:**
```json
{
  "anonymous_id": "string (required, UUID of the user)"
}
```

**Response (200 OK):**
```json
{
  "token": "string (JWT token for session)",
  "user": {
    "anonymous_id": "string",
    "created_at": "string",
    "updated_at": "string"
  }
}
```

**Error Codes:**
- 400 Bad Request: Missing or invalid anonymous_id
  ```json
  {
    "error": "BAD_REQUEST",
    "message": "Invalid format for field: anonymous_id",
    "details": {
      "field": "anonymous_id",
      "issue": "Must be a valid UUID"
    }
  }
  ```
- 404 Not Found: User not found
  ```json
  {
    "error": "NOT_FOUND",
    "message": "User not found"
  }
  ```
- 500 Internal Server Error: Server error

### GET /api/user/profile
Retrieves the current user's profile information.

**Headers:**
- Authorization: Bearer {token}

**Response (200 OK):**
```json
{
  "anonymous_id": "string",
  "created_at": "string",
  "updated_at": "string",
  "total_media": "integer",
  "total_drafts": "integer"
}
```

**Error Codes:**
- 401 Unauthorized: Invalid or missing token
  ```json
  {
    "error": "UNAUTHORIZED",
    "message": "Authentication token is required",
    "details": {
      "header": "Authorization",
      "expected": "Bearer <token>"
    }
  }
  ```
- 404 Not Found: User not found
- 500 Internal Server Error: Server error

## Media Endpoints

### GET /api/media
Retrieves a list of published media with optional filtering and pagination.

**Query Parameters:**
- search: string (optional, search by title/description)
- category: string (optional, filter by category)
- bot_id: integer (optional, filter by bot)
- page: integer (optional, default 1)
- per_page: integer (optional, default 20, max 100)

**Headers:**
- Authorization: Bearer {token}

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": "integer",
      "title": "string",
      "description": "string",
      "price": "number (decimal)",
      "file_url": "string",
      "file_type": "string (photo/video/audio/document)",
      "bot_id": "integer",
      "bot_name": "string",
      "anonymous_user_id": "string",
      "rating": "number (average rating, 0-5)",
      "review_count": "integer",
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "pagination": {
    "current_page": "integer",
    "per_page": "integer",
    "total": "integer",
    "last_page": "integer"
  }
}
```

**Error Codes:**
- 401 Unauthorized: Invalid token
- 422 Unprocessable Entity: Invalid query parameters
- 500 Internal Server Error: Server error

### GET /api/media/{id}
Retrieves detailed information about a specific published media.

**Path Parameters:**
- id: integer (media ID)

**Headers:**
- Authorization: Bearer {token}

**Response (200 OK):**
```json
{
  "id": "integer",
  "title": "string",
  "description": "string",
  "price": "number",
  "file_url": "string",
  "file_type": "string",
  "bot_id": "integer",
  "bot_name": "string",
  "anonymous_user_id": "string",
  "rating": "number",
  "reviews": [
    {
      "user_anonymous_id": "string",
      "rating": "integer (1-5)",
      "comment": "string",
      "created_at": "string"
    }
  ],
  "album_id": "integer (optional, if part of album)",
  "created_at": "string",
  "updated_at": "string"
}
```

**Error Codes:**
- 401 Unauthorized: Invalid token
- 404 Not Found: Media not found
- 500 Internal Server Error: Server error

### POST /api/media
Publishes a draft as a published media item.

**Payload:**
```json
{
  "draft_id": "integer (required)",
  "title": "string (optional, overrides draft title)",
  "description": "string (optional, overrides draft description)",
  "price": "number (required, price in currency)"
}
```

**Headers:**
- Authorization: Bearer {token}
- Content-Type: application/json

**Response (201 Created):**
```json
{
  "id": "integer",
  "title": "string",
  "description": "string",
  "price": "number",
  "file_url": "string",
  "file_type": "string",
  "bot_id": "integer",
  "anonymous_user_id": "string",
  "created_at": "string",
  "updated_at": "string"
}
```

**Error Codes:**
- 400 Bad Request: Missing required fields or invalid data
- 401 Unauthorized: Invalid token
- 403 Forbidden: Draft does not belong to user
- 404 Not Found: Draft not found
- 422 Unprocessable Entity: Validation errors
- 500 Internal Server Error: Server error

## Draft Endpoints

### GET /api/drafts
Retrieves a list of user's draft media.

**Query Parameters:**
- page: integer (optional, default 1)
- per_page: integer (optional, default 20, max 100)

**Headers:**
- Authorization: Bearer {token}

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": "integer",
      "file_url": "string",
      "file_type": "string",
      "bot_id": "integer",
      "bot_name": "string",
      "anonymous_user_id": "string",
      "created_at": "string"
    }
  ],
  "pagination": {
    "current_page": "integer",
    "per_page": "integer",
    "total": "integer",
    "last_page": "integer"
  }
}
```

**Error Codes:**
- 401 Unauthorized: Invalid token
- 422 Unprocessable Entity: Invalid query parameters
- 500 Internal Server Error: Server error

### POST /api/drafts/publish
Publishes a draft as a published media item with default settings.

**Payload:**
```json
{
  "draft_id": "integer (required)",
  "price": "number (required, price in currency)"
}
```

**Headers:**
- Authorization: Bearer {token}
- Content-Type: application/json

**Response (201 Created):**
```json
{
  "id": "integer",
  "title": "string (from draft or auto-generated)",
  "description": "string (from draft or empty)",
  "price": "number",
  "file_url": "string",
  "file_type": "string",
  "bot_id": "integer",
  "anonymous_user_id": "string",
  "created_at": "string"
}
```

**Error Codes:**
- 400 Bad Request: Missing required fields
- 401 Unauthorized: Invalid token
- 403 Forbidden: Draft does not belong to user
- 404 Not Found: Draft not found
- 422 Unprocessable Entity: Validation errors
- 500 Internal Server Error: Server error

### DELETE /api/drafts/{id}
Deletes a draft media item.

**Path Parameters:**
- id: integer (draft ID)

**Headers:**
- Authorization: Bearer {token}

**Response (204 No Content):**
No response body

**Error Codes:**
- 401 Unauthorized: Invalid token
- 403 Forbidden: Draft does not belong to user
- 404 Not Found: Draft not found
- 500 Internal Server Error: Server error

## Album Endpoints

### POST /api/albums
Creates a new album from published media items (max 10 media per album).

**Payload:**
```json
{
  "title": "string (required)",
  "description": "string (optional)",
  "media_ids": "array of integers (required, 1-10 media IDs)",
  "price": "number (required, total price for album)"
}
```

**Headers:**
- Authorization: Bearer {token}
- Content-Type: application/json

**Response (201 Created):**
```json
{
  "id": "integer",
  "title": "string",
  "description": "string",
  "price": "number",
  "media_count": "integer",
  "media": [
    {
      "id": "integer",
      "file_url": "string",
      "file_type": "string"
    }
  ],
  "bot_id": "integer",
  "anonymous_user_id": "string",
  "created_at": "string"
}
```

**Error Codes:**
- 400 Bad Request: Missing required fields or invalid media IDs
- 401 Unauthorized: Invalid token
- 403 Forbidden: Media does not belong to user
- 404 Not Found: One or more media not found
- 422 Unprocessable Entity: Too many media (max 10) or validation errors
- 500 Internal Server Error: Server error

### GET /api/albums/{id}
Retrieves detailed information about a specific album.

**Path Parameters:**
- id: integer (album ID)

**Headers:**
- Authorization: Bearer {token}

**Response (200 OK):**
```json
{
  "id": "integer",
  "title": "string",
  "description": "string",
  "price": "number",
  "media_count": "integer",
  "media": [
    {
      "id": "integer",
      "title": "string",
      "file_url": "string",
      "file_type": "string"
    }
  ],
  "bot_id": "integer",
  "bot_name": "string",
  "anonymous_user_id": "string",
  "rating": "number",
  "reviews": [
    {
      "user_anonymous_id": "string",
      "rating": "integer",
      "comment": "string",
      "created_at": "string"
    }
  ],
  "created_at": "string",
  "updated_at": "string"
}
```

**Error Codes:**
- 401 Unauthorized: Invalid token
- 404 Not Found: Album not found
- 500 Internal Server Error: Server error

## Bot Management Endpoints

### GET /api/bots
Retrieves a list of available Telegram bots.

**Headers:**
- Authorization: Bearer {token}

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": "integer",
      "name": "string",
      "username": "string (@botusername)",
      "description": "string",
      "is_active": "boolean",
      "created_at": "string"
    }
  ]
}
```

**Error Codes:**
- 401 Unauthorized: Invalid token
- 500 Internal Server Error: Server error

### POST /api/user/select-bot
Selects a bot for the current user (sets active bot for uploads).

**Payload:**
```json
{
  "bot_id": "integer (required)"
}
```

**Headers:**
- Authorization: Bearer {token}
- Content-Type: application/json

**Response (200 OK):**
```json
{
  "selected_bot": {
    "id": "integer",
    "name": "string",
    "username": "string"
  },
  "message": "Bot selected successfully"
}
```

**Error Codes:**
- 400 Bad Request: Missing bot_id
- 401 Unauthorized: Invalid token
- 404 Not Found: Bot not found
- 422 Unprocessable Entity: Invalid bot_id
- 500 Internal Server Error: Server error

## Webhook Endpoints

### POST /webhook/bot/{bot_id}
Handles incoming updates from Telegram Bot API for a specific bot.

**Path Parameters:**
- bot_id: integer (ID of the bot)

**Payload (from Telegram):**
```json
{
  "update_id": "integer",
  "message": {
    "message_id": "integer",
    "from": {
      "id": "integer (Telegram user ID)",
      "is_bot": "boolean",
      "first_name": "string",
      "username": "string"
    },
    "chat": {
      "id": "integer",
      "type": "string"
    },
    "date": "integer",
    "text": "string (optional, for /start)",
    "photo": "array (optional, for media upload)",
    "video": "object (optional)",
    "audio": "object (optional)",
    "document": "object (optional)"
  }
}
```

**Response (200 OK):**
```json
{
  "ok": "boolean (true)",
  "description": "string (success message)"
}
```

**Error Codes:**
- 400 Bad Request: Invalid webhook payload
- 404 Not Found: Bot not found
- 500 Internal Server Error: Server error

**Notes:**
- For /start command: Responds with list of available bots
- For media uploads: Forwards to backup channel, creates draft, sends inline button to webapp
- Requires proper Telegram bot token verification (not shown in payload)

## Common Error Codes

All endpoints may return the following standard HTTP error codes:

- **400 Bad Request**: The request payload is malformed or missing required fields
- **401 Unauthorized**: Missing or invalid authentication token
- **403 Forbidden**: User does not have permission to access the resource
- **404 Not Found**: The requested resource does not exist
- **422 Unprocessable Entity**: Validation failed for the provided data
- **500 Internal Server Error**: Unexpected server error occurred

Error responses follow this format:
```json
{
  "error": "string (error code)",
  "message": "string (human-readable description)",
  "details": "object (optional, additional error details)"
}
```