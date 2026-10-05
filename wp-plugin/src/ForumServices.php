<?php
if (!defined('ABSPATH')) exit;
class TOP_Forum_Services {
    public static function boot() {
        add_action('init', [__CLASS__, 'routes'], 45);
        add_filter('template_include', [__CLASS__, 'template'], 150);
        add_filter('pre_get_document_title', [__CLASS__, 'title'], 101);
        add_action('template_redirect', [__CLASS__, 'feed'], 1);
    }
    public static function labels() {
        return ['recent'=>'Новые сообщения','members'=>'Участники','rules'=>'Правила форума','search'=>'Поиск','rss'=>'RSS'];
    }
    public static function url($key) {
        return home_url(['recent'=>'/forum/0-0-1-34','members'=>'/forum/0-0-1-35','rules'=>'/forum/0-0-0-36','search'=>'/forum/0-0-0-6','rss'=>'/forum/0-0-0-37'][$key]);
    }
    public static function routes() {
        add_rewrite_tag('%top_forum_service%', '([a-z]+)');
        foreach(['34'=>'recent','35'=>'members'] as $code=>$key) add_rewrite_rule('^forum/0-0-([0-9]+)-'.$code.'/?$', 'index.php?top_community=forum&top_forum_service='.$key.'&top_page=$matches[1]', 'top');
        add_rewrite_rule('^forum/([1-9][0-9]*)-0-0-37/?$', 'index.php?top_community=forum&top_forum_service=rss&top_section=$matches[1]', 'top');
        foreach(['36'=>'rules','6'=>'search','37'=>'rss'] as $code=>$key) add_rewrite_rule('^forum/0-0-0-'.$code.'/?$', 'index.php?top_community=forum&top_forum_service='.$key, 'top');
    }
    public static function template($template) {
        return isset(self::labels()[get_query_var('top_forum_service')]) ? TOP_PLUGIN_DIR.'templates/forum-services.php' : $template;
    }
    public static function title($title) {
        $label=self::labels()[get_query_var('top_forum_service')]??'';
        return $label ? $label.' — Форум Thai Online' : $title;
    }
    public static function navigation() {
        $section=(int)get_query_var('top_section');
        echo '<table class="thai-forum-service-nav" border="0" cellpadding="0" height="30" cellspacing="0" width="100%" aria-label="Форум"><tr><td align="right">[ ';
        $first=true;
        foreach(self::labels() as $key=>$label){
            $url=($key==='rss'&&$section)?home_url('/forum/'.$section.'-0-0-37'):self::url($key);
            if(!$first)echo ' &middot; ';
            echo '<a class="fNavLink" href="'.esc_url($url).'" rel="nofollow">'.esc_html($label).'</a>';
            $first=false;
        }
        echo ' ]</td></tr></table>';
    }
    public static function post_date($comment) {
        // Public legacy dates are Russian even when the WP admin locale is English.
        $days=['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
        return $days[(int)get_comment_date('w',$comment)].', '.get_comment_date('d.m.Y, H:i',$comment);
    }

    public static function statistics() {
        global $wpdb;
        // Imported opening messages live in comments; native topics use post_content.
        $counts=$wpdb->get_row("SELECT COUNT(*) topics,COALESCE(SUM(GREATEST(COALESCE(c.messages,0)-IF(TRIM(p.post_content)='',1,0),0)),0) replies FROM {$wpdb->posts} p LEFT JOIN (SELECT comment_post_ID,COUNT(*) messages FROM {$wpdb->comments} WHERE comment_approved='1' GROUP BY comment_post_ID) c ON c.comment_post_ID=p.ID WHERE p.post_type='thai_forum_topic' AND p.post_status='publish'");
        if(!$counts)return;
        $members=get_option('thai_forum_public_members',[]);
        if(!is_array($members))$members=[];
        $newest=null;$newest_time=0;
        foreach($members as $member){
            $time=strtotime($member['date']??'');
            if($time&&$time>$newest_time&&!empty($member['name'])){$newest=$member;$newest_time=$time;}
        }
        echo '<section class="thai-forum-statistics" aria-label="Статистика форума"><div class="gDivLeft"><div class="gDivRight"><table class="gTable" cellspacing="1" cellpadding="0"><tbody><tr><th class="gTableTop">Статистика форума</th></tr><tr><td class="gTableBody">';
        echo 'Всего создано '.(int)$counts->topics.' тем, в которые добавлено '.(int)$counts->replies.' ответов.';
        if($members){
            echo '<br>Участников в перенесённом справочнике: <a href="'.esc_url(self::url('members')).'">'.count($members).'</a>.';
            if($newest)echo ' Последний зарегистрированный участник справочника: '.esc_html($newest['name']).'.';
        }
        echo '</td></tr></tbody></table></div></div></section>';
    }

    public static function topic_page_size($legacy_topic) {
        // Verified legacy exception: currency topic has 50 posts per page.
        // Other topics retain their existing pagination contract.
        return (int)$legacy_topic===102 ? 50 : 20;
    }
    public static function topic_page_for_position($position,$legacy_topic) {
        return max(1,(int)ceil($position/self::topic_page_size($legacy_topic)));
    }

    public static function comment_url($comment) {
        global $wpdb;
        $topic=(int)get_post_meta($comment->comment_post_ID,'_ucoz_forum_id',true);
        $section=(int)get_post_meta($comment->comment_post_ID,'_thai_forum_section',true);
        $position=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_post_ID=%d AND comment_approved='1' AND (comment_date<%s OR (comment_date=%s AND comment_ID<=%d))",$comment->comment_post_ID,$comment->comment_date,$comment->comment_date,$comment->comment_ID));
        $legacy=(int)get_comment_meta($comment->comment_ID,'_ucoz_forum_post',true);
        return home_url('/forum/'.$section.'-'.$topic.'-'.self::topic_page_for_position($position,$topic)).'#'.($legacy?'post'.$legacy:'comment'.$comment->comment_ID);
    }
    public static function comment_args() {
        return ['status'=>'approve','post_type'=>'thai_forum_topic','post_status'=>'publish','orderby'=>['comment_date'=>'DESC','comment_ID'=>'DESC']];
    }
    public static function section_ids($section) {
        $sections=get_option('thai_forum_sections',[]);
        if(!isset($sections[$section]))return null;
        $ids=[$section];
        foreach($sections as $id=>$row)if((int)($row['parent']??0)===$section)$ids[]=(int)$id;
        return array_unique($ids);
    }
    public static function section_feed_comments($section) {
        $ids=self::section_ids($section);
        if($ids===null)return null;
        $topics=get_posts(['post_type'=>'thai_forum_topic','post_status'=>'publish','numberposts'=>50,'orderby'=>'meta_value_num','meta_key'=>'_thai_forum_updated','order'=>'DESC','meta_query'=>[['key'=>'_thai_forum_section','value'=>array_unique($ids),'compare'=>'IN','type'=>'NUMERIC']]]);
        $comments=[];
        foreach($topics as $topic){
            $last=get_comments(array_merge(self::comment_args(),['post_id'=>$topic->ID,'number'=>1]));
            if($last)$comments[]=$last[0];
        }
        return $comments;
    }
    public static function feed() {
        if(get_query_var('top_forum_service')!=='rss')return;
        $section=(int)get_query_var('top_section');
        $comments=$section?self::section_feed_comments($section):get_comments(array_merge(self::comment_args(),['number'=>30]));
        if($comments===null){status_header(404);nocache_headers();header('Content-Type: text/plain; charset=UTF-8');echo 'Раздел форума не найден';exit;}
        $sections=get_option('thai_forum_sections',[]);
        $feed_title=$section?($sections[$section]['name'].' — Форум Thai Online'):'Форум Thai Online';
        $feed_url=home_url('/forum'.($section?'/'.$section:''));
        status_header(200);header('Content-Type: application/rss+xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0"><channel><title>'.esc_xml($feed_title).'</title><link>'.esc_xml($feed_url).'</link><description>Новые сообщения форума Thai Online</description><language>ru</language>';
        foreach($comments as $c){
            if(get_post_status($c->comment_post_ID)!=='publish')continue;
            $url=self::comment_url($c);
            echo '<item><title>'.esc_xml(get_the_title($c->comment_post_ID)).'</title><link>'.esc_xml($url).'</link><guid isPermaLink="true">'.esc_xml($url).'</guid><pubDate>'.esc_xml(gmdate(DATE_RSS,strtotime($c->comment_date_gmt.' UTC'))).'</pubDate><description>'.esc_xml(wp_trim_words(wp_strip_all_tags($c->comment_content),100)).'</description></item>';
        }
        echo '</channel></rss>';exit;
    }
    public static function pages($total,$per,$page,$url) {
        if($total<=$per)return;
        echo '<nav class="thai-community-pages" aria-label="Страницы">';
        echo paginate_links(['base'=>add_query_arg('pg','%#%',$url),'format'=>'','total'=>(int)ceil($total/$per),'current'=>$page,'prev_text'=>'«','next_text'=>'»']);
        echo '</nav>';
    }
}
TOP_Forum_Services::boot();