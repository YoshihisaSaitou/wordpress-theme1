<?php
$page_data = get_page_by_path('bannerbox');
$page = get_post($page_data);
echo $page->post_content;
?>
</div>
<footer>
    <div class="box1">
        <div class="logo">株式会社</div>
        <address>株式会社<br>〒000-0000<br>東京都</address>
    </div>
    <nav><?php wp_nav_menu(['theme_location'=>'footer-navi']); ?></nav>
    <small>Copyright © 20xx abc Co.,Ltd. All Rights Reserved.</small>
</footer>
</div>
<?php wp_footer(); ?>
<script src="<?php bloginfo('template_url'); ?>/js/common.js?<?php  echo '?' . filemtime(get_stylesheet_directory() . '/js/common.js'); ?>"></script>
</body>
</html>
