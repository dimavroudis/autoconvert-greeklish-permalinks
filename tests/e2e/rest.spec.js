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

test("REST convert respects its batch limit", async ({ page }) => {
  test.setTimeout(120000);
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId, "setup-large", 101);

  try {
    const api = await getAdminApi(page);
    const selection = { post_types: ["post"], taxonomies: [] };
    const first = await api.convert({ ...selection, limit: 100 });
    expect((await first.json()).data).toEqual({ posts: 100, terms: 0 });
    const second = await api.convert({ ...selection, limit: 100 });
    expect((await second.json()).data).toEqual({ posts: 1, terms: 0 });
    const third = await api.convert({ ...selection, limit: 100 });
    expect((await third.json()).data).toEqual({ posts: 0, terms: 0 });
    expect(getFixturePosts(fixtureId)).toHaveLength(101);
    expect(
      getFixturePosts(fixtureId).every((post) =>
        /^[a-z0-9-]+$/.test(post.post_name),
      ),
    ).toBe(true);
  } finally {
    cleanupFixtures(fixtureId);
  }
});

test("REST validation rejects empty selections and unknown post types", async ({
  page,
}) => {
  const api = await getAdminApi(page);
  const empty = await api.check({ post_types: [], taxonomies: [] });
  expect((await empty.json()).code).toBe("no_posttypes_taxonomies_selected");

  const invalid = await api.check({
    post_types: ["not-a-real-post-type"],
    taxonomies: [],
  });
  expect((await invalid.json()).code).toBe("invalid_post_types");
  const invalidLimit = await api.convert({
    post_types: ["post"],
    taxonomies: [],
    limit: 0,
  });
  expect(invalidLimit.status()).toBe(400);
});
