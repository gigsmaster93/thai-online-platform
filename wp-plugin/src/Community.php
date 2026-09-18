<?php
if (!defined('ABSPATH')) exit;
class TOP_Community {
    public static function boot() {
        add_action('init', [__CLASS__, 'routes'], 30);
        add_action('admin_post_nopriv_thai_booking', [__CLASS__, 'submit_booking']);
        add_action('admin_post_thai_booking', [__CLASS__, 'submit_booking']);
        add_action('admin_post_nopriv_thai_forum', [__CLASS__, 'submit_forum']);
        add_action('admin_post_thai_forum', [__CLASS__, 'submit_forum']);
        add_filter('template_include', [__CLASS__, 'template'], 100);
        add_filter('document_title_parts', [__CLASS__, 'title'], 100);
        add_action('admin_post_nopriv_thai_contact', [__CLASS__, 'submit_contact']);
        add_action('admin_post_thai_contact', [__CLASS__, 'submit_contact']);
        add_action('admin_post_nopriv_thai_review', [__CLASS__, 'submit_review']);
        add_action('admin_post_thai_review', [__CLASS__, 'submit_review']);
        add_action('parse_request', static function($wp){if(preg_match('~^gb(?:/(?:page/)?([0-9]+))?/?$~',$wp->request,$m)){$wp->query_vars=['top_community'=>'guestbook','top_page'=>max(1,(int)($m[1]??1))];return;}$aliases=get_option('thai_photo_aliases',[]);$key=trim($wp->request,'/');if(isset($aliases[$key]))$wp->query_vars=['top_community'=>'photo','top_id'=>$aliases[$key][0],'top_section'=>$aliases[$key][1]];});
        add_shortcode('thai_contact_form', [__CLASS__, 'contact_form']);
        add_filter('redirect_canonical', static function($url){return get_query_var('top_community') ? false : $url;});
        add_action('wp_enqueue_scripts', static function(){
            if(get_query_var('top_community') || is_page('contact')) wp_enqueue_style('thai-community',plugins_url('assets/community.css',TOP_PLUGIN_FILE),[], '4.4.27');
            if(is_page('contact')) wp_enqueue_style('thai-contact-legacy',home_url('/css/pages/1.css'),[], '52535335');
        },1000);
    }
    public static function routes() {
        add_post_type_support('thai_forum_topic','comments');
        register_taxonomy('thai_photo_album', ['thai_photo'], ['label'=>'Photo Albums','hierarchical'=>true,'public'=>true,'rewrite'=>false,'show_in_rest'=>true]);
        register_post_type('thai_message', ['label'=>'Support Inbox','public'=>false,'show_ui'=>true,'show_in_menu'=>'thai-online','supports'=>['title','editor'],'capability_type'=>'post']);
        add_rewrite_tag('%top_community%', '([^&]+)');
        add_rewrite_tag('%top_section%', '([0-9]+)');
        add_rewrite_tag('%top_page%', '([0-9]+)');
        add_rewrite_rule('^forum/?$', 'index.php?top_community=forum', 'top');
        add_rewrite_rule('^forum/([0-9]+)/?$', 'index.php?top_community=forum&top_section=$matches[1]', 'top');
        add_rewrite_rule('^forum/([0-9]+)-([0-9]+)-([0-9]+)/?$', 'index.php?top_community=forum&top_section=$matches[1]&top_id=$matches[2]&top_page=$matches[3]', 'top');
        add_rewrite_rule('^photo/?$', 'index.php?top_community=photo', 'top');
        add_rewrite_rule('^photo/(?:.+/)?([0-9]+)-0-([0-9]+)/?$', 'index.php?top_community=photo&top_section=$matches[1]&top_id=$matches[2]', 'top');
        add_rewrite_rule('^photo/(?:.+/)?([0-9]+)/?$', 'index.php?top_community=photo&top_section=$matches[1]', 'top');
        add_rewrite_rule('^gb(?:/([0-9]+))?/?$', 'index.php?top_community=guestbook&top_page=$matches[1]', 'top');
    }
    public static function template($template) {
        $module=get_query_var('top_community');
        if(in_array($module,['forum','photo','guestbook'],true)) {
            status_header(200); return TOP_PLUGIN_DIR.'templates/community-'.$module.'.php';
        }
        if(is_page('contact')) return TOP_PLUGIN_DIR.'templates/community-contact.php';
        return $template;
    }
    public static function title($parts) {
        $module=get_query_var('top_community');
        if($module) $parts['title']=['forum'=>'Форум','photo'=>'Фотогалерея','guestbook'=>'Отзывы о сервисе Thai-Online'][$module]??$parts['title'];
        return $parts;
    }
    public static function page() {
        $page=max(1,(int)get_query_var('top_page'));
        foreach(array_keys($_GET) as $key) if(preg_match('/^page([0-9]+)$/',$key,$m))$page=max(1,(int)$m[1]);
        return $page;
    }
    public static function pagination($total,$per,$page,$url) {
        $pages=(int)ceil($total/$per);if($pages<2)return;
        echo '<nav class="thai-community-pages" aria-label="Страницы">';
        foreach(($pages<=10?range(1,$pages):array_unique([1,max(1,$page-1),$page,min($pages,$page+1),$pages])) as $n) {
            echo $n===$page?'<b class="swchItemA">'.$n.'</b>':'<a class="swchItem" href="'.esc_url(str_replace('{page}',(string)$n,$url)).'">'.$n.'</a>';
        }
        echo '</nav>';
    }
    private static function validate_submission($action) {
        if($_SERVER['REQUEST_METHOD']!=='POST'||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce']??'')),$action))wp_die('Обнови страницу и попробуй ещё раз.', '', ['response'=>403]);
        if(!empty($_POST['website']))wp_die('Сообщение отклонено.', '', ['response'=>400]);
        $key='thai_submit_'.hash('sha256',($_SERVER['REMOTE_ADDR']??'').$action);
        if(get_transient($key))wp_die('Подожди минуту перед повторной отправкой.', '', ['response'=>429]);
        $name=sanitize_text_field(wp_unslash($_POST['name']??''));
        $body=sanitize_textarea_field(wp_unslash($_POST['message']??''));
        if(!$name||mb_strlen($name)>100||mb_strlen($body)<5||mb_strlen($body)>10000)wp_die('Заполни имя и текст сообщения.', '', ['response'=>400]);
        set_transient($key,1,60);return [$name,$body];
    }
    public static function submit_contact() {
        [$name,$body]=self::validate_submission('thai_contact');
        $email=sanitize_email(wp_unslash($_POST['email']??''));
        if(!is_email($email))wp_die('Укажи корректный E-mail.', '', ['response'=>400]);
        $id=wp_insert_post(wp_slash(['post_type'=>'thai_message','post_status'=>'private','post_title'=>$name,'post_content'=>$body."\n\nE-mail: ".$email,'meta_input'=>['_thai_email'=>$email]]),true);
        if(is_wp_error($id))wp_die('Не удалось сохранить сообщение. Попробуй позже.', '', ['response'=>500]);
        wp_mail('info@thai-online.org','Обратная связь: '.$name,$body."\n\n".$email,['Reply-To: '.$email]);
        wp_safe_redirect(home_url('/contact?sent=1#support'));exit;
    }
    public static function submit_review() {
        [$name,$body]=self::validate_submission('thai_review');
        $id=wp_insert_post(wp_slash(['post_type'=>'thai_guestbook','post_status'=>'pending','post_title'=>$name,'post_content'=>$body,'meta_input'=>['_thai_live_review'=>1]]),true);
        if(is_wp_error($id))wp_die('Не удалось сохранить отзыв.', '', ['response'=>500]);
        wp_safe_redirect(home_url('/gb?submitted=1#sign'));exit;
    }
    public static function submit_forum() {
        [$name,$body]=self::validate_submission('thai_forum');
        $section=absint($_POST['section']??0);$topic=absint($_POST['topic']??0);
        $sections=get_option('thai_forum_sections',[]);
        if(!isset($sections[$section]))wp_die('Раздел не найден.', '', ['response'=>404]);
        if($topic){
            $ids=get_posts(['post_type'=>'thai_forum_topic','post_status'=>'publish','meta_key'=>'_ucoz_forum_id','meta_value'=>$topic,'numberposts'=>1,'fields'=>'ids']);
            if(!$ids||(int)get_post_meta($ids[0],'_thai_forum_section',true)!==$section)wp_die('Тема не найдена.', '', ['response'=>404]);
            $saved=wp_insert_comment(['comment_post_ID'=>$ids[0],'comment_author'=>$name,'comment_content'=>$body,'comment_approved'=>0,'comment_type'=>'comment']);
        }else{
            $title=sanitize_text_field(wp_unslash($_POST['title']??''));
            if(!$title||mb_strlen($title)>200)wp_die('Укажи название темы.', '', ['response'=>400]);
            $saved=wp_insert_post(wp_slash(['post_type'=>'thai_forum_topic','post_status'=>'pending','post_title'=>$title,'post_content'=>$body,'meta_input'=>['_thai_forum_section'=>$section,'_thai_forum_author'=>$name,'_thai_forum_updated'=>time()]]),true);
            if(!is_wp_error($saved)&&$saved)update_post_meta($saved,'_ucoz_forum_id',1000000000+$saved);
        }
        if(!$saved||is_wp_error($saved))wp_die('Не удалось сохранить сообщение.', '', ['response'=>500]);
        wp_safe_redirect(home_url('/forum/'.($topic?$section.'-'.$topic.'-1':$section).'?submitted=1#forum-form'));exit;
    }
    public static function forum_form($section,$topic=0) {
        if(!isset(get_option('thai_forum_sections',[])[$section]))return;
        if(isset($_GET['submitted']))echo '<p role="status">Сообщение отправлено на проверку и появится после одобрения.</p>';
        echo '<form id="forum-form" class="thai-forum-form" method="post" action="'.esc_url(admin_url('admin-post.php')).'"><h2>'.($topic?'Ответить в теме':'Создать тему').'</h2>';
        wp_nonce_field('thai_forum');
        echo '<input type="hidden" name="action" value="thai_forum"><input type="hidden" name="section" value="'.(int)$section.'"><input type="hidden" name="topic" value="'.(int)$topic.'"><p hidden><input name="website" tabindex="-1" autocomplete="off" aria-label="Website"></p><p><label>Ваше имя<br><input name="name" maxlength="100" required></label></p>';
        if(!$topic)echo '<p><label>Название темы<br><input name="title" maxlength="200" required></label></p>';
        echo '<p><label>Сообщение<br><textarea name="message" rows="6" minlength="5" maxlength="10000" required></textarea></label></p><p>Сообщения публикуются после проверки модератором.</p><button type="submit">Отправить</button></form>';
    }
    public static function submit_booking() {
        $product=get_post(absint($_POST['product']??0));
        if(!$product||$product->post_type!=='thai_excursion'||$product->post_status!=='publish')wp_die('Экскурсия не найдена.', '', ['response'=>404]);
        if(empty($_POST['policy']))wp_die('Подтверди согласие с пользовательским соглашением.', '', ['response'=>400]);
        $labels=['date'=>'Дата выезда','quantity'=>'Количество человек','hotel'=>'Отель','room'=>'Комната','email'=>'E-mail','phone'=>'Телефон','wishes'=>'Пожелания'];$values=[];$lines=[];
        foreach($labels as $key=>$label){$values[$key]=sanitize_textarea_field(wp_unslash($_POST[$key]??''));$lines[]=$label.': '.$values[$key];}
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$values['date'])||!ctype_digit($values['quantity'])||(int)$values['quantity']<1||(int)$values['quantity']>1000||!$values['phone'])wp_die('Заполни дату, количество человек и телефон.', '', ['response'=>400]);
        if($values['email']&&!is_email($values['email']))wp_die('Укажи корректный E-mail.', '', ['response'=>400]);
        $_POST['name']=wp_slash('Заявка: '.mb_substr($product->post_title,0,85));$_POST['message']=wp_slash(implode("\n",$lines));
        [$name,$body]=self::validate_submission('thai_booking');
        $saved=wp_insert_post(wp_slash(['post_type'=>'thai_message','post_status'=>'private','post_title'=>$name,'post_content'=>$body,'meta_input'=>['_thai_product'=>$product->ID,'_thai_email'=>$values['email']]]),true);
        if(!$saved||is_wp_error($saved))wp_die('Не удалось сохранить заявку.', '', ['response'=>500]);
        wp_mail('info@thai-online.org',$name,$body,$values['email']?['Reply-To: '.$values['email']]:[]);
        $id=get_post_meta($product->ID,'_ucoz_shop_id',true);
        wp_safe_redirect(home_url('/shop/'.(int)$id.'/desc/'.$product->post_name.'?booked=1#calculatey'));exit;
    }
    public static function booking_form($product) {
        if(isset($_GET['booked']))echo '<p role="status">Заявка принята. Мы свяжемся с тобой для подтверждения.</p>';
        echo '<form class="thai-booking-form" method="post" action="'.esc_url(admin_url('admin-post.php')).'">';wp_nonce_field('thai_booking');
        echo '<input type="hidden" name="action" value="thai_booking"><input type="hidden" name="product" value="'.(int)$product->ID.'"><p hidden><input name="website" tabindex="-1" autocomplete="off" aria-label="Website"></p>';
        echo '<input type="date" name="date" aria-label="Дата выезда" required style="width:95%"><br><br><input type="number" name="quantity" placeholder="Количество человек" aria-label="Количество человек" min="1" max="1000" required style="width:95%;margin-bottom:5px"><br><input name="hotel" placeholder="Отель" aria-label="Отель" maxlength="200" style="width:95%;margin-bottom:5px"><br><input name="room" placeholder="Комната" aria-label="Комната" maxlength="50" style="width:95%"><br><br><input type="email" name="email" placeholder="E-mail" aria-label="E-mail" maxlength="200" style="width:95%;margin-bottom:5px"><br><input type="tel" name="phone" placeholder="Телефон" aria-label="Телефон" maxlength="60" required style="width:95%"><br><br><textarea name="wishes" rows="7" placeholder="Пожелания" aria-label="Пожелания" maxlength="5000" style="width:95%"></textarea><br><br><label><input type="checkbox" name="policy" value="1" required> <a href="/index/0-4" target="_blank" rel="noopener" style="color:green">Согласен с Пользовательским соглашением</a></label><br><br><input type="submit" value="Заказать!" style="width:100%"></form>';
    }
    public static function contact_form() {
        ob_start();
        if(isset($_GET['sent']))echo '<p role="status">Сообщение принято. Спасибо за обращение!</p>';
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="support">
          <?php wp_nonce_field('thai_contact'); ?><input type="hidden" name="action" value="thai_contact">
          <p hidden><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></p>
          <table width="100%" cellspacing="1" cellpadding="2">
            <tr><td width="35%"><label for="contact-name">Ваше Имя *</label></td><td><input id="contact-name" name="name" maxlength="100" required style="width:95%"></td></tr>
            <tr><td><label for="contact-email">Ваш E-mail *</label></td><td><input id="contact-email" name="email" type="email" required style="width:95%"></td></tr>
            <tr><td><label for="contact-message">Ваше сообщение *</label></td><td><textarea id="contact-message" name="message" rows="7" minlength="5" maxlength="10000" required style="width:95%"></textarea></td></tr>
            <tr><td colspan="2" align="center"><br><button type="submit" style="width:50%">Отправить</button></td></tr>
          </table>
        </form>
        <?php return ob_get_clean();
    }
}
TOP_Community::boot();