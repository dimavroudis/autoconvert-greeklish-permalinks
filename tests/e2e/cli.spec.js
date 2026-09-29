import { test, expect } from "@playwright/test";
import {
  cleanupFixtures,
  createFixtureId,
  runWpCli,
  setupFixtures,
} from "./support.js";

test("WP-CLI check and convert operate on the selected fixture types", async () => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    const selection = [
      "--post_types=post,page",
      "--taxonomies=category,post_tag",
    ];
    const initialCheck = runWpCli("agp", "check", ...selection);
    expect(initialCheck).toContain("3 posts and 2 terms are in greek");

    const conversion = runWpCli("agp", "convert", ...selection);
    expect(conversion).toContain("3 posts and 2 terms converted");

    const finalCheck = runWpCli("agp", "check", ...selection);
    expect(finalCheck).toContain("All your posts are already in greeklish");
  } finally {
    cleanupFixtures(fixtureId);
  }
});
