# API Testing Guide

This guide provides quick instructions for testing the SolidInvoice API endpoints.

## API Access

- **Base URL**: `http://localhost:3000/api`
- **Documentation**: `http://localhost:3000/api/docs` (Swagger UI)

## Authentication

Use the following API token in the `X-API-TOKEN` header:

```
569a168393da35304d9f2a2e0e3fa4305db7efcd9e8e3d36d6b9ca1a9d8c6269
```

## Quick Test Examples

### List Jobs
```bash
curl -H "X-API-TOKEN: 569a168393da35304d9f2a2e0e3fa4305db7efcd9e8e3d36d6b9ca1a9d8c6269" \
     -H "Content-Type: application/json" \
     http://localhost:3000/api/jobs
```

### Create a Job
```bash
curl -X POST \
  -H "X-API-TOKEN: 569a168393da35304d9f2a2e0e3fa4305db7efcd9e8e3d36d6b9ca1a9d8c6269" \
  -H "Content-Type: application/json" \
  -d '{
    "client": "/api/clients/01JYWV16DA3JNQZ4S5KYXZ8X83",
    "status": "pending",
    "description": "Tree removal service",
    "scheduledDate": "2025-07-15T10:00:00+00:00",
    "jobId": "JOB-001"
  }' \
  http://localhost:3000/api/jobs
```

### Update Job Status
```bash
curl -X PATCH \
  -H "X-API-TOKEN: 569a168393da35304d9f2a2e0e3fa4305db7efcd9e8e3d36d6b9ca1a9d8c6269" \
  -H "Content-Type: application/merge-patch+json" \
  -d '{"status": "in_progress"}' \
  http://localhost:3000/api/jobs/{job_id}
```

## Available Endpoints

### Jobs
- `GET /api/jobs` - List all jobs
- `GET /api/jobs/{id}` - Get specific job
- `POST /api/jobs` - Create new job
- `PATCH /api/jobs/{id}` - Update job
- `DELETE /api/jobs/{id}` - Delete job

### Clients
- `GET /api/clients` - List all clients
- `GET /api/clients/{id}` - Get specific client
- `POST /api/clients` - Create new client
- `PATCH /api/clients/{id}` - Update client
- `DELETE /api/clients/{id}` - Delete client

### Invoices
- `GET /api/invoices` - List all invoices
- `GET /api/invoices/{id}` - Get specific invoice
- `POST /api/invoices` - Create new invoice
- `PATCH /api/invoices/{id}` - Update invoice
- `DELETE /api/invoices/{id}` - Delete invoice

### Quotes
- `GET /api/quotes` - List all quotes
- `GET /api/quotes/{id}` - Get specific quote
- `POST /api/quotes` - Create new quote
- `PATCH /api/quotes/{id}` - Update quote
- `DELETE /api/quotes/{id}` - Delete quote

## Tips

1. Use `jq` for pretty JSON output:
   ```bash
   curl ... | jq
   ```

2. To get the first client ID for testing:
   ```bash
   curl -H "X-API-TOKEN: 569a168393da35304d9f2a2e0e3fa4305db7efcd9e8e3d36d6b9ca1a9d8c6269" \
        http://localhost:3000/api/clients | jq '.member[0]."@id"'
   ```

3. The API uses JSON-LD format with `@id` references for relationships

4. For PATCH requests, use `Content-Type: application/merge-patch+json`

5. All entity IDs are ULIDs (Universally Unique Lexicographically Sortable Identifiers)

## Web UI Login

For testing via the web interface:
- **URL**: `http://localhost:3000`
- **Email**: `jeremiah@symbiotek.com.au`
- **Password**: `Thr3ftygui!`