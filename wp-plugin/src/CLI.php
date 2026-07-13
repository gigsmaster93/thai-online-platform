<?php
if (!defined('ABSPATH')) exit;

class TOP_CLI {
    public static function register() {
        WP_CLI::add_command('thai-platform', [__CLASS__, 'dispatch']);
    }

    public function dispatch($args, $assoc_args) {
        $cmd = $args[0] ?? 'help';
        switch ($cmd) {
            case 'info':
                WP_CLI::line(TOP_Core::diagnostics_text());
                break;
            case 'doctor':
            case 'diagnostics':
                $this->doctor($assoc_args);
                break;
            case 'profile':
                $this->profile($args, $assoc_args);
                break;
            case 'migrate':
                $this->migrate($assoc_args);
                break;
            case 'import':
                $this->import($args, $assoc_args);
                break;
            case 'routes':
                flush_rewrite_rules();
                WP_CLI::success('Rewrite routes rebuilt.');
                break;
            default:
                WP_CLI::line("Thai Online Platform commands:");
                WP_CLI::line("  wp thai-platform info");
                WP_CLI::line("  wp thai-platform doctor");
                WP_CLI::line("  wp thai-platform profile set ru");
                WP_CLI::line("  wp thai-platform migrate --profile=ru --backup=/home/thaionline/backups/full-backup");
                WP_CLI::line("  wp thai-platform import faq --source=/path/to/faq.txt");
        }
    }

    private function doctor($assoc_args) {
        WP_CLI::line(TOP_Core::diagnostics_text());
        $root = ABSPATH;
        foreach (['_ph','images','img','ExcPlates','social_icons'] as $p) {
            WP_CLI::line($p . ': ' . (file_exists($root . $p) ? 'OK' : 'MISSING'));
        }
    }

    private function profile($args, $assoc_args) {
        $sub = $args[1] ?? 'show';
        if ($sub === 'set') {
            $code = $args[2] ?? 'ru';
            $profile = [
                'site_code' => $code,
                'frontend_language' => $code === 'en' ? 'en' : 'ru',
                'admin_language' => 'en',
                'timezone' => 'Asia/Bangkok',
                'currency' => 'THB',
            ];
            update_option('top_site_profile', $profile);
            WP_CLI::success('Profile set: ' . $code);
            return;
        }
        WP_CLI::line(json_encode(get_option('top_site_profile', []), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function migrate($assoc_args) {
        $profile = $assoc_args['profile'] ?? 'ru';
        $backup = rtrim($assoc_args['backup'] ?? getenv('HOME') . '/backups/full-backup', '/');
        update_option('top_site_profile', [
            'site_code' => $profile,
            'frontend_language' => $profile === 'en' ? 'en' : 'ru',
            'admin_language' => 'en',
            'timezone' => 'Asia/Bangkok',
            'currency' => 'THB',
            'backup_path' => $backup,
        ]);

        WP_CLI::line("Thai Online Platform migration started");
        WP_CLI::line("Profile: $profile");
        WP_CLI::line("Backup: $backup");

        $s1 = $backup . '/_s1';
        if (!is_dir($s1)) WP_CLI::error("Missing backup _s1 directory: $s1");

        $this->ensure_media_links($backup);

        $this->import_site($s1 . '/site.txt', $backup . '/_s1/ownurl.txt');
        $this->import_shop_categories($s1 . '/shop_cat.txt');
        $this->import_shop($s1 . '/shop.txt');
        $this->import_faq($s1 . '/faq.txt');
        $this->import_guestbook($s1 . '/gb.txt');
        $this->import_simple_module('news', $s1 . '/news.txt', 'thai_news');
        $this->import_simple_module('publ', $s1 . '/publ.txt', 'thai_article');

        flush_rewrite_rules();
        WP_CLI::success("Migration completed. Run: wp thai-platform doctor");
    }

    private function import($args, $assoc_args) {
        $module = $args[1] ?? '';
        $source = $assoc_args['source'] ?? '';
        if (!$source || !file_exists($source)) WP_CLI::error('Missing --source file');

        switch ($module) {
            case 'faq': $this->import_faq($source); break;
            case 'guestbook': $this->import_guestbook($source); break;
            case 'shop': $this->import_shop($source); break;
            case 'shop-categories': $this->import_shop_categories($source); break;
            case 'site': $this->import_site($source, ''); break;
            default: WP_CLI::error('Unknown import module: ' . $module);
        }
        flush_rewrite_rules();
    }

    private function ensure_media_links($backup) {
        $links = ['_ph','images','img','ExcPlates','social_icons'];
        foreach ($links as $link) {
            $target = $backup . '/' . $link;
            $dest = ABSPATH . $link;
            if (file_exists($dest)) {
                WP_CLI::line("Media link exists: $link");
                continue;
            }
            if (file_exists($target)) {
                @symlink($target, $dest);
                WP_CLI::line("Media link created: $link -> $target");
            } else {
                WP_CLI::warning("Media target missing: $target");
            }
        }
    }

    private function import_site($source, $ownurl='') {
        if (!file_exists($source)) { WP_CLI::warning("site.txt missing"); return; }
        $text = file_get_contents($source);
        preg_match_all('/(?m)^\d+\|0\|0\|/', $text, $m, PREG_OFFSET_CAPTURE);
        $matches = $m[0];
        $own = $ownurl && file_exists($ownurl) ? @file_get_contents($ownurl) : '';
        $created=0; $updated=0;
        foreach ($matches as $i=>$match) {
            $start = $match[1];
            $end = isset($matches[$i+1]) ? $matches[$i+1][1] : strlen($text);
            $rec = substr($text, $start, $end-$start);
            $f = explode('|', $rec);
            if (count($f)<7) continue;
            $id = $f[0]; $title = html_entity_decode($f[4], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $content = count($f)>10 ? implode('|', array_slice($f, 6, -4)) : ($f[6] ?? '');
            $content = str_replace(["\\\n","\\"], ["\n",""], $content);
            $slug = sanitize_title($title);
            if ($id==='2') $slug='about';
            if ($id==='3') $slug='contact';
            if ($id==='4') $slug='policy';
            if ($id==='5') $slug='license-and-insurance';
            $post_id = $this->upsert_post('page', '_ucoz_site_id', $id, [
                'post_title'=>$title, 'post_name'=>$slug, 'post_content'=>$content, 'post_status'=>'publish'
            ], $created, $updated);
            update_post_meta($post_id, '_ucoz_source_module', 'site');
            update_post_meta($post_id, '_ucoz_legacy_urls', ['/index/0-'.$id]);
        }
        WP_CLI::success("Site pages imported. Created: $created, updated: $updated");
    }

    private function import_faq($source) {
        if (!file_exists($source)) { WP_CLI::warning("faq.txt missing"); return; }
        $text = file_get_contents($source);
        preg_match_all('/(?m)^\d+\|1\|0\|0\|/', $text, $m, PREG_OFFSET_CAPTURE);
        $matches = $m[0];
        $created=0; $updated=0;
        foreach ($matches as $i=>$match) {
            $start=$match[1]; $end=isset($matches[$i+1])?$matches[$i+1][1]:strlen($text);
            $rec=substr($text,$start,$end-$start); $f=explode('|',$rec);
            if (count($f)<13) continue;
            $id=$f[0]; $question=html_entity_decode($f[10], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $slug=trim($f[count($f)-2] ?? sanitize_title($question));
            $answer=implode('|', array_slice($f,12,-7));
            if (!$answer) $answer=$f[12] ?? '';
            $answer=html_entity_decode(str_replace(["\\\n","\\"], ["\n",""], $answer), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $date = is_numeric($f[4] ?? '') ? date('Y-m-d H:i:s',(int)$f[4]) : current_time('mysql');
            $post_id=$this->upsert_post('thai_faq','_ucoz_faq_id',$id,[
                'post_title'=>$question,'post_name'=>$slug,'post_content'=>$answer,'post_status'=>'publish','post_date'=>$date
            ],$created,$updated);
            update_post_meta($post_id,'_ucoz_source_module','faq');
            update_post_meta($post_id,'_ucoz_legacy_urls',['/faq/1-'.$id]);
        }
        WP_CLI::success("FAQ imported. Created: $created, updated: $updated");
    }

    private function import_guestbook($source) {
        if (!file_exists($source)) { WP_CLI::warning("gb.txt missing"); return; }
        $lines=file($source, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $created=0; $updated=0;
        foreach($lines as $line){
            $f=explode('|',$line);
            if(count($f)<11) continue;
            $id=$f[0]; $author=html_entity_decode($f[1] ?: 'Guest #'.$id, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $date=is_numeric($f[8]??'') ? date('Y-m-d H:i:s',(int)$f[8]) : current_time('mysql');
            $content=html_entity_decode($f[10], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $post_id=$this->upsert_post('thai_guestbook','_ucoz_gb_id',$id,[
                'post_title'=>$author,'post_content'=>$content,'post_status'=>'publish','post_date'=>$date
            ],$created,$updated);
            update_post_meta($post_id,'_guest_author',$author);
            update_post_meta($post_id,'_guest_ip',$f[9]??'');
            update_post_meta($post_id,'_ucoz_source_module','gb');
        }
        WP_CLI::success("Guestbook imported. Created: $created, updated: $updated");
    }

    private function import_shop_categories($source) {
        if (!file_exists($source)) { WP_CLI::warning("shop_cat.txt missing"); return; }
        $lines=file($source, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $map=[];
        foreach($lines as $line){
            $f=explode('|',$line);
            if(count($f)<4) continue;
            $id=$f[0]; $parent=$f[1]; $slug=$f[2]; $name=html_entity_decode($f[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $term=term_exists($name,'thai_excursion_cat');
            if(!$term) $term=wp_insert_term($name,'thai_excursion_cat',['slug'=>$slug]);
            if(!is_wp_error($term)){
                $tid=is_array($term)?$term['term_id']:$term;
                update_term_meta($tid,'_ucoz_cat_id',$id);
                $map[$id]=$tid;
            }
        }
        foreach($lines as $line){
            $f=explode('|',$line); if(count($f)<4) continue;
            if(($f[1]??'0')!='0' && isset($map[$f[0]],$map[$f[1]])) wp_update_term($map[$f[0]],'thai_excursion_cat',['parent'=>$map[$f[1]]]);
        }
        WP_CLI::success("Shop categories imported: ".count($map));
    }

    private function import_shop($source) {
        if (!file_exists($source)) { WP_CLI::warning("shop.txt missing"); return; }
        $text=file_get_contents($source);
        preg_match_all('/(?m)^\d+\|/', $text, $m, PREG_OFFSET_CAPTURE);
        $matches=$m[0]; $created=0; $updated=0;
        foreach($matches as $i=>$match){
            $start=$match[1]; $end=isset($matches[$i+1])?$matches[$i+1][1]:strlen($text);
            $rec=trim(substr($text,$start,$end-$start)); if(!$rec) continue;
            $f=explode('|',$rec); if(count($f)<20) continue;
            $id=$f[0]; $cat=$f[1]; $slug=$f[3]; $short=$f[5]??''; $title=html_entity_decode($f[6]??'', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $content=html_entity_decode(str_replace(["\\\n","\\"],["\n",""],$f[7]??''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $price=$f[11]??''; $seo_title=$f[17]??''; $seo_desc=$f[18]??''; $date=is_numeric($f[21]??'')?date('Y-m-d H:i:s',(int)$f[21]):current_time('mysql');
            if(!$title) continue;
            $post_id=$this->upsert_post('thai_excursion','_ucoz_shop_id',$id,[
                'post_title'=>$title,'post_name'=>$slug,'post_excerpt'=>$short,'post_content'=>$content,'post_status'=>'publish','post_date'=>$date
            ],$created,$updated);
            update_post_meta($post_id,'_ucoz_source_module','shop');
            update_post_meta($post_id,'_ucoz_category_id',$cat);
            update_post_meta($post_id,'_thai_price',$price);
            update_post_meta($post_id,'_seo_title',$seo_title);
            update_post_meta($post_id,'_seo_description',$seo_desc);
            update_post_meta($post_id,'_thai_price_variants',$f[40]??'');
            update_post_meta($post_id,'_thai_quick_facts',$f[43]??'');
            update_post_meta($post_id,'_ucoz_raw_record',$rec);
            $terms=get_terms(['taxonomy'=>'thai_excursion_cat','hide_empty'=>false,'meta_key'=>'_ucoz_cat_id','meta_value'=>$cat,'fields'=>'ids']);
            if(!is_wp_error($terms) && $terms) wp_set_object_terms($post_id,array_map('intval',$terms),'thai_excursion_cat');
        }
        WP_CLI::success("Shop imported. Created: $created, updated: $updated");
    }

    private function import_simple_module($module, $source, $post_type) {
        if (!file_exists($source) || filesize($source)==0) { WP_CLI::warning("$module missing or empty"); return; }
        $lines=file($source, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $created=0; $updated=0;
        foreach($lines as $line){
            $f=explode('|',$line); if(count($f)<3) continue;
            $id=$f[0]; $title=html_entity_decode($f[1] ?: $module.' #'.$id, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $content=html_entity_decode(end($f), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $post_id=$this->upsert_post($post_type,'_ucoz_'.$module.'_id',$id,[
                'post_title'=>$title,'post_content'=>$content,'post_status'=>'publish'
            ],$created,$updated);
            update_post_meta($post_id,'_ucoz_source_module',$module);
            update_post_meta($post_id,'_ucoz_raw_record',$line);
        }
        WP_CLI::success("$module imported. Created: $created, updated: $updated");
    }

    private function upsert_post($post_type,$meta_key,$meta_value,$data,&$created,&$updated){
        $existing=get_posts(['post_type'=>$post_type,'post_status'=>'any','meta_key'=>$meta_key,'meta_value'=>$meta_value,'numberposts'=>1,'fields'=>'ids']);
        $data['post_type']=$post_type;
        if($existing){ $data['ID']=$existing[0]; $post_id=wp_update_post($data,true); $updated++; }
        else { $post_id=wp_insert_post($data,true); $created++; }
        if(is_wp_error($post_id)) WP_CLI::warning($post_id->get_error_message());
        else update_post_meta($post_id,$meta_key,$meta_value);
        return is_wp_error($post_id) ? 0 : $post_id;
    }
}
