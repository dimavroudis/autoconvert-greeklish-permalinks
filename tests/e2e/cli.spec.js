import { test, expect } from "@playwright/test";
import { runWpCliOutput } from "./support.js";

test("WP-CLI reports an empty selection without changing content", () => {
  const result = runWpCliOutput(
    "agp",
    "check",
    "--post_types=none",
    "--taxonomies=none",
  );

  expect(result).toContain("No post types or taxonomies selected");
});
