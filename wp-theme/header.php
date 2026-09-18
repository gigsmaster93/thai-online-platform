<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class('lazyload replacex'); ?> data-src="/img/bgx.webp">
<?php wp_body_open(); ?>
<div id="preloader"><div class="div1"></div></div>
<div class="notification notification--cookie"><div class="notification_inner"><div class="notification_content">Мы собираем ваши метаданные (cookie, IP-адрес и местоположение) для полноценного функционирования сайта. Если вы не согласны, пожалуйста, покиньте сайт.</div><button class="notification_close" aria-label="Закрыть">×</button></div></div>
<?php
$phone = get_theme_mod('thai_phone', '+66-838-383-539');
$email = get_theme_mod('thai_email', 'info@thai-online.org');
$wa = preg_replace('/\D+/', '', get_theme_mod('thai_whatsapp', '66838383539'));
$tg = ltrim(get_theme_mod('thai_telegram', 'thaionlinetours'), '@');
?>
<header class="header">
  <div class="header-main">
    <div class="width clearfix">
      <div class="left">
        <a href="<?php echo esc_url(home_url('/')); ?>" id="logo" class="clearfix">
          <div class="left"></div>
          <div class="right with-slogan"><span class="span2" style="float:right"><b><?php echo esc_html(get_theme_mod('thai_brand_name', 'Thai-Online')); ?></b><br><?php echo nl2br(esc_html(get_theme_mod('thai_slogan', "Все экскурсии\nбез переплат"))); ?></span><div id="xlogo"></div></div>
          <div class="topBonCont"></div>
        </a>
      </div>
      <div class="clearfix right">
        <div id="top-head-text" class="left"><div id="tht-area">
          <a href="#" id="tellnk"><span class="flaticon-phone-call top-text-icon"></span><span class="tht-up-text tohide">Позвонить</span></a>
          <div id="telblock" style="display:none;position:absolute">
            <a href="tel:<?php echo esc_attr($phone); ?>"><span class="flaticon-phone-call top-text-icon"></span><span class="tht-up-text"><?php echo esc_html($phone); ?></span></a>
            <a href="https://wa.me/<?php echo esc_attr($wa); ?>"><span class="tel-icos tel-wh top-text-icon"></span><span class="tht-up-text"><?php echo esc_html($phone); ?></span></a>
            <a href="viber://add?number=<?php echo esc_attr($wa); ?>"><span class="tel-icos tel-vib top-text-icon"></span><span class="tht-up-text"><?php echo esc_html($phone); ?></span></a>
            <a href="https://t.me/<?php echo esc_attr($tg); ?>"><span class="tel-icos tel-tg top-text-icon"></span><span class="tht-up-text">@<?php echo esc_html($tg); ?></span></a>
          </div>
          <a href="mailto:<?php echo esc_attr($email); ?>" class="topmail"><span class="flaticon-envelope top-text-icon"></span><span class="tht-up-text tohide"><?php echo esc_html($email); ?></span></a>
        </div></div>
        <div id="top-head-manage" class="right clearfix">
          <div id="shop-header-currency"><a href="#" class="utml bl"><span class="flaticon-customer-service top-text-icon"></span><span class="tht-up-text tohide">Язык</span></a><div class="drop-area"><div class="drop-area-main"><div id="shop-currency"><div class="tac"><small>Выберите желаемый язык:</small></div><div id="newVals"><div id="curValue"><ul></ul></div><ul><li class="selected"><a href="<?php echo esc_url(home_url('/')); ?>"><div class="val">RU</div><div class="flag rub"></div></a></li><li><a href="https://thai-online.tours"><div class="val">EN</div><div class="flag usd"></div></a></li></ul></div></div></div></div></div>
          <div id="shop-header-profile"><a href="#" class="utml bl"><span class="flaticon-avatar top-text-icon"></span><span class="tht-up-text tohide">Мой аккаунт</span></a><div class="drop-area"><div class="drop-area-main"><ul class="account-links"><?php if (is_user_logged_in()): ?><li><a href="<?php echo esc_url(admin_url('profile.php')); ?>">Профиль</a></li><li><a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Выход</a></li><?php else: ?><li><a href="<?php echo esc_url(wp_login_url()); ?>">Вход</a></li><?php if (get_option('users_can_register')): ?><li><a href="<?php echo esc_url(wp_registration_url()); ?>">Регистрация</a></li><?php endif; ?><?php endif; ?></ul></div></div></div>
        </div>
      </div>
      <?php if (!is_front_page()): ?>
      <div class="clearfix right" id="topsearch"><div class="searchForm"><form method="get" action="<?php echo esc_url(home_url('/')); ?>" style="margin:0"><input name="s" class="queryField" placeholder="Поиск экскурсий и интересных мест..." type="text" autocomplete="off"><button class="searchSbmFl" type="submit" aria-label="Поиск"><span class="fa fa-search"></span></button></form></div></div>
      <?php endif; ?>
      <div id="navigation"><div class="width"><div id="mobile-navigation-button">Навигация</div><div id="uNMenuDiv1" class="uMenuV"><?php if (has_nav_menu('primary')) { wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_class'=>'uMenuRoot','fallback_cb'=>'thai_online_primary_menu_fallback','depth'=>2]); } else { thai_online_primary_menu_fallback(); } ?></div></div></div>
    </div>
  </div>
  <div class="clr"></div>
  <?php if (is_front_page()): ?>
  <div class="topButtons"><div class="width">
    <ul class="sn width"><li><a href="https://vk.com/thaibooking" rel="nofollow" target="_blank"><span class="fa thai-vk">VK</span></a></li><li><a href="https://www.facebook.com/thaibookingportal/" rel="nofollow" target="_blank"><span class="fa fa-facebook"></span></a></li><li><a href="https://www.instagram.com/thaionlineorg/" rel="nofollow" target="_blank"><span class="fa fa-instagram"></span></a></li><li><a href="https://www.youtube.com/channel/UCDIGpPr7O6JXF9icDT0bTEA" rel="nofollow" target="_blank"><span class="fa fa-youtube-play"></span></a></li></ul>
    <div class="clr"></div>
    <div class="topBtn right"><div class="topBtnHdrs">Не знаю что выбрать...</div><div class="viewAlldiv"><a href="<?php echo esc_url(home_url('/shop/all')); ?>" class="viewAllbtn">Смотреть все варианты</a></div></div>
    <div class="topBtn left"><div class="topBtnHdrs">Ищу кое-что конкретное!</div><div class="search-main"><div class="searchForm"><form method="get" action="<?php echo esc_url(home_url('/')); ?>" style="margin:0"><input name="s" class="queryField" placeholder="Поиск экскурсий и интересных мест..." type="text"><button class="searchSbmFl" type="submit" aria-label="Поиск"><span class="fa fa-search"></span></button></form></div></div></div>
  </div></div>
  <div class="videox"><div class="videoOl"></div></div><div class="clr"></div>
  <?php endif; ?>
</header>
<?php
$land_classes = (get_query_var('top_module') === 'shop_single' || get_query_var('top_community'))
    ? 'page-full clearfix top-wp-runtime'
    : 'page-full width clearfix top-wp-runtime';
?>
<div id="land-full" class="<?php echo esc_attr($land_classes); ?>">