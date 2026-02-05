<?

/**
 * Добавляет фильтры по таксономиям в админку для всех типов записей
 */

add_action('restrict_manage_posts', 'add_custom_post_types_taxonomy_filters');

function add_custom_post_types_taxonomy_filters($post_type) {
    // Список типов записей, для которых НЕ нужно выводить фильтры
    $excluded_post_types = array('post', 'page', 'attachment');

    // Если текущий тип записи в списке исключений — выходим
    if (in_array($post_type, $excluded_post_types)) {
        return;
    }

    // Получаем все таксономии, привязанные к текущему произвольному типу записи
    $taxonomies = get_object_taxonomies($post_type, 'objects');

    foreach ($taxonomies as $taxonomy) {
        // Выводим фильтр, если таксономия публичная
        if ($taxonomy->show_ui) {
            $selected = isset($_GET[$taxonomy->name]) ? $_GET[$taxonomy->name] : '';
            
            wp_dropdown_categories(array(
                'show_option_all' => "Все",
                'taxonomy'        => $taxonomy->name,
                'name'            => $taxonomy->name,
                'orderby'         => 'name',
                'selected'        => $selected,
                'show_count'      => true,
                'hide_empty'      => false,
                'value_field'     => 'slug', // Важно для корректной фильтрации в URL
                'hierarchical'    => true,
                'depth'           => 3,
            ));
        }
    }
}





// 1. Добавляем колонку во все зарегистрированные типы записей
add_action('admin_init', 'add_thumbnail_column_to_all_post_types');

function add_thumbnail_column_to_all_post_types() {
    $post_types = get_post_types(array('public' => true), 'names');
    
    foreach ($post_types as $post_type) {
        // Фильтр для заголовков колонок
        add_filter("manage_{$post_type}_posts_columns", 'add_featured_image_column');
        // Действие для вывода контента в колонке
        add_action("manage_{$post_type}_posts_custom_column", 'display_featured_image_column', 10, 2);
    }
}

// 2. Определяем саму колонку и ставим её в начало (перед ID или заголовком)
function add_featured_image_column($columns) {
    // Создаем новый массив колонок, чтобы вставить миниатюру в нужное место
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        // Вставляем нашу колонку перед 'cb' (чекбокс) или 'title'
        if ($key === 'cb') {
            $new_columns[$key] = $value;
            $new_columns['admin_post_thumb'] = __('Фото');
            continue;
        }
        $new_columns[$key] = $value;
    }
    
    return $new_columns;
}

// 3. Выводим изображение
function display_featured_image_column($column, $post_id) {
    if ($column === 'admin_post_thumb') {
        if (has_post_thumbnail($post_id)) {
            // Выводим миниатюру с ограничением высоты 50px
            echo get_the_post_thumbnail($post_id, array(80, 50), array(
                'style' => 'max-height: 50px; width: auto; border-radius: 4px;'
            ));
        } else {
            echo '<span style="color:#ccc;">—</span>';
        }
    }
}

// 4. Немного CSS для оформления ширины колонки
add_action('admin_head', 'add_thumbnail_column_css');
function add_thumbnail_column_css() {
    echo '<style>
        .column-admin_post_thumb { width: 60px; }
        .column-admin_post_thumb img { display: block; }
    </style>';
}
