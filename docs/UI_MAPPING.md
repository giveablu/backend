# UI_MAPPING.md

## Blu Charity – Mobile to Web UI Mapping

This document maps each key screen and component from the React Native mobile app to the planned web version. It includes screenshot references, component/screen names, asset file names, and special UI/UX notes. This ensures the web version matches the mobile experience as closely as possible.

---

## 1. Login Screen
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `DonorSignInScreen` (or similar)
- **Web Component:** `LoginPage` (planned)
- **Assets:**
  - Logo: `logo.png`
  - Google/Apple icons: `google.png`, `apple.png`
- **UI/UX Notes:**
  - Role toggle ("I can Help" / "I need Help")
    - Determines post-login flow: donors are taken to the donation flow, receivers are taken to a separate receiver section (to be mapped)
  - Email/password fields with icons
  - Social login buttons
  - Blue/white color scheme, rounded buttons

---

## 2. Donor Home / Donation Opportunity Screen
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `HomeScreen` or `DonateScreen`
- **Web Component:** `DonationOpportunityPage` (planned)
- **Assets:**
  - Post image: e.g., `testImage/v_1.jpg`
  - Tag icons: `IconsPNG/heart.png`, etc.
- **UI/UX Notes:**
  - Full-screen background image
  - Receiver name, tags, amount, description
  - Action buttons: Skip (red X), Details (document), Donate (blue hand)
  - Bottom tab navigation (Profile, Home, Donate, Notifications)

---

## 3. Donation Modal/Card
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `DonateScreen` (modal/card)
- **Web Component:** `DonationModal` or `DonationCard` (planned)
- **Assets:**
  - Post image: e.g., `testImage/v_1.jpg`
- **UI/UX Notes:**
  - Shows receiver info, target amount, date/time
  - **Planned Change:** Replace plus/minus buttons with a draggable slider (dot/handle) for donation amount selection, capped at $50
  - Send and Cancel buttons

---

## 4. Profile Screen
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `ProfileScreen`
- **Web Component:** `ProfilePage` (planned)
- **Assets:**
  - Avatar: `IconsPNG/user.png` or similar
- **UI/UX Notes:**
  - Editable profile info (name, email, phone)
  - Edit Profile button, Delete account (red), Log Out
  - Bottom tab navigation

---

## 5. Notifications/Updates List
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `NotificationScreen`
- **Web Component:** `NotificationsPage` (planned)
- **Assets:**
  - Post image: e.g., `testImage/v_1.jpg`
- **UI/UX Notes:**
  - List of updates, alternating background colors
  - Bottom tab navigation

---

## 6. Payment Screen (PayPal)
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `PayPalApi.js` (webview or SDK)
- **Web Component:** `PaymentPage` (planned)
- **Assets:**
  - PayPal logo (external)
- **UI/UX Notes:**
  - Standard PayPal login/checkout flow

---

## 7. Receiver Profile/Details Screen
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `ReceiverProfileScreen` or `ReceiverDetailsScreen`
- **Web Component:** `ReceiverProfilePage` (planned)
- **Assets:**
  - Profile image: (dynamic, user-uploaded or placeholder)
  - Tag icons: `IconsPNG/heart.png`, etc.
- **UI/UX Notes:**
  - Header with back arrow and "Details" title
  - Centered profile image with rounded corners
  - Tags row (charity, giveback, donation, etc.)
  - Large donation amount ($1.00)
  - Description text
  - Two large buttons: Donate (blue), Skip (white)
  - Light blue background

---

## 8. Updates / Notifications Page
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `NotificationScreen` or `UpdatesScreen`
- **Web Component:** `NotificationsPage` or `UpdatesPage` (planned)
- **Assets:**
  - Post image: e.g., `testImage/v_1.jpg`
- **UI/UX Notes:**
  - List of update cards, each with image, title ("Post Updated"), description, and date
  - Alternating background colors (white, light blue)
  - Header with logo and hamburger menu
  - Bottom tab navigation (Profile, Home, Notifications)
  - **Planned Feature:** This page will also display thank-you messages and impact stories from receivers after donations

---

## 9. Donor Profile Page
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `ProfileScreen` (Donor)
- **Web Component:** `DonorProfilePage` (planned)
- **Assets:**
  - Avatar: `IconsPNG/user.png` or similar
  - Email/phone icons: (SVG or PNG, as in codebase)
- **UI/UX Notes:**
  - Header with logo and hamburger menu
  - Large circular avatar, donor name
  - Editable fields: email, phone (with icons)
  - Edit Profile button (blue, rounded)
  - Account actions: Delete account (red), Log Out (blue)
  - Join date display
  - Bottom tab navigation (Profile, Home, Notifications)

---

## 10. Payment Page (PayPal)
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `PayPalApi.js` (webview or SDK)
- **Web Component:** `PaymentPage` (planned)
- **Assets:**
  - PayPal logo (external)
- **UI/UX Notes:**
  - Donor is taken here after selecting donation amount
  - Standard PayPal login/checkout flow
  - After successful payment, donor is returned to the app and shown a confirmation/thank-you screen

---

## 11. Donor History Page (Planned)
- **Screenshot Reference:** (to be provided)
- **Mobile Component:** (to be implemented)
- **Web Component:** `DonorHistoryPage` (planned)
- **Assets:**
  - Donation confirmation icons, receiver images, etc.
- **UI/UX Notes:**
  - Shows a list of past donations with confirmation status and thank-you messages
  - Accessible from the donor profile or main navigation

---

## 12. Receiver Home / Dashboard Page
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `ReceiverHomeScreen` or `ReceiverDashboardScreen`
- **Web Component:** `ReceiverHomePage` or `ReceiverDashboardPage` (planned)
- **Assets:**
  - Profile pic (user-uploaded or placeholder)
  - Wallet/balance icon: `IconsPNG/wallet.png` or similar
  - Share icon: `IconsPNG/share.png` or similar
- **UI/UX Notes:**
  - Header with logo and hamburger menu
  - Show current balance (wallet icon + amount)
  - Option to share the app (button with share icon)
  - FAQ section with expandable/collapsible questions
  - Bottom tab navigation (Profile, Home, Notifications)
  - Group photo is not needed; use profile pic instead

---

## 13. Hamburger Menu (Receiver Side)
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `ReceiverMenuDrawer` or similar
- **Web Component:** `ReceiverMenuDrawer` (planned)
- **UI/UX Notes:**
  - Drawer slides in from the left
  - Menu options: Home, Profile, Balance, Notification, Log Out
  - Logo at the top
  - Simple, clean design
  - A similar hamburger menu should exist for the donor side with corresponding navigation options (e.g., Home, Profile, Donation History, Notifications, Log Out)

---

## 14. Receiver Profile Page
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `ReceiverProfileScreen`
- **Web Component:** `ReceiverProfilePage` (planned)
- **Assets:**
  - Profile pic (user-uploaded or placeholder)
  - Email icon, edit icons, PayPal logo
- **UI/UX Notes:**
  - Header with logo and hamburger menu
  - Large circular profile pic, receiver name
  - Editable profile info (email, with edit icon)
  - Button to edit PayPal details (PayPal logo, outlined button)
  - Section for managing receiver's post(s), with edit icon
  - Bottom tab navigation (Profile, Home, Notifications)
  - **Loading spinner overlay is shown when data is being fetched or an action is in progress. This pattern should be used on other pages as well.**

---

## 15. Receiver Balance Page
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `ReceiverBalanceScreen`
- **Web Component:** `ReceiverBalancePage` (planned)
- **Assets:**
  - Wallet/balance icon: `IconsPNG/wallet.png` or similar
- **UI/UX Notes:**
  - Header with logo and hamburger menu
  - Shows available balance (large, bold)
  - Withdraw button (blue, rounded)
  - Tabs for 'Received' and 'Withdraw'
  - Ignore error popup (not part of intended UI)
  - Bottom tab navigation (Profile, Home, Notifications)

---

## 16. Logout Confirmation Modal
- **Screenshot Reference:** Provided (see above)
- **Mobile Component:** `LogoutModal` or similar
- **Web Component:** `LogoutModal` (planned)
- **UI/UX Notes:**
  - Modal overlays the current screen
  - Asks user to confirm logout: "Are you sure you want logout?"
  - Two buttons: Log Out (primary, blue), Cancel (secondary, outlined)
  - Should be shown on both donor and receiver sides whenever the user taps logout

---

## 17. Dynamic Hardship Tags & Region/Country Selection
- **Recipient Side:**
  - Recipients can add/edit up to 3 hardship tags (e.g., "autism", "poverty") in their profile.
  - Tags are dynamic: as users type, existing tags are suggested; new tags are added to the system and become available for future suggestions.
  - Tags are public and visible to donors.
  - Tags are limited to 3 per recipient; no emoji or special characters for now.
  - Recipients must select their country and region (dropdowns/searchable selects) in their profile.
- **Donor Side:**
  - Donors can filter and sort recipients by country, region, and hardship tags (multi-select or chips UI).
  - Tags, country, and region are visible on recipient cards/profiles.
- **Suggestions for future:**
  - Admin moderation of tags/regions, analytics on tag usage, privacy options for region/country.

---

## 18. Recipient Social Account Verification & Donor Transparency
- **Recipient Side:**
  - Recipients must verify their account by linking one social network (Facebook, X, Google, LinkedIn, etc.), which must be at least 1 month old.
  - Profile picture is imported from the social account.
  - Recipients may add a second account for login only (not for verification).
  - Recipients can update/change their linked social account later.
  - Manual review process for flagged/suspicious accounts is required.
- **Donor Side:**
  - Donors can see which network(s) were used for verification (icons/links on recipient profile).
  - Donors can view recipient's social profile(s) before donating (external links).

---

## 19. Donation Impact Visualization (Future Feature)
- As donors adjust the donation amount, dynamic images/clipart/emoji are shown to represent what that amount can buy in the recipient's country (e.g., groceries, school supplies).
- Show equivalent value in the donor's country (currency conversion and purchasing power parity).
- Technical notes: Requires real-time or regularly updated data on cost-of-living and exchange rates.
- Potential data sources: World Bank, Numbeo, OECD, or custom APIs.

---

## General Notes
- **All design, assets, and flows are referenced from the React Native frontend and provided screenshots.**
- **Web version should match mobile in look, feel, and logic unless otherwise specified.**
- **Any new UI/UX changes (e.g., donation slider) should be documented here.**

**NOTE:** All features and requirements in this document are to be implemented as described, regardless of current backend status. Backend scan is for integration understanding only, not for limiting or altering planned features. 