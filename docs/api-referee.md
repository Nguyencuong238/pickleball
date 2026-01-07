# Referee API Documentation

**Last Updated**: 2025-12-16
**Version**: 1.0

## Overview

Referee API provides endpoints for referee operations, match officiating, and public referee directory. All endpoints return JSON responses.

## Response Format

All endpoints return responses in this format:

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

Error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Authentication

### Public Endpoints
No authentication required:
- `GET /api/referees`
- `GET /api/referees/{referee}`

### Protected Endpoints
Require authentication via Laravel Sanctum (auth:api middleware) and referee role:
- `GET /api/referee/dashboard`
- `GET /api/referee/matches`
- `GET /api/referee/matches/{match}`
- `POST /api/referee/matches/{match}/start`
- `PUT /api/referee/matches/{match}/score`

Include authentication token in request header:
```
Authorization: Bearer {token}
```

## Public Endpoints

### List Active Referees

**Endpoint**: `GET /api/referees`

**Description**: Retrieve list of active referees with optional search and filtering.

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | Search by name |
| status | string | No | Filter by status (active/inactive) |
| per_page | integer | No | Results per page (default: 15) |

**Example Request**:
```
GET /api/referees?search=john&status=active&per_page=10
```

**Response**:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "John Smith",
        "email": "john@example.com",
        "referee_bio": "Experienced referee with 5+ years",
        "referee_status": "active",
        "referee_rating": 4.5,
        "matches_officiated": 45
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

### Get Referee Profile

**Endpoint**: `GET /api/referees/{referee}`

**Description**: Retrieve detailed referee profile with statistics.

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| referee | integer | Yes | Referee ID |

**Example Request**:
```
GET /api/referees/1
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Smith",
    "email": "john@example.com",
    "referee_bio": "Experienced referee with 5+ years",
    "referee_status": "active",
    "referee_rating": 4.5,
    "matches_officiated": 45,
    "tournaments": [
      {
        "id": 1,
        "name": "Summer Championship",
        "status": "active"
      }
    ],
    "stats": {
      "total_matches": 45,
      "completed_matches": 42,
      "upcoming_matches": 3
    }
  }
}
```

## Protected Endpoints (Referee Role Required)

### Get Referee Dashboard

**Endpoint**: `GET /api/referee/dashboard`

**Description**: Retrieve referee dashboard with statistics and upcoming matches.

**Authentication**: Required (referee role)

**Response**:
```json
{
  "success": true,
  "data": {
    "stats": {
      "total_matches": 45,
      "completed_matches": 42,
      "upcoming_matches": 3,
      "tournaments": 5
    },
    "upcoming_matches": [
      {
        "id": 1,
        "tournament": {
          "id": 1,
          "name": "Summer Championship"
        },
        "team_1_name": "Team Alpha",
        "team_2_name": "Team Beta",
        "scheduled_date": "2025-12-20",
        "scheduled_time": "14:00:00",
        "status": "scheduled"
      }
    ]
  }
}
```

### List Assigned Matches

**Endpoint**: `GET /api/referee/matches`

**Description**: Retrieve list of matches assigned to the authenticated referee.

**Authentication**: Required (referee role)

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tournament_id | integer | No | Filter by tournament |
| status | string | No | Filter by status (scheduled/in_progress/completed) |
| date_from | date | No | Filter from date (Y-m-d format) |
| date_to | date | No | Filter to date (Y-m-d format) |
| per_page | integer | No | Results per page (default: 15) |

**Example Request**:
```
GET /api/referee/matches?status=scheduled&date_from=2025-12-20&per_page=10
```

**Response**:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "tournament": {
          "id": 1,
          "name": "Summer Championship"
        },
        "team_1_name": "Team Alpha",
        "team_2_name": "Team Beta",
        "scheduled_date": "2025-12-20",
        "scheduled_time": "14:00:00",
        "status": "scheduled",
        "referee_id": 1,
        "referee_name": "John Smith"
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

### Get Match Detail

**Endpoint**: `GET /api/referee/matches/{match}`

**Description**: Retrieve detailed information about a specific match.

**Authentication**: Required (referee role)

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| match | integer | Yes | Match ID |

**Example Request**:
```
GET /api/referee/matches/1
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "tournament": {
      "id": 1,
      "name": "Summer Championship"
    },
    "team_1_name": "Team Alpha",
    "team_2_name": "Team Beta",
    "scheduled_date": "2025-12-20",
    "scheduled_time": "14:00:00",
    "status": "scheduled",
    "referee_id": 1,
    "referee_name": "John Smith",
    "set_scores": null,
    "final_score": null,
    "winner_id": null,
    "actual_start_time": null,
    "actual_end_time": null
  }
}
```

### Start Match

**Endpoint**: `POST /api/referee/matches/{match}/start`

**Description**: Start a scheduled match, changing status to in_progress.

**Authentication**: Required (referee role)

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| match | integer | Yes | Match ID |

**Validation**:
- Match must be assigned to authenticated referee
- Match status must be 'scheduled'

**Example Request**:
```
POST /api/referee/matches/1/start
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "in_progress",
    "actual_start_time": "2025-12-20 14:05:23"
  },
  "message": "Match started successfully"
}
```

**Error Responses**:
```json
{
  "success": false,
  "message": "Match not assigned to you"
}
```

```json
{
  "success": false,
  "message": "Match already started or completed"
}
```

### Update Match Scores

**Endpoint**: `PUT /api/referee/matches/{match}/score`

**Description**: Update match scores and determine winner.

**Authentication**: Required (referee role)

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| match | integer | Yes | Match ID |
| set_scores | array | Yes | Array of set scores |

**Request Body**:
```json
{
  "set_scores": [
    {
      "team_1_score": 11,
      "team_2_score": 9
    },
    {
      "team_1_score": 11,
      "team_2_score": 7
    }
  ]
}
```

**Validation**:
- Match must be assigned to authenticated referee
- Match status must be 'in_progress'
- set_scores must be array
- Each set must have team_1_score and team_2_score

**Example Request**:
```
PUT /api/referee/matches/1/score
Content-Type: application/json

{
  "set_scores": [
    {"team_1_score": 11, "team_2_score": 9},
    {"team_1_score": 11, "team_2_score": 7}
  ]
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "set_scores": [
      {"team_1_score": 11, "team_2_score": 9},
      {"team_1_score": 11, "team_2_score": 7}
    ],
    "final_score": "11-9, 11-7",
    "winner_id": 1,
    "status": "completed",
    "actual_end_time": "2025-12-20 15:30:45"
  },
  "message": "Match score updated successfully"
}
```

**Error Responses**:
```json
{
  "success": false,
  "message": "Match not assigned to you"
}
```

```json
{
  "success": false,
  "message": "Match not in progress"
}
```

```json
{
  "success": false,
  "message": "Invalid set scores format",
  "errors": {
    "set_scores": ["The set scores field is required."]
  }
}
```

## Winner Determination Logic

Winner is determined by counting set wins:
- Count sets won by each team
- Team with most set wins is declared winner
- Match status changes to 'completed'
- actual_end_time is recorded

Example:
```json
{
  "set_scores": [
    {"team_1_score": 11, "team_2_score": 9},  // Team 1 wins
    {"team_1_score": 8, "team_2_score": 11},  // Team 2 wins
    {"team_1_score": 11, "team_2_score": 5}   // Team 1 wins
  ]
}
```
Result: Team 1 wins (2 sets vs 1 set)

## Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request (validation error) |
| 401 | Unauthorized (not authenticated) |
| 403 | Forbidden (not referee or wrong match) |
| 404 | Not Found (match not found) |
| 422 | Unprocessable Entity (validation failed) |
| 500 | Server Error |

## Rate Limiting

API endpoints are subject to rate limiting:
- Public endpoints: 60 requests per minute
- Protected endpoints: 60 requests per minute per user

## Related Documentation

- [System Architecture](./system-architecture.md)
- [Codebase Summary](./codebase-summary.md)
- [Code Standards](./code-standards.md)
