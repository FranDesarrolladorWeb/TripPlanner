# Railway Deployment Guide

## Required Environment Variables

Set these in your Railway project settings:

### Application Settings
```
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=<generate-random-32-char-string>
```

Generate APP_SECRET with:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### Database Settings
Railway will automatically provide `DATABASE_URL` if you add a MySQL database service.

If setting manually:
```
DATABASE_URL=mysql://user:password@host:port/database?serverVersion=8.0&charset=utf8mb4
```

### Optional Settings
```
MAILER_DSN=null://null
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

## Deployment Steps

### 1. Add MySQL Database
In Railway:
- Click "New" → "Database" → "Add MySQL"
- Railway will automatically set `DATABASE_URL` environment variable

### 2. Configure Environment Variables
Go to your service → Variables tab and add:
- `APP_ENV=prod`
- `APP_DEBUG=false`
- `APP_SECRET=<your-secret-here>`

### 3. Deploy
Railway will automatically:
- Build using `Dockerfile.railway`
- Run migrations on startup
- Listen on the PORT provided by Railway

### 4. Verify Deployment
Check the logs for:
```
Starting TripPlanner on port XXXX
```

## Troubleshooting

### 502 Bad Gateway
- Check if DATABASE_URL is set correctly
- Verify APP_SECRET is configured
- Check deployment logs for errors

### Database Connection Errors
- Ensure MySQL database is running
- Verify DATABASE_URL format is correct
- Check if migrations ran successfully

### Application Errors
- Set `APP_DEBUG=true` temporarily to see detailed errors
- Check logs: `railway logs`
- Verify all environment variables are set

## Local Testing

Build the Railway Docker image locally:
```bash
docker build -f Dockerfile.railway -t tripplanner-railway .
docker run -p 8080:8080 \
  -e PORT=8080 \
  -e APP_ENV=prod \
  -e APP_DEBUG=false \
  -e APP_SECRET=test-secret-key-32-characters \
  -e DATABASE_URL="mysql://root:root@host.docker.internal:3306/tripplanner?serverVersion=8.0&charset=utf8mb4" \
  tripplanner-railway
```

Access at: http://localhost:8080