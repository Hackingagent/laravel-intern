# Laravel Inactive User Reminder

A Laravel application that automatically detects inactive users and queues a job to send them a reminder message.

## Features

- **Automatic Detection**: Identifies users who haven't logged in for 7 days (configurable)
- **Scheduled Task**: Runs daily via Laravel's scheduler
- **Queued Jobs**: Dispatches reminder jobs to the queue for processing
- **Duplicate Prevention**: Ensures users receive at most one reminder per day
- **Logging**: Records when reminders are sent

## Requirements

- PHP 8.2+
- MySQL
- Composer

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-intern
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   ```

4. **Update .env file** with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

## Running the Application

### Development Server

```bash
php artisan serve
```

Access the application at `http://localhost:8000`

### Routes

- `/` - Welcome page
- `/login` - Login page
- `/users` - View all users with their activity status

## How It Works

### 1. User Login Tracking
When a user logs in, the `last_login_at` timestamp is automatically updated in the database.

### 2. Scheduled Command
The `users:check-inactive` command runs daily at 8:00 AM (configurable) and:
- Finds users who haven't logged in for 7+ days
- Filters out users who already received a reminder today
- Dispatches a `SendReminderJob` for each eligible user

### 3. Queued Job
The `SendReminderJob`:
- Logs the reminder message
- Updates `reminder_sent_at` timestamp to prevent duplicate processing

## Commands

### Run the scheduler (for testing)
```bash
php artisan schedule:run
```

### Run the queue worker
```bash
php artisan queue:work
```

### Run the check manually
```bash
php artisan users:check-inactive
```

### With custom inactivity period
```bash
php artisan users:check-inactive --days=14
```

## Configuration

### Change the inactivity period

Edit `routes/console.php`:
```php
Schedule::command('users:check-inactive --days=7')->dailyAt('8:00');
```

### Change the schedule time

Edit `routes/console.php`:
```php
Schedule::command('users:check-inactive --days=7')->dailyAt('9:00');
```

### Using Cron (Production)

Add this to your crontab:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This will run the scheduler every minute, which will execute scheduled tasks at their specified times.

## Testing the Application

1. Start the development server: `php artisan serve`
2. Start the queue worker: `php artisan queue:work`
3. Navigate to `/login` and log in with any email
4. Go to `/users` to see all users
5. Use the "Set Inactive" button to set a user's last login to 7+ days ago
6. Run `php artisan schedule:run` to trigger the check
7. Check the logs at `storage/logs/laravel.log` for reminder messages

## Database Schema

The application adds two columns to the default users table:

- `last_login_at` - Timestamp of the user's last login
- `reminder_sent_at` - Timestamp when the last reminder was sent

## Queue Configuration

The application uses the database queue by default. For production, consider using:
- Redis
- RabbitMQ
- AWS SQS

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
