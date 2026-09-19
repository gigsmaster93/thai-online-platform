<?php
if (!defined('ABSPATH')) exit;
status_header(404);
get_header();
?>
<main class="site-wrap"><h1>Материал не найден</h1><p>Проверьте адрес или <a href="<?php echo esc_url(home_url('/shop/all')); ?>">вернитесь в каталог экскурсий</a>.</p></main>
<?php get_footer(); ?>