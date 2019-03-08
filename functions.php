<?php
require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');

/*remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_print_styles', 'print_emoji_styles');
*/
add_theme_support('post-formats', array(
    'aside',
    'gallery',
    'image',
    'link',
    'quote',
    'status',
    'video',
    'audio',
    'chat',
));
add_theme_support('post-thumbnails');
add_theme_support('custom-background');
add_theme_support('custom-header');
add_theme_support('title-tag');

add_action('wp_enqueue_scripts', 'dequeue_plugins_style', 9999);
function dequeue_plugins_style(){
    //プラグインIDを指定し解除する
    wp_dequeue_style('wp-block-library');
    wp_deregister_script('jquery');
}

add_action('after_setup_theme', 'register_my_menu');
function register_my_menu(){
    register_nav_menu('global-navi', 'グローバルナビ');
    register_nav_menu('footer-navi', 'フッターナビ');
    register_nav_menu('about-navi', '企業情報ナビ');
    register_nav_menu('business-navi', '事業内容ナビ');
    register_nav_menu('recruit-navi', '採用情報ナビ');
    register_nav_menu('sitemap', 'サイトマップ');
}

add_action('init', 'create_post_type');
function create_post_type(){
    register_post_type('news', array(
        'labels'=>array(
            'name'=>'NEWS',
            'singular_name'=>'news',
        ),
        'public'=>true,
        'has_archive'=>false,
        'show_in_rest'=>true,
        'menu_position'=>5,
        'supports'=>array(
            'title',
            'editor',
            'author',
            'thumbnail',
            'excerpt',
            'trackbacks',
            'custom-fields',
            'comments',
            'revisions',
            'page-attributes',
            'post-formats',
        )
    ));
}

/**
 * ニュース一覧取得
 */
add_shortcode('news_list', 'getNewsList');
function getNewsList($atts){
    extract(shortcode_atts(array(
        'list_num'=>5,
    ), $atts));
    
    $args = array(
        'posts_per_page'   => $list_num,//表示する記事の数
        'offset'           => 0,
        'category'         => '',
        'category_name'    => '',
        'orderby'          => 'date',
        'order'            => 'DESC',
        'include'          => '',
        'exclude'          => '',
        'meta_key'         => '',
        'meta_value'       => '',
        'post_type'        => 'news',
        'post_mime_type'   => '',
        'post_parent'      => '',
        'author'	   => '',
        'post_status'      => 'publish',
        'suppress_filters' => true 
    );
    $posts_array = get_posts($args);
    
    $html = '<dl>';
    foreach($posts_array as $post){
        setup_postdata($post);
        $html .= '<dt>';
        $html .= get_the_time('Y.n', $post->ID);
        $html .= '</dt>';
        $html .= '<dd>';
        $html .= '<a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a>';
        $html .= '</dd>';
    }
    $html .= '</dl>';
    wp_reset_postdata();
    
    return $html;
}

/**
 * パンくずリスト取得
 */
function getBreadcrumbList($wp_obj = null){
    //トップページでは何も出力しない
    if(is_home() || is_front_page()) return false;
    
    $connector = '<span>&nbsp;&gt;&nbsp;&nbsp;</span>';
    
    $html = '<div id="breadcrumb">';
    $html .= '<a href="/" title="'.get_bloginfo('name').'">TOP</a>';
    
    //現在のwpオブジェクト取得
    $wp_obj = $wp_obj ?: get_queried_object();
    
    if(is_attachment()){
        //添付ファイルページ ( $wp_obj : WP_Post )、添付ファイルページでは is_single() も true になるので先に分岐
        $html .= $connector.'<span>'.$wp_obj->post_title.'</span>';
    }else if(is_single()){
        //投稿ページ ( $wp_obj : WP_Post )
        //カスタム投稿タイプかどうか
        if($wp_obj->post_type !== 'post'){
            //投稿タイプに紐づいたタクソノミーを取得 (投稿フォーマットは除く)
            $tax_array = get_object_taxonomies($wp_obj->post_type, 'names');
            foreach($tax_array as $tax_name){
                if ($tax_name !== 'post_format') {
                    $the_tax = $tax_name;
                    break;
                }
            }
            
            //カスタム投稿タイプ名の表示
            $html .= $connector.'<a href="'.get_post_type_archive_link($wp_obj->post_type).'">'.get_post_type_object($wp_obj->post_type)->label.'</a>';
        }else{
            //通常の投稿の場合、カテゴリーを表示
            $the_tax = 'category';
        }
        
        //タクソノミーが紐づいていれば表示
        if($the_tax !== ""){
            //子を持たないタームだけを集める配列
            $child_terms = array();
            
            //子を持つタームだけを集める配列
            $parents_list = array();
            
            //投稿に紐づくタームを全て取得
            $terms = get_the_terms($wp_obj->ID, $the_tax);
            
            if(!empty($terms)){
                //全タームの親IDを取得
                foreach($terms as $term){
                    if($term->parent !== 0 ) $parents_list[] = $term->parent;
                }
                
                //親リストに含まれないタームのみ取得
                foreach($terms as $term){
                    if(!in_array($term->term_id, $parents_list)) $child_terms[] = $term;
                }
                
                //最下層のターム配列から一つだけ取得
                $term = $child_terms[0];
                
                if($term->parent !== 0){
                    //親タームのIDリストを取得
                    $parent_array = array_reverse(get_ancestors($term->term_id, $the_tax));
                    
                    foreach($parent_array as $parent_id){
                        $parent_term = get_term($parent_id, $the_tax);
                        $html .= $connector.'<a href="'.get_term_link($parent_id, $the_tax).'">'.$parent_term->name.'</a>';
                    }
                }
                
                //最下層のタームを表示
                $html .= $connector.'<a href="'.get_term_link($term->term_id, $the_tax).'">'.$term->name.'</a>';
            }
        }
        
        //投稿自身の表示
        $html .= $connector.'<span>'.$wp_obj->post_title.'</span>';
    }else if(is_page()){
        //固定ページ ( $wp_obj : WP_Post )
        //親ページがあれば順番に表示
        if ($wp_obj->post_parent !== 0){
            $parent_array = array_reverse(get_post_ancestors($wp_obj->ID));
            foreach($parent_array as $parent_id){
                $html .= $connector.'<a href="'.get_permalink($parent_id).'">'.get_the_title($parent_id).'</a>';
            }
        }
        
        //投稿自身の表示
        $html .= $connector.'<span>'.$wp_obj->post_title.'</span>';
    }else if(is_post_type_archive()){
        //投稿タイプアーカイブページ ( $wp_obj : WP_Post_Type )
        $html .= '<span>'.$connector.$wp_obj->label.'</span>';
    }else if(is_date()){
        //日付アーカイブ ( $wp_obj : null )
        $year = get_query_var('year');
        $month = get_query_var('monthnum');
        $day = get_query_var('day');
        
        if($day !== 0){
            $html .= $connector.'<a href="'.get_year_link($year).'">'.$year.'年</a>';
            $html .= $connector.'<a href="'.get_month_link($year, $month).'">'.$month.'月</a>';
            $html .= $connector.'<span>'.$day.'日'.'</span>';
        }else if($month !== 0){
            $html .= $connector.'<a href="'.get_year_link($year).'">'.$year.'年</a>';
            $html .= $connector.'<span>'.$month.'月'.'</span>';
        }else{
            $html .= $connector.'<span>'.$year.'年'.'<span>';
        }
    }else if(is_author()){
        //投稿者アーカイブ ( $wp_obj : WP_User )
        $html .= '<span>'.$connector.$wp_obj->display_name.'の執筆記事'.'<span>';
    }else if(is_archive()){
        //タームアーカイブ ( $wp_obj : WP_Term )
        //親ページがあれば順番に表示
        if($wp_obj->parent !== 0){
            $parent_array = array_reverse(get_ancestors($wp_obj->term_id, $wp_obj->taxonomy));
            foreach($parent_array as $parent_id){
                $parent_term = get_term($parent_id, $tax_name);
                $html .= $connector.'<a href="'.get_term_link($parent_id, $tax_name).'">'.$parent_term->name.'</a>';
            }
        }
        
        //ターム自身の表示
        $html .= $connector.'<span>'.$wp_obj->name.'</span>';
    }else if(is_search()){
        //検索結果ページ
        $html .= $connector.'<span>'.'「'. get_search_query() .'」で検索した結果'.'<span>';
    }else if(is_404()){
        //404ページ
        $html .= $connector.'<span>'.'お探しの記事は見つかりませんでした。'.'<span>';
    }else{
        //その他のページ（無いと思うが一応）
        $html .= $connector.'<span>'.get_the_title().'<span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * カスタムメニューをショートコードで取得
 */
add_shortcode('custom_menu', 'getCustomMenu');
function getCustomMenu($atts){
    extract(shortcode_atts(array(
        'name'=>'',
    ), $atts));
    
    return wp_nav_menu(array('theme_location'=>$name, 'echo'=>false));
}

/**
 * テンプレートディレクトリURLをショートコードで取得
 */
add_shortcode('get_template_directory_uri', 'getTemplateDirectoryUri');
function getTemplateDirectoryUri(){
    return get_template_directory_uri();
}

/**
 * Minify実行
 */
function runMinify(){
    echo getcwd();
    echo '<br>';
    echo __FILE__;
    echo '<br>';
    echo __DIR__;
    echo '<br>';
    //echo dirname(__FILE__);
    /*$list = [];
    $a = glob(__DIR__.'/*');
    //var_dump($a);
    //echo '<br>';echo '<br>';
    foreach($a as $k=>$v){
        $list[$v] = $v;
        $b = glob($v.'/*');
        foreach($b as $k2=>$v2){
            $list[$v2] = $v2;
        }
        //var_dump($b);
        //echo '<br>';echo '<br>';
    }*/
    $list = getFileList(__DIR__);
    print_r($list);
    //var_dump($list);
}

/**
 * ファイル取得
 */
function getFileList($dir){
    $files = glob(rtrim($dir, '/') . '/*');
    $list = array();
    foreach($files as $file){
        if(is_file($file)){
            $list[] = $file;
        }else if(is_dir($file)){
            $list = array_merge($list, getFileList($file));
        }
    }
    return $list;
}


/**
 * ファイルの更新日時のタイムスタンプでキャッシュリセットしたURLを取得
 */
function getFileCacheReset($file_name){
    return get_template_directory_uri().$file_name.'?'.filemtime(get_template_directory().$file_name);
}

/**
 * cssの圧縮(mynify)
 */
/*mynifyCss();
function mynifyCss(){
    global $wp_filesystem;
    
    //オリジナルCSS
    $origin_file_path = get_template_directory().'/style.css';
    //minify後CSS
    $minify_file_path = get_template_directory() . '/style.min.css';
    
    //オリジナルCSSの更新日時タイムスタンプ取得
    $origin_filetime = filemtime($origin_file_path);
    //minify後CSSの更新日時タイムスタンプ取得
    $minify_filetime = filemtime($minify_file_path);
    
    //オリジナルの方がminifyよりも新しい場合
    if($minify_filetime < $origin_filetime){
        //オリジナルのファイル取得
        $css = WP_Filesystem_Direct::get_contents($origin_file_path);
        //$css = $wp_filesystem->get_contents($origin_file_path);
        //mynify実行
        //$css = minify_css($css);
        //$wp_filesystem->put_contents($minify_file_path, $css);
    }
}*/
