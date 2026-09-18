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
        <p>Telegram: <a href="tg://resolve?domain=thaionlinetours" class="btmtels">@thaionlinetours</a></p>
        <p>Skype: <a href="skype:hunterfortornado">Thai-Online</a></p>
        <p>E-mail: <a href="mailto:info@thai-online.org" class="btmmail">info@thai-online.org</a></p>
        <ul class="sn" style="display:block;height:50px;position:initial;margin-top:10px !important;text-align:left;">
          <li><a href="https://vk.com/thaibooking" rel="nofollow" title="Мы вКонтакте" target="_blank"><span class="flaticon-vk"></span></a></li>
          <li><a href="https://www.facebook.com/thaibookingportal/" rel="nofollow" title="Мы на Facebook" target="_blank"><span class="flaticon-facebook-logo"></span></a></li>
          <li><a href="https://ok.ru/thaionline" title="Мы в Одноклассниках" target="_blank"><span style="font-weight:bold;font-size:20px;">OK</span></a></li>
          <li><a href="https://www.youtube.com/channel/UCDIGpPr7O6JXF9icDT0bTEA" rel="nofollow" title="Наш Youtube-канал" target="_blank"><span class="flaticon-youtube-symbol"></span></a></li>
          <li><a href="https://twitter.com/SCPattaya" rel="nofollow" title="Наш Twitter" target="_blank"><span class="flaticon-twitter-black-shape"></span></a></li>
          <li><a href="https://www.instagram.com/thaionlineorg/" rel="nofollow" title="Мы в Instagram" target="_blank"><span class="fa fa-instagram" style="font-size:20px;padding:3px;"></span></a></li>
          <li><a href="https://dzen.ru/thaionline/" title="Мы в Дзен" target="_blank"><span class="Y" style="font-weight:bold;font-size:20px;padding:3px;">Y</span></a></li>
        </ul>
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
          <li><a href="<?php echo esc_url(wp_registration_url()); ?>">Регистрация</a></li>
          <li><a href="<?php echo esc_url(wp_login_url()); ?>">Вход</a></li>
        <?php endif; ?>
        <li><a href="/shop/wishlist">Избранное</a></li>
      </ul></div>
    </div>
  </div>
  <div class="copyrights width clearfix"><div class="left">2016-<?php echo esc_html(wp_date('Y')); ?>
    <div class="right"></div>&copy; Thai-Online. Все права защищены. При перепосте активная ссылка на сайт обязательна.<br>
  </div></div>
  <div id="up-me" title="Наверх"></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
