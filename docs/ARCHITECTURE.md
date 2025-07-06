# Blu Charity - System Architecture Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [High-Level Architecture](#high-level-architecture)
4. [Database Schema](#database-schema)
5. [API Architecture](#api-architecture)
6. [Frontend Architecture](#frontend-architecture)
7. [Security Architecture](#security-architecture)
8. [Deployment Architecture](#deployment-architecture)
9. [Performance Considerations](#performance-considerations)
10. [Error Handling & Logging](#error-handling--logging)
11. [Testing Strategy](#testing-strategy)

## System Overview

Blu Charity is a comprehensive donation platform that connects donors with recipients in need. The system consists of three main components:

- **Backend API** (Laravel 10) - RESTful API service
- **Web Application** (React + Vite) - Donor interface
- **Mobile Application** (React Native + Expo) - Cross-platform mobile app

### Core Business Logic
- **Donor Management**: Registration, authentication, donation tracking
- **Recipient Management**: Profile creation, story sharing, withdrawal requests
- **Payment Processing**: PayPal integration for secure transactions
- **Admin Dashboard**: User management, withdrawal approvals, content moderation

## Technology Stack

### Backend (Laravel 10)
- **Framework**: Laravel 10.x (PHP 8.1+)
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum (JWT tokens)
- **API Documentation**: Swagger/OpenAPI
- **Payment Gateway**: PayPal API
- **Push Notifications**: Firebase Cloud Messaging
- **File Storage**: Local filesystem (configurable for cloud storage)
- **Queue System**: Laravel Queue (database driver)
- **Caching**: Redis (optional)

### Web Frontend (Next.js)
- **Framework**: Next.js 15.x
- **Directory**: bluweb-next/
- **Pages**: login, register, donate, profile, donations (history), main landing
- **Asset Strategy**: All images shared with mobile app are stored in public/images/ and referenced in UI components/pages
- **API Integration**: Uses src/services/apiService.js to connect to backend endpoints for authentication, donation, profile, and history
- **Component Structure**: Modular, with shared components (e.g., SwipeCard, DebugPanel)

### Mobile Frontend (React Native)
- **Framework**: React Native 0.76.9
- **Development Platform**: Expo SDK 52
- **Navigation**: React Navigation 6.x
- **State Management**: Redux + Redux Persist
- **HTTP Client**: Axios
- **UI Components**: Custom components with React Native Vector Icons
- **Animations**: React Native Reanimated
- **Storage**: AsyncStorage + Expo SecureStore

## High-Level Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web App       │    │  Mobile App     │    │  Admin Panel    │
│   (React)       │    │ (React Native)  │    │   (Laravel)     │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
                    ┌─────────────┴─────────────┐
                    │      API Gateway          │
                    │      (Laravel API)        │
                    └─────────────┬─────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    │      Core Services        │
                    │  ┌─────────────────────┐  │
                    │  │   Authentication    │  │
                    │  │   User Management   │  │
                    │  │   Payment Service   │  │
                    │  │   Notification      │  │
                    │  │   File Upload       │  │
                    │  └─────────────────────┘  │
                    └─────────────┬─────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    │      Data Layer           │
                    │  ┌─────────────────────┐  │
                    │  │   MySQL Database    │  │
                    │  │   Redis Cache       │  │
                    │  │   File Storage      │  │
                    │  └─────────────────────┘  │
                    └───────────────────────────┘
```

## Database Schema

### Core Tables

#### Users Table
```sql
users (
    id (BIGINT, PRIMARY KEY)
    name (VARCHAR)
    email (VARCHAR, UNIQUE, NULLABLE)
    phone (VARCHAR, UNIQUE, NULLABLE)
    role (ENUM: 'donor', 'receiver', 'admin')
    phone_verified_at (TIMESTAMP, NULLABLE)
    email_verified_at (TIMESTAMP, NULLABLE)
    device_token (TEXT, NULLABLE)
    password (VARCHAR, NULLABLE)
    photo (VARCHAR, NULLABLE)
    gender (VARCHAR, NULLABLE)
    remember_token (VARCHAR, NULLABLE)
    created_at (TIMESTAMP)
    updated_at (TIMESTAMP)
)
```

#### Posts Table (Recipient Stories)
```sql
posts (
    id (BIGINT, PRIMARY KEY)
    user_id (BIGINT, FOREIGN KEY -> users.id)
    amount (VARCHAR, NULLABLE)
    paid (VARCHAR, DEFAULT: '0')
    biography (TEXT, NULLABLE)
    image (VARCHAR, NULLABLE)
    created_at (TIMESTAMP)
    updated_at (TIMESTAMP)
)
```

#### Donations Table
```sql
donations (
    id (BIGINT, PRIMARY KEY)
    post_id (BIGINT, FOREIGN KEY -> posts.id)
    user_id (BIGINT, FOREIGN KEY -> users.id)
    paid_amount (VARCHAR)
    activity (BOOLEAN, DEFAULT: FALSE)
    created_at (TIMESTAMP)
    updated_at (TIMESTAMP)
)
```

#### Bank Details Table
```sql
bank_details (
    id (BIGINT, PRIMARY KEY)
    user_id (BIGINT, FOREIGN KEY -> users.id)
    bank_name (VARCHAR, NULLABLE)
    account_name (VARCHAR, NULLABLE)
    account_no (VARCHAR, NULLABLE)
    ifsc_code (VARCHAR, NULLABLE)
    created_at (TIMESTAMP)
    updated_at (TIMESTAMP)
)
```

#### Withdraws Table
```sql
withdraws (
    id (BIGINT, PRIMARY KEY)
    user_id (BIGINT, FOREIGN KEY -> users.id)
    amount (VARCHAR)
    status (TINYINT, DEFAULT: 0)
    created_at (TIMESTAMP)
    updated_at (TIMESTAMP)
)
```

### Supporting Tables
- `otps` - OTP verification for phone/email
- `mail_otps` - Email change verification
- `app_faqs` - FAQ content
- `tags` - Content categorization
- `post_tag` - Many-to-many relationship
- `notifications` - Push notification storage
- `settings` - Application configuration
- `user_socials` - Social login integration

## API Architecture

### Authentication Endpoints
```
POST /api/auth/register          # User registration (validates name, email, phone, password)
POST /api/auth/verify-otp        # OTP verification
POST /api/auth/resend-otp        # Resend OTP
POST /api/auth/sign-in           # User login (email/password + userType)
POST /api/auth/forgot-password   # Password reset request
POST /api/auth/reset-password    # Password reset
POST /api/auth/social-login      # Social authentication
POST /api/auth/logout            # User logout
GET  /api/auth/delete/{id}       # Account deletion
```

### Authentication Flow Debugging (2024-12-21)
**Issue Identified**: Backend registration and password reset functionality not working properly
- ✅ Old credentials work (existing users in database)
- ❌ New registrations don't work (not being saved to database)
- ❌ Password changes don't work (not being updated in database)

**Root Cause**: Backend user management endpoints failing silently
**Frontend Status**: Login code working correctly, issue is in backend registration/password update
**Debugging Protocol**: Implemented comprehensive logging to trace API requests/responses

### Registration Flow Architecture
The registration system uses a multi-layered validation approach:

1. **Frontend Validation**: HTML5 `required` attributes for name, email, phone fields
2. **Backend Validation**: Laravel validation rules in `RegisterController`
3. **OTP Verification**: Email-based OTP for account activation
4. **Role Assignment**: User type selection (donor/receiver) during registration

**Key Components**:
- `RegisterController.php`: Handles registration logic and validation
- `register-screen.tsx`: Frontend form with HTML5 validation
- `api.ts`: API service layer for registration requests
- `AuthProvider`: Context provider for authentication state management

### Donor Endpoints
```
GET  /api/donor-account/home              # Donor dashboard
GET  /api/donor-account/home/delete/{id}  # Soft delete post
GET  /api/donor-account/profile           # Donor profile
POST /api/donor-account/profile/update    # Update profile
POST /api/donor-account/payment           # Payment processing
GET  /api/donor-account/donations         # Donation history
GET  /api/donor-account/donations/{id}    # Donation details
GET  /api/donor-account/notification/list # Notifications
POST /api/donor-account/notification/remove # Mark as read
```

### Receiver Endpoints
```
POST /api/receiver-account/donation/store/detail  # Create donation post
POST /api/receiver-account/donation/store/bank    # Add bank details
GET  /api/receiver-account/home                   # Receiver dashboard
GET  /api/receiver-account/profile                # Receiver profile
POST /api/receiver-account/profile/update/detail  # Update profile
POST /api/receiver-account/profile/update/bank    # Update bank details
POST /api/receiver-account/profile/update/post    # Update post
GET  /api/receiver-account/balance                # Check balance
POST /api/receiver-account/withdraw/create        # Request withdrawal
GET  /api/receiver-account/notification/list      # Notifications
POST /api/receiver-account/notification/remove    # Mark as read
```

### Public Endpoints
```
GET /api/faqs                    # FAQ content
GET /api/country-list            # Country data
GET /api/tags                    # Available tags
GET /api/post/detail/{id}        # Post details
GET /api/test                    # Health check
```

### Payment Endpoints
```
POST /api/paypal/create-order    # Create PayPal order
POST /api/paypal/capture-order   # Capture payment
GET  /api/paypal/success         # Success callback
GET  /api/paypal/cancel          # Cancel callback
```

## Frontend Architecture

### Web Application (React)

#### Component Hierarchy
```
App
├── ErrorBoundary
├── AuthProvider
├── Router
│   ├── LandingPage
│   ├── LoginPage
│   ├── RegisterPage
│   ├── VerifyOtpPage
│   ├── ForgotPasswordPage
│   └── SwipePage (Protected)
└── DebugPanel (Development)
```

#### State Management
- **Authentication State**: React Context API
- **API Configuration**: Environment variables
- **User Session**: Local storage with JWT tokens
- **Form State**: Local component state
- **Error Handling**: Centralized error boundaries

#### Key Components
- `ErrorBoundary`: Production error handling
- `ProtectedRoute`: Authentication guards
- `DebugPanel`: Development debugging tools
- `SwipeCard`: Tinder-like donation interface

### Mobile Application (React Native)

#### Navigation Structure
```
RootStack
├── AuthStack
│   ├── GetStartScreen
│   ├── LoginScreen
│   ├── RegisterScreen
│   └── ForgotPasswordScreen
└── MainStack
    ├── DrawerNavigator
    │   └── TabNavigator
    │       ├── HomeScreen (Donor/Receiver)
    │       ├── NotificationScreen
    │       └── ProfileScreen
    └── ModalScreens
        ├── DonateScreen
        ├── ReceiverNeedForScreen
        └── SettingsScreen
```

#### State Management
- **Redux Store**: Centralized state management
- **Redux Persist**: Persistent storage
- **Async Storage**: Local data caching
- **Secure Store**: Sensitive data storage

#### Key Features
- **Responsive Design**: Adaptive layouts for different screen sizes
- **Offline Support**: Cached data and offline indicators
- **Push Notifications**: Firebase integration
- **Image Handling**: Expo Image Picker
- **Payment Integration**: PayPal SDK

## Security Architecture

### Authentication & Authorization
- **JWT Tokens**: Laravel Sanctum for API authentication
- **Role-Based Access**: Donor, Receiver, Admin roles
- **Token Expiration**: Configurable token lifetime
- **Device Management**: Device token tracking for notifications

### Data Protection
- **Password Hashing**: Laravel's bcrypt hashing
- **Input Validation**: Server-side validation with Laravel
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **XSS Protection**: Output escaping and CSP headers
- **CSRF Protection**: Laravel's built-in CSRF tokens

### API Security
- **Rate Limiting**: 60 requests per minute per user/IP
- **CORS Configuration**: Cross-origin resource sharing
- **Request Validation**: Comprehensive input sanitization
- **Error Handling**: Secure error messages (no sensitive data)

### Payment Security
- **PayPal Integration**: Secure payment processing
- **Transaction Logging**: Complete audit trail
- **Fraud Prevention**: Amount validation and limits
- **PCI Compliance**: No direct card data storage

## Deployment Architecture

### Backend Deployment
```
Production Server
├── Web Server (Nginx/Apache)
├── PHP-FPM (PHP 8.1+)
├── MySQL Database
├── Redis Cache (Optional)
└── File Storage
    ├── Public Assets
    ├── User Uploads
    └── Application Logs
```

### Frontend Deployment

#### Web Application
- **Platform**: Vercel
- **Build Process**: Vite production build
- **CDN**: Global content delivery
- **Environment**: Production environment variables
- **Monitoring**: Vercel analytics and error tracking

#### Mobile Application
- **Platform**: Expo Application Services
- **Build Process**: EAS Build
- **Distribution**: App Store / Google Play
- **Updates**: Over-the-air updates
- **Analytics**: Expo Analytics

### Environment Configuration
```
Development
├── Local Database
├── Local File Storage
├── Development API Keys
└── Debug Logging

Staging
├── Staging Database
├── Cloud Storage (S3)
├── Staging API Keys
└── Limited Logging

Production
├── Production Database
├── Cloud Storage (S3/CloudFront)
├── Production API Keys
└── Error Logging Only
```

## Performance Considerations

### Backend Optimization
- **Database Indexing**: Optimized queries with proper indexes
- **Caching Strategy**: Redis for frequently accessed data
- **Query Optimization**: Eager loading and relationship optimization
- **File Optimization**: Image compression and CDN delivery
- **Queue Processing**: Background job processing for heavy operations

### Frontend Optimization
- **Code Splitting**: Lazy loading of components and routes
- **Bundle Optimization**: Tree shaking and minification
- **Image Optimization**: WebP format and responsive images
- **Caching Strategy**: Browser caching and service workers
- **Performance Monitoring**: Core Web Vitals tracking

### Mobile Optimization
- **Bundle Size**: Code splitting and asset optimization
- **Image Caching**: Efficient image loading and caching
- **Network Optimization**: Request batching and caching
- **Memory Management**: Proper component lifecycle management
- **Battery Optimization**: Efficient background processing

## Error Handling & Logging

### Error Categories
1. **Authentication Errors**: Invalid credentials, expired tokens
2. **Validation Errors**: Invalid input data
3. **Database Errors**: Connection issues, constraint violations
4. **Payment Errors**: Transaction failures, gateway errors
5. **Network Errors**: API timeouts, connectivity issues
6. **System Errors**: Server errors, unexpected exceptions

### Logging Strategy
- **Error Logging**: Structured error logs with context
- **Performance Logging**: Response times and resource usage
- **Security Logging**: Authentication attempts and suspicious activity
- **Business Logging**: User actions and transaction events
- **Debug Logging**: Development-only detailed logging

### Error Response Format
```json
{
  "response": false,
  "message": ["Error description"],
  "errors": {
    "field": ["Validation error"]
  },
  "status": 400
}
```

## Testing Strategy

### Backend Testing
- **Unit Tests**: PHPUnit for individual components
- **Feature Tests**: API endpoint testing
- **Integration Tests**: Database and external service testing
- **Performance Tests**: Load testing and benchmarking

### Frontend Testing
- **Unit Tests**: Jest for component testing
- **Integration Tests**: API integration testing
- **E2E Tests**: User workflow testing
- **Visual Tests**: UI component regression testing

### Mobile Testing
- **Unit Tests**: Jest for React Native components
- **Integration Tests**: API and native module testing
- **Device Testing**: Cross-platform compatibility testing
- **Performance Testing**: Memory and battery usage testing

### Test Coverage Requirements
- **Backend**: Minimum 80% code coverage
- **Frontend**: Minimum 70% code coverage
- **Critical Paths**: 100% coverage for payment and authentication
- **API Endpoints**: 100% endpoint testing coverage

### Continuous Integration
- **Automated Testing**: GitHub Actions workflow
- **Code Quality**: ESLint, PHPStan, and SonarQube
- **Security Scanning**: Dependency vulnerability scanning
- **Performance Monitoring**: Automated performance regression testing

## [UPDATE: June 2024]

### **Frontend Reference & Web Version Development**
- The **React Native frontend** is now the **primary reference for all UI/UX and flows**. The web version will be built to match the mobile app's look, feel, and logic.
- The previous bluWeb (React + Tailwind) implementation is deprecated for design reference and will not be used as a UI/UX source.

### **Donor Flow (as implemented in React Native)**
1. **Login Screen:**
   - Branding, role toggle ("I can Help" / "I need Help"), email/password, social login.
2. **Post-login Landing:**
   - Donor lands directly on the donation opportunity screen.
   - Full-screen background image, receiver info, tags, amount, description.
   - Action buttons: Skip (red X), Details (document), Donate (blue hand).
3. **Donation Modal/Card:**
   - Triggered by Donate button.
   - Shows receiver info, target amount, date/time, and donation amount selector.
   - **Planned Change:** Replace plus/minus buttons with a draggable slider (dot/handle) for donation amount selection, capped at $50.
   - Send and Cancel buttons.
4. **Navigation:**
   - Bottom tab bar: Profile, Home, Donate, Notifications.

### **Design & Asset Reference**
- All UI, assets, and flows are to be referenced from the React Native frontend (screenshots and codebase).
- Figma or screenshots may be used to supplement the reference, but the mobile app is the source of truth.

## [UPDATE: Receiver Profile Page]
- The receiver profile page features a large profile pic, receiver name, editable profile info (email), and a button to edit PayPal details.
- The page includes a section for managing the receiver's post(s), with edit options.
- Accessible from the bottom navigation bar, with a header containing the logo and hamburger menu.

## [UPDATE: Updates/Notifications Page]
- The updates/notifications page displays a list of update cards with images, titles, descriptions, and dates, using alternating background colors for clarity.
- It is accessible from the bottom navigation bar and features a header with the logo and menu.
- **Planned Feature:** This page will also be used to show thank-you messages and impact stories from receivers, providing feedback to donors on how their contributions helped.

## [UPDATE: Donor Profile Page]
- The donor profile page features a large avatar, donor name, editable email and phone fields (with icons), and account actions (Edit Profile, Delete account, Log Out).
- The page displays the join date and is accessible from the bottom navigation bar.
- The layout and actions are consistent with the receiver profile page, ensuring a unified user experience.

## [UPDATE: Payment Flow and Donor History]
- After selecting the donation amount, the donor is taken to the PayPal payment page for secure payment processing.
- Upon successful payment, the donor is returned to the app and shown a confirmation/thank-you screen.
- A Donor History page is planned to track all past donations, confirmation statuses, and thank-you messages from receivers. This page will be accessible from the donor profile or main navigation.

## [UPDATE: Role Selection and Post-Login Flow]
- The login screen includes a role selection toggle ("I can Help" for donors, "I need Help" for receivers).
- The selected role determines the post-login flow:
  - Donors are directed to the donation flow (as documented).
  - Receivers are directed to a separate receiver section (to be mapped and documented as receiver flows are added).

## [UPDATE: Receiver Home/Dashboard Page]
- The receiver home/dashboard page displays the user's profile pic, current balance (with wallet icon), an option to share the app, and an FAQ section with expandable questions.
- The group photo is not needed; use the profile pic instead.
- The page includes a header with logo/menu and bottom tab navigation (Profile, Home, Notifications).

## [UPDATE: Hamburger Menu (Drawer Navigation)]
- Both receiver and donor sides feature a hamburger menu (drawer navigation) accessible from the top right.
- The menu includes navigation options such as Home, Profile, Balance (or Donation History for donors), Notifications, and Log Out.
- The menu provides quick access to all main sections and ensures a consistent navigation experience for both user types.

## [UPDATE: Receiver Balance Page]
- The receiver balance page displays the user's available balance, a withdraw button, and tabs for viewing received and withdrawn funds.
- The page includes a header with logo/menu and bottom tab navigation.
- Ignore the error popup, as it is not part of the intended UI.

## [UPDATE: Logout Confirmation Modal]
- A logout confirmation modal is shown whenever the user taps logout (from either donor or receiver side).
- The modal overlays the current screen and asks the user to confirm logout, with options to Log Out or Cancel.

## [UPDATE: Loading Spinner Overlay]
- A loading spinner overlay is shown on the profile page (and other pages) when data is being fetched or an action is in progress, providing clear feedback to the user.

## [UPDATE: Dynamic Hardship Tags & Region/Country Selection]
- Recipients can add/edit up to 3 hardship tags in their profile. Tags are dynamic, public, and visible to donors. No emoji or special characters allowed.
- Recipients must select their country and region in their profile (dropdowns/searchable selects).
- Donors can filter and sort recipients by country, region, and hardship tags. These fields are visible on recipient cards/profiles.
- Data model: Recipients have fields for country, region, and up to 3 tags. Tags are deduplicated and suggested as users type.
- API: Endpoints for tag/region suggestions, recipient filtering, and tag creation/association.
- Suggestions for future: admin moderation of tags/regions, analytics on tag usage, privacy options for region/country.

## [UPDATE: Recipient Social Account Verification & Donor Transparency]
- Recipients must verify their account by linking one social network (Facebook, X, Google, LinkedIn, etc.), which must be at least 1 month old. Profile picture is imported from the social account.
- Recipients may add a second account for login only (not for verification) and can update/change their linked social account later.
- Donors can see which network(s) were used for verification and view recipient's social profile(s) before donating (external links).
- Manual review process for flagged/suspicious accounts is required.

## [UPDATE: Donation Impact Visualization (Future Feature)]
- As donors adjust the donation amount, dynamic images/clipart/emoji are shown to represent what that amount can buy in the recipient's country.
- Show equivalent value in the donor's country (currency conversion and purchasing power parity).
- Technical notes: Requires real-time or regularly updated data on cost-of-living and exchange rates.
- Potential data sources: World Bank, Numbeo, OECD, or custom APIs.

---

**NOTE:** All features and requirements in this document are to be implemented as described, regardless of current backend status. Backend scan is for integration understanding only, not for limiting or altering planned features.

---

**Document Version**: 1.0  
**Last Updated**: December 2024  
**Maintained By**: Development Team  
**Review Schedule**: Quarterly 