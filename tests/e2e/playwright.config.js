import { defineConfig } from "@playwright/test";

export default defineConfig({
  testDir: ".",
  testMatch: "**/*.spec.js",
  fullyParallel: false,
  workers: 1,
  timeout: 45000,
  expect: {
    timeout: 10000,
  },
  reporter: [
    ["list"],
    ["html", { outputFolder: "output/playwright-report", open: "never" }],
  ],
  outputDir: "output/test-results",
  use: {
    baseURL: process.env.WP_BASE_URL || "http://localhost:8888",
    browserName: "chromium",
    headless: true,
    screenshot: "only-on-failure",
    trace: "retain-on-failure",
  },
});
