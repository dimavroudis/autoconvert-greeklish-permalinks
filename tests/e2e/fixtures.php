<?php

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$fixture_args = isset($args) ? $args : array();
$mode         = isset($fixture_args[0]) ? $fixture_args[0] : '';
$fixture_id   = isset($fixture_args[1]) ? sanitize_key($fixture_args[1]) : '';
$batch_count  = isset($fixture_args[2]) ? absint($fixture_args[2]) : 101;
$settings_key = '_agp_e2e_settings_' . $fixture_id;
$setting_names = array(
    'agp_automatic',
    'agp_automatic_post',
    'agp_automatic_tax',
    'agp_diphthongs',
);

function agp_e2e_restore_settings($settings)
{
    foreach ($settings as $name => $value) {
        if (false === $value) {
            delete_option($name);
        } else {
            update_option($name, $value);
        }
    }
}

function agp_e2e_delete_fixtures($fixture_id)
{
    $posts = get_posts(
        array(
            'post_type'      => 'any',
            'post_status'    => 'any',
            'numberposts'    => -1,
            'fields'         => 'ids',
            'meta_key'       => '_agp_e2e_run',
            'meta_value'     => $fixture_id,
        )
    );

    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
    }

    foreach (array('category', 'post_tag') as $taxonomy) {
        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key'   => '_agp_e2e_run',
                        'value' => $fixture_id,
                    ),
                ),
            )
        );

        if (is_wp_error($terms)) {
            continue;
        }

        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomy);
        }
    }
}

function agp_e2e_remove_demo_content()
{
    $posts = get_posts(
        array(
            'post_type'      => 'any',
            'post_status'    => 'any',
            'numberposts'    => -1,
            'fields'         => 'ids',
            'meta_key'       => '_agp_demo_key',
        )
    );
    $terms_by_taxonomy = array();

    foreach ($posts as $post_id) {
        foreach (get_object_taxonomies(get_post_type($post_id), 'names') as $taxonomy) {
            $term_ids = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
            if (is_wp_error($term_ids)) {
                throw new RuntimeException($term_ids->get_error_message());
            }
            foreach ($term_ids as $term_id) {
                $terms_by_taxonomy[$taxonomy][(int) $term_id] = true;
            }
        }
    }

    foreach ($posts as $post_id) {
        if (false === wp_delete_post($post_id, true)) {
            throw new RuntimeException('Unable to delete an E2E demo post.');
        }
    }

    $default_category = (int) get_option('default_category');
    foreach ($terms_by_taxonomy as $taxonomy => $term_ids) {
        foreach (array_keys($term_ids) as $term_id) {
            $term = get_term($term_id, $taxonomy);
            if (is_wp_error($term)) {
                throw new RuntimeException($term->get_error_message());
            }
            if (! $term || 0 !== (int) $term->count || $default_category === $term_id) {
                continue;
            }
            $deleted = wp_delete_term($term_id, $taxonomy);
            if (is_wp_error($deleted) || ! $deleted) {
                throw new RuntimeException(
                    is_wp_error($deleted) ? $deleted->get_error_message() : 'Unable to delete an unused E2E demo term.'
                );
            }
        }
    }
}

function agp_e2e_insert_post($fixture_id, $post_type, $title, $slug)
{
    $post_id = wp_insert_post(
        array(
            'post_type'    => $post_type,
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_name'    => $slug,
            'meta_input'   => array(
                '_agp_e2e_run' => $fixture_id,
            ),
        ),
        true
    );

    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }

    return $post_id;
}

function agp_e2e_insert_term($fixture_id, $taxonomy, $name, $slug)
{
    $term = wp_insert_term($name, $taxonomy, array('slug' => $slug));
    if (is_wp_error($term)) {
        throw new RuntimeException($term->get_error_message());
    }

    add_term_meta($term['term_id'], '_agp_e2e_run', $fixture_id, true);
    return (int) $term['term_id'];
}

if ('prepare-suite' === $mode) {
    agp_e2e_remove_demo_content();
    WP_CLI::success('E2E demo content removed.');
    return;
}

if ('' === $fixture_id || ! in_array(
    $mode,
    array('setup', 'setup-posts', 'setup-counts', 'setup-empty', 'setup-empty-off', 'setup-large', 'cleanup'),
    true
)) {
    WP_CLI::error('Expected a fixture mode and run ID.');
}

if ('cleanup' === $mode) {
    $saved_settings = get_option($settings_key, false);
    if (is_array($saved_settings)) {
        agp_e2e_restore_settings($saved_settings);
        delete_option($settings_key);
    }

    agp_e2e_delete_fixtures($fixture_id);
    WP_CLI::success('E2E fixtures removed.');
    return;
}

$saved_settings = get_option($settings_key, false);
if (is_array($saved_settings)) {
    agp_e2e_restore_settings($saved_settings);
    delete_option($settings_key);
}
agp_e2e_delete_fixtures($fixture_id);
$previous_settings = array();
foreach ($setting_names as $setting_name) {
    $previous_settings[$setting_name] = get_option($setting_name, false);
}
update_option($settings_key, $previous_settings);
update_option('agp_automatic', false);
update_option('agp_automatic_post', array('no_options'));
update_option('agp_automatic_tax', array('no_options'));

try {
    if (in_array($mode, array('setup-large', 'setup-posts'), true)) {
        $post_count = 'setup-posts' === $mode ? min($batch_count, 2) : $batch_count;
        for ($index = 1; $index <= $post_count; $index++) {
            $sequence = str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            agp_e2e_insert_post(
                $fixture_id,
                'post',
                'E2E batch ' . $fixture_id . ' ' . $sequence,
                'παρτίδα-' . $fixture_id . '-' . $sequence
            );
        }
    } elseif ('setup-counts' === $mode) {
        agp_e2e_insert_term(
            $fixture_id,
            'category',
            'E2E category ' . $fixture_id,
            'ελληνική-κατηγορία-' . $fixture_id
        );
        agp_e2e_insert_post(
            $fixture_id,
            'post',
            'E2E post ' . $fixture_id,
            'ελληνικό-άρθρο-' . $fixture_id
        );
    } elseif ('setup' === $mode) {
        $category_id = agp_e2e_insert_term(
            $fixture_id,
            'category',
            'E2E category ' . $fixture_id,
            'ελληνική-κατηγορία-' . $fixture_id
        );
        $tag_id = agp_e2e_insert_term(
            $fixture_id,
            'post_tag',
            'E2E tag ' . $fixture_id,
            'δοκιμή-' . $fixture_id
        );
        $slug = 'καλημέρα-αθήνα-' . $fixture_id;
        $primary_post_id = agp_e2e_insert_post($fixture_id, 'post', 'Καλημέρα Αθήνα ' . $fixture_id, $slug);
        agp_e2e_insert_post($fixture_id, 'post', 'Καλημέρα Αθήνα collision ' . $fixture_id, $slug);
        $page_id = agp_e2e_insert_post($fixture_id, 'page', 'Οδηγός δοκιμών ' . $fixture_id, 'οδηγός-δοκιμών-' . $fixture_id);
        wp_set_object_terms($primary_post_id, array($category_id), 'category');
        wp_set_object_terms($primary_post_id, array($tag_id), 'post_tag');
        wp_set_object_terms($page_id, array($category_id), 'category');
    }
} catch (Throwable $error) {
    agp_e2e_delete_fixtures($fixture_id);
    agp_e2e_restore_settings($previous_settings);
    delete_option($settings_key);
    WP_CLI::error($error->getMessage());
}

agp_e2e_restore_settings($previous_settings);
if ('setup-empty-off' === $mode) {
    update_option('agp_automatic', false);
}
WP_CLI::success('E2E fixture data created.');
