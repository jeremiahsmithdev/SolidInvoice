# Build and Deployment

This document describes the processes for building frontend assets, Docker images, and deployment strategies for SolidInvoice.

## Frontend Asset Compilation

Frontend assets (TypeScript, SCSS) are compiled using Webpack Encore, managed via Bun.

### Development Build

For development with source maps and debugging:

```bash
bun run dev
```

For development with automatic recompilation on file changes:

```bash
bun run watch
```

### Production Build

For production, a minified and optimized build is generated:

```bash
bun run build
```

This command generates compiled assets into the `public/static/` directory as configured in `webpack.config.js`.

### Asset Structure

Compiled assets include:
- **core.js**: Main application JavaScript bundle
- **app.css**: Main application styles
- **email.css**: Email template styles  
- **pdf.css**: PDF generation styles

## Docker Image Building

SolidInvoice uses Docker for containerization with FrankenPHP as the application server.

### Docker Hub Images

SolidInvoice provides official Docker images available at [Docker Hub](https://hub.docker.com/r/solidinvoice/solidinvoice/):

```bash
# Pull the latest image
docker pull solidinvoice/solidinvoice:latest

# Run with Docker Compose
docker-compose up -d
```

### Current Docker Setup

The current `docker-compose.yml` uses pre-built images:

```yaml
services:
  db:
    image: "mysql:8.0"
  app:
    image: "solidinvoice/solidinvoice:latest"
```

### Building Custom Images

For custom builds, use the `Dockerfile.linux-static-build` in the `docker/` directory:

```bash
# Build production image
docker build -f docker/Dockerfile.linux-static-build -t solidinvoice:custom .

# Build with docker-compose for development
docker-compose build
```

### Build Process

The Docker build process includes:
1. **Base Image**: FrankenPHP with PHP 8.3+
2. **PHP Dependencies**: Composer install with optimized autoloader
3. **Frontend Assets**: Bun install and production build
4. **Application Code**: Copy source code and configuration
5. **Permissions**: Set appropriate file permissions
6. **Optimization**: OPcache and other PHP optimizations

## Application Serving with FrankenPHP

SolidInvoice uses FrankenPHP as its application server. FrankenPHP is a modern PHP application server built on top of Caddy, providing a performant and robust way to serve PHP applications.

### How it Works

*   **Caddy Integration:** FrankenPHP embeds the Caddy web server, handling HTTP requests directly.
*   **PHP FPM Replacement:** It replaces the traditional PHP-FPM setup, serving PHP requests natively.
*   **Static File Serving:** Caddy also serves static assets (like compiled frontend files from `public/build/`) efficiently.
*   **Configuration:** The `Caddyfile` at the project root configures how FrankenPHP/Caddy handles requests, including routing, static file serving, and PHP execution.

### Development Environment

In the development setup (`docker-compose.yml`), FrankenPHP runs as a service, exposing the application on a specified port (e.g., 80 or 8000).

### Production Environment

For production, the Docker image contains FrankenPHP, which starts automatically when the container runs, serving the application.

## Deployment Strategies

### Docker Compose Deployment

For simple deployments, use the provided `docker-compose.yml`:

```bash
# Production deployment
docker-compose up -d

# With custom environment
docker-compose --env-file .env.prod up -d
```

### Environment Configuration

Create environment-specific configuration files:

**`.env.prod`** (Production):
```bash
SOLIDINVOICE_ENV=prod
SOLIDINVOICE_DEBUG=0
DATABASE_URL="mysql://user:pass@db:3306/solidinvoice"
MAILER_DSN="brevo+api://your-api-key@default"
```

**`.env.staging`** (Staging):
```bash
SOLIDINVOICE_ENV=prod
SOLIDINVOICE_DEBUG=1
DATABASE_URL="mysql://user:pass@staging-db:3306/solidinvoice_staging"
```

### Database Migrations in Production

Always run migrations during deployment:

```bash
# Run migrations without interaction
docker-compose exec app bin/console doctrine:migrations:migrate --no-interaction

# Check migration status
docker-compose exec app bin/console doctrine:migrations:status
```

### Cache Management

Clear and warm up caches for production:

```bash
# Clear cache
docker-compose exec app bin/console cache:clear --env=prod

# Warm up cache
docker-compose exec app bin/console cache:warmup --env=prod
```

### Health Checks

Implement health checks for monitoring:

```bash
# Check application status
curl http://localhost:8765/health

# Check database connectivity
docker-compose exec app bin/console doctrine:query:sql "SELECT 1"
```

### Backup Strategy

Regular backup procedures:

```bash
# Database backup
docker-compose exec db mysqldump -u root solidinvoice > backup_$(date +%Y%m%d).sql

# Application data backup
docker-compose exec app tar -czf /tmp/app_data.tar.gz /etc/solidinvoice
```

### Scaling Considerations

For high-traffic deployments:

1. **Load Balancing**: Use multiple app containers behind a load balancer
2. **Database Optimization**: Use read replicas and connection pooling
3. **Caching**: Implement Redis for session and application caching
4. **CDN**: Serve static assets via CDN
5. **Monitoring**: Use APM tools for performance monitoring

### Security Hardening

Production security checklist:

1. **HTTPS**: Always use SSL/TLS certificates
2. **Environment Variables**: Never commit secrets to version control
3. **Database Security**: Use strong passwords and limit access
4. **File Permissions**: Ensure proper file and directory permissions
5. **Updates**: Keep dependencies and base images updated
