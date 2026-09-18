<?php
if (!defined('ABSPATH')) exit;
get_header();
while(have_posts()){the_post(); ?>
<div class="page width clearfix thai-contact-page"><div class="content clearfix"><div class="content-view">
<?php echo do_shortcode(get_the_content()); ?>
</div></div></div>
<?php } get_footer(); ?>