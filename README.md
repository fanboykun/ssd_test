## Tech Stack

- **Backend Framework:** Laravel 11
- **Frontend Framework:**
  - Livewire 3.x for reactive components
  - Alpine.js 3.x for JavaScript interactivity
  - Tailwind CSS 3.x for styling
- **Authentication:** Laravel Breeze
- **Database:** MySQL
- **Development Environment:** Laravel Sail (Docker)

## Requirements

- PHP 8.4+
- Composer 2.5.8+
- Node.js 20.x
- MySQL 8.0+
- Docker (optional, for containerized development)

## Installation

1. Clone the repository:
   ```bash
   git clone https://your-repository-url/ssd-test.git
   cd ssd-test
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

## Development Setup

1. Configure your `.env` file:
   ```env
   APP_NAME="Persons Management"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ssd_test
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. Run database migrations:
   ```bash
   php artisan migrate
   ```

3. Seed the database with sample data:
   ```bash
   php artisan db:seed
   ```

4. Start the development server:
   ```bash
   php artisan serve
   ```

5. Compile assets:
   ```bash
   npm run dev
   ```

6. Visit `http://localhost:8000` in your browser

## Testing

The application uses Pest PHP testing framework for both unit and feature tests.

### Running Tests

1. Run all tests:
   ```bash
   php artisan test
   ```

2. Run tests with coverage report (requires Xdebug):
   ```bash
   php artisan test --coverage
   ```

### Test Structure

- `tests/Unit/`: Contains unit tests for individual components
  - `PersonTest.php`: Tests for Person model
- `tests/Feature/`: Contains feature tests for full functionality
  - `PersonManagementTest.php`: Tests for person management features

### Key Test Cases

1. Unit Tests:
   - Person model creation and attributes
   - Date format handling
   - Model relationships and methods

2. Feature Tests:
   - Dashboard rendering
   - Person listing and pagination
   - Search functionality
   - CRUD operations
   - Form validation
   - Authentication

## Code Documentation

### Database Schema

#### Persons Table

```php
Schema::create('persons', function (Blueprint $table) {
    $table->id();
    $table->string('name', 255);
    $table->date('birthday');
    $table->string('residence', 255);
    $table->timestamps();
});
```

### Models

#### Person Model

- Location: `app/Models/Person.php`
- Uses Laravel's Factory pattern for seeding
- Fillable fields: name, birthday, residence

### Livewire Components

#### IndexPerson Component

- Location: `app/Livewire/Person/IndexPerson.php`
- Features:
  - Real-time search
  - Dynamic pagination
  - CRUD operations
  - Event handling

#### PersonForm

- Location: `app/Livewire/Forms/PersonForm.php`
- Handles:
  - Form validation
  - Create/Update/Delete operations
  - Data sanitization

### Factory & Seeder

#### PersonFactory

- Location: `database/factories/PersonFactory.php`
- Generates fake data using Faker
- Creates realistic test data for development

#### DatabaseSeeder

- Location: `database/seeders/DatabaseSeeder.php`
- Seeds 10 random persons by default

## API Documentation

### Authentication

All endpoints require authentication using Laravel Sanctum. Users must first log
in through the web interface at `/login`.

### Available Endpoints

#### Dashboard (`/dashboard`)

The main interface for managing persons. Protected by the `auth` and `verified`
middleware.

##### Features:

1. **List Persons**
   - Method: GET
   - Path: `/dashboard`
   - Query Parameters:
     - `search`: Filter persons by name
     - `page`: Page number for pagination
   - Response: Returns a paginated list of persons

2. **Create Person**
   - Method: POST
   - Path: `/dashboard`
   - Form Data:
     ```json
     {
         "name": "string",
         "birthday": "date (Y-m-d)",
         "residence": "string"
     }
     ```
   - Response: Returns the created person data

3. **Update Person**
   - Method: PUT/PATCH
   - Path: `/dashboard`
   - Form Data:
     ```json
     {
         "person_id": "integer",
         "name": "string",
         "birthday": "date (Y-m-d)",
         "residence": "string"
     }
     ```
   - Response: Returns the updated person data

4. **Delete Person**
   - Method: DELETE
   - Path: `/dashboard`
   - Form Data:
     ```json
     {
         "person_id": "integer"
     }
     ```
   - Response: Returns a success message

### Dashboard Interface

The dashboard (`/dashboard`) provides a full-featured interface for managing
persons:

1. **Search Functionality**
   - Real-time search by person name
   - Debounced input (50ms) to optimize performance
   - Updates results automatically as you type

2. **Person List**
   - Displays persons in a responsive table
   - Columns:
     - Name
     - Birthday (formatted as "d F Y")
     - Residence
     - Actions (Edit/Delete)
   - Pagination with 10 items per page

3. **Create New Person**
   - Click "Create Person" button
   - Modal form with fields:
     - Name (required)
     - Birthday (date picker)
     - Residence (required)
   - Form validation with error messages
   - Success notification on completion

4. **Edit Person**
   - Click edit icon on person row
   - Pre-filled modal form
   - Same validation as create
   - Success notification on save

5. **Delete Person**
   - Click delete icon on person row
   - Confirmation modal
   - Requires confirmation
   - Success notification on deletion

6. **Mobile Responsive**
   - Adapts to different screen sizes
   - Optimized table layout for mobile
   - Touch-friendly buttons and inputs

All operations are performed in real-time using Livewire, providing instant
feedback without page reloads.
