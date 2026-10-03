import { expect } from "@playwright/test";
import { getFixturePosts } from "./support.js";
import { test } from "./fixtures.js";

const checkEndpoint = "/wp-json/agp/v1/check-permalinks";
const convertEndpoint = "/wp-json/agp/v1/convert-permalinks";

test("authenticated REST check and convert endpoints process fixture content", async ({
  adminApi,
  fixtureId,
}) => {
  const selection = {
    post_types: ["post", "page"],
    taxonomies: ["category", "post_tag"],
  };
  const checkResponse = await adminApi.check(selection);
  expect(checkResponse.ok()).toBe(true);
  expect(await checkResponse.json()).toEqual({
    data: { posts: 3, terms: 2 },
    message: "3 posts and 2 terms are in greek.",
  });

  const convertResponse = await adminApi.convert({
    ...selection,
    limit: 100,
  });
  expect(convertResponse.ok()).toBe(true);
  expect((await convertResponse.json()).data).toEqual({ posts: 3, terms: 2 });

  const afterResponse = await adminApi.check(selection);
  expect(await afterResponse.json()).toEqual({
    data: { posts: 0, terms: 0 },
    message: "All your permalinks were already in greeklish.",
  });
  expect(
    getFixturePosts(fixtureId).every((post) =>
      /^[a-z0-9-]+$/.test(post.post_name),
    ),
  ).toBe(true);
});

test("REST endpoints reject unauthenticated requests", async ({
  unauthenticatedApi,
}) => {
  const response = await unauthenticatedApi.post(checkEndpoint, {
    data: { post_types: ["post"], taxonomies: ["category"] },
  });
  expect(response.status()).toBe(401);

  const convertResponse = await unauthenticatedApi.post(convertEndpoint, {
    data: { post_types: ["post"], taxonomies: [], limit: 1 },
  });
  expect(convertResponse.status()).toBe(401);
});

test("REST validation rejects empty selections, unknown post types, and invalid taxonomies", async ({
  adminApi,
}) => {
  const empty = await adminApi.check({ post_types: [], taxonomies: [] });
  expect((await empty.json()).code).toBe("no_posttypes_taxonomies_selected");

  const invalidPostType = await adminApi.check({
    post_types: ["not-a-real-post-type"],
    taxonomies: [],
  });
  expect((await invalidPostType.json()).code).toBe("invalid_post_types");

  const invalidTaxonomy = await adminApi.check({
    post_types: ["post"],
    taxonomies: ["not-a-real-taxonomy"],
  });
  expect((await invalidTaxonomy.json()).code).toBe("invalid_taxonomy");

  const invalidPostTypeConvert = await adminApi.convert({
    post_types: ["not-a-real-post-type"],
    taxonomies: [],
    limit: 100,
  });
  expect((await invalidPostTypeConvert.json()).code).toBe("invalid_post_types");

  const invalidTaxonomyConvert = await adminApi.convert({
    post_types: [],
    taxonomies: ["not-a-real-taxonomy"],
    limit: 100,
  });
  expect((await invalidTaxonomyConvert.json()).code).toBe("invalid_taxonomy");

  const emptyConvert = await adminApi.convert({
    post_types: [],
    taxonomies: [],
    limit: 100,
  });
  expect((await emptyConvert.json()).code).toBe(
    "no_posttypes_taxonomies_selected",
  );
});

test("REST check counts only the selected posts and taxonomies", async ({
  adminApi,
  fixtureId,
}) => {
  const postOnly = await adminApi.check({
    post_types: ["post", "page"],
    taxonomies: [],
  });
  expect((await postOnly.json()).data).toEqual({ posts: 3, terms: 0 });

  const taxonomyOnly = await adminApi.check({
    post_types: [],
    taxonomies: ["category", "post_tag"],
  });
  expect((await taxonomyOnly.json()).data).toEqual({ posts: 0, terms: 2 });
});

test("REST endpoints validate argument types and supported methods", async ({
  adminApi,
  unauthenticatedApi,
}) => {
  const invalidPostTypes = await adminApi.check({
    post_types: "post",
    taxonomies: [],
  });
  expect(invalidPostTypes.status()).toBe(400);
  expect((await invalidPostTypes.json()).code).toBe("rest_invalid_param");

  const invalidTaxonomies = await adminApi.convert({
    post_types: [],
    taxonomies: "category",
  });
  expect(invalidTaxonomies.status()).toBe(400);
  expect((await invalidTaxonomies.json()).code).toBe("rest_invalid_param");

  const unsupportedMethod = await unauthenticatedApi.get(checkEndpoint);
  expect(unsupportedMethod.status()).toBe(404);
  expect((await unsupportedMethod.json()).code).toBe("rest_no_route");
});

test("REST routes deny authenticated users without manage_options", async ({
  subscriberApi,
}) => {
  const response = await subscriberApi.check({
    post_types: ["post"],
    taxonomies: [],
  });
  expect(response.status()).toBe(403);
});

test("REST convert endpoint respects the requested batch limit", async ({
  adminApi,
  fixtureId,
}) => {
  const selection = {
    post_types: ["post", "page"],
    taxonomies: ["category", "post_tag"],
  };

  const before = await adminApi.check(selection);
  expect((await before.json()).data).toEqual({ posts: 3, terms: 2 });

  const limited = await adminApi.convert({ ...selection, limit: 1 });
  expect(limited.ok()).toBe(true);
  expect((await limited.json()).data).toEqual({ posts: 1, terms: 0 });

  const remaining = await adminApi.check(selection);
  expect((await remaining.json()).data).toEqual({ posts: 2, terms: 2 });

  const zeroLimit = await adminApi.convert({ ...selection, limit: 0 });
  expect((await zeroLimit.json()).data).toEqual({ posts: 0, terms: 0 });

  const stillRemaining = await adminApi.check(selection);
  expect((await stillRemaining.json()).data).toEqual({ posts: 2, terms: 2 });
});

test("REST convert defaults to batches of 100 and leaves remaining posts", async ({
  adminApi,
  largeFixtureId,
}) => {
  const selection = { post_types: ["post"], taxonomies: [] };
  const before = await adminApi.check(selection);
  expect((await before.json()).data).toEqual({ posts: 101, terms: 0 });

  const firstBatch = await adminApi.convert(selection);
  expect((await firstBatch.json()).data).toEqual({ posts: 100, terms: 0 });

  const remaining = await adminApi.check(selection);
  expect((await remaining.json()).data).toEqual({ posts: 1, terms: 0 });
});
