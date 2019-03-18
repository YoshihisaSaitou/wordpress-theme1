<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="shortcut icon" href="<?php bloginfo('template_url'); ?>/favicon.ico">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?php echo getFileCacheReset('/js/common.js'); ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo getFileCacheReset('/style.css'); ?>" />
<?php wp_head(); ?>
</head>
<body>
<div id="app">
<header>
    <h1><a href="/">abc株式会社</a></h1>
    <nav>
        <input id="header_nav_input" class="nav_input nav_unshown" type="checkbox">
        <label class="nav_open" for="header_nav_input"><span></span></label>
        <label class="nav_close nav_unshown" for="header_nav_input"></label>
        <div class="nav_content"><?php wp_nav_menu(array('theme_location'=>'global-navi')); ?></div>
    </nav>
</header>
<div id="contents">
