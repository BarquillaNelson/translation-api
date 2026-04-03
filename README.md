# Translation API

A robust RESTful API built with Laravel 10 for managing translations. This service provides endpoints for user registration, authentication, and comprehensive full CRUD (Create, Read, Update, Delete) management of translation records.

## Setup Instructions

This project is fully containerized with Docker, meaning you do not need PHP, Composer, or MySQL installed directly on your host machine to run it.

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop) installed and running.

### Installation Steps

1. **Clone the repository** (if you haven't already):
   ```bash
   git clone <your-repository-url>
   cd translation-api
   ```

2. **Prepare Environment Files**:
   The Docker container relies on the `.env.local` file for configuration. Copy the example file to set up your local environment:
   ```bash
   cp .env.example .env.local
   cp .env.example .env
   ```
   *(Note: Ensure `APP_KEY` is populated in your `.env.local`. If it is blank, Laravel may throw a 500 error on the first run. You can generate one via `php artisan key:generate` or manually insert a 32-character base64 string.)*

3. **Build and Run the Containers**:
   Execute the following command to build the image and start the services in detached mode:
   ```bash
   docker-compose up -d --build
   ```

4. **Automatic Initialization**:
   Once the container starts, the `startup.sh` script handles everything automatically:
   - Composer dependencies are installed during the image build.
   - Database migrations are automatically executed against the MySQL container.
   - Caches are cleared and storage permissions are automatically fixed.

5. **Access the API**:
   The API is now running and accessible at: **http://localhost:8080**
   
   *Example:*
   ```bash
   curl http://localhost:8080/
   # Response: {"message": "API is running"}
   ```

---

## Architectural & Design Choices

The application was designed specifically for scalability, consistency, and maintaining a clean codebase. 

### 1. Framework: Laravel 10
Laravel was chosen for its expressive routing, robust Eloquent ORM, and rapid API development capabilities. It drastically reduces development time by providing battle-tested implementations of database migrations, routing, and caching out of the box.

### 2. Authentication: Laravel Sanctum
**Sanctum** is implemented to provide lightweight, robust token-based authentication. Rather than using heavy JWT or OAuth2 protocols like Passport, Sanctum issues simple API tokens which are perfect for mobile applications or SPAs (Single Page Applications) communicating with the API. This keeps the auth flow fast and simple.

### 3. Containerization: Docker & Supervisord
The entire stack (PHP 8.2 FPM, Nginx, MySQL 8) is bundled using `docker-compose`. 
- **Uniformity**: Eliminates the "it works on my machine" problem.
- **Zero-Touch Startup**: A custom `startup.sh` automates the execution of schema migrations and permission updates upon every container boot. 

### 4. Controller Encapsulation & The `BaseController`
Instead of duplicating `try/catch` and JSON response formatting logic across every controller method, a generic `executeFunction` wrapper is implemented in the `BaseController.php`.
- **DRY (Don't Repeat Yourself)**: Controllers like `TranslationController` and `LoginController` solely contain business logic wrapped in anonymous functions.
- **Consistency**: All API success and error responses are guaranteed to follow a uniform structural format.

### 5. Form Request Validation
Validation logic is entirely decoupled from the HTTP Controllers by utilizing Laravel Form Requests (`TranslationRequest`, `LoginRequest`). This ensures that controllers remain thin, and invalid data is intercepted and returned to the user with a standardized 422 Unprocessable Entity response before the controller logic is even executed.

### 6. Performance Optimization (Caching & Database Queries)
To ensure the API responds with sub-millisecond latency under high load, aggressive caching and optimized database querying have been implemented:
- **Eager Loading**: The Eloquent queries meticulously define select columns and strictly eager load relationships (`with(['values', 'tags'])`). This entirely prevents the N+1 query problem commonly found in ORM architectures.
- **Cache Invalidation Strategy**: Index lists and specific translation queries are cached using `Cache::remember`. The model utilizes Laravel Eloquent boot events (`booted()`) to automatically track version changes and clear specifically related cache tags/keys upon resource creation, update, or deletion.
