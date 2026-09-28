<?php

if (! class_exists('WP_CLI')) {
    return;
}

$demo_terms = array(
    'category' => array('Αθήνα', 'Ελληνική κουζίνα'),
    'post_tag' => array('δοκιμή', 'Greeklish test'),
);
$term_ids   = array();

foreach ($demo_terms as $taxonomy => $term_names) {
    foreach ($term_names as $term_name) {
        $term = term_exists($term_name, $taxonomy);

        if (! $term) {
            $term = wp_insert_term($term_name, $taxonomy);
            if (is_wp_error($term)) {
                WP_CLI::error($term->get_error_message());
            }
        }

        $term_ids[$taxonomy][$term_name] = (int) (is_array($term) ? $term['term_id'] : $term);
    }
}

$demo_posts = array(
    array(
        'key'     => 'athens-post',
        'type'    => 'post',
        'title'   => 'Καλημέρα Αθήνα',
        'content' => 'A published post for checking Greek-to-Greeklish permalink conversion.',
        'terms'   => array(
            'category' => 'Αθήνα',
            'post_tag' => 'δοκιμή',
        ),
    ),
    array(
        'key'     => 'athens-collision-post',
        'type'    => 'post',
        'title'   => 'Καλημέρα Αθήνα',
        'content' => 'A second matching title for checking unique permalink handling.',
        'terms'   => array(
            'category' => 'Αθήνα',
            'post_tag' => 'Greeklish test',
        ),
    ),
    array(
        'key'     => 'thessaloniki-post',
        'type'    => 'post',
        'title'   => 'Θεσσαλονίκη: ιστορία και γεύσεις',
        'content' => 'A second Greek title and category for testing.',
        'terms'   => array(
            'category' => 'Ελληνική κουζίνα',
            'post_tag' => 'δοκιμή',
        ),
    ),
    array(
        'key'     => 'greek-guide-page',
        'type'    => 'page',
        'title'   => 'Οδηγός δοκιμών με ελληνικά',
        'content' => 'A published page for checking conversion on pages.',
        'terms'   => array(),
    ),
);

$created = 0;

foreach ($demo_posts as $demo_post) {
    $existing_posts = get_posts(
        array(
            'post_type'      => $demo_post['type'],
            'post_status'    => 'any',
            'numberposts'    => 1,
            'fields'         => 'ids',
            'meta_key'       => '_agp_demo_key',
            'meta_value'     => $demo_post['key'],
        )
    );

    if ($existing_posts) {
        continue;
    }

    $post_id = wp_insert_post(
        array(
            'post_type'    => $demo_post['type'],
            'post_status'  => 'publish',
            'post_title'   => $demo_post['title'],
            'post_content' => $demo_post['content'],
            'meta_input'   => array('_agp_demo_key' => $demo_post['key']),
        ),
        true
    );

    if (is_wp_error($post_id)) {
        WP_CLI::error($post_id->get_error_message());
    }

    foreach ($demo_post['terms'] as $taxonomy => $term_name) {
        $result = wp_set_object_terms($post_id, $term_ids[$taxonomy][$term_name], $taxonomy);
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
    }

    $created++;
}

WP_CLI::success(sprintf('Demo data ready: %d posts/pages created; existing records were kept.', $created));
