# Blu Charity - High-Level Changes & Feature Development

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Business Requirements](#business-requirements)
3. [Major Features & Milestones](#major-features--milestones)
4. [Technical Decisions & Trade-offs](#technical-decisions--trade-offs)
5. [Integration Points](#integration-points)
6. [Performance Benchmarks](#performance-benchmarks)
7. [Security Considerations](#security-considerations)
8. [Deployment Strategy](#deployment-strategy)
9. [Future Roadmap](#future-roadmap)

## 2024-12-21 - Homepage Implementation & Deployment Protocol Enforcement

- Implemented a real homepage for the Next.js app (`bluweb-next`) with links to Login, Register, Donate, and Donation History
- Strictly audited Vercel deployments to ensure only `bluweb-next` is deployed and mapped to the correct repo/branch
- Verified all static assets, routing, and user flows are accessible from the homepage
- Documented troubleshooting steps for deployment confusion and project misconfiguration
- Confirmed production deployment matches latest GitHub commit and homepage is no longer the default template

## Executive Summary

Blu Charity is a comprehensive donation platform designed to connect donors with recipients in need through a modern, user-friendly interface. The platform consists of three main applications: a Laravel backend API, a React web application, and a React Native mobile application.

### Project Vision
- **Mission**: Facilitate direct connections between donors and recipients
- **Vision**: Create a transparent, secure, and efficient donation ecosystem
- **Values**: Trust, transparency, accessibility, and impact

### Key Achievements
- ✅ Complete authentication system with OTP verification
- ✅ PayPal payment integration for secure transactions
- ✅ Tinder-like swipe interface for donor discovery
- ✅ Cross-platform mobile application
- ✅ Admin dashboard for platform management
- ✅ Push notification system
- ✅ Comprehensive API documentation

## Business Requirements

### Core User Stories

#### Donor Personas
1. **Primary Donor**: "I want to easily discover and donate to people in need"
   - Browse recipient stories through swipe interface
   - Make secure payments via PayPal
   - Track donation history
   - Receive notifications about impact

2. **Regular Donor**: "I want to manage my donations and see their impact"
   - View donation history and receipts
   - Update profile and preferences
   - Receive updates on recipients
   - Manage payment methods

#### Recipient Personas
1. **Story Creator**: "I want to share my story and receive help"
   - Create compelling donation posts
   - Upload photos and videos
   - Set funding goals
   - Track received donations

2. **Fund Manager**: "I want to manage my received funds"
   - View donation balance
   - Request withdrawals
   - Update bank details
   - Track withdrawal status

#### Admin Personas
1. **Platform Manager**: "I want to ensure platform integrity and user safety"
   - Moderate user content
   - Approve withdrawal requests
   - Manage user accounts
   - Monitor platform metrics

### Functional Requirements

#### Authentication & Security
- Multi-factor authentication (OTP)
- Role-based access control
- Secure payment processing
- Data encryption and privacy protection

#### User Management
- User registration and profile management
- Social login integration
- Account verification and moderation
- Password reset functionality

#### Content Management
- Story creation and editing
- Image and media upload
- Content moderation and approval
- Tag-based categorization

#### Payment Processing
- PayPal integration for donations
- Transaction tracking and logging
- Withdrawal request management
- Financial reporting

#### Communication
- Push notifications
- Email notifications
- In-app messaging (future)
- Admin-user communication

## Major Features & Milestones

### Phase 1: Foundation (Completed)
**Timeline**: Q3-Q4 2024

#### Backend Development
- ✅ Laravel 10 API framework setup
- ✅ Database schema design and migrations
- ✅ User authentication system
- ✅ Role-based authorization
- ✅ File upload system
- ✅ Basic CRUD operations

#### Web Application
- ✅ React application setup with Vite
- ✅ Authentication pages (login, register, OTP)
- ✅ Basic donor interface
- ✅ Responsive design with Tailwind CSS
- ✅ Error handling and validation

#### Mobile Application
- ✅ React Native setup with Expo
- ✅ Navigation structure
- ✅ Authentication flow
- ✅ Basic UI components

### Phase 2: Core Features (Completed)
**Timeline**: Q4 2024

#### Payment Integration
- ✅ PayPal API integration
- ✅ Payment flow implementation
- ✅ Transaction logging
- ✅ Success/failure handling

#### User Experience
- ✅ Swipe interface for donors
- ✅ Story creation for recipients
- ✅ Profile management
- ✅ Notification system

#### Admin Dashboard
- ✅ User management interface
- ✅ Content moderation tools
- ✅ Withdrawal approval system
- ✅ Platform analytics

### Phase 3: Enhancement (Completed)
**Timeline**: Q1 2025

#### Advanced Features
- ✅ Enhanced security measures
- ✅ Performance optimization
- ✅ Advanced analytics
- ✅ Mobile app improvements

#### Integration & Testing
- ✅ Comprehensive testing suite (82.75% coverage)
- ✅ API documentation
- ✅ Deployment automation
- ✅ Monitoring and logging

### Phase 4: Production Deployment (Completed)
**Timeline**: Q1 2025

#### Next.js Web Application
- ✅ Complete Next.js 15 migration with app router
- ✅ TypeScript integration with strict type checking
- ✅ Comprehensive Jest test suite with 84.93% coverage
- ✅ ESLint and TypeScript error resolution
- ✅ Next.js Jest configuration for optimal testing
- ✅ Production-ready build process
- ✅ Vercel deployment preparation
- ✅ Cross-platform asset sharing with mobile app
- ✅ Modern UI/UX with Tailwind CSS
- ✅ Responsive design for all devices
- ✅ Error boundary and loading states
- ✅ SEO optimization and meta tags

## Recent Technical Improvements & Fixes (Q1 2025)

### Authentication System - Backend Registration Issue (2024-12-21)

#### Critical Issue Identified
- **Problem**: New user registrations and password resets not working
- **Evidence**: Old credentials work, new credentials fail with "Please check your credentials"
- **Root Cause**: Backend registration/password update endpoints failing silently
- **Frontend Status**: Login code working correctly, comprehensive debugging implemented

#### Debugging Protocol Implementation
- **API Request Logging**: Added detailed logging for all authentication endpoints
- **Response Analysis**: Implemented full request/response tracing
- **State Management**: Added logout process debugging
- **Evidence-Based Approach**: Following strict protocol to identify actual root cause

#### Debugging Code Added (To Be Removed Later)
**File: `lib/api.ts`**
- Added `console.log("🔍 Sending login request to:", ...)` in `authApi.login()`
- Added `console.log("🔍 Request body:", ...)` in `authApi.login()`
- Added `console.log("🌐 Making API request:", ...)` in `apiRequest()`
- Added `console.log("📤 Sending request...")` in `apiRequest()`
- Added `console.log("📥 Received response:", ...)` in `apiRequest()`
- Added `console.log("📡 Backend response status:", ...)` in `apiRequest()`
- Added `console.log("📡 Backend response data:", ...)` in `apiRequest()`
- Added `console.log("🔍 Validating login response...")` in `authApi.login()`
- Added `console.log("🔍 response.response:", ...)` in `authApi.login()`
- Added `console.log("🔍 response.data:", ...)` in `authApi.login()`
- Added `console.log("🔍 response.meta?.access_token:", ...)` in `authApi.login()`
- Added `console.log("❌ Login validation failed - missing required fields")` in `authApi.login()`
- Added `console.log("✅ Response OK - returning data")` in `apiRequest()`
- Added `console.log("❌ Response not OK - clearing auth if unauthorized")` in `apiRequest()`
- Added `console.log("❌ API request failed:", ...)` in `apiRequest()`
- Added `console.log("📝 Starting registration process...")` in `authApi.register()`
- Added `console.log("📝 Registration data:", ...)` in `authApi.register()`
- Added `console.log("📝 Registration response:", ...)` in `authApi.register()`
- Added `console.log("✅ Registration successful")` in `authApi.register()`
- Added `console.log("❌ Registration failed")` in `authApi.register()`
- Added `console.log("🚪 Starting logout process...")` in `authApi.logout()`
- Added `console.log("📤 Calling backend logout endpoint...")` in `authApi.logout()`
- Added `console.log("✅ Backend logout successful")` in `authApi.logout()`
- Added `console.log("❌ Backend logout failed:", ...)` in `authApi.logout()`
- Added `console.log("🧹 Clearing local storage...")` in `authApi.logout()`
- Added `console.log("✅ Logout complete")` in `authApi.logout()`
- Added `console.log("🧹 Clearing storage keys:", ...)` in `storage.clear()`
- Added `console.log("🧹 Cleared ${key}: ...")` in `storage.clear()`
- Added `console.log("✅ All auth storage cleared")` in `storage.clear()`

**File: `hooks/useAuth.tsx`**
- Added `console.log("🚪 Starting useAuth logout...")` in `logout()`
- Added `console.log("🚪 Current state before logout:", ...)` in `logout()`
- Added `console.log("🚪 Calling authApi.logout()...")` in `logout()`
- Added `console.log("✅ authApi.logout() completed")` in `logout()`
- Added `console.log("❌ authApi.logout() error:", ...)` in `logout()`
- Added `console.log("🧹 Clearing useAuth state...")` in `logout()`
- Added `console.log("✅ useAuth logout complete")` in `logout()`

**File: `components/login-screen.tsx`**
- Added `id="email"` and `id="password"` attributes to input fields
- Added `htmlFor="email"` and `htmlFor="password"` attributes to labels
- Added `autoComplete="email"` and `autoComplete="current-password"` attributes

**Cleanup Required**: All debugging console.log statements must be removed after issue resolution

#### Technical Investigation
- **Registration Endpoint**: Added logging to `/api/auth/register` to trace user creation
- **Password Reset**: Added logging to password reset flow
- **Database Verification**: Need to confirm if new users are being saved
- **Backend Logs**: Require backend server logs to identify endpoint failures

#### Root Cause Identified and Fixed (2024-12-21)
**Database Migration Issues:**
- Empty migration file `2025_07_02_212202_add_reset_otp_columns_to_users_table.php` - Fixed
- Missing `search_id` and `joined_date` columns in users table - Fixed
- Missing `reset_otp` columns for password reset - Fixed

**Registration Controller Issues:**
- Missing required fields (`search_id`, `joined_date`) - Fixed
- No email verification handling - Fixed

**Login Controller Issues:**
- Blocking unverified users from login - Fixed
- Improved error messages for email verification - Fixed

**User Model Issues:**
- Missing fields in fillable array - Fixed
- Missing datetime casts - Fixed

#### Backend Fixes Applied
**File: `../backend/database/migrations/2025_07_02_212202_add_reset_otp_columns_to_users_table.php`**
- Added proper reset OTP columns to users table

**File: `../backend/app/Http/Controllers/Api/Auth/RegisterController.php`**
- Added `search_id` and `joined_date` fields to user creation
- Added proper Str import for search_id generation

**File: `../backend/app/Http/Controllers/Api/Auth/LoginController.php`**
- Fixed email verification check to provide better error messages
- Improved login flow for unverified users

**File: `../backend/app/Models/User.php`**
- Added missing fields to fillable array
- Added proper datetime casts for all date fields

**File: `../backend/scripts/fix-database.php`**
- Created database fix script to apply missing columns
- Script updates existing users with missing fields

### Authentication System - Registration Flow Fix (Previous)

#### Critical Bug Resolution
- **Registration Form Validation**: Fixed "name field required" error that persisted despite filled fields
- **Root Cause Analysis**: Identified missing `RegisterController` in frontend workspace and authentication architecture mismatch
- **Solution Implementation**: Replaced current registration system with reference implementation using HTML5 validation and proper API integration
- **Architecture Alignment**: Updated authentication system to use `AuthProvider` context pattern with role-based login

#### Technical Details
- **Frontend Validation**: Switched from JavaScript validation to HTML5 `required` attributes for name, email, phone fields
- **Backend Integration**: Confirmed `RegisterController` performs server-side validation, user creation, OTP generation, and email sending
- **API Service**: Updated `api.ts` to match reference implementation for proper registration flow
- **Authentication Context**: Implemented `AuthProvider` wrapper in layout for consistent auth state management

#### Impact Assessment
- **User Experience**: Registration now works correctly without false validation errors
- **Code Quality**: Simplified validation logic reduces complexity and improves maintainability
- **System Reliability**: Proper backend integration ensures data integrity and security

### Next.js Web Application - Production Readiness

#### Build & Deployment Fixes
- **Babel/SWC Conflict Resolution**: Removed all Babel configuration, implemented Next.js Jest transformer for optimal compatibility
- **TypeScript Strict Mode**: Implemented comprehensive type safety across all components and API services
- **ESLint Compliance**: Fixed all unused variables, implicit any types, and code quality issues
- **Production Build**: Achieved zero-error builds with full TypeScript and ESLint compliance

#### Testing Infrastructure
- **Jest Configuration**: Next.js Jest transformer setup for optimal compatibility without Babel conflicts
- **Test Coverage**: Achieved 84.93% statement coverage across all application flows
- **Mock Strategy**: Comprehensive mocking of Next.js router, API services, and static assets
- **Test Flows**: Complete coverage of login, registration, donation, profile, and donation history

#### Component Improvements
- **SwipeCard Component**: Fixed React key warnings and improved animation performance
- **Error Handling**: Implemented proper error boundaries and user-friendly error messages
- **Type Safety**: Added proper TypeScript interfaces for all API responses and component props
- **Performance**: Optimized component rendering and state management

#### Code Quality
- **TypeScript Interfaces**: Created comprehensive type definitions for Recipient, Donation, User, and API responses
- **Error Boundaries**: Implemented proper error handling for all async operations
- **Form Validation**: Enhanced form handling with proper TypeScript event types
- **State Management**: Improved state typing and error handling patterns

## Technical Decisions & Trade-offs

### Framework Selection

#### Backend: Laravel 10
**Decision**: Chose Laravel over Node.js/Express
**Rationale**:
- ✅ Rapid development with built-in features
- ✅ Excellent ORM (Eloquent) for database operations
- ✅ Built-in authentication and authorization
- ✅ Strong ecosystem and community support
- ✅ Built-in API resource classes
- ❌ Higher memory usage compared to Node.js
- ❌ Slower execution for CPU-intensive tasks

#### Frontend: React 18
**Decision**: Chose React over Vue.js/Angular
**Rationale**:
- ✅ Large ecosystem and community
- ✅ Excellent for component-based architecture
- ✅ Strong TypeScript support
- ✅ Rich ecosystem of libraries
- ✅ Easy to find developers
- ❌ Steeper learning curve for beginners
- ❌ More complex state management

#### Mobile: React Native + Expo
**Decision**: Chose React Native over Flutter/Native
**Rationale**:
- ✅ Code sharing with web application
- ✅ Faster development with Expo
- ✅ Large ecosystem of libraries
- ✅ Over-the-air updates
- ❌ Performance limitations for complex animations
- ❌ Platform-specific code for advanced features

### Database Design

#### MySQL Selection
**Decision**: Chose MySQL over PostgreSQL
**Rationale**:
- ✅ Better Laravel integration
- ✅ Simpler setup and maintenance
- ✅ Sufficient for current scale
- ✅ Cost-effective hosting options
- ❌ Less advanced features compared to PostgreSQL
- ❌ Limited JSON support

#### Schema Design Decisions
- **Soft Deletes**: Implemented for user data preservation
- **Polymorphic Relationships**: Used for notifications
- **Enum Fields**: Used for role and status fields
- **Nullable Fields**: Strategic use for optional data

### Payment Integration

#### PayPal Selection
**Decision**: Chose PayPal over Stripe
**Rationale**:
- ✅ Global recognition and trust
- ✅ Built-in fraud protection
- ✅ Multiple payment methods
- ✅ Lower transaction fees for charity
- ❌ Less developer-friendly API
- ❌ Limited customization options

### State Management

#### Web: React Context API
**Decision**: Chose Context API over Redux
**Rationale**:
- ✅ Built into React
- ✅ Simpler setup and maintenance
- ✅ Sufficient for current complexity
- ✅ No additional dependencies
- ❌ Less powerful than Redux for complex state
- ❌ No built-in dev tools

#### Mobile: Redux + Redux Persist
**Decision**: Chose Redux over Context API for mobile
**Rationale**:
- ✅ Better performance for complex state
- ✅ Built-in persistence
- ✅ Better debugging tools
- ✅ More predictable state updates
- ❌ More boilerplate code
- ❌ Steeper learning curve

## Integration Points

### External Services

#### PayPal API
- **Purpose**: Payment processing
- **Integration**: REST API with webhooks
- **Security**: OAuth 2.0 authentication
- **Error Handling**: Comprehensive retry logic
- **Monitoring**: Transaction logging and alerts

#### Firebase Cloud Messaging
- **Purpose**: Push notifications
- **Integration**: HTTP v1 API
- **Security**: Service account authentication
- **Features**: Topic-based messaging
- **Monitoring**: Delivery tracking

#### Email Services
- **Purpose**: Transactional emails
- **Provider**: Laravel Mail (configurable)
- **Templates**: Blade templates with localization
- **Tracking**: Email delivery and open rates

### Internal Integrations

#### API Gateway
- **Authentication**: Laravel Sanctum
- **Rate Limiting**: 60 requests/minute per user
- **CORS**: Cross-origin resource sharing
- **Validation**: Request validation middleware
- **Documentation**: Swagger/OpenAPI

#### File Storage
- **Local Storage**: Development environment
- **Cloud Storage**: Production (AWS S3/CloudFront)
- **Image Processing**: Automatic resizing and optimization
- **Security**: Signed URLs for private files

#### Caching Layer
- **Redis**: Session and data caching
- **Database**: Query result caching
- **CDN**: Static asset delivery
- **Browser**: HTTP caching headers

## Performance Benchmarks

### Backend Performance

#### API Response Times
- **Authentication**: < 200ms
- **User Profile**: < 150ms
- **Post Listing**: < 300ms
- **Payment Processing**: < 2s
- **File Upload**: < 5s (1MB file)

#### Database Performance
- **Query Optimization**: Indexed on frequently queried fields
- **Connection Pooling**: Optimized for concurrent requests
- **Caching Strategy**: Redis for session and query caching
- **Load Testing**: 1000 concurrent users supported

### Frontend Performance

#### Web Application
- **First Contentful Paint**: < 1.5s
- **Largest Contentful Paint**: < 2.5s
- **Cumulative Layout Shift**: < 0.1
- **First Input Delay**: < 100ms
- **Bundle Size**: < 500KB (gzipped)

#### Mobile Application
- **App Launch Time**: < 3s
- **Screen Navigation**: < 200ms
- **Image Loading**: < 1s (cached)
- **Offline Functionality**: Core features available
- **Memory Usage**: < 100MB

### Scalability Targets

#### Current Capacity
- **Concurrent Users**: 1,000
- **Daily Transactions**: 10,000
- **Storage**: 100GB
- **Bandwidth**: 1TB/month

#### Growth Projections
- **6 Months**: 5,000 concurrent users
- **1 Year**: 20,000 concurrent users
- **2 Years**: 100,000 concurrent users

## Security Considerations

### Authentication & Authorization

#### Multi-Factor Authentication
- **OTP Verification**: Required for registration
- **Email Verification**: Required for account activation
- **Phone Verification**: Optional but recommended
- **Session Management**: JWT tokens with expiration

#### Role-Based Access Control
- **Donor Role**: Browse and donate
- **Receiver Role**: Create posts and withdraw funds
- **Admin Role**: Platform management
- **Permission System**: Granular permissions

### Data Protection

#### Encryption
- **Data at Rest**: Database encryption
- **Data in Transit**: TLS 1.3
- **Sensitive Data**: Additional encryption layer
- **API Keys**: Environment variable storage

#### Privacy Compliance
- **GDPR Compliance**: Data processing and storage
- **Data Retention**: Configurable retention policies
- **User Consent**: Explicit consent collection
- **Data Portability**: Export functionality

### Payment Security

#### PCI Compliance
- **No Card Data Storage**: All payment data handled by PayPal
- **Secure Communication**: Encrypted API calls
- **Transaction Logging**: Complete audit trail
- **Fraud Prevention**: Amount limits and validation

#### Transaction Security
- **Amount Validation**: Server-side validation
- **Duplicate Prevention**: Transaction deduplication
- **Refund Handling**: Secure refund process
- **Dispute Resolution**: Clear dispute procedures

### Application Security

#### Input Validation
- **Server-Side Validation**: Laravel validation rules
- **SQL Injection Prevention**: Eloquent ORM
- **XSS Protection**: Output escaping
- **CSRF Protection**: Token-based protection

#### Vulnerability Management
- **Dependency Scanning**: Automated vulnerability detection
- **Security Updates**: Regular dependency updates
- **Penetration Testing**: Quarterly security assessments
- **Incident Response**: Security incident procedures

## Deployment Strategy

### Environment Strategy

#### Development Environment
- **Local Development**: Docker containers
- **Database**: Local MySQL instance
- **File Storage**: Local filesystem
- **API Keys**: Development credentials
- **Logging**: Verbose debug logging

#### Staging Environment
- **Hosting**: Cloud platform (AWS/GCP)
- **Database**: Managed MySQL service
- **File Storage**: Cloud storage (S3)
- **API Keys**: Staging credentials
- **Monitoring**: Basic monitoring and alerting

#### Production Environment
- **Hosting**: High-availability cloud setup
- **Database**: Managed MySQL with read replicas
- **File Storage**: CDN-backed cloud storage
- **API Keys**: Production credentials
- **Monitoring**: Comprehensive monitoring and alerting

### Deployment Pipeline

#### Continuous Integration
- **Code Quality**: ESLint, PHPStan, SonarQube
- **Automated Testing**: Unit, integration, and E2E tests
- **Security Scanning**: Dependency vulnerability scanning
- **Performance Testing**: Automated performance regression testing

#### Continuous Deployment
- **Automated Builds**: GitHub Actions workflows
- **Environment Promotion**: Staging → Production
- **Rollback Strategy**: Quick rollback capabilities
- **Blue-Green Deployment**: Zero-downtime deployments

### Monitoring & Observability

#### Application Monitoring
- **Error Tracking**: Sentry integration
- **Performance Monitoring**: APM tools
- **User Analytics**: User behavior tracking
- **Business Metrics**: Key performance indicators

#### Infrastructure Monitoring
- **Server Monitoring**: CPU, memory, disk usage
- **Database Monitoring**: Query performance and connections
- **Network Monitoring**: Response times and availability
- **Security Monitoring**: Intrusion detection and alerts

## Future Roadmap

### Q1 2025: Platform Enhancement

#### Advanced Features
- **Real-time Chat**: Donor-recipient communication
- **Video Stories**: Enhanced content creation
- **Social Sharing**: Integration with social media
- **Advanced Analytics**: Detailed impact reporting

#### Technical Improvements
- **Performance Optimization**: Database and API optimization
- **Mobile App Enhancement**: Advanced features and UI improvements
- **Security Hardening**: Additional security measures
- **Testing Coverage**: Comprehensive test suite

### Q2 2025: Scale & Growth

#### Platform Scaling
- **Microservices Architecture**: Service decomposition
- **Load Balancing**: Horizontal scaling
- **Database Sharding**: Data distribution
- **CDN Optimization**: Global content delivery

#### Feature Expansion
- **Multi-language Support**: Internationalization
- **Advanced Payment Methods**: Additional payment gateways
- **AI-powered Matching**: Smart donor-recipient matching
- **Blockchain Integration**: Transparent transaction tracking

### Q3-Q4 2025: Innovation & Expansion

#### New Platforms
- **Progressive Web App**: Enhanced web experience
- **Desktop Application**: Native desktop app
- **API Marketplace**: Third-party integrations
- **Mobile SDK**: Native mobile integrations

#### Advanced Capabilities
- **Machine Learning**: Predictive analytics
- **Voice Integration**: Voice-activated donations
- **AR/VR Support**: Immersive storytelling
- **IoT Integration**: Smart device donations

### Long-term Vision (2026+)

#### Platform Evolution
- **Global Expansion**: Multi-region deployment
- **Enterprise Features**: B2B donation management
- **Advanced Analytics**: AI-powered insights
- **Ecosystem Development**: Third-party developer platform

#### Technology Innovation
- **Blockchain Foundation**: Decentralized platform
- **AI Integration**: Intelligent platform features
- **Advanced Security**: Zero-trust architecture
- **Sustainability**: Green computing practices

## 2025-06-30 - Feature - Next.js Web App Asset & API Integration

- Migrated web frontend to Next.js (bluweb-next)
- Integrated all mobile app images into public/images/ for unified branding
- Scaffolded and connected pages: login, register, donate (swipe), profile, donation history
- All pages use backend API via apiService.js for authentication, donation, profile, and history
- Updated UI to use shared assets and match mobile app experience
- Updated documentation to reflect new architecture and integration

---

**Document Version**: 1.0  
**Last Updated**: December 2024  
**Maintained By**: Product & Engineering Teams  
**Review Schedule**: Monthly 