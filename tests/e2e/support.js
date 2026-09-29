import spawn from "cross-spawn";
import { basename } from "node:path";
import { randomUUID } from "node:crypto";

const pluginDirectory = basename(process.cwd());
const fixtureFile = `/var/www/html/wp-content/plugins/${pluginDirectory}/tests/e2e/fixtures.php`;

export function createFixtureId() {
  return `e2e-${Date.now()}-${randomUUID().slice(0, 8)}`;
}

export function runWpCli(...args) {
  const result = spawn.sync("wp-env", ["run", "cli", "wp", ...args], {
    encoding: "utf8",
    windowsHide: true,
  });

  if (result.error || result.status !== 0) {
    const output = `${result.stdout || ""}${result.stderr || ""}`.trim();
    throw new Error(
      `wp-env run cli wp ${args.join(" ")} failed${output ? `:\n${output}` : ""}`,
      {
        cause: result.error,
      },
    );
  }

  return result.stdout.trim();
}

export function setupFixtures(fixtureId, mode = "setup", count) {
  runWpCli("eval-file", fixtureFile, "prepare", fixtureId);

  const args = ["eval-file", fixtureFile, mode, fixtureId];
  if (count) {
    args.push(String(count));
  }

  try {
    return runWpCli(...args);
  } catch (error) {
    cleanupFixtures(fixtureId);
    throw error;
  }
}

export function cleanupFixtures(fixtureId) {
  return setupFixtures(fixtureId, "cleanup");
}

export function getFixturePosts(fixtureId) {
  const output = runWpCli(
    "post",
    "list",
    "--post_type=any",
    "--post_status=publish",
    "--meta_key=_agp_e2e_run",
    `--meta_value=${fixtureId}`,
    "--fields=ID,post_name,post_type,post_title",
    "--format=json",
  );
  return output ? JSON.parse(output) : [];
}

export async function loginAsAdmin(page) {
  await page.goto("/wp-login.php");
  await page.locator("#user_login").fill(process.env.WP_ADMIN_USER || "admin");
  await page
    .locator("#user_pass")
    .fill(process.env.WP_ADMIN_PASSWORD || "password");
  await page.locator("#wp-submit").click();
  await page.waitForURL((url) => url.pathname.includes("/wp-admin/"));
}
