# Blu Charity - Better Lives United

> **2024-12-21 Update:**
> - The Next.js homepage (`/`) is now a real landing page with links to Login, Register, Donate, and Donation History.
> - Vercel deployments have been strictly audited: only the `bluweb-next` project should be deployed for production.
> - All static assets and user flows are verified and accessible from the homepage.
> - If you see the default Next.js template, check that you are visiting the correct Vercel deployment for `bluweb-next` and that your browser cache is cleared.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18.2.0-blue.svg)](https://reactjs.org)
[![React Native](https://img.shields.io/badge/React%20Native-0.76.9-blue.svg)](https://reactnative.dev)

A comprehensive donation platform that connects donors with recipients in need through a modern, user-friendly interface. Built with Laravel, React, and React Native.

## 🌟 Features

- **Multi-Platform**: Web application, mobile app, and admin dashboard
- **Secure Authentication**: OTP verification and role-based access control
- **Payment Processing**: PayPal integration for secure donations
- **Swipe Interface**: Tinder-like donor discovery experience
- **Push Notifications**: Real-time updates via Firebase
- **Admin Dashboard**: Comprehensive platform management tools
- **Responsive Design**: Optimized for all devices and screen sizes

## 🏗️ Architecture

Blu Charity consists of three main components:

- **Backend API** (Laravel 10) - RESTful API service with authentication, payment processing, and data management
- **Web Application** (React + Vite) - Modern donor interface with swipe functionality
- **Mobile Application** (React Native + Expo) - Cross-platform mobile app for donors and recipients

## 🚀 Quick Start

### Prerequisites

- **PHP**: 8.1 or higher
- **Node.js**: 18.x or higher
- **Composer**: Latest version
- **MySQL**: 8.0 or higher
- **Redis**: 6.x or higher (optional)
- **Expo CLI**: Latest version (for mobile development)

### Backend Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd blu/backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

### Web Application Setup (Next.js)

1. **Navigate to web directory**
   ```bash
   cd bluweb-next
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Environment configuration**
   - Set NEXT_PUBLIC_API_BASE_URL in .env.local to your backend API URL

4. **Start development server**
   ```bash
   npm run dev
   ```

### Asset Usage
- All images shared with the mobile app are in public/images/
- Reference images in components/pages as /images/filename.ext

### Features
- Login, registration, donation/swipe, profile, donation history
- All features use backend API via src/services/apiService.js
- UI matches mobile app branding and experience

### Mobile Application Setup

1. **Navigate to mobile directory**
   ```bash
   cd ../frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start Expo development server**
   ```bash
   npx expo start
   ```

## ⚙️ Configuration

### Environment Variables

#### Backend (.env)
```env
# Application
APP_NAME="Blu Charity"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blu_charity
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@blucharity.com"
MAIL_FROM_NAME="${APP_NAME}"

# PayPal
PAYPAL_CLIENT_ID=your-paypal-client-id
PAYPAL_CLIENT_SECRET=your-paypal-client-secret
PAYPAL_MODE=sandbox

# Firebase
FIREBASE_CREDENTIALS=path/to/firebase-credentials.json
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Web Application (.env.local)
```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME="Blu Charity"
VITE_APP_VERSION=1.0.0
```

#### Mobile Application (app.json)
```json
{
  "expo": {
    "name": "Blu Charity",
    "slug": "blu-charity",
    "version": "1.0.0",
    "orientation": "portrait",
    "icon": "./assets/icon.png",
    "splash": {
      "image": "./assets/splash.png",
      "resizeMode": "contain",
      "backgroundColor": "#ffffff"
    },
    "updates": {
      "fallbackToCacheTimeout": 0
    },
    "assetBundlePatterns": [
      "**/*"
    ],
    "ios": {
      "supportsTablet": true
    },
    "android": {
      "adaptiveIcon": {
        "foregroundImage": "./assets/adaptive-icon.png",
        "backgroundColor": "#FFFFFF"
      }
    },
    "web": {
      "favicon": "./assets/favicon.png"
    }
  }
}
```

## 🛠️ Development Workflow

### Code Standards

- **Backend**: PSR-12 coding standards with Laravel Pint
- **Frontend**: ESLint with React and TypeScript rules
- **Mobile**: ESLint with React Native rules
- **Git**: Conventional commits format

### Development Commands

#### Backend
```bash
# Code quality
composer pint                    # Code formatting
composer test                    # Run tests
php artisan test --coverage      # Test coverage

# Database
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Rollback migrations
php artisan db:seed              # Seed database

# Cache
php artisan cache:clear          # Clear cache
php artisan config:clear         # Clear config
php artisan route:clear          # Clear routes
```

#### Web Application
```bash
# Development
npm run dev                      # Start development server
npm run build                    # Build for production
npm run preview                  # Preview production build

# Code quality
npm run lint                     # Run ESLint
npm run lint:fix                 # Fix ESLint issues
npm run type-check               # TypeScript checking

# Testing
npm run test                     # Run tests
npm run test:coverage            # Test coverage
```

#### Mobile Application
```bash
# Development
npx expo start                   # Start Expo development server
npx expo start --ios             # Start iOS simulator
npx expo start --android         # Start Android emulator

# Building
eas build --platform ios         # Build for iOS
eas build --platform android     # Build for Android

# Testing
npm test                         # Run tests
```

### Git Workflow

1. **Create feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make changes and commit**
   ```bash
   git add .
   git commit -m "feat: add new feature"
   ```

3. **Push and create pull request**
   ```bash
   git push origin feature/your-feature-name
   ```

4. **Code review and merge**

## 🧪 Testing

### Backend Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage --min=80

# Run specific test
php artisan test --filter=UserTest
```

### Frontend Testing

```bash
# Run all tests
npm test

# Run with coverage
npm run test:coverage

# Run specific test
npm test -- --testNamePattern="Login"

# Run E2E tests
npm run test:e2e
```

### Mobile Testing

```bash
# Run unit tests
npm test

# Run on device
npx expo start --tunnel
```

### API Testing

```bash
# Test API endpoints
php artisan test --filter=ApiTest

# Test with Postman collection
# Import: docs/postman/Blu_Charity_API.postman_collection.json
```

## 🚀 Deployment

### Backend Deployment

#### Production Server Setup
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --force
php artisan db:seed --force

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Docker Deployment
```bash
# Build and run with Docker Compose
docker-compose up -d

# Or build individual containers
docker build -t blu-backend .
docker run -p 8000:8000 blu-backend
```

### Web Application Deployment

#### Vercel (Recommended)
```bash
# Install Vercel CLI
npm i -g vercel

# Deploy
vercel --prod

# Or connect GitHub repository to Vercel dashboard
```

#### Manual Deployment
```bash
# Build for production
npm run build

# Deploy to web server
# Upload dist/ folder to web server
```

### Mobile Application Deployment

#### Expo Application Services
```bash
# Build for production
eas build --platform all --profile production

# Submit to app stores
eas submit --platform ios
eas submit --platform android
```

#### Manual Build
```bash
# iOS
npx expo run:ios --configuration Release

# Android
npx expo run:android --variant release
```

## 📚 API Documentation

### Interactive Documentation
- **Swagger UI**: `http://your-domain.com/api-doc`
- **Postman Collection**: `docs/postman/Blu_Charity_API.postman_collection.json`

### API Endpoints

#### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/verify-otp` - OTP verification
- `POST /api/auth/sign-in` - User login
- `POST /api/auth/logout` - User logout

#### Donor Endpoints
- `GET /api/donor-account/home` - Donor dashboard
- `GET /api/donor-account/profile` - Donor profile
- `GET /api/donor-account/donations` - Donation history

#### Receiver Endpoints
- `POST /api/receiver-account/donation/store/detail` - Create post
- `GET /api/receiver-account/home` - Receiver dashboard
- `GET /api/receiver-account/balance` - Check balance

#### Payment Endpoints
- `POST /api/paypal/create-order` - Create PayPal order
- `POST /api/paypal/capture-order` - Capture payment

### Response Format
```json
{
  "response": true,
  "message": ["Success message"],
  "data": {
    // Response data
  }
}
```

## 🔧 Troubleshooting

### Common Issues

#### Backend Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reset database
php artisan migrate:fresh --seed

# Check logs
tail -f storage/logs/laravel.log
```

#### Frontend Issues
```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear build cache
npm run build -- --force

# Check for TypeScript errors
npm run type-check
```

#### Mobile Issues
```