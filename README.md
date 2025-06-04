
# Uber Car

A **Laravel**-powered backend for a simple ride-hailing (Uber-style) application. This project implements user authentication, passenger and driver roles, ride requests, and basic ride-management workflows. It follows the code demonstrated in the Laracasts “Uber Car” course.

---

## Features

- **User Authentication**  
  - Register and login for both passengers and drivers  
  - Token-based authentication using Laravel Sanctum (or Passport)

- **User Roles & Permissions**  
  - **Passenger**: can request a ride, view ride status, and see ride history  
  - **Driver**: can view available ride requests, accept or decline a ride, and update ride status  
  - **Admin (optional)**: can manage users and view all rides

- **Ride Requests**  
  - Passengers can request a new ride by specifying pickup and drop-off locations  
  - Drivers see a queue of open ride requests and can choose to accept one  
  - Once accepted, the ride’s status updates and is no longer available to other drivers

- **Ride Management**  
  - Drivers can update ride status (e.g., “en route,” “arrived,” “in progress,” “completed,” “canceled”)  
  - Passengers can see real-time status updates for their current ride  
  - Fare is calculated automatically based on distance and/or time

- **Notifications (optional)**  
  - Email (or SMS) notifications to driver when a new ride is requested  
  - Email (or SMS) notifications to passenger when a driver accepts their ride or updates status

- **API Versioning**  
  - All endpoints are prefixed with `/api/v1` (future v2 additions can include payment integration, driver ratings, etc.)

- **Validation & Error Handling**  
  - Request validation via Form Requests  
  - Consistent JSON-formatted error responses

---

## Tech Stack

- **Laravel 10** (PHP framework)  
- **PHP 8.1+**  
- **MySQL** (or MariaDB)  
- **Laravel Sanctum** (or Passport) for API tokens  
- **Composer** for dependency management  
- **Redis** (optional—for queues or caching)  
- **Postman / Insomnia** (recommended for testing endpoints)

---

## Getting Started

### Prerequisites

- PHP ≥ 8.1  
- Composer  
- MySQL (or any other supported relational database)  
- (Optional) Redis if you plan to queue notifications  
- (Optional) Laravel Sanctum (or Passport) for API authentication  

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/shad-arf/uber_car.git
   cd uber_car

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Environment setup**

   * Copy the example environment file and generate an app key:

     ```bash
     cp .env.example .env
     php artisan key:generate
     ```
   * Open `.env` and configure your database credentials:

     ```dotenv
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=uber_car
     DB_USERNAME=your_db_user
     DB_PASSWORD=your_db_password
     ```

4. **Run Migrations & Seeders**

   ```bash
   php artisan migrate --seed
   ```

   The seeder will create a few users with roles (passenger, driver, admin) and sample rides for testing.

5. **Install & Configure Sanctum (or Passport)**

   * If using **Laravel Sanctum**, publish its config:

     ```bash
     php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
     php artisan migrate
     ```
   * If using **Laravel Passport**, run:

     ```bash
     php artisan passport:install
     ```

     Make sure your `AuthServiceProvider` calls `Passport::routes()`.

6. **Start the development server**

   ```bash
   php artisan serve
   ```

   The API will be available at `http://127.0.0.1:8000`.

---

## API Endpoints

All API routes live under `/api/v1`. Use a Bearer token (Sanctum/Passport) for protected routes.

### Authentication

* **Register**
  `POST /api/v1/auth/register`
  **Request Body**:

  ```json
  {
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "secret123",
    "password_confirmation": "secret123",
    "role": "passenger"      // or "driver"
  }
  ```

  **Response**:

  ```json
  {
    "user": { /* user object */ },
    "token": "sanctum_or_passport_token"
  }
  ```

* **Login**
  `POST /api/v1/auth/login`
  **Request Body**:

  ```json
  {
    "email": "jane@example.com",
    "password": "secret123"
  }
  ```

  **Response**:

  ```json
  {
    "user": { /* user object */ },
    "token": "sanctum_or_passport_token"
  }
  ```

* **Logout**
  `POST /api/v1/auth/logout`
  Requires `Authorization: Bearer <token>`

---

### Passengers

* **Request a Ride**
  `POST /api/v1/rides`
  *Requires Bearer token (role = passenger)*
  **Request Body**:

  ```json
  {
    "pickup_address": "123 Main St, Cityville",
    "dropoff_address": "456 Oak Ave, Townsville",
    "pickup_lat": 40.7128,
    "pickup_lng": -74.0060,
    "dropoff_lat": 40.7580,
    "dropoff_lng": -73.9855
  }
  ```

  **Response**:

  ```json
  {
    "ride": {
      "id": 27,
      "passenger_id": 5,
      "driver_id": null,
      "pickup_address": "...",
      "dropoff_address": "...",
      "status": "requested",
      "fare_estimate": 12.50,
      "created_at": "2025-05-20T14:32:01.000Z"
    }
  }
  ```

* **View My Rides**
  `GET /api/v1/rides/mine`
  *Returns an array of all rides requested by the authenticated passenger.*

* **View Single Ride**
  `GET /api/v1/rides/{ride}`
  *Returns ride details, including driver info (if assigned) and status.*

* **Cancel Ride**
  `DELETE /api/v1/rides/{ride}`
  *Only allowed if ride status is still “requested”.*

---

### Drivers

* **List Available Rides**
  `GET /api/v1/rides/available`
  *Returns rides with status = “requested”. Drivers can choose to accept.*

* **Accept a Ride**
  `POST /api/v1/rides/{ride}/accept`
  *Requires driver token. Sets `ride.status = “accepted”` and `driver_id = <current_user>`. Sends notification to passenger.*

* **Start Trip**
  `POST /api/v1/rides/{ride}/start`
  *Requires driver token. Allowed only if status = “accepted”. Changes status to “in\_progress”.*

* **Complete Trip**
  `POST /api/v1/rides/{ride}/complete`
  *Requires driver token. Allowed only if status = “in\_progress”. Changes status to “completed” and charges fare.*

* **View My Assigned Rides**
  `GET /api/v1/rides/assigned`
  *Returns an array of rides the driver has accepted (statuses: accepted, in\_progress).*

* **Decline a Ride**
  `POST /api/v1/rides/{ride}/decline`
  *Driver can decline before starting. Reverts status to “requested” and removes driver assignment.*

---

### Admin (Optional)

* **List All Users**
  `GET /api/v1/admin/users`
  *Requires admin token.*

* **List All Rides**
  `GET /api/v1/admin/rides`
  *Filtering by status, date, passenger, or driver is supported via query params.*

* **Force-Assign / Reassign Driver**
  `PUT /api/v1/admin/rides/{ride}/assign`
  **Request Body**:

  ```json
  {
    "driver_id": 8
  }
  ```

* **Close Ride**
  `POST /api/v1/admin/rides/{ride}/close`
  *Allows admin to mark any ride as canceled or completed.*

---

## Database Schema

Below is an overview of the main tables (see `database/migrations` for full details).

* **users**

  * `id`
  * `name`
  * `email`
  * `password`
  * `role` (enum: `passenger`, `driver`, `admin`)
  * `created_at` · `updated_at`

* **rides**

  * `id`
  * `passenger_id` (foreign key → `users.id`)
  * `driver_id` (foreign key → `users.id`, nullable until assigned)
  * `pickup_address`
  * `pickup_lat`
  * `pickup_lng`
  * `dropoff_address`
  * `dropoff_lat`
  * `dropoff_lng`
  * `status` (enum: `requested`, `accepted`, `in_progress`, `completed`, `canceled`)
  * `fare_estimate` (decimal)
  * `fare_actual` (decimal, nullable until completed)
  * `requested_at` (timestamp)
  * `started_at` (timestamp, nullable)
  * `completed_at` (timestamp, nullable)
  * `created_at` · `updated_at`

* **notifications** (if implemented)

  * `id`
  * `user_id`
  * `type`
  * `data` (JSON payload)
  * `read_at`
  * `created_at` · `updated_at`

---

## Testing

Run the automated test suite (PHPUnit + Laravel Feature tests):

```bash
php artisan test
```

Test coverage typically includes:

* Authentication (registration/login/logout)
* Role-based access control (passenger vs. driver vs. admin)
* Ride request workflows (request → accept → start → complete)
* Input validation and error responses
* Notification dispatch (if implemented)

---

## Environment Variables

Sensitive or environment-specific settings live in your `.env` file. Key variables include:

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uber_car
DB_USERNAME=your_user
DB_PASSWORD=your_pass

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# If using Laravel Sanctum:
SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DOMAIN=localhost

# If using third-party services (e.g., Twilio) for SMS:
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_auth_token
TWILIO_FROM=+1234567890
```

---

## Contributing

1. Fork the repository.
2. Create a new branch:

   ```bash
   git checkout -b feature/YourFeatureName
   ```
3. Make your changes and write tests if needed.
4. Commit your changes:

   ```bash
   git commit -m "Add feature: ..."
   ```
5. Push to your branch:

   ```bash
   git push origin feature/YourFeatureName
   ```
6. Open a Pull Request and describe your changes.

Please adhere to PSR-12 coding standards, include clear commit messages, and ensure all tests pass.

---

## License

This project is released under the [MIT License](LICENSE).

```
::contentReference[oaicite:0]{index=0}
```
