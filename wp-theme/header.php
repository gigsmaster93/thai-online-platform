<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
  <div class="site-wrap">
    <div class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>">Thai Online</a></div>
    <nav class="site-nav">
      <a href="<?php echo esc_url(home_url('/shop/all')); ?>">Экскурсии</a>
      <a href="<?php echo esc_url(home_url('/faq')); ?>">FAQ</a>
      <a href="<?php echo esc_url(home_url('/gb')); ?>">Отзывы</a>
      <a href="<?php echo esc_url(home_url('/contact')); ?>">Контакты</a>
    </nav>
  </div>
</header>
