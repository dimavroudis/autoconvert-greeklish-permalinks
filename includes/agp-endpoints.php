<?php
    /**
     * The class responsible for the Rest API
     *
     * @package    Agp
     * @subpackage Agp/includes
     *
     * @since    4.0.0
     *
     */
    class Agp_Endpoints
    {
        /**
         * The plugin path of this plugin.
         *
         * @since    4.0.0
         * @access   protected
         * @var      string $plugin_path The plugin path of this plugin.
         */
        protected $plugin_path;
        /**
         * The ID of this plugin.
         *
         * @since    4.0.0
         * @access   private
         * @var      string $plugin_name The ID of this plugin.
         */
        private $plugin_name;
        /**
         * The version of this plugin.
         *
         * @since    4.0.0
         * @access   private
         * @var      string $version The current version of this plugin.
         */
        private $version;

        /**
         * The api version 
         * 
         * @since    4.0.0
         * @access   private
         * @var      string $api_version The current api version.
         */
        private $api_version = '1';

        /**
         * The namespace for the api 
         * 
         * @since    4.0.0
         * @access   private
         * @var      string $namespace
         */
        private $namespace;

        /**
         * An instance of the converter class
         *
         * @since    4.0.0
         * @access   protected
         * @var Agp_Converter
         */
        protected $converter;

        public function __construct($plugin_name, $version, $plugin_path)
        {

            $this->plugin_name = $plugin_name;
            $this->plugin_path = $plugin_path;
            $this->version     = $version;
            $this->namespace   = $this->plugin_name . '/v' . $this->api_version;
            $this->converter   = new Agp_Converter();
        }


        public function register_routes()
        {
            register_rest_route($this->namespace, 'check-permalinks', array(
                'methods' => 'POST',
                'callback' => array($this, 'check'),
                'args' => array(
                    'post_types' => array(
                        'description' => __('Post types to convert', 'agp'),
                        'validate_callback' => function ($param, $request, $key) {
                            return is_array($param);
                        }
                    ),
                    'taxonomies' => array(
                        'description' => __('Taxonomies to convert', 'agp'),
                        'validate_callback' => function ($param, $request, $key) {
                            return is_array($param);
                        }
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ));

            register_rest_route($this->namespace, 'convert-permalinks', array(
                'methods' => 'POST',
                'callback' => array($this, 'convert'),
                'args' => array(
                    'post_types' => array(
                        'description' => __('Post types to convert', 'agp'),
                        'validate_callback' => function ($param, $request, $key) {
                            return is_array($param);
                        }
                    ),
                    'taxonomies' => array(
                        'description' => __('Taxonomies to convert', 'agp'),
                        'validate_callback' => function ($param, $request, $key) {
                            return is_array($param);
                        }
                    ),
                    'limit'  => array(
                        'default' => 100,
                        'description' => __('Max number of items to be converted', 'agp'),
                        'sanitize_callback' => 'absint',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ));
        }


        public function check($data)
        {
            $this->converter = new Agp_Converter();
            //Get posts types
            $post_types = array();
            if (isset($data['post_types']) && is_array($data['post_types'])) {
                foreach ($data['post_types'] as $post_type) {
                    if (!post_type_exists($post_type)) {
                        return new WP_Error('invalid_post_types', sprintf(__("'%s' is not a registered post type.", "agp"), $post_type), $data['post_types']);
                    } else {
                        $post_types[] = $post_type;
                    }
                }
            }

            //Get taxonomies
            $taxonomies = array();
            if (isset($data['taxonomies']) && is_array($data['taxonomies'])) {

                foreach ($data['taxonomies'] as $taxonomy) {
                    if (!taxonomy_exists($taxonomy)) {
                        return new WP_Error('invalid_taxonomy', sprintf(__("'%s' is not a registered taxonomy.", "agp"), $taxonomy), $data['taxonomies']);
                    } else {
                        $taxonomies[] = $taxonomy;
                    }
                }
            }

            if (empty($post_types) && empty($taxonomies)) {
                return new WP_Error('no_posttypes_taxonomies_selected', __('No post types or taxonomies selected', 'agp'), $data);
            }

            $post_count = $this->converter->postQuery($post_types);
            $term_count = $this->converter->termQuery($taxonomies);

            $count = $post_count + $term_count;

            if ($count !== 0) {
                return array(
                    'data' => array(
                        'posts' => $post_count,
                        'terms' => $term_count
                    ),
                    'message' => sprintf(__('%d posts and %d terms are in greek.', 'agp'), $post_count, $term_count)
                );
            } else {
                return array(
                    'data' => array(
                        'posts' => $post_count,
                        'terms' => $term_count
                    ),
                    'message' => __('All your permalinks were already in greeklish.', 'agp')
                );
            }
        }

        public function convert($data)
        {
            $limit = $data['limit'];
            $count = 0;
            $post_count = $term_count = 0;
            $posts_to_update =  $terms_to_update = array();
            $post_types =  $taxonomies = array();

            //Get posts types
            if (isset($data['post_types']) && is_array($data['post_types'])) {
                foreach ($data['post_types'] as $post_type) {
                    if (!post_type_exists($post_type)) {
                        return new WP_Error('invalid_post_types', sprintf(__("'%s' is not a registered post type.", "agp"), $post_type), $data['post_types']);
                    } else {
                        $post_types[] = $post_type;
                    }
                }
            }

            //Get taxonomies
            if (isset($data['taxonomies']) && is_array($data['taxonomies'])) {
                foreach ($data['taxonomies'] as $taxonomy) {
                    if (!taxonomy_exists($taxonomy)) {
                        return new WP_Error('invalid_taxonomy', sprintf(__("'%s' is not a registered taxonomy.", "agp"), $taxonomy), $data['taxonomies']);
                    } else {
                        $taxonomies[] = $taxonomy;
                    }
                }
            }

            if (empty($post_types) && empty($taxonomies)) {
                return new WP_Error('no_posttypes_taxonomies_selected', __('No post types or taxonomies selected', 'agp'), $data);
            }


            $posts_to_update = $this->converter->postQuery($post_types, 'object', $limit);
            if ($posts_to_update) {
                $count += count($posts_to_update);
            }

            if ($count < 100) {
                $terms_to_update = $this->converter->termQuery($taxonomies, 'object', $limit - $count);
                if ($terms_to_update) {
                    $count += count($terms_to_update);
                }
            }

            if ($count !== 0) {

                //Update posts
                if (!empty($posts_to_update)) {
                    foreach ($posts_to_update as $post) {
                        $is_converted = wp_update_post($post, true);
                        if (!is_wp_error($is_converted)) {
                            $post_count++;
                        }
                    }
                }

                //Update terms
                if (!empty($terms_to_update)) {
                    foreach ($terms_to_update as $term) {
                        $is_converted = Agp_Converter::updateTerm($term['id'], $term['taxonomy'], $term['slug']);
                        if (!is_wp_error($is_converted)) {
                            $term_count++;
                        }
                    }
                }
            }

            return array(
                'data' => array(
                    'posts' => $post_count,
                    'terms' => $term_count
                ),
            );
        }

        /**
         * Pass REST API Endpoints for admin.js
         *
         * @since    1.0.0
         *
         * @param   string      The hook
         */
        public function localize_scripts()
        {
            wp_localize_script('agp-admin', 'AgpSettings', array(
                'root' => esc_url_raw(rest_url($this->namespace)),
                'endpoints' => array(
                    'check' =>  esc_url_raw(rest_url($this->namespace . '/check-permalinks')),
                    'convert' =>  esc_url_raw(rest_url($this->namespace . '/convert-permalinks'))
                ),
                'nonce' => wp_create_nonce('wp_rest'),
                'messages' => array(
                    'converting' => __('<b>Conversion in progress!</b> Keep the window open.<br>Converted {0}/{1} posts and {2}/{3} terms.', 'agp'),
                    'success' => __('<b>Conversion complete!</b><br>Converted {0} posts and {1} terms.', 'agp')
                )
            ));
        }
    }
