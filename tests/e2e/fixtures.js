import { test as base, request } from "@playwright/test";
import {
  cleanupFixtures,
  createFixtureId,
  createWpApplicationPassword,
  deleteWpApplicationPassword,
  runWpCli,
  setupFixtures,
} from "./support.js";

const restBaseUrl = process.env.WP_BASE_URL || "http://localhost:8888";
const checkEndpoint = "/wp-json/agp/v1/check-permalinks";
const convertEndpoint = "/wp-json/agp/v1/convert-permalinks";

async function createAuthenticatedApi(username, applicationName) {
  const password =
    applicationName === undefined
      ? process.env.WP_E2E_ADMIN_APPLICATION_PASSWORD
      : createWpApplicationPassword(username, applicationName);
  if (!password) {
    throw new Error("The REST API test Application Password was not configured.");
  }

  let context;
  try {
    context = await request.newContext({
      baseURL: restBaseUrl,
      httpCredentials: { username, password, send: "always" },
    });
  } catch (error) {
    if (applicationName !== undefined) {
      deleteWpApplicationPassword(username, applicationName);
    }
    throw error;
  }

  return {
    check: (data) => context.post(checkEndpoint, { data }),
    convert: (data) => context.post(convertEndpoint, { data }),
    createPost: (data) => context.post("/wp-json/wp/v2/posts", { data }),
    createCategory: (data) =>
      context.post("/wp-json/wp/v2/categories", { data }),
    dispose: async () => {
      try {
        await context.dispose();
      } finally {
        if (applicationName !== undefined) {
          deleteWpApplicationPassword(username, applicationName);
        }
      }
    },
  };
}

export const test = base.extend({
  adminApi: async ({}, use) => {
    const api = await createAuthenticatedApi("admin");
    try {
      await use(api);
    } finally {
      await api.dispose();
    }
  },
  unauthenticatedApi: async ({}, use) => {
    const api = await request.newContext({ baseURL: restBaseUrl });
    try {
      await use(api);
    } finally {
      await api.dispose();
    }
  },
  fixtureId: async ({}, use) => {
    const fixtureId = createFixtureId();
    setupFixtures(fixtureId);
    try {
      await use(fixtureId);
    } finally {
      cleanupFixtures(fixtureId);
    }
  },
  automaticOffFixtureId: async ({}, use) => {
    const fixtureId = createFixtureId();
    setupFixtures(fixtureId, "setup-empty-off");
    try {
      await use(fixtureId);
    } finally {
      cleanupFixtures(fixtureId);
    }
  },
  postsFixtureId: async ({}, use) => {
    const fixtureId = createFixtureId();
    setupFixtures(fixtureId, "setup-posts", 2);
    try {
      await use(fixtureId);
    } finally {
      cleanupFixtures(fixtureId);
    }
  },
  countsFixtureId: async ({}, use) => {
    const fixtureId = createFixtureId();
    setupFixtures(fixtureId, "setup-counts");
    try {
      await use(fixtureId);
    } finally {
      cleanupFixtures(fixtureId);
    }
  },
  emptyFixtureId: async ({}, use) => {
    const fixtureId = createFixtureId();
    setupFixtures(fixtureId, "setup-empty");
    try {
      await use(fixtureId);
    } finally {
      cleanupFixtures(fixtureId);
    }
  },
  largeFixtureId: async ({}, use) => {
    const fixtureId = createFixtureId();
    setupFixtures(fixtureId, "setup-large", 101);
    try {
      await use(fixtureId);
    } finally {
      cleanupFixtures(fixtureId);
    }
  },
  subscriberApi: async ({}, use) => {
    const username = createFixtureId();
    const password = createFixtureId();
    const applicationName = `playwright-${createFixtureId()}`;
    runWpCli(
      "user",
      "create",
      username,
      `${username}@example.test`,
      "--role=subscriber",
      `--user_pass=${password}`,
      "--porcelain",
    );

    let api;
    try {
      api = await createAuthenticatedApi(username, applicationName);
      await use(api);
    } finally {
      try {
        await api?.dispose();
      } finally {
        runWpCli("user", "delete", username, "--yes");
      }
    }
  },
});
