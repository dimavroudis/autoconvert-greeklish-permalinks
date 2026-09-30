import { test, expect, request } from "@playwright/test";
import {
  cleanupFixtures,
  createFixtureId,
  getFixturePosts,
  loginAsAdmin,
  runWpCli,
  setupFixtures,
} from "./support.js";

async function getAdminApi(page) {
  await loginAsAdmin(page);
  await page.goto(
    "/wp-admin/options-general.php?page=agp&tab=generate_permalinks",
  );
  const settings = await page.evaluate(() => window.AgpSettings);
  return {
    check: (data) =>
      page.request.post(settings.endpoints.check, {
        headers: { "X-WP-Nonce": settings.nonce },
        data,
      }),
    convert: (data) =>
      page.request.post(settings.endpoints.convert, {
        headers: { "X-WP-Nonce": settings.nonce },
        data,
      }),
  };
}

test("authenticated REST check and convert endpoints process fixture content", async ({
  page,
}) => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    const api = await getAdminApi(page);
    const selection = {
      post_types: ["post", "page"],
      taxonomies: ["category", "post_tag"],
    };
    const checkResponse = await api.check(selection);
    expect(checkResponse.ok()).toBe(true);
    expect((await checkResponse.json()).data).toEqual({ posts: 3, terms: 2 });

    const convertResponse = await api.convert({ ...selection, limit: 100 });
    expect(convertResponse.ok()).toBe(true);
    expect((await convertResponse.json()).data).toEqual({ posts: 3, terms: 2 });

    const afterResponse = await api.check(selection);
    expect((await afterResponse.json()).data).toEqual({ posts: 0, terms: 0 });
    expect(
      getFixturePosts(fixtureId).every((post) =>
        /^[a-z0-9-]+$/.test(post.post_name),
      ),
    ).toBe(true);
  } finally {
    cleanupFixtures(fixtureId);
  }
});

test("REST endpoints reject unauthenticated requests", async () => {
  const unauthenticated = await request.newContext({
    baseURL: "http://localhost:8888",
  });
  try {
    const response = await unauthenticated.post(
      "/wp-json/agp/v1/check-permalinks",
      {
        data: { post_types: ["post"], taxonomies: ["category"] },
      },
    );
    expect(response.status()).toBe(401);
  } finally {
    await unauthenticated.dispose();
  }
});

test("REST validation rejects empty selections, unknown post types, and invalid taxonomies", async ({
  page,
}) => {
  const api = await getAdminApi(page);
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

  const emptyConvert = await api.convert({
    post_types: [],
    taxonomies: [],
    limit: 100,
  });
  expect((await emptyConvert.json()).code).toBe(
    "no_posttypes_taxonomies_selected",
  );
});

test.fixme("REST convert endpoint respects the batch limit", async ({
  page,
}) => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    const api = await getAdminApi(page);
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
  } finally {
    cleanupFixtures(fixtureId);
  }
});
