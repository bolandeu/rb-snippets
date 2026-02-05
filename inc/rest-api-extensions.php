<?php
add_action('rest_api_init', 'register_yoast_rest_fields');

function register_yoast_rest_fields() {

    // 1) Пост-типы
    $post_types = get_post_types([
        'public'       => true,
        'show_in_rest' => true,
    ], 'names');

    foreach ($post_types as $post_type) {

        register_rest_field($post_type, 'seo_title', [
            'get_callback' => function ($object) use ($post_type) {
                $post_id = isset($object['id']) ? (int) $object['id'] : 0;
                if (!$post_id) return '';

                $post  = get_post($post_id);
                $title = (string) get_post_meta($post_id, '_yoast_wpseo_title', true);

                if ($title === '' && function_exists('wpseo_replace_vars')) {
                    $titles_options = get_option('wpseo_titles', []);
                    $template_key   = 'title-' . $post_type;
                    $tpl            = (is_array($titles_options) && isset($titles_options[$template_key])) ? (string) $titles_options[$template_key] : '';

                    if ($tpl !== '' && $post) {
                        $title = wpseo_replace_vars($tpl, $post);
                    }
                }

                return $title !== '' ? $title : get_the_title($post_id);
            },
            'update_callback' => function ($value, $object) {
                if (!isset($object->ID)) return false;
                return update_post_meta($object->ID, '_yoast_wpseo_title', sanitize_text_field(wp_strip_all_tags((string) $value)));
            },
            'schema' => [
                'type' => 'string',
                'arg_options' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_field($post_type, 'seo_description', [
            'get_callback' => function ($object) use ($post_type) {
                $post_id = isset($object['id']) ? (int) $object['id'] : 0;
                if (!$post_id) return '';

                $post = get_post($post_id);
                $desc = (string) get_post_meta($post_id, '_yoast_wpseo_metadesc', true);

                if ($desc === '' && function_exists('wpseo_replace_vars')) {
                    $titles_options = get_option('wpseo_titles', []);
                    $template_key   = 'metadesc-' . $post_type;
                    $tpl            = (is_array($titles_options) && isset($titles_options[$template_key])) ? (string) $titles_options[$template_key] : '';

                    if ($tpl !== '' && $post) {
                        $desc = wpseo_replace_vars($tpl, $post);
                    }
                }

                return $desc;
            },
            'update_callback' => function ($value, $object) {
                if (!isset($object->ID)) return false;
                return update_post_meta($object->ID, '_yoast_wpseo_metadesc', sanitize_text_field(wp_strip_all_tags((string) $value)));
            },
            'schema' => [
                'type' => 'string',
                'arg_options' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_field($post_type, 'seo_noindex', [
            'get_callback' => function ($object) {
                $post_id = isset($object['id']) ? (int) $object['id'] : 0;
                return $post_id ? (string) get_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', true) : '';
            },
            'update_callback' => function ($value, $object) {
                if (!isset($object->ID)) return false;
                return update_post_meta($object->ID, '_yoast_wpseo_meta-robots-noindex', sanitize_text_field((string) $value));
            },
            'schema' => ['type' => 'string'],
        ]);

        register_rest_field($post_type, 'seo_nofollow', [
            'get_callback' => function ($object) {
                $post_id = isset($object['id']) ? (int) $object['id'] : 0;
                return $post_id ? (string) get_post_meta($post_id, '_yoast_wpseo_meta-robots-nofollow', true) : '';
            },
            'update_callback' => function ($value, $object) {
                if (!isset($object->ID)) return false;
                return update_post_meta($object->ID, '_yoast_wpseo_meta-robots-nofollow', sanitize_text_field((string) $value));
            },
            'schema' => ['type' => 'string'],
        ]);

        register_rest_field($post_type, 'seo_focus_keyword', [
            'get_callback' => function ($object) {
                $post_id = isset($object['id']) ? (int) $object['id'] : 0;
                return $post_id ? (string) get_post_meta($post_id, '_yoast_wpseo_focuskw', true) : '';
            },
            'update_callback' => function ($value, $object) {
                if (!isset($object->ID)) return false;
                return update_post_meta($object->ID, '_yoast_wpseo_focuskw', sanitize_text_field((string) $value));
            },
            'schema' => [
                'type' => 'string',
                'description' => 'Yoast SEO focus keyword',
            ],
        ]);

        register_rest_field($post_type, 'seo_meta_keywords', [
            'get_callback' => function ($object) {
                $post_id = isset($object['id']) ? (int) $object['id'] : 0;
                return $post_id ? (string) get_post_meta($post_id, '_yoast_wpseo_metakeywords', true) : '';
            },
            'update_callback' => function ($value, $object) {
                if (!isset($object->ID)) return false;
                return update_post_meta($object->ID, '_yoast_wpseo_metakeywords', sanitize_text_field((string) $value));
            },
            'schema' => [
                'type' => 'string',
                'description' => 'Yoast SEO meta keywords',
            ],
        ]);

        register_rest_field($post_type, 'image_url', [
            'get_callback' => function ($object) {
                $post_id = isset($object['id']) ? (int) $object['id'] : 0;
                return $post_id ? (string) get_the_post_thumbnail_url($post_id, 'full') : '';
            },
            'schema' => [
                'type' => 'string',
                'description' => 'URL of the featured image',
            ],
        ]);
    }

    // 2) Таксономии
    $taxonomies = get_taxonomies(['show_in_rest' => true], 'names');

    // вспомогалки
    $get_term_ctx = function ($object, $request) {
        $term_id = 0;
        $tax     = '';

        // get_callback обычно получает массив подготовленных данных [web:3]
        if (is_array($object)) {
            if (isset($object['id'])) $term_id = (int) $object['id'];
            if (isset($object['taxonomy'])) $tax = (string) $object['taxonomy'];
        }

        // update_callback может получить объект (WP_Term) или другое [web:3][web:4]
        if (!$term_id && is_object($object) && isset($object->term_id)) $term_id = (int) $object->term_id;
        if ($tax === '' && is_object($object) && isset($object->taxonomy)) $tax = (string) $object->taxonomy;

        // Фолбэк из request
        if ((!$term_id || $tax === '') && $request instanceof WP_REST_Request) {
            $req_id  = $request->get_param('id');
            $req_tax = $request->get_param('taxonomy');
            if (!$term_id && $req_id) $term_id = (int) $req_id;
            if ($tax === '' && $req_tax) $tax = (string) $req_tax;
        }

        return [$term_id, $tax];
    };

    $get_taxmeta_value = function ($taxonomy, $term_id, $key) {
        $options = get_option('wpseo_taxonomy_meta', []); // важно: дефолт массив [web:20]
        if (!is_array($options)) return '';
        if (!isset($options[$taxonomy]) || !is_array($options[$taxonomy])) return '';
        if (!isset($options[$taxonomy][$term_id]) || !is_array($options[$taxonomy][$term_id])) return '';
        return isset($options[$taxonomy][$term_id][$key]) ? (string) $options[$taxonomy][$term_id][$key] : '';
    };

    $set_taxmeta_value = function ($taxonomy, $term_id, $key, $value) {
        $options = get_option('wpseo_taxonomy_meta', []);
        if (!is_array($options)) $options = [];
        if (!isset($options[$taxonomy]) || !is_array($options[$taxonomy])) $options[$taxonomy] = [];
        if (!isset($options[$taxonomy][$term_id]) || !is_array($options[$taxonomy][$term_id])) $options[$taxonomy][$term_id] = [];

        $options[$taxonomy][$term_id][$key] = sanitize_text_field((string) $value);
        return update_option('wpseo_taxonomy_meta', $options);
    };

    foreach ($taxonomies as $taxonomy) {

        register_rest_field($taxonomy, 'seo_title', [
            'get_callback' => function ($object, $field_name, $request) use ($get_term_ctx, $get_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return '';

                $term = get_term($term_id, $tax);
                if (!$term || is_wp_error($term)) return '';

                $title = $get_taxmeta_value($tax, $term_id, 'wpseo_title');

                if ($title === '') {
                    $titles_options = get_option('wpseo_titles', []);
                    $template_key   = 'title-tax-' . $tax;
                    $title          = (is_array($titles_options) && isset($titles_options[$template_key])) ? (string) $titles_options[$template_key] : '';
                }

                if ($title !== '' && function_exists('wpseo_replace_vars')) {
                    $title = wpseo_replace_vars($title, $term);
                }

                return $title !== '' ? $title : (string) $term->name;
            },
            'update_callback' => function ($value, $object, $field_name, $request) use ($get_term_ctx, $set_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return false;
                return $set_taxmeta_value($tax, $term_id, 'wpseo_title', $value);
            },
            'schema' => [
                'type' => 'string',
                'description' => 'Yoast SEO Title for the term',
            ],
        ]);

        register_rest_field($taxonomy, 'seo_description', [
            'get_callback' => function ($object, $field_name, $request) use ($get_term_ctx, $get_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return '';

                $term = get_term($term_id, $tax);
                if (!$term || is_wp_error($term)) return '';

                $desc = $get_taxmeta_value($tax, $term_id, 'wpseo_desc');

                if ($desc === '') {
                    $titles_options = get_option('wpseo_titles', []);
                    $template_key   = 'metadesc-tax-' . $tax;
                    $desc           = (is_array($titles_options) && isset($titles_options[$template_key])) ? (string) $titles_options[$template_key] : '';
                }

                if ($desc !== '' && function_exists('wpseo_replace_vars')) {
                    $desc = wpseo_replace_vars($desc, $term);
                }

                return $desc !== '' ? $desc : (string) $term->description;
            },
            'update_callback' => function ($value, $object, $field_name, $request) use ($get_term_ctx, $set_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return false;
                return $set_taxmeta_value($tax, $term_id, 'wpseo_desc', $value);
            },
            'schema' => [
                'type' => 'string',
                'description' => 'Yoast SEO Description for the term',
            ],
        ]);

        register_rest_field($taxonomy, 'seo_noindex', [
            'get_callback' => function ($object, $field_name, $request) use ($get_term_ctx, $get_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return '';
                return $get_taxmeta_value($tax, $term_id, 'wpseo_noindex');
            },
            'update_callback' => function ($value, $object, $field_name, $request) use ($get_term_ctx, $set_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return false;
                return $set_taxmeta_value($tax, $term_id, 'wpseo_noindex', $value);
            },
            'schema' => ['type' => 'string'],
        ]);

        register_rest_field($taxonomy, 'seo_nofollow', [
            'get_callback' => function ($object, $field_name, $request) use ($get_term_ctx, $get_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return '';
                return $get_taxmeta_value($tax, $term_id, 'wpseo_nofollow');
            },
            'update_callback' => function ($value, $object, $field_name, $request) use ($get_term_ctx, $set_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return false;
                return $set_taxmeta_value($tax, $term_id, 'wpseo_nofollow', $value);
            },
            'schema' => ['type' => 'string'],
        ]);

        register_rest_field($taxonomy, 'seo_focus_keyword', [
            'get_callback' => function ($object, $field_name, $request) use ($get_term_ctx, $get_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return '';
                return $get_taxmeta_value($tax, $term_id, 'wpseo_focuskw');
            },
            'update_callback' => function ($value, $object, $field_name, $request) use ($get_term_ctx, $set_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return false;
                return $set_taxmeta_value($tax, $term_id, 'wpseo_focuskw', $value);
            },
            'schema' => [
                'type' => 'string',
                'description' => 'Yoast SEO focus keyword for the term',
            ],
        ]);

        register_rest_field($taxonomy, 'seo_meta_keywords', [
            'get_callback' => function ($object, $field_name, $request) use ($get_term_ctx, $get_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return '';
                return $get_taxmeta_value($tax, $term_id, 'wpseo_metakeywords');
            },
            'update_callback' => function ($value, $object, $field_name, $request) use ($get_term_ctx, $set_taxmeta_value) {
                [$term_id, $tax] = $get_term_ctx($object, $request);
                if (!$term_id || $tax === '') return false;
                return $set_taxmeta_value($tax, $term_id, 'wpseo_metakeywords', $value);
            },
            'schema' => [
                'type' => 'string',
                'description' => 'Yoast SEO meta keywords for the term',
            ],
        ]);
    }
}


/**
 * ACF options endpoint
 */
add_action('rest_api_init', 'register_acf_options_endpoint');

function register_acf_options_endpoint() {
    register_rest_route('siteoptions/v1', '/options', [
        [
            'methods'  => 'GET',
            'callback' => 'get_acf_options',
            'permission_callback' => function() { return is_user_logged_in(); },
        ],
        [
            'methods'  => 'POST',
            'callback' => 'update_acf_options',
            'permission_callback' => function() { return is_user_logged_in(); },
        ],
    ]);
}

function get_acf_options() {
    if (!function_exists('get_fields')) {
        return new WP_Error('acf_not_active', 'ACF плагин не активен', ['status' => 500]);
    }
    return get_fields('options');
}

function update_acf_options(WP_REST_Request $request) {
    if (!function_exists('update_field')) {
        return new WP_Error('acf_not_active', 'ACF плагин не активен', ['status' => 500]);
    }

    $fields = json_decode($request->get_body(), true);
    if (is_array($fields) && $fields) {
        foreach ($fields as $k => $v) {
            update_field($k, $v, 'options');
        }
    }

    return get_fields('options');
}
