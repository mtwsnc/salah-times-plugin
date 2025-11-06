# Salah Times Plugin - Implementation Plan

## Project Overview
Build a plugin that displays Islamic prayer times with a clean, scannable interface showing the current prayer window and countdown to the next prayer. The plugin should support configurable API endpoints rather than hardcoded values.

---

## Phase 1: Project Setup & Configuration
### 1.1 Initialize Project Structure
- [ ] Set up plugin manifest/configuration file
- [ ] Define project dependencies
- [ ] Create folder structure:
  - `/src` - Source code
  - `/assets` - Icons, images
  - `/styles` - CSS/styling
  - `/settings` - Settings UI components

### 1.2 Settings Configuration System
- [ ] Create settings page/panel with form inputs:
  - API base URL input field (default: `https://iqamah.mtws.org`)
  - Location name input (e.g., "Durham")
  - Optional: Timezone selection
  - Optional: Notification preferences
- [ ] Implement settings storage (localStorage/plugin storage API)
- [ ] Add validation for API URL format
- [ ] Create settings save/load functionality

---

## Phase 2: API Integration Layer
### 2.1 API Service Module
- [ ] Create API client class/module with configurable base URL
- [ ] Implement endpoint methods:
  - `getAllPrayerTimes(date?)` → `/api/all`
  - `getFajr(date?)` → `/api/fajr`
  - `getDhuhr(date?)` → `/api/dhuhr`
  - `getAsr(date?)` → `/api/asr`
  - `getMaghrib(date?)` → `/api/maghrib`
  - `getIsha(date?)` → `/api/isha`
  - `getShurooq(date?)` → `/api/shurooq`

### 2.2 Data Processing
- [ ] Parse API responses into standardized data structure
- [ ] Handle date parameter formatting (ISO 8601)
- [ ] Implement error handling and retry logic
- [ ] Add caching mechanism to reduce API calls
- [ ] Handle timezone conversions if needed

---

## Phase 3: Core Logic - Prayer Time Calculations
### 3.1 Current Prayer Detection
- [ ] Implement logic to determine current prayer window:
  - Compare current time against all prayer times
  - Identify which prayer period is active
  - Handle edge cases (before Fajr, after Isha)

### 3.2 Next Prayer Calculation
- [ ] Calculate time remaining until next prayer
- [ ] Format countdown as "Xh Xm Xs"
- [ ] Handle day transitions (Isha → next day's Fajr)
- [ ] Update countdown in real-time (every second)

### 3.3 Prayer Order Logic
- [ ] Define prayer sequence: Fajr → Sunrise → Dhuhr → Asr → Maghrib → Isha
- [ ] Handle Sunrise (Shurooq) as non-prayer marker
- [ ] Implement circular navigation through prayer times

---

## Phase 4: UI Components
### 4.1 Main Prayer Times Table
- [ ] Create table/list component with structure:
  ```
  Prayer Name | Time
  -----------|------
  Fajr       | 5:30 AM
  Sunrise    | 6:45 AM
  Dhuhr      | 1:15 PM (highlighted)
  Asr        | 4:30 PM
  Maghrib    | 7:45 PM
  Isha       | 9:00 PM
  ```

### 4.2 Visual Design Elements
- [ ] Implement row highlighting for current prayer
- [ ] Style prayer names (left-aligned, bold/clear)
- [ ] Format times consistently (right-aligned, AM/PM)
- [ ] Add visual separator between prayers
- [ ] Ensure responsive layout

### 4.3 Header Section
- [ ] Display location name (from settings)
- [ ] Show current date (Gregorian)
- [ ] Show Hijri date (from API metadata)
- [ ] Display day of week

### 4.4 Footer/Status Section
- [ ] Show "Next: [Prayer Name] in Xh Xm Xs"
- [ ] Make countdown prominent and easy to read
- [ ] Update every second

---

## Phase 5: Real-Time Updates
### 5.1 Auto-Refresh System
- [ ] Implement timer to refresh prayer times at midnight
- [ ] Update countdown every second
- [ ] Re-evaluate current prayer window every minute
- [ ] Handle system sleep/wake events

### 5.2 Notification System (Optional)
- [ ] Trigger notifications before prayer time
- [ ] Configurable notification timing (5, 10, 15 min before)
- [ ] Sound/visual notification options

---

## Phase 6: Error Handling & Edge Cases
### 6.1 API Failure Handling
- [ ] Display user-friendly error messages
- [ ] Show cached data when API unavailable
- [ ] Retry failed requests with exponential backoff
- [ ] Validate API response structure

### 6.2 Edge Cases
- [ ] Handle missing Iqamah times (Shurooq)
- [ ] Deal with invalid date parameters
- [ ] Manage timezone mismatches
- [ ] Handle before-Fajr and after-Isha periods
- [ ] Support date changes during runtime

---

## Phase 7: Polish & Optimization
### 7.1 Performance
- [ ] Minimize API calls (cache daily data)
- [ ] Optimize countdown timer performance
- [ ] Lazy-load non-critical components
- [ ] Reduce memory footprint

### 7.2 Accessibility
- [ ] Add ARIA labels for screen readers
- [ ] Ensure keyboard navigation works
- [ ] Provide high-contrast mode option
- [ ] Test with accessibility tools

### 7.3 User Experience
- [ ] Add loading states
- [ ] Smooth transitions for highlight changes
- [ ] Provide visual feedback for settings changes
- [ ] Add help/documentation section

---

## Phase 8: Testing
### 8.1 Unit Tests
- [ ] Test API client methods
- [ ] Test prayer time calculations
- [ ] Test countdown logic
- [ ] Test date/time utilities

### 8.2 Integration Tests
- [ ] Test settings persistence
- [ ] Test API integration with real endpoints
- [ ] Test UI updates with mock data

### 8.3 Manual Testing
- [ ] Test across different times of day
- [ ] Test date transitions (midnight)
- [ ] Test with different API configurations
- [ ] Test error scenarios

---

## Phase 9: Documentation
- [ ] Write README with setup instructions
- [ ] Document API configuration process
- [ ] Create user guide for settings
- [ ] Add inline code comments
- [ ] Document data structures and interfaces

---

## Phase 10: Deployment
- [ ] Package plugin for distribution
- [ ] Create installation instructions
- [ ] Set up version control/releases
- [ ] Prepare demo/screenshots
- [ ] Submit to plugin marketplace (if applicable)

---

## Technical Considerations

### Data Structure Example
```json
{
  "location": "Durham",
  "date": "2025-11-06",
  "hijriDate": "الأربعاء 4 جمادى الأولى 1447",
  "weekday": "Wed",
  "prayers": [
    { "name": "Fajr", "adhan": "5:30", "iqamah": "6:00 AM", "isCurrent": false },
    { "name": "Sunrise", "adhan": "6:45", "iqamah": null, "isCurrent": false },
    { "name": "Dhuhr", "adhan": "1:15", "iqamah": "1:30 PM", "isCurrent": true },
    { "name": "Asr", "adhan": "4:30", "iqamah": "5:00 PM", "isCurrent": false },
    { "name": "Maghrib", "adhan": "7:45", "iqamah": "7:50 PM", "isCurrent": false },
    { "name": "Isha", "adhan": "9:00", "iqamah": "9:15 PM", "isCurrent": false }
  ],
  "nextPrayer": {
    "name": "Dhuhr",
    "timeRemaining": "4h 40m 54s"
  }
}
```

### Key Design Principles
1. **Configurability**: No hardcoded API URLs
2. **Scannability**: Clean table layout, consistent formatting
3. **Clarity**: Visual highlighting of current prayer
4. **Actionability**: Prominent countdown to next prayer
5. **Reliability**: Graceful error handling and caching

---

## Success Criteria
- ✅ User can configure custom API endpoint
- ✅ Prayer times display in chronological order
- ✅ Current prayer window is clearly highlighted
- ✅ Countdown to next prayer updates in real-time
- ✅ UI is clean, scannable, and intuitive
- ✅ Plugin handles API failures gracefully
- ✅ Works across day boundaries (midnight transitions)
