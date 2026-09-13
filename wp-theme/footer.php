</div>
<footer class="footer">
  <div class="width clearfix">
    <div class="footer-block">
      <div class="footer-block-title">О нас</div>
      <div class="footer-block-body"><p>На нашем сайте представлен весь ассортимент экскурсий, выполняемых из Паттайи и из Бангкока для англоязычных и русскоязычных гостей Королевства Таиланд. Помимо туров и путешествий, сегодня у нас можно заказать такси по Таиланду, трансферы на острова, забронировать авиабилеты и отели по всему миру, найти жилье в аренду в Паттайе, забронировать бесплатный трансфер в магазины Паттайи.</p></div>
      <img alt="Оплата экскурсий онлайн в Паттайе" src="/img/payments.png" title="Купить и оплатить экскурсии в Паттайе онлайн" width="150">
    </div>
    <div class="footer-block">
      <div class="footer-block-title">Контакты</div>
      <div class="footer-block-body">
        <p>Время работы: 10:00 - 22:00<br>без перерывов и выходных</p>
        <p>Горячая линия: <a href="tel:0838383539" class="btmtels">083-8383-539</a></p>
        <p>Whatsapp: <a href="https://wa.me/66838383539" class="btmtels">+66-838-383-539</a></p>
        <p>Viber: <a href="viber://add?number=66838383539" class="btmtels">+66-838-383-539</a></p>
        <p>Telegram: <a href="https://t.me/thaionlinetours" class="btmtels">@thaionlinetours</a></p>
        <p>E-mail: <a href="mailto:info@thai-online.org" class="btmmail">info@thai-online.org</a></p>
      </div>
    </div>
    <div class="footer-block">
      <div class="footer-block-title">Информация</div>
      <div class="footer-block-body"><ul class="uMenuRoot">
        <li><a href="/faq/1-1">ЧаВо</a></li>
        <li><a href="/index/0-5">Лицензия и страхование</a></li>
        <li><a href="/index/0-4">Пользовательское соглашение</a></li>
        <li><a href="/photo">Фотогалерея</a></li>
        <li><a href="/301-tours-to-thai">Купить пакетный тур в Таиланд</a></li>
        <li><a href="/301-air-tickets">Авиабилеты в Таиланд</a></li>
        <li><a href="/301-hotels">Отели в Таиланде и не только</a></li>
        <li><a href="/other_countries_ru">Другие страны</a></li>
        <li><a href="/301-currency-exchanger">Самый выгодный обменник</a></li>
      </ul></div>
    </div>
    <div class="footer-block">
      <div class="footer-block-title">Мой аккаунт</div>
      <div class="footer-block-body"><ul class="uMenuRoot">
        <?php if (is_user_logged_in()): ?>
          <li><a href="<?php echo esc_url(admin_url('profile.php')); ?>">Профиль</a></li>
          <li><a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Выход</a></li>
        <?php else: ?>
          <?php if (get_option('users_can_register')): ?><li><a href="<?php echo esc_url(wp_registration_url()); ?>">Регистрация</a></li><?php endif; ?>
          <li><a href="<?php echo esc_url(wp_login_url()); ?>">Вход</a></li>
        <?php endif; ?>
        <li><a href="/shop/wishlist">Избранное</a></li>
      </ul></div>
    </div>
  </div>
  <div class="copyrights width clearfix"><div class="left">2016-<?php echo esc_html(wp_date('Y')); ?> © Thai-Online. Все права защищены. При перепосте активная ссылка на сайт обязательна.</div></div>
  <div id="up-me" title="Наверх"></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
