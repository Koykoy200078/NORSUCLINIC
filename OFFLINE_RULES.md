# Offline Development & Asset Rules

This project is strictly optimized for **offline-first** operation and deployment in local networks without internet connectivity. All developers and AI assistants must adhere to the following rules:

## 1. Zero External Dependencies (CDN)
- **Prohibited**: Using `<script src="https://cdn...">`, `<link href="https://cdnjs...">`, or any external URL for assets.
- **Required**: All third-party libraries must be installed via `npm` or manually placed in the `public/assets/` directory.
- **Reference**: Always use the Laravel `asset()` or `mix()` helpers to reference local files.

## 2. Local Font Hosting
- **Prohibited**: Importing fonts from Google Fonts (`fonts.googleapis.com`) or Adobe Fonts.
- **Required**: Download font files (woff2, ttf) and host them in `public/fonts/`. Reference them via a local CSS file (e.g., `public/css/poppins.css`).

## 3. Library Installation
- If a new library is needed, it must be added to `package.json` and compiled via Laravel Mix (`npm run dev` / `npm run prod`) so that the resulting bundle is self-contained.
- If working in a disconnected environment, libraries must be manually added to the `public/vendor/` folder and documented.

## 4. Verification
- Before submitting any UI changes, verify that the page renders correctly with the browser in **Offline Mode** (DevTools > Network > Offline).
- Check for "404 Not Found" errors in the console relating to external domains.

---
**Note**: This rule was established to ensure system reliability in clinic environments where internet access may be restricted or unavailable.
