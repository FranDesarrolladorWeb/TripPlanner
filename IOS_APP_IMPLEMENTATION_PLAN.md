# TripPlanner iOS App - Implementation Plan
**Capacitor + Symfony Backend Strategy**

## 📋 Executive Summary

**Goal**: Convert TripPlanner web app into a native iOS app using Capacitor

**Approach**: MVP-First (2-3 weeks) with progressive enhancement

**Key Technologies**:
- **Frontend**: Capacitor (WebView wrapper with native capabilities)
- **Backend**: Symfony API endpoints (convert from Twig templates)
- **Build**: Xcode + Capacitor CLI
- **Distribution**: TestFlight → App Store (optional)

---

## 🎯 Feature Priority Matrix

| Feature | Priority | MVP Phase | Implementation Complexity |
|---------|----------|-----------|---------------------------|
| Offline trip viewing | ⭐⭐⭐⭐⭐ | ✅ Phase 1 | Medium |
| GPS/Maps integration | ⭐⭐⭐⭐ | ✅ Phase 1 | Low |
| Biometric login (Face ID) | ⭐⭐⭐⭐ | ⏸️ Phase 2 | Medium |
| Share trips (iOS share sheet) | ⭐⭐⭐⭐ | ⏸️ Phase 2 | Low |
| Push notifications | ⭐⭐⭐ | ⏸️ Phase 3 | High |
| Camera photo uploads | ⭐ | ❌ Future | Low |

---

## 🏗️ Technical Architecture

### Current State
```
User Browser → Symfony (Twig Templates) → MySQL Database
```

### Target State (Hybrid)
```
iOS App (Capacitor WebView)
    ↓
Symfony API Endpoints (JSON)
    ↓
MySQL Database
```

### Architecture Decisions

**✅ Keep Existing Web App**
- Maintain Twig templates for web users
- Add API controllers alongside web controllers
- Dual-mode: Web (HTML) + Mobile (JSON)

**✅ API-First Mobile Experience**
- iOS app consumes JSON APIs
- Capacitor handles native features
- Service Worker for offline support

**✅ Progressive Enhancement**
- Start with webview wrapper
- Add native features incrementally
- Maintain web parity

---

## 📱 Phase 1: MVP Implementation (2-3 Weeks)

### Week 1: Foundation & API Conversion

#### Day 1-2: Capacitor Setup
**Tasks**:
1. Install Capacitor in project
2. Create iOS app configuration
3. Setup Xcode project structure
4. Configure app icons and splash screens

**Commands**:
```bash
# Install Capacitor
npm install @capacitor/core @capacitor/cli
npx cap init "TripPlanner" "com.tripplanner.app"

# Add iOS platform
npm install @capacitor/ios
npx cap add ios

# Essential plugins for MVP
npm install @capacitor/storage      # Offline data
npm install @capacitor/geolocation  # GPS/Maps (priority 4)
npm install @capacitor/app          # App lifecycle
npm install @capacitor/status-bar   # iOS status bar
npm install @capacitor/splash-screen # Launch screen
```

#### Day 3-5: API Conversion Strategy

**Priority APIs for MVP**:

1. **Authentication API** (Critical)
   - `POST /api/auth/login` - User login
   - `POST /api/auth/register` - User registration
   - `POST /api/auth/logout` - User logout
   - `GET /api/auth/me` - Current user info

2. **Trips API** (Core Feature)
   - `GET /api/trips` - List user trips
   - `GET /api/trips/{id}` - Get trip details (with offline support)
   - `POST /api/trips` - Create new trip
   - `PUT /api/trips/{id}` - Update trip
   - `DELETE /api/trips/{id}` - Delete trip

3. **Destinations API** (Carousel Feature)
   - `GET /api/destinations` - Featured destinations
   - `GET /api/destinations/{id}` - Destination details

**Implementation Pattern**:
```php
// Example: src/Controller/Api/TripApiController.php
<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/trips', name: 'api_trip_')]
class TripApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Return JSON for mobile app
        return $this->json([
            'trips' => [/* trip data */],
            'success' => true
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        // Include all data needed for offline storage
        return $this->json([
            'trip' => [/* full trip details */],
            'offline_timestamp' => time()
        ]);
    }
}
```

#### Day 6-7: Mobile Frontend Development

**Create Mobile-Optimized HTML/JS**:

**Option A: Keep Current Twig + Add JS Layer** (Recommended for MVP)
- Enhance existing pages with Capacitor APIs
- Add JavaScript for offline storage
- Minimal changes to existing templates

**Option B: Separate Mobile App** (Better long-term)
- Create `public/mobile/` directory
- Build dedicated mobile SPA (Vue/React/Vanilla JS)
- Consume APIs exclusively

**For MVP: Choose Option A**

```javascript
// public/js/mobile-app.js
import { Capacitor } from '@capacitor/core';
import { Storage } from '@capacitor/storage';
import { Geolocation } from '@capacitor/geolocation';

// Detect if running in native app
const isNativeApp = Capacitor.isNativePlatform();

// Offline storage helper
async function saveForOffline(key, data) {
    await Storage.set({
        key: key,
        value: JSON.stringify(data)
    });
}

async function getOfflineData(key) {
    const { value } = await Storage.get({ key });
    return JSON.parse(value);
}

// Fetch trips with offline fallback
async function fetchTrips() {
    try {
        const response = await fetch('/api/trips', {
            headers: {
                'Authorization': `Bearer ${getAuthToken()}`
            }
        });
        const data = await response.json();

        // Save for offline access (Priority 5)
        await saveForOffline('trips', data);

        return data;
    } catch (error) {
        // Network error - return offline data
        console.log('Offline mode - loading cached trips');
        return await getOfflineData('trips');
    }
}

// GPS integration (Priority 4)
async function getCurrentLocation() {
    if (!isNativeApp) return null;

    const coordinates = await Geolocation.getCurrentPosition();
    return {
        lat: coordinates.coords.latitude,
        lng: coordinates.coords.longitude
    };
}
```

### Week 2: Native Features & Polish

#### Day 8-9: Offline Support Implementation (Priority 5 ⭐⭐⭐⭐⭐)

**Strategy**: Capacitor Storage + Service Worker

1. **Install and configure**:
```bash
npm install @capacitor/storage
npx cap sync
```

2. **Implement offline-first architecture**:
```javascript
// Offline Manager
class OfflineManager {
    static async syncTrips() {
        const trips = await fetch('/api/trips').then(r => r.json());
        await Storage.set({
            key: 'offline_trips',
            value: JSON.stringify({
                data: trips,
                timestamp: Date.now()
            })
        });
    }

    static async getTrips() {
        try {
            // Try network first
            return await this.syncTrips();
        } catch {
            // Fallback to offline
            const cached = await Storage.get({ key: 'offline_trips' });
            return JSON.parse(cached.value);
        }
    }

    static async isOffline() {
        return !navigator.onLine;
    }
}
```

3. **Add offline indicator UI**:
```html
<!-- Add to base template -->
<div id="offline-banner" style="display: none;">
    📡 You're offline - viewing cached data
</div>

<script>
window.addEventListener('online', () => {
    document.getElementById('offline-banner').style.display = 'none';
    // Sync data when back online
    OfflineManager.syncTrips();
});

window.addEventListener('offline', () => {
    document.getElementById('offline-banner').style.display = 'block';
});
</script>
```

#### Day 10-11: GPS/Maps Integration (Priority 4 ⭐⭐⭐⭐)

**Use Capacitor Geolocation + Apple Maps**:

```javascript
// Location-based features
import { Geolocation } from '@capacitor/geolocation';

async function addLocationToTrip(tripId) {
    // Request permission
    const permission = await Geolocation.requestPermissions();

    if (permission.location === 'granted') {
        const position = await Geolocation.getCurrentPosition();

        // Send to backend
        await fetch(`/api/trips/${tripId}/location`, {
            method: 'POST',
            body: JSON.stringify({
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                accuracy: position.coords.accuracy
            })
        });
    }
}

// Show trip on native map
function openInMaps(lat, lng) {
    const url = `maps://?q=${lat},${lng}`;
    window.open(url, '_system');
}
```

**Update iOS Info.plist** (Xcode):
```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>TripPlanner needs your location to show nearby destinations and add places to your trips.</string>
```

#### Day 12-13: iOS Native UI Polish

**Status Bar Configuration**:
```typescript
import { StatusBar, Style } from '@capacitor/status-bar';

// Match your app theme
await StatusBar.setStyle({ style: Style.Light });
await StatusBar.setBackgroundColor({ color: '#667eea' });
```

**Splash Screen**:
```typescript
import { SplashScreen } from '@capacitor/splash-screen';

// Auto-hide after app loads
window.addEventListener('load', () => {
    setTimeout(() => {
        SplashScreen.hide();
    }, 500);
});
```

**Safe Area Handling** (for iPhone notch):
```css
/* Add to base.html.twig or mobile.css */
body {
    padding-top: env(safe-area-inset-top);
    padding-bottom: env(safe-area-inset-bottom);
}

/* Fix for bottom navigation/buttons */
.bottom-nav {
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
}
```

#### Day 14: Testing & TestFlight Preparation

**Testing Checklist**:
- [ ] Authentication flows (login/register/logout)
- [ ] Trip CRUD operations
- [ ] Offline mode (airplane mode test)
- [ ] GPS location requests
- [ ] Deep linking (optional for MVP)
- [ ] App lifecycle (background/foreground)
- [ ] iPhone models (SE, 14, 14 Pro Max)
- [ ] iOS versions (16.0+)

**Build for TestFlight**:
```bash
# Sync web assets to iOS
npx cap sync ios

# Open in Xcode
npx cap open ios

# In Xcode:
# 1. Set development team
# 2. Update Bundle Identifier: com.tripplanner.app
# 3. Set version: 1.0.0 (MVP)
# 4. Product > Archive
# 5. Distribute App > TestFlight
```

### Week 3: Polish & Documentation

#### Day 15-17: Bug Fixes & Optimization
- Performance profiling
- Memory leak checks
- API response optimization
- Loading states and error handling
- User feedback integration

#### Day 18-19: Documentation
- API documentation (Swagger/OpenAPI)
- Mobile app architecture docs
- Deployment guide
- TestFlight invitation process

#### Day 20-21: Buffer & Contingency
- Address unexpected issues
- Additional testing
- App Store preparation (if ready)

---

## 🔧 Prerequisites & Setup

### Required Software
```bash
# Check versions
node --version    # v18+ required
npm --version     # v9+ required
php --version     # 8.2+ (you already have 8.5.2)
composer --version

# macOS/Xcode
xcodebuild -version  # Xcode 14+ required
```

### Install Capacitor
```bash
cd /Users/franciscochavezromero/PhpstormProjects/TripPlanner

# Initialize npm if not exists
npm init -y

# Install Capacitor
npm install @capacitor/core @capacitor/cli --save-dev

# Initialize Capacitor project
npx cap init TripPlanner com.tripplanner.app --web-dir=public

# Add iOS platform
npm install @capacitor/ios
npx cap add ios
```

### Configure capacitor.config.ts
```typescript
import { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.tripplanner.app',
  appName: 'TripPlanner',
  webDir: 'public',
  server: {
    // Development: point to Symfony dev server
    // Production: use bundled assets
    url: process.env.NODE_ENV === 'development'
      ? 'http://localhost:8000'
      : undefined,
    cleartext: true // Allow HTTP in development
  },
  plugins: {
    SplashScreen: {
      launchShowDuration: 2000,
      backgroundColor: '#667eea',
      showSpinner: false
    },
    StatusBar: {
      style: 'LIGHT',
      backgroundColor: '#667eea'
    }
  },
  ios: {
    contentInset: 'automatic',
    scrollEnabled: true
  }
};

export default config;
```

---

## 📂 Project Structure (After Capacitor Integration)

```
TripPlanner/
├── src/
│   ├── Controller/
│   │   ├── Web/              # Existing Twig controllers
│   │   │   ├── HomeController.php
│   │   │   └── ...
│   │   └── Api/              # NEW: API endpoints for mobile
│   │       ├── AuthApiController.php
│   │       ├── TripApiController.php
│   │       └── DestinationApiController.php
│   ├── Entity/
│   ├── Repository/
│   └── ...
├── public/
│   ├── index.php             # Symfony entry point
│   ├── js/
│   │   ├── mobile-app.js     # NEW: Mobile-specific JavaScript
│   │   └── offline-manager.js
│   ├── css/
│   │   └── mobile.css        # NEW: Mobile-specific styles
│   └── ...
├── ios/                      # NEW: iOS Xcode project
│   ├── App/
│   │   ├── App/
│   │   │   ├── Info.plist    # iOS permissions
│   │   │   ├── Assets.xcassets  # App icons
│   │   │   └── ...
│   │   └── App.xcodeproj
│   └── Podfile
├── capacitor.config.ts       # NEW: Capacitor configuration
├── package.json              # NEW: Node dependencies
├── composer.json
└── README.md
```

---

## 🔐 API Authentication Strategy

### Option 1: JWT Tokens (Recommended for Mobile)

**Install LexikJWTAuthenticationBundle**:
```bash
composer require lexik/jwt-authentication-bundle
```

**Configure JWT**:
```yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%kernel.project_dir%/config/jwt/private.pem'
    public_key: '%kernel.project_dir%/config/jwt/public.pem'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600  # 1 hour
```

**Generate keys**:
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

**API Login Endpoint**:
```php
#[Route('/api/auth/login', name: 'api_login', methods: ['POST'])]
public function login(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    // Authenticate user...

    return $this->json([
        'token' => $jwtToken,
        'user' => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName()
        ]
    ]);
}
```

**Mobile App Usage**:
```javascript
// Store token securely
import { Storage } from '@capacitor/storage';

async function login(email, password) {
    const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    });

    const data = await response.json();

    // Store token in secure storage
    await Storage.set({
        key: 'auth_token',
        value: data.token
    });

    return data;
}

// Use token in subsequent requests
async function fetchProtectedData() {
    const { value: token } = await Storage.get({ key: 'auth_token' });

    const response = await fetch('/api/trips', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });

    return response.json();
}
```

---

## ⏭️ Phase 2: Enhanced Features (Week 4-6)

### Biometric Authentication (Priority 4)
```bash
npm install @capacitor-community/biometric-auth
```

### iOS Share Sheet (Priority 4)
```bash
npm install @capacitor/share
```

### Implementation:
```javascript
import { Share } from '@capacitor/share';

async function shareTrip(tripId) {
    await Share.share({
        title: 'Check out my trip!',
        text: 'I planned an amazing trip with TripPlanner',
        url: `https://tripplanner.app/trips/${tripId}`,
        dialogTitle: 'Share your adventure'
    });
}
```

---

## ⏭️ Phase 3: Advanced Features (Week 7+)

### Push Notifications (Priority 3)
```bash
npm install @capacitor/push-notifications
```

**Requires**:
- Apple Developer account ($99/year)
- APNs certificates
- Backend notification service

---

## 📦 Deployment Strategy

### Development Flow
```bash
# 1. Develop locally with live reload
npm run dev  # Start Symfony dev server
npx cap sync ios
npx cap run ios  # Opens iOS simulator

# 2. Make changes to web assets
# 3. Sync changes
npx cap copy ios
```

### Production Build
```bash
# 1. Build optimized assets
npm run build  # If you add a build step

# 2. Sync to iOS
npx cap sync ios

# 3. Open Xcode
npx cap open ios

# 4. In Xcode:
#    - Select "Any iOS Device"
#    - Product > Archive
#    - Distribute App > TestFlight
```

### Backend Deployment (Railway)
- No changes needed - your Symfony backend continues to deploy as-is
- API endpoints accessible at your Railway URL
- Ensure CORS configured for mobile app origin

---

## 🧪 Testing Strategy

### Local Testing
1. **iOS Simulator** (Xcode)
   - Test basic functionality
   - Fast iteration
   - Limited: no Face ID, no actual GPS

2. **Physical iPhone** (via Xcode)
   - Full native features
   - Real GPS, biometrics
   - True performance testing

### Beta Testing
1. **TestFlight** (Free with Apple Developer account)
   - Invite up to 10,000 testers
   - Automatic updates
   - Crash reports

### Production
1. **App Store** (Requires review)
   - Full public availability
   - App Store optimization
   - Ratings and reviews

---

## 💰 Cost Breakdown

| Item | Cost | Required For |
|------|------|--------------|
| Mac + Xcode | $0 (you have) | Development ✅ |
| Apple Developer Account | $99/year | TestFlight + App Store |
| Capacitor | $0 (open source) | All |
| Plugins | $0 (community) | All |
| Railway Backend | Current cost | API hosting |

**Total MVP Cost**: $0 (TestFlight requires developer account)

---

## ⚠️ Potential Challenges & Solutions

### Challenge 1: Symfony Session Management
**Problem**: Web uses PHP sessions, mobile needs stateless auth

**Solution**: Dual authentication system
- Web: Keep existing session-based auth
- Mobile: JWT tokens for API

### Challenge 2: CORS Issues
**Problem**: Mobile app may face CORS restrictions

**Solution**: Configure Symfony CORS
```bash
composer require nelmio/cors-bundle
```

```yaml
# config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['*']
        allow_methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']
        allow_headers: ['*']
        max_age: 3600
    paths:
        '^/api/':
            allow_origin: ['*']
```

### Challenge 3: Offline Sync Conflicts
**Problem**: User edits offline data, creates conflicts when syncing

**Solution**: Timestamp-based conflict resolution
```javascript
async function syncOfflineChanges() {
    const pendingChanges = await Storage.get({ key: 'pending_changes' });

    for (const change of JSON.parse(pendingChanges.value)) {
        try {
            await fetch(`/api/trips/${change.id}`, {
                method: 'PUT',
                body: JSON.stringify({
                    ...change.data,
                    client_timestamp: change.timestamp
                })
            });
        } catch (error) {
            // Handle conflict - server may have newer data
        }
    }
}
```

---

## 📊 Success Metrics

### MVP Launch Criteria
- [ ] User can login/register via mobile app
- [ ] User can view trips offline
- [ ] GPS location services functional
- [ ] App passes iOS review guidelines
- [ ] No critical bugs in TestFlight
- [ ] App launches in < 3 seconds
- [ ] Offline mode works reliably

### Performance Targets
- App launch time: < 3 seconds
- API response time: < 500ms
- Offline mode: 100% trip viewing
- GPS accuracy: < 50 meters
- Battery impact: < 5% per hour of active use

---

## 📚 Resources & Documentation

### Official Documentation
- [Capacitor Docs](https://capacitorjs.com/docs)
- [iOS Human Interface Guidelines](https://developer.apple.com/design/human-interface-guidelines/)
- [Symfony REST API](https://symfony.com/doc/current/rest.html)

### Community Resources
- [Capacitor Community Plugins](https://github.com/capacitor-community)
- [Ionic Forum](https://forum.ionicframework.com/)

### Required Reading
- App Store Review Guidelines
- iOS Privacy Requirements
- TestFlight Best Practices

---

## 🚀 Next Steps

1. **Review this plan** - Any questions or adjustments?
2. **Set up Apple Developer account** (if pursuing TestFlight/App Store)
3. **Start Phase 1, Day 1** - Install Capacitor
4. **Daily standups** - Track progress against timeline
5. **Iterate based on testing** - Adjust plan as needed

---

## 📞 Decision Points

Before starting, confirm:

- [ ] Comfortable with 2-3 week MVP timeline?
- [ ] Ready to convert controllers to API endpoints?
- [ ] Apple Developer account decision ($99/year)?
- [ ] Preferred testing method (Simulator vs Physical device)?
- [ ] Want separate mobile codebase or enhance existing templates?

**Let me know when you're ready to start, and I'll help implement Phase 1, Day 1! 🚀**
