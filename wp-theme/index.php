<?php get_header(); ?>
<main class="site-wrap">
<?php if(have_posts()): while(have_posts()): the_post(); ?>
<article>
<h1><?php the_title(); ?></h1>
<?php the_content(); ?>
</article>
<?php endwhile; else: ?>
<h1>Материал не найден</h1>
<?php endif; ?>
</main>
<?php get_footer(); ?>
