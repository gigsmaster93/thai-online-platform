<?php
if (!defined('ABSPATH')) exit;
get_header();
while(have_posts()){the_post(); ?>
<div class="page width clearfix"><div class="content clearfix" style="width:100%"><div class="content-view">
<?php echo do_shortcode(get_the_content()); ?>
</div></div></div>
<?php } get_footer(); ?>