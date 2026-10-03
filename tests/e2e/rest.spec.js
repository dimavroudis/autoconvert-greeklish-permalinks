import { test, expect, request } from "@playwright/test";
import {
  cleanupFixtures,
  createFixtureId,
  createWpApplicationPassword,
  deleteWpApplicationPassword,
  getFixturePosts,
  runWpCli,
  setupFixtures,
} from "./support.js";

const restBaseUrl = process.env.WP_BASE_URL || "http://localhost:8888";
const checkEndpoint = "/wp-json/agp/v1/check-permalinks";
const convertEndpoint = "/wp-json/agp/v1/convert-permalinks";

async function getAuthenticatedApi(username, applicationName) {
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
    getCheck: () => context.get(checkEndpoint),
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

async function getAdminApi() {
  return getAuthenticatedApi("admin");
}

test("authenticated REST check and convert endpoints process fixture content", async () => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    const api = await getAdminApi();
    try {
      const selection = {
        post_types: ["post", "page"],
        taxonomies: ["category", "post_tag"],
      };
      const checkResponse = await api.check(selection);
      expect(checkResponse.ok()).toBe(true);
      expect(await checkResponse.json()).toEqual({
        data: { posts: 3, terms: 2 },
        message: "3 posts and 2 terms are in greek.",
      });

      const convertResponse = await api.convert({ ...selection, limit: 100 });
      expect(convertResponse.ok()).toBe(true);
      expect((await convertResponse.json()).data).toEqual({
        posts: 3,
        terms: 2,
      });

      const afterResponse = await api.check(selection);
      expect(await afterResponse.json()).toEqual({
        data: { posts: 0, terms: 0 },
        message: "All your permalinks were already in greeklish.",
      });
      expect(
        getFixturePosts(fixtureId).every((post) =>
          /^[a-z0-9-]+$/.test(post.post_name),
        ),
      ).toBe(true);
    } finally {
      await api.dispose();
    }
  } finally {
    cleanupFixtures(fixtureId);
  }
});

test("REST endpoints reject unauthenticated requests", async () => {
  const unauthenticated = await request.newContext({
    baseURL: restBaseUrl,
  });
  try {
    const response = await unauthenticated.post(
      "/wp-json/agp/v1/check-permalinks",
      {
        data: { post_types: ["post"], taxonomies: ["category"] },
      },
    );
    expect(response.status()).toBe(401);

    const convertResponse = await unauthenticated.post(
      "/wp-json/agp/v1/convert-permalinks",
      {
        data: { post_types: ["post"], taxonomies: [], limit: 1 },
      },
    );
    expect(convertResponse.status()).toBe(401);
  } finally {
    await unauthenticated.dispose();
  }
});

test("REST validation rejects empty selections, unknown post types, and invalid taxonomies", async () => {
  const api = await getAdminApi();
  try {
    const empty = await api.check({ post_types: [], taxonomies: [] });
    expect((await empty.json()).code).toBe("no_posttypes_taxonomies_selected");

    const invalidPostType = await api.check({
      post_types: ["not-a-real-post-type"],
      taxonomies: [],
    });
    expect((await invalidPostType.json()).code).toBe("invalid_post_types");

    const invalidTaxonomy = await api.check({
      post_types: ["post"],
      taxonomies: ["not-a-real-taxonomy"],
    });
    expect((await invalidTaxonomy.json()).code).toBe("invalid_taxonomy");

    const invalidPostTypeConvert = await api.convert({
      post_types: ["not-a-real-post-type"],
      taxonomies: [],
      limit: 100,
    });
    expect((await invalidPostTypeConvert.json()).code).toBe(
      "invalid_post_types",
    );

    const invalidTaxonomyConvert = await api.convert({
      post_types: [],
      taxonomies: ["not-a-real-taxonomy"],
      limit: 100,
    });
    expect((await invalidTaxonomyConvert.json()).code).toBe("invalid_taxonomy");

    const emptyConvert = await api.convert({
      post_types: [],
      taxonomies: [],
      limit: 100,
    });
    expect((await emptyConvert.json()).code).toBe(
      "no_posttypes_taxonomies_selected",
    );
  } finally {
    await api.dispose();
  }
});

test("REST check counts only the selected posts and taxonomies", async () => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    const api = await getAdminApi();
    try {
      const postOnly = await api.check({
        post_types: ["post", "page"],
        taxonomies: [],
      });
      expect((await postOnly.json()).data).toEqual({ posts: 3, terms: 0 });

      const taxonomyOnly = await api.check({
        post_types: [],
        taxonomies: ["category", "post_tag"],
      });
      expect((await taxonomyOnly.json()).data).toEqual({ posts: 0, terms: 2 });
    } finally {
      await api.dispose();
    }
  } finally {
    cleanupFixtures(fixtureId);
  }
});

test("REST endpoints validate argument types and supported methods", async () => {
  const api = await getAdminApi();
  try {
    const invalidPostTypes = await api.check({
      post_types: "post",
      taxonomies: [],
    });
    expect(invalidPostTypes.status()).toBe(400);
    expect((await invalidPostTypes.json()).code).toBe("rest_invalid_param");

    const invalidTaxonomies = await api.convert({
      post_types: [],
      taxonomies: "category",
    });
    expect(invalidTaxonomies.status()).toBe(400);
    expect((await invalidTaxonomies.json()).code).toBe("rest_invalid_param");

    const unauthenticated = await request.newContext({
      baseURL: restBaseUrl,
    });
    try {
      const unsupportedMethod = await unauthenticated.get(checkEndpoint);
      expect(unsupportedMethod.status()).toBe(404);
      expect((await unsupportedMethod.json()).code).toBe("rest_no_route");
    } finally {
      await unauthenticated.dispose();
    }
  } finally {
    await api.dispose();
  }
});

test("REST routes deny authenticated users without manage_options", async () => {
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

  try {
    const api = await getAuthenticatedApi(username, applicationName);

    try {
      const response = await api.check({
        post_types: ["post"],
        taxonomies: [],
      });
      expect(response.status()).toBe(403);
    } finally {
      await api.dispose();
    }
  } finally {
    runWpCli("user", "delete", username, "--yes");
  }
});

test("REST convert endpoint respects the requested batch limit", async () => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    const api = await getAdminApi();
    try {
      const selection = {
        post_types: ["post", "page"],
        taxonomies: ["category", "post_tag"],
      };

      const before = await api.check(selection);
      expect((await before.json()).data).toEqual({ posts: 3, terms: 2 });

      const limited = await api.convert({ ...selection, limit: 1 });
      expect(limited.ok()).toBe(true);
      expect((await limited.json()).data).toEqual({ posts: 1, terms: 0 });

      const remaining = await api.check(selection);
      expect((await remaining.json()).data).toEqual({ posts: 2, terms: 2 });

      const zeroLimit = await api.convert({
        ...selection,
        limit: 0,
      });
      expect((await zeroLimit.json()).data).toEqual({ posts: 0, terms: 0 });

      const stillRemaining = await api.check(selection);
      expect((await stillRemaining.json()).data).toEqual({
        posts: 2,
        terms: 2,
      });
    } finally {
      await api.dispose();
    }
  } finally {
    cleanupFixtures(fixtureId);
  }
});

test("REST convert defaults to batches of 100 and leaves remaining posts", async () => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId, "setup-large", 101);

  try {
    const api = await getAdminApi();
    try {
      const selection = { post_types: ["post"], taxonomies: [] };
      const before = await api.check(selection);
      expect((await before.json()).data).toEqual({ posts: 101, terms: 0 });

      const firstBatch = await api.convert(selection);
      expect((await firstBatch.json()).data).toEqual({
        posts: 100,
        terms: 0,
      });

      const remaining = await api.check(selection);
      expect((await remaining.json()).data).toEqual({ posts: 1, terms: 0 });
    } finally {
      await api.dispose();
    }
  } finally {
    cleanupFixtures(fixtureId);
  }
});
