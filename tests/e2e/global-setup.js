import { existsSync } from "node:fs";
import { join } from "node:path";
import { randomUUID } from "node:crypto";
import spawn from "cross-spawn";
import { setTimeout as delay } from "node:timers/promises";
import { prepareE2ESuite } from "./support.js";

function isDockerAvailable() {
  const result = spawn.sync("docker", ["info"], {
    encoding: "utf8",
    windowsHide: true,
  });

  if (result.error) {
    throw new Error(`Unable to run Docker CLI: ${result.error.message}`, {
      cause: result.error,
    });
  }

  return result.status === 0;
}

function getDockerDesktopCommand() {
  if (process.platform === "darwin") {
    return ["open", ["-a", "Docker"]];
  }

  if (process.platform === "win32") {
    const candidates = [
      join(process.env.ProgramFiles || "C:\\Program Files", "Docker", "Docker", "Docker Desktop.exe"),
      process.env.LOCALAPPDATA &&
        join(process.env.LOCALAPPDATA, "Programs", "Docker", "Docker", "Docker Desktop.exe"),
    ].filter(Boolean);
    const executable = candidates.find((candidate) => existsSync(candidate));

    if (executable) {
      return [executable, []];
    }
  }

  return null;
}

function launchDockerDesktop(command, args) {
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, {
      detached: true,
      stdio: "ignore",
      windowsHide: true,
    });

    child.once("error", reject);
    child.once("spawn", () => {
      child.unref();
      resolve();
    });
  });
}

async function ensureDockerAvailable() {
  if (isDockerAvailable()) {
    return;
  }

  const desktopCommand = getDockerDesktopCommand();
  if (!desktopCommand) {
    throw new Error(
      "Docker is not running. Start Docker Desktop and rerun the Playwright tests.",
    );
  }

  await launchDockerDesktop(...desktopCommand);
  for (let attempt = 0; attempt < 60; attempt++) {
    await delay(2000);
    if (isDockerAvailable()) {
      return;
    }
  }

  throw new Error(
    "Docker Desktop was opened, but Docker did not become ready within 2 minutes.",
  );
}

function runWpEnv(command) {
  const result = spawn.sync("wp-env", [command], {
    encoding: "utf8",
    windowsHide: true,
    stdio: "inherit",
  });

  if (result.error || result.status !== 0) {
    throw new Error(
      `wp-env ${command} failed${result.error ? `: ${result.error.message}` : ` with exit code ${result.status}`}`,
      { cause: result.error },
    );
  }
}

function runWpCli(...args) {
  const result = spawn.sync("wp-env", ["run", "cli", "wp", ...args], {
    encoding: "utf8",
    windowsHide: true,
  });

  if (result.error || result.status !== 0) {
    const output = `${result.stdout || ""}${result.stderr || ""}`.trim();
    throw new Error(
      `wp-env run cli wp ${args.join(" ")} failed${output ? `:\n${output}` : ""}`,
      { cause: result.error },
    );
  }

  return result.stdout.trim();
}

function deleteAdminApplicationPassword(name) {
  const passwords = JSON.parse(
    runWpCli("user", "application-password", "list", "admin", "--format=json"),
  );
  const password = passwords.find((entry) => entry.name === name);

  if (password) {
    runWpCli(
      "user",
      "application-password",
      "delete",
      "admin",
      password.uuid,
    );
  }
}

export default async function globalSetup() {
  await ensureDockerAvailable();
  runWpEnv("start");

  const applicationName = `playwright-${randomUUID()}`;
  try {
    prepareE2ESuite();
    process.env.WP_E2E_ADMIN_APPLICATION_PASSWORD = runWpCli(
      "user",
      "application-password",
      "create",
      "admin",
      applicationName,
      "--porcelain",
    );
  } catch (error) {
    runWpEnv("stop");
    throw error;
  }

  return async () => {
    try {
      deleteAdminApplicationPassword(applicationName);
    } finally {
      delete process.env.WP_E2E_ADMIN_APPLICATION_PASSWORD;
      runWpEnv("stop");
    }
  };
}
