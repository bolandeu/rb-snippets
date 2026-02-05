<?php
/**
 * RB Snippets - Blocksy Theme Extensions
 *
 * Расширения для темы Blocksy (добавление соцсетей и др.)
 * Активируется только если активна тема Blocksy или её дочерняя тема
 */

if (!defined('ABSPATH')) exit;

/**
 * Проверка активности темы Blocksy
 */
function rb_is_blocksy_theme() {
    $theme = wp_get_theme();
    $theme_name = $theme->get('Name');
    $parent_theme = $theme->parent();

    // Проверяем основную тему или родительскую
    if (strtolower($theme_name) === 'blocksy' ||
        ($parent_theme && strtolower($parent_theme->get('Name')) === 'blocksy')) {
        return true;
    }

    return false;
}

// Не подключаем функционал если тема не Blocksy
if (!rb_is_blocksy_theme()) {
    return;
}

/**
 * Добавление кастомных социальных сетей в Blocksy
 */
add_filter('blocksy:social-box:dynamic-social-networks', function ($networks) {
    // Дзен
    $networks[] = [
        'id' => 'dzen',
        'name' => __('DZEN', 'blocksy'),
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50"><path d="M46.894 23.986c.004 0 .007 0 .011 0 .279 0 .545-.117.734-.322.192-.208.287-.487.262-.769C46.897 11.852 38.154 3.106 27.11 2.1c-.28-.022-.562.069-.77.262-.208.192-.324.463-.321.746C26.193 17.784 28.129 23.781 46.894 23.986zM46.894 26.014c-18.765.205-20.7 6.202-20.874 20.878-.003.283.113.554.321.746.186.171.429.266.679.266.03 0 .061-.001.091-.004 11.044-1.006 19.787-9.751 20.79-20.795.025-.282-.069-.561-.262-.769C47.446 26.128 47.177 26.025 46.894 26.014zM22.823 2.105C11.814 3.14 3.099 11.884 2.1 22.897c-.025.282.069.561.262.769.189.205.456.321.734.321.004 0 .008 0 .012 0 18.703-.215 20.634-6.209 20.81-20.875.003-.283-.114-.555-.322-.747C23.386 2.173 23.105 2.079 22.823 2.105zM3.107 26.013c-.311-.035-.555.113-.746.321-.192.208-.287.487-.262.769.999 11.013 9.715 19.757 20.724 20.792.031.003.063.004.094.004.25 0 .492-.094.678-.265.208-.192.325-.464.322-.747C23.741 32.222 21.811 26.228 3.107 26.013z"></path></svg>',
    ];

    // Rutube
    $networks[] = [
        'id' => 'rutube',
        'name' => __('RUTUBE', 'blocksy'),
        'icon' => '<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_12_17186)"><rect x="0.248047" y="0.396973" width="24.5234" height="24.5234" fill="#100943"></rect><path d="M24.7099 12.6281C31.4651 12.6281 36.9413 7.15191 36.9413 0.396693C36.9413 -6.35853 31.4651 -11.8347 24.7099 -11.8347C17.9547 -11.8347 12.4785 -6.35853 12.4785 0.396693C12.4785 7.15191 17.9547 12.6281 24.7099 12.6281Z" fill="#ED143B"></path><path d="M15.3586 12.0697H8.13142V9.2098H15.3586C15.7808 9.2098 16.0743 9.28338 16.2216 9.41188C16.3689 9.54037 16.4602 9.7787 16.4602 10.1268V11.1537C16.4602 11.5206 16.3689 11.7589 16.2216 11.8874C16.0743 12.0159 15.7808 12.0708 15.3586 12.0708V12.0697ZM15.8544 6.51355H5.06641V18.7439H8.13142V14.7648H13.7799L16.4602 18.7439H19.8924L16.9373 14.7462C18.0267 14.5847 18.516 14.2508 18.9194 13.7006C19.3229 13.1504 19.5252 12.2707 19.5252 11.0966V10.1796C19.5252 9.48326 19.4515 8.93303 19.3229 8.5113C19.1943 8.08956 18.9744 7.72274 18.6622 7.39326C18.3324 7.08135 17.9652 6.8617 17.5243 6.71453C17.0835 6.58604 16.5327 6.51245 15.8544 6.51245V6.51355Z" fill="white"></path></g><defs><clipPath id="clip0_12_17186"><rect x="0.248047" y="0.396729" width="24.4628" height="24.4628" rx="5.95041" fill="white"></rect></clipPath></defs></svg>',
    ];

    return $networks;
});


function rb_breadcrumbs() {
    global $post;
    
    // ========== НАЧАЛО БЛОКА: Отключение breadcrumbs на определенных страницах ==========
    // Не показываем breadcrumbs на архиве CPT (например, /catalog/)
    if (is_post_type_archive()) {
        return; // Закомментируйте эту строку, чтобы показывать breadcrumbs на архивах CPT
    }
	
	if (is_home() || is_category()) {
        return; // Закомментируйте эту строку, чтобы показывать breadcrumbs на архиве блога
    }
    
    // Не показываем breadcrumbs на главной странице (опционально)
    // if (is_front_page()) {
    //     return; // Раскомментируйте, чтобы скрыть на главной
    // }
    
    // Не показываем breadcrumbs на страницах категорий (опционально)
    // if (is_category()) {
    //     return; // Раскомментируйте, чтобы скрыть на категориях
    // }
    
    // Не показываем breadcrumbs на страницах таксономий (опционально)
    // if (is_tax()) {
    //     return; // Раскомментируйте, чтобы скрыть на архивах таксономий
    // }
    // ========== КОНЕЦ БЛОКА: Отключение breadcrumbs на определенных страницах ==========
    
    $page_num = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $separator = ' / ';
    $position = 1;
    $breadcrumbs = array();
    
    // Всегда добавляем главную
    $breadcrumbs[] = array(
        'url' => site_url(),
        'title' => 'Главная',
        'position' => $position++
    );
    
    if (is_front_page()) {
        if ($page_num > 1) {
            $breadcrumbs[] = array(
                'url' => '',
                'title' => $page_num . '-я страница',
                'position' => $position++
            );
        }
    } elseif (is_singular()) {
        $post_type = get_post_type();
        $post_type_obj = get_post_type_object($post_type);
        
        if ($post_type == 'post') {
            $categories = get_the_category();
            if ($categories) {
                $category = $categories[0];
                
                // Родительские категории
                if ($category->parent) {
                    $parent_cats = array();
                    $parent_id = $category->parent;
                    
                    while ($parent_id) {
                        $parent = get_category($parent_id);
                        $parent_cats[] = array(
                            'url' => get_category_link($parent->term_id),
                            'title' => $parent->name,
                            'position' => 0
                        );
                        $parent_id = $parent->parent;
                    }
                    
                    $parent_cats = array_reverse($parent_cats);
                    foreach ($parent_cats as $cat) {
                        $cat['position'] = $position++;
                        $breadcrumbs[] = $cat;
                    }
                }
                
                $breadcrumbs[] = array(
                    'url' => get_category_link($category->term_id),
                    'title' => $category->name,
                    'position' => $position++
                );
            }
            
            $breadcrumbs[] = array(
                'url' => '',
                'title' => get_the_title(),
                'position' => $position++
            );
            
        } else {
            // CPT
            $post_type_archive = get_post_type_archive_link($post_type);
            if ($post_type_archive && $post_type_obj) {
                $archive_name = ($post_type == 'catalog') ? 'Марки' : $post_type_obj->labels->name;
                $breadcrumbs[] = array(
                    'url' => $post_type_archive,
                    'title' => $archive_name,
                    'position' => $position++
                );
            }
            
            $taxonomies = get_object_taxonomies($post_type, 'objects');
            $taxonomies = array_filter($taxonomies, function($tax) {
                return !in_array($tax->name, array('post_tag', 'post_format'));
            });
            
            if (!empty($taxonomies)) {
                $taxonomy = reset($taxonomies);
                $terms = wp_get_post_terms(get_the_ID(), $taxonomy->name, array('orderby' => 'parent'));
                
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term = $terms[0];
                    
                    // Родительские термины
                    $parents = array();
                    $parent_id = $term->parent;
                    
                    while ($parent_id) {
                        $parent = get_term($parent_id, $taxonomy->name);
                        if (!is_wp_error($parent) && $parent) {
                            $parents[] = array(
                                'url' => get_term_link($parent),
                                'title' => $parent->name,
                                'position' => 0
                            );
                            $parent_id = $parent->parent;
                        } else {
                            break;
                        }
                    }
                    
                    if (!empty($parents)) {
                        $parents = array_reverse($parents);
                        foreach ($parents as $parent) {
                            $parent['position'] = $position++;
                            $breadcrumbs[] = $parent;
                        }
                    }
                    
                    $breadcrumbs[] = array(
                        'url' => get_term_link($term),
                        'title' => $term->name,
                        'position' => $position++
                    );
                }
            }
            
            $breadcrumbs[] = array(
                'url' => '',
                'title' => get_the_title(),
                'position' => $position++
            );
        }
        
    } elseif (is_page()) {
        if ($post->post_parent) {
            $parent_id = $post->post_parent;
            $parents = array();
            
            while ($parent_id) {
                $page = get_post($parent_id);
                $parents[] = array(
                    'url' => get_permalink($page->ID),
                    'title' => get_the_title($page->ID),
                    'position' => 0
                );
                $parent_id = $page->post_parent;
            }
            
            $parents = array_reverse($parents);
            foreach ($parents as $parent) {
                $parent['position'] = $position++;
                $breadcrumbs[] = $parent;
            }
        }
        
        $breadcrumbs[] = array(
            'url' => '',
            'title' => get_the_title(),
            'position' => $position++
        );
        
    } elseif (is_tax()) {
        $current_term = get_queried_object();
        $taxonomy = get_taxonomy($current_term->taxonomy);
        
        if (!empty($taxonomy->object_type[0])) {
            $post_type_obj = get_post_type_object($taxonomy->object_type[0]);
            $post_type_archive = get_post_type_archive_link($taxonomy->object_type[0]);
            
            if ($post_type_archive && $post_type_obj) {
                $archive_name = ($taxonomy->object_type[0] == 'catalog') ? 'Марки' : $post_type_obj->labels->name;
                $breadcrumbs[] = array(
                    'url' => $post_type_archive,
                    'title' => $archive_name,
                    'position' => $position++
                );
            }
        }
        
        $parents = array();
        $parent_id = $current_term->parent;
        
        while ($parent_id) {
            $parent = get_term($parent_id, $current_term->taxonomy);
            if (!is_wp_error($parent) && $parent) {
                $parents[] = array(
                    'url' => get_term_link($parent),
                    'title' => $parent->name,
                    'position' => 0
                );
                $parent_id = $parent->parent;
            } else {
                break;
            }
        }
        
        if (!empty($parents)) {
            $parents = array_reverse($parents);
            foreach ($parents as $parent) {
                $parent['position'] = $position++;
                $breadcrumbs[] = $parent;
            }
        }
        
        $breadcrumbs[] = array(
            'url' => '',
            'title' => $current_term->name,
            'position' => $position++
        );
        
    } elseif (is_post_type_archive()) {
        // Этот блок не выполнится из-за раннего return выше
        $breadcrumbs[] = array(
            'url' => '',
            'title' => post_type_archive_title('', false),
            'position' => $position++
        );
        
    } elseif (is_category()) {
        $category = get_queried_object();
        
        if ($category->parent) {
            $parents = array();
            $parent_id = $category->parent;
            
            while ($parent_id) {
                $parent = get_category($parent_id);
                $parents[] = array(
                    'url' => get_category_link($parent->term_id),
                    'title' => $parent->name,
                    'position' => 0
                );
                $parent_id = $parent->parent;
            }
            
            $parents = array_reverse($parents);
            foreach ($parents as $parent) {
                $parent['position'] = $position++;
                $breadcrumbs[] = $parent;
            }
        }
        
        $breadcrumbs[] = array(
            'url' => '',
            'title' => single_cat_title('', false),
            'position' => $position++
        );
        
    } elseif (is_tag()) {
        $breadcrumbs[] = array(
            'url' => '',
            'title' => single_tag_title('', false),
            'position' => $position++
        );
        
    } elseif (is_day()) {
        $breadcrumbs[] = array(
            'url' => get_year_link(get_the_time('Y')),
            'title' => get_the_time('Y'),
            'position' => $position++
        );
        $breadcrumbs[] = array(
            'url' => get_month_link(get_the_time('Y'), get_the_time('m')),
            'title' => get_the_time('F'),
            'position' => $position++
        );
        $breadcrumbs[] = array(
            'url' => '',
            'title' => get_the_time('d'),
            'position' => $position++
        );
        
    } elseif (is_month()) {
        $breadcrumbs[] = array(
            'url' => get_year_link(get_the_time('Y')),
            'title' => get_the_time('Y'),
            'position' => $position++
        );
        $breadcrumbs[] = array(
            'url' => '',
            'title' => get_the_time('F'),
            'position' => $position++
        );
        
    } elseif (is_year()) {
        $breadcrumbs[] = array(
            'url' => '',
            'title' => get_the_time('Y'),
            'position' => $position++
        );
        
    } elseif (is_author()) {
        global $author;
        $userdata = get_userdata($author);
        $breadcrumbs[] = array(
            'url' => '',
            'title' => 'Опубликовал(а) ' . $userdata->display_name,
            'position' => $position++
        );
        
    } elseif (is_search()) {
        $breadcrumbs[] = array(
            'url' => '',
            'title' => 'Результаты поиска для: ' . get_search_query(),
            'position' => $position++
        );
        
    } elseif (is_404()) {
        $breadcrumbs[] = array(
            'url' => '',
            'title' => 'Ошибка 404',
            'position' => $position++
        );
    }
    
    if ($page_num > 1) {
        $breadcrumbs[] = array(
            'url' => '',
            'title' => $page_num . '-я страница',
            'position' => $position++
        );
    }
    
    // Вывод HTML с разметкой Schema.org
    echo '<nav class="ct-breadcrumbs" data-source="default" itemscope itemtype="https://schema.org/BreadcrumbList">';
    
    $total = count($breadcrumbs);
    foreach ($breadcrumbs as $index => $crumb) {
        $is_last = ($index === $total - 1);
        
        // Открывающий span с классом
        if ($is_last) {
            echo '<span class="last-item" aria-current="page" itemscope itemprop="itemListElement" itemtype="https://schema.org/ListItem">';
        } else {
            echo '<span itemscope itemprop="itemListElement" itemtype="https://schema.org/ListItem">';
        }
        
        // Position
        echo '<meta itemprop="position" content="' . $crumb['position'] . '">';
        
        // Ссылка или текст (последний элемент БЕЗ ссылки)
        if (!empty($crumb['url']) && !$is_last) {
            echo '<a href="' . esc_url($crumb['url']) . '" itemprop="item">';
            echo '<span itemprop="name">' . esc_html($crumb['title']) . '</span>';
            echo '</a>';
            echo '<meta itemprop="url" content="' . esc_url($crumb['url']) . '"/>';
        } else {
            echo '<span itemprop="name">' . esc_html($crumb['title']) . '</span>';
        }
        
        // Разделитель внутри span (кроме последнего элемента)
        if (!$is_last) {
            echo '<span class="ct-separator">' . $separator . '</span>';
        }
        
        // Закрывающий span
        echo '</span>';
    }
    
    echo '</nav>';
}