<?php get_header(); ?>
<?php
if(have_posts()){
    while(have_posts()){
        the_post();
        the_post_thumbnail('full', array('class'=>'eyecatch'));
        echo getBreadcrumbList();
        the_content();
    }
}
?>
<?php get_footer(); ?>