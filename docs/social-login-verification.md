# Social Login & Verification Design

_Last updated: 2025-10-14_

## Objectives

- Support OAuth-based authentication for **recipients** via Facebook, Instagram, and X, using the connected account as a verification source.
- Support OAuth-based authentication for **donors** via Facebook, Instagram, X, and Google.
- Persist social metadata (profile URL, avatar, follower counts, etc.), import it into the user profile when available, and surface the associated verification state to clients.
- Flag and communicate when a connected social account is less than one year old or when creation date is unavailable.
- Provide administrative visibility into linked accounts for manual review.

## Provider matrix

| Role       | Providers | Notes |
|------------|-----------|-------|
| Recipient  | Facebook, Instagram, X | Verification required; at least one provider must be linked. |
| Donor      | Facebook, Instagram, X, Google | Google is optional; other social logins mirror recipient behaviour. |

## Environment variables

Add the following keys to `.env` and populate with real provider credentials when they become available:

```env
SOCIAL_FACEBOOK_CLIENT_ID=
SOCIAL_FACEBOOK_CLIENT_SECRET=
SOCIAL_FACEBOOK_REDIRECT_URI=
SOCIAL_FACEBOOK_GRAPH_VERSION=

SOCIAL_INSTAGRAM_CLIENT_ID=
SOCIAL_INSTAGRAM_CLIENT_SECRET=
SOCIAL_INSTAGRAM_REDIRECT_URI=
SOCIAL_INSTAGRAM_GRAPH_VERSION=

SOCIAL_X_CLIENT_ID=
SOCIAL_X_CLIENT_SECRET=
SOCIAL_X_REDIRECT_URI=

SOCIAL_GOOGLE_CLIENT_ID=
SOCIAL_GOOGLE_CLIENT_SECRET=
SOCIAL_GOOGLE_REDIRECT_URI=
```

### Local development defaults

The backend `.env.example` now ships with descriptive placeholder values so the stack boots without real OAuth apps:

```env
SOCIAL_FACEBOOK_CLIENT_ID=fb-client-id-demo
SOCIAL_FACEBOOK_CLIENT_SECRET=fb-client-secret-demo
SOCIAL_FACEBOOK_REDIRECT_URI=http://localhost:3000/auth/social/callback
SOCIAL_FACEBOOK_GRAPH_VERSION=v24.0

SOCIAL_INSTAGRAM_CLIENT_ID=ig-client-id-demo
SOCIAL_INSTAGRAM_CLIENT_SECRET=ig-client-secret-demo
SOCIAL_INSTAGRAM_REDIRECT_URI=http://localhost:3000/auth/social/callback
SOCIAL_INSTAGRAM_GRAPH_VERSION=v24.0

SOCIAL_X_CLIENT_ID=x-client-id-demo
SOCIAL_X_CLIENT_SECRET=x-client-secret-demo
SOCIAL_X_REDIRECT_URI=http://localhost:3000/auth/social/callback

SOCIAL_GOOGLE_CLIENT_ID=google-client-id-demo.apps.googleusercontent.com
SOCIAL_GOOGLE_CLIENT_SECRET=google-client-secret-demo
SOCIAL_GOOGLE_REDIRECT_URI=http://localhost:3000/auth/social/callback
```

Replace these with production credentials before running end-to-end OAuth flows with real provider apps.

### Third-party prerequisites

- Register separate apps for web/mobile clients where required.
- Store client IDs and secrets in environment variables (`SOCIAL_{PROVIDER}_CLIENT_ID`, `SOCIAL_{PROVIDER}_CLIENT_SECRET`, `SOCIAL_{PROVIDER}_REDIRECT_URI`).
- Undergo provider app reviews for scopes beyond basic profile data (Meta App Review, X elevated plan, Google OAuth consent publishing).
- Terms of Service / Privacy Policy URLs must be shared with every provider.

#### Facebook specifics

- In the Meta App Dashboard, open **Facebook Login → Settings** and enable both **Client OAuth Login** and **Web OAuth Login**.
- Under **Valid OAuth Redirect URIs**, add every environment exactly as it will appear in the browser (no query strings or trailing slashes). At minimum include:
  - `http://localhost:3000/auth/social/callback` (local development)
  - `https://blu.gives/auth/social/callback` (production web)
  - `https://www.blu.gives/auth/social/callback` (if the `www` alias is live)
  - `https://staging.blu.gives/auth/social/callback` (or any staging domain you expose to testers)
- In **Settings → Basic**, add your domains (for example `blu.gives`, `www.blu.gives`) to **App Domains** and set the **Website URL** to the corresponding site (`https://blu.gives`).
- Save the changes and, if you rotate secrets, remember to redeploy with updated `SOCIAL_FACEBOOK_*` values followed by `php artisan config:clear`.
- The Laravel Socialite driver is now configured via `SOCIAL_FACEBOOK_GRAPH_VERSION`. Set it to the version Meta mandates (currently `v24.0`) so backend requests line up with your app dashboard.

#### Instagram specifics

- Instagram Basic Display also requires you to track Meta’s rolling version. Set `SOCIAL_INSTAGRAM_GRAPH_VERSION` to the current release (`v24.0` today) so the backend calls `https://graph.instagram.com/v24.0/...`.
- If you enable the Instagram Test Users feature, add the same redirect URIs under **Instagram Basic Display → Client OAuth Settings**.
- Meta sometimes delays provisioning of Basic Display testers—invite real accounts under **Roles → Instagram Testers** and have each user accept from the Instagram app if the tester wizard is unavailable.

#### X (Twitter) specifics

- Create a project + app in the [X Developer Portal](https://developer.twitter.com/) with Elevated access so email and follower metadata can be returned.
- Under **User authentication settings**, enable OAuth 2.0 and OAuth 1.0a. Set the callback URL to your frontend handler (for example `https://blu.gives/auth/social/callback`) and add your local URL (`http://localhost:3000/auth/social/callback`) while developing. Twitter requires an **exact** match, so omit any query parameters when configuring the callback.
- Record the **Client ID**, **Client Secret**, and (if using OAuth 1.0a) the **API Key** and **API Key Secret**; the Socialite Twitter driver will use the OAuth 1.0a credentials while the client ID/secret power OAuth 2.0 scoped calls.
- Add the following scopes/permissions: `tweet.read`, `users.read`, and request **Read** access; enable the "Request email address" toggle so we can fetch the user's email via the `include_email=true` parameter.
- Update `.env` in every environment with `SOCIAL_X_CLIENT_ID`, `SOCIAL_X_CLIENT_SECRET`, and `SOCIAL_X_REDIRECT_URI`. Remember to clear the config cache (`php artisan config:clear`) after deploying.

## Data model changes

### `user_socials` table

Add the following columns:

| Column | Type | Description |
|--------|------|-------------|
| `provider` | enum(`facebook`,`instagram`,`x`,`google`) | Replaces/validates the existing `service` string. |
| `provider_user_id` | string | Canonical provider user ID (rename of `social_id`). |
| `username` | string nullable | Handle or username from the provider. |
| `profile_url` | string nullable | Public profile URL. |
| `avatar_url` | string nullable | Highest resolution profile image URL. |
| `account_created_at` | timestamp nullable | Parsed account creation timestamp, when exposed by provider. |
| `followers_count` | unsigned integer nullable | Snapshot at sync time. |
| `raw_payload` | json nullable | Store provider payload for audit/debug. |
| `last_synced_at` | timestamp nullable | Last successful metadata sync. |
| `is_primary` | boolean default false | Marks the account used for login. |

### `users` table additions

| Column | Type | Description |
|--------|------|-------------|
| `social_verified_at` | timestamp nullable | When a social account satisfied verification rules. |
| `social_verification_status` | enum(`pending`,`verified`,`needs_review`,`insufficient_data`) default `pending` | Drives frontend badge state. |
| `social_verification_notes` | text nullable | Optional manual notes for admin review. |

### New table: `social_verification_events`

| Column | Type |
|--------|------|
| `id` | bigIncrements |
| `user_id` | foreignId -> users |
| `user_social_id` | foreignId -> user_socials |
| `status` | enum(`pending`,`verified`,`rejected`) |
| `reason` | text nullable |
| `metadata` | json nullable |
| `created_at` / `updated_at` | timestamps |

Purpose: retain verification history, automated checks, and manual overrides.

## Backend API updates

### OAuth endpoints

```
POST /api/auth/social/{role}/{provider}/redirect
POST /api/auth/social/{role}/{provider}/callback
```

- `redirect` returns the provider authorization URL and a CSRF-protected `state` token.
- `callback` exchanges the authorization code for user data, stores/updates `user_socials`, imports profile data, and issues Sanctum tokens.
- Supported `{role}` values: `donor`, `recipient`.

### Account linking

```
POST   /api/profile/social/{provider}
DELETE /api/profile/social/{provider}
GET    /api/profile/social
```

- `POST` allows existing logged-in users to link additional providers.
- `DELETE` removes a linked provider (must keep at least one for recipients).
- `GET` returns linked providers, verification status, and account-age warnings.

### Admin endpoints

```
GET    /api/admin/users/{user}/social
PATCH  /api/admin/users/{user}/social/{userSocial}
```

- Provide moderators with the ability to inspect linked data and override verification status.

## Verification logic

1. **Metadata import:** After OAuth callback, use provider APIs (Graph API, Instagram Basic Display, X v2, Google People API) to fetch profile fields.
2. **Account age calculation:**
   - If `account_created_at` is available, compute `age_in_days`. Anything under 365 days sets `social_verification_status` to `needs_review` and stores `reason` in event log.
   - If unavailable, set status to `insufficient_data` and prompt manual verification.
3. **Recipient badge:**
   - Verified when at least one linked account is older than one year and fetch succeeded in the last 30 days.
4. **Sync job:** Schedule a nightly job to refresh `user_socials` metadata for verified users to detect account maturity over time.

## Frontend contract

`GET /api/profile/social` response shape:

```json
{
  "linked": [
    {
      "provider": "facebook",
      "username": "giveablu",
      "profile_url": "https://facebook.com/giveablu",
      "avatar_url": "https://...",
      "account_created_at": "2018-05-04T12:34:00Z",
      "age_in_days": 2700,
      "is_primary": true,
      "status": "verified",
      "last_synced_at": "2025-10-14T09:00:00Z"
    }
  ],
  "verification_status": "verified",
  "warnings": [
    "Account giveablu on X is newer than one year (120 days old)."
  ]
}
```

Frontend updates required:

- Registration flows offering provider buttons according to role.
- Profile settings showing linked accounts and verification badge.
- Tooltip/banner when accounts are too new or data is missing.

## Security & compliance

- Rotate provider secrets periodically and store them in server-side vaults.
- Encrypt `raw_payload` at rest when possible (Laravel casts + encryption key).
- Respect provider rate limits; backoff within the sync job.
- Log OAuth errors and provider responses to aid debugging while stripping PII beyond what is necessary.

## Open questions / follow-ups

- Should recipients be blocked from donation payouts until verification is complete?
- Do we need granular scopes per provider (e.g., Facebook Pages vs Profile)? Gather requirements from product/legal.
- What is the UX for recipients with no eligible social accounts? Consider manual verification fallback.
