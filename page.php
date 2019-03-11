<?php get_header(); ?>
<?php
if(have_posts()){
    while(have_posts()){
        the_post();
        //the_post_thumbnail('full', array('class'=>'eyecatch'));
        //echo getBreadcrumbList();
        the_content();
        //$content = get_the_content();
        //$content = apply_filters('the_content', $content);
        //$content = str_replace(']]>', ']]&gt;', $content);
        //$content = mynifyHtml($content);
        //echo $content;
    }
}
?>
<?php get_footer(); ?>