import { defineConfig, devices } from "@playwright/test";

/**
 * End-to-end tests run against a running stack:
 *   backend  → php artisan serve --port=8091
 *   frontend → npm run dev (port 3091)
 * Admin credentials come from E2E_ADMIN_EMAIL / E2E_ADMIN_PASSWORD.
 */
export default defineConfig({
  testDir: "./e2e",
  timeout: 300_000,
  expect: { timeout: 15_000 },
  fullyParallel: false,
  retries: 0,
  reporter: [["list"]],
  use: {
    baseURL: process.env.E2E_SITE_URL ?? "http://localhost:3091",
    trace: "retain-on-failure",
    screenshot: "only-on-failure",
  },
  projects: [{ name: "chromium", use: { ...devices["Desktop Chrome"], viewport: { width: 1440, height: 900 } } }],
});
