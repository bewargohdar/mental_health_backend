# 🧠 Mental Health Backend API

A comprehensive Laravel 12 REST API backend for a mental health platform featuring multi-role authentication, mood tracking, community features, doctor appointments, and learning content.

## ✨ Features

| Feature | Description |
|---------|-------------|
| **Multi-Role Auth** | User, Doctor, and Admin roles with Sanctum tokens |
| **Mood Tracking** | Track moods with analytics and statistics |
| **Community Posts** | Anonymous posting, likes, comments with moderation |
| **Appointments** | Doctor scheduling, booking, and management |
| **Learning Content** | Articles, videos, and guided exercises |
| **Notifications** | Email and database notifications |
| **Admin Dashboard** | Filament 3 admin panel at `/admin` |

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Auth**: Laravel Sanctum
- **Roles**: Spatie Laravel-Permission
- **Admin**: Filament 3
- **Activity Log**: Spatie Activity Log

## 🚀 Installation

```bash
# Clone repository
git clone https://github.com/bewargohdar/mental_health_backend.git
cd mental_health_backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mental_health
DB_USERNAME=root
DB_PASSWORD=your_password

# Generate key
php artisan key:generate

# Run migrations with seed data
php artisan migrate:fresh --seed

# Start server
php artisan serve
```

## 📌 Access Points

| Service | URL |
|---------|-----|
| API Base | http://127.0.0.1:8000/api/v1 |
| Admin Panel | http://127.0.0.1:8000/admin |

## 🔐 Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@mentalhealth.com | password |
| Doctor | doctor@mentalhealth.com | password |
| User | user@mentalhealth.com | password |

## 📡 API Endpoints

### Authentication
```
POST   /api/v1/auth/register          # Register user
POST   /api/v1/auth/register/doctor   # Register doctor
POST   /api/v1/auth/login             # Login
POST   /api/v1/auth/logout            # Logout (protected)
GET    /api/v1/auth/me                # Get current user (protected)
POST   /api/v1/auth/forgot-password   # Request password reset
POST   /api/v1/auth/reset-password    # Reset password
```

### Mood Tracking (Protected)
```
GET    /api/v1/moods                  # List mood entries
POST   /api/v1/moods                  # Create mood entry
GET    /api/v1/moods/statistics       # Get statistics
GET    /api/v1/moods/{id}             # Get mood entry
PUT    /api/v1/moods/{id}             # Update mood entry
DELETE /api/v1/moods/{id}             # Delete mood entry
```

### Community Posts
```
GET    /api/v1/posts                  # List posts (public)
GET    /api/v1/posts/{id}             # Get post (public)
POST   /api/v1/posts                  # Create post (protected)
PUT    /api/v1/posts/{id}             # Update post (protected)
DELETE /api/v1/posts/{id}             # Delete post (protected)
POST   /api/v1/posts/{id}/like        # Like/unlike (protected)
```

### Content
```
GET    /api/v1/content/articles       # List articles
GET    /api/v1/content/articles/{id}  # Get article
GET    /api/v1/content/videos         # List videos
GET    /api/v1/content/exercises      # List exercises
POST   /api/v1/content/bookmark       # Toggle bookmark (protected)
```

### Appointments (Protected)
```
GET    /api/v1/appointments           # List appointments
POST   /api/v1/appointments           # Book appointment
GET    /api/v1/appointments/{id}      # Get appointment
POST   /api/v1/appointments/{id}/confirm  # Doctor confirms
POST   /api/v1/appointments/{id}/cancel   # Cancel
POST   /api/v1/appointments/{id}/complete # Complete
GET    /api/v1/doctors/{id}/availability  # Doctor's schedule
GET    /api/v1/doctors/{id}/slots         # Available slots
```

### Notifications (Protected)
```
GET    /api/v1/notifications          # List notifications
GET    /api/v1/notifications/unread-count  # Unread count
POST   /api/v1/notifications/{id}/read     # Mark as read
POST   /api/v1/notifications/read-all      # Mark all read
```

## 📁 Project Structure

```
app/
├── Enums/           # UserRole, MoodType, AppointmentStatus
├── Filament/        # Admin panel resources
├── Http/
│   ├── Controllers/Api/   # API controllers
│   └── Requests/          # Form validation
├── Models/          # Eloquent models
├── Notifications/   # Email/DB notifications
└── Policies/        # Authorization policies
```

## 🔒 Security Features

- **Sanctum** token-based authentication
- **Encrypted** appointment notes
- **Activity logging** for audit trails
- **Role-based** access control
- **Email verification** for new users

## 📝 License

MIT License

## 👤 Author

**Bewar Gohdar**
- GitHub: [@bewargohdar](https://github.com/bewargohdar)
