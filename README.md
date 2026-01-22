# TripPlanner

A modern trip planning application built with Symfony 8.

## Tech Stack

- **PHP**: 8.3
- **Symfony**: 8.0
- **Database**: MySQL 8.0
- **Web Server**: Nginx (Alpine)
- **Containerization**: Docker & Docker Compose

## Included Packages

- Doctrine ORM - Database abstraction and ORM
- Twig - Template engine
- Symfony Security - Authentication and authorization
- Symfony Forms - Form handling
- Symfony Validator - Data validation
- Maker Bundle - Code generation tools
- PHPUnit - Testing framework
- Monolog - Logging
- Symfony Messenger - Message queue system
- Symfony Mailer - Email handling

## Prerequisites

- Docker Desktop 28.5.2 or higher
- Docker Compose v2.40.3 or higher

## Getting Started

### 1. Start the Docker containers

```bash
docker-compose up -d
```

This will start three containers:
- `tripplanner_php` - PHP-FPM 8.3
- `tripplanner_nginx` - Nginx web server
- `tripplanner_db` - MySQL 8.0 database

### 2. Install dependencies (if needed)

```bash
docker-compose exec php composer install
```

### 3. Create database schema

```bash
docker-compose exec php bin/console doctrine:database:create
docker-compose exec php bin/console doctrine:migrations:migrate
```

### 4. Access the application

Open your browser and navigate to: `http://localhost:8081`

## Development

### Running Symfony commands

Execute any Symfony console command using:

```bash
docker-compose exec php bin/console <command>
```

Examples:
```bash
# Clear cache
docker-compose exec php bin/console cache:clear

# Create a new controller
docker-compose exec php bin/console make:controller

# Create a new entity
docker-compose exec php bin/console make:entity

# Generate migration
docker-compose exec php bin/console make:migration
```

### Running tests

```bash
docker-compose exec php bin/phpunit
```

### Database access

**From host machine:**
- Host: `localhost`
- Port: `3306`
- Database: `tripplanner`
- Username: `tripplanner`
- Password: `tripplanner`

**From PHP container:**
- Host: `database`
- Port: `3306`
- Database: `tripplanner`
- Username: `tripplanner`
- Password: `tripplanner`

### Logs

View application logs:
```bash
docker-compose exec php tail -f var/log/dev.log
```

View Nginx logs:
```bash
docker-compose logs -f nginx
```

## Project Structure

```
TripPlanner/
├── assets/              # Frontend assets (JS, CSS)
├── bin/                 # Executable scripts (console, phpunit)
├── config/              # Configuration files
├── docker/              # Docker configuration files
│   └── nginx/          # Nginx configuration
├── migrations/          # Database migrations
├── public/              # Public web directory
├── src/                 # Application source code
│   ├── Controller/     # Controllers
│   ├── Entity/         # Doctrine entities
│   └── Repository/     # Doctrine repositories
├── templates/           # Twig templates
├── tests/              # Test files
├── translations/        # Translation files
├── var/                # Cache and logs
├── vendor/             # Composer dependencies
├── .env                # Environment variables
├── docker-compose.yml  # Docker Compose configuration
└── Dockerfile          # PHP container configuration
```

## Useful Commands

### Docker Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f

# Access PHP container shell
docker-compose exec php bash

# Rebuild containers
docker-compose up -d --build
```

### Code Generation

```bash
# Create controller
docker-compose exec php bin/console make:controller

# Create entity
docker-compose exec php bin/console make:entity

# Create form
docker-compose exec php bin/console make:form

# Create CRUD
docker-compose exec php bin/console make:crud
```

### Database Commands

```bash
# Create database
docker-compose exec php bin/console doctrine:database:create

# Drop database
docker-compose exec php bin/console doctrine:database:drop --force

# Generate migration
docker-compose exec php bin/console make:migration

# Run migrations
docker-compose exec php bin/console doctrine:migrations:migrate

# Rollback migration
docker-compose exec php bin/console doctrine:migrations:migrate prev
```

## Environment Configuration

Environment variables are stored in `.env` file. Key configurations:

- `APP_ENV` - Application environment (dev, prod, test)
- `APP_SECRET` - Application secret key
- `DATABASE_URL` - Database connection string
- `MESSENGER_TRANSPORT_DSN` - Message queue configuration
- `MAILER_DSN` - Email configuration

## Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Make your changes
3. Run tests: `docker-compose exec php bin/phpunit`
4. Commit your changes: `git commit -am 'Add new feature'`
5. Push to the branch: `git push origin feature/your-feature`
6. Create a Pull Request

## License

This project is private and proprietary.