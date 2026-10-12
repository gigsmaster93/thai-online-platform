<?php
// Hermetic tests: no WordPress bootstrap, database, network or real mail.
define('ABSPATH', __DIR__ . '/fixture/');
class WP_Error {
    private $code, $message, $data;
    function __construct($code, $message = '', $data = []) { $this->code=$code; $this->message=$message; $this->data=$data; }
    function get_error_code() { return $this->code; }
    function get_error_message() { return $this->message; }
    function get_error_data() { return $this->data; }
}
class RecaptchaTestDie extends Exception { public $status; function __construct($status) { $this->status=$status; } }
class RecaptchaTestRedirect extends Exception {}
$checks=[]; $http=[]; $events=[]; $response=null; $nonce=true; $limited=false;
$home='https://new.thai-online.org'; $insertions=[]; $mails=[];
function check($name,$pass) { global $checks; $checks[]=['name'=>$name,'pass'=>(bool)$pass]; if(!$pass) throw new Exception($name); }
function add_action(...$args) {}
function add_filter(...$args) {}
function add_shortcode(...$args) {}
function is_wp_error($v) { return $v instanceof WP_Error; }
function wp_unslash($v) { return is_string($v)?stripslashes($v):$v; }
function wp_slash($v) { return $v; }
function sanitize_text_field($v) { return trim((string)$v); }
function sanitize_textarea_field($v) { return trim((string)$v); }
function sanitize_email($v) { return trim((string)$v); }
function is_email($v) { return filter_var($v,FILTER_VALIDATE_EMAIL)!==false; }
function absint($v) { return abs((int)$v); }
function esc_url_raw($v,$protocols=[]) { return $v; }
function wp_http_validate_url($v) { return filter_var($v,FILTER_VALIDATE_URL)!==false; }
function esc_attr($v) { return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
function home_url($path='/') { global $home; return $home.$path; }
function wp_parse_url($v,$part) { return parse_url($v,$part); }
function wp_remote_post($url,$args) { global $http,$response,$events; $http[]=[$url,$args]; $events[]='http'; return $response; }
function wp_remote_retrieve_response_code($r) { return $r['code']??0; }
function wp_remote_retrieve_body($r) { return $r['body']??''; }
function wp_verify_nonce($v,$action) { global $nonce; return $nonce; }
function get_transient($k) { global $limited,$events; $events[]='read-rate'; return $limited; }
function set_transient($k,$v,$ttl) { global $events; $events[]='write-rate'; }
function wp_die($text,$title='',$args=[]) { throw new RecaptchaTestDie($args['response']); }
function get_post($id) { return (object)['ID'=>$id,'post_type'=>'thai_excursion','post_status'=>'publish','post_title'=>'Fixture excursion','post_name'=>'fixture-excursion']; }
function get_post_meta($id,$key,$single) { return 484; }
function wp_insert_post($post,$return_error) { global $insertions,$events; $events[]='insert'; $insertions[]=$post; return 123; }
function wp_mail($to,$subject,$body,$headers) { global $mails,$events; $events[]='mail'; $mails[]=true; return true; }
function wp_safe_redirect($url) { throw new RecaptchaTestRedirect; }
require __DIR__.'/../wp-plugin/src/Recaptcha.php';
require __DIR__.'/../wp-plugin/src/Community.php';
function reset_case($result=null) {
    global $response,$http,$events,$nonce,$limited,$insertions,$mails;
    $response=$result??['code'=>200,'body'=>json_encode(['success'=>true,'hostname'=>'new.thai-online.org'])];
    $http=[]; $events=[]; $nonce=true; $limited=false; $insertions=[]; $mails=[];
    $_SERVER=['REQUEST_METHOD'=>'POST','REMOTE_ADDR'=>'192.0.2.1','HTTP_HOST'=>'attacker.example'];
    $_POST=['_wpnonce'=>'fixture-nonce','website'=>'','product'=>'484','offer_url'=>'https://offers.example/excursion','offer_price'=>'1900','email'=>'person@example.com','phone'=>'+66000000000','g-recaptcha-response'=>'fixture-token'];
}
function error_status($r) { return is_wp_error($r)?$r->get_error_data()['status']:null; }
try {
    putenv('THAI_RECAPTCHA_SITE_KEY'); putenv('THAI_RECAPTCHA_SECRET_KEY');
    reset_case();
    check('public key exact', TOP_Recaptcha::site_key()==='6LesJOMtAAAAACLMzyJ18Z4MMAeqVGxCY71YzxNW');
    check('missing secret fails closed', error_status(TOP_Recaptcha::verify('fixture-token'))===503 && count($http)===0);
    putenv('THAI_RECAPTCHA_SECRET_KEY=fixture-private-secret');
    check('configured pair detected',TOP_Recaptcha::configured());
    putenv('THAI_RECAPTCHA_SITE_KEY=');
    check('explicit missing site key fails closed',error_status(TOP_Recaptcha::verify('fixture-token'))===503);
    putenv('THAI_RECAPTCHA_SITE_KEY');
    foreach(['', '   ', [], 1, new stdClass(), str_repeat('x',4097)] as $i=>$token) {
        reset_case(); check('invalid token '.$i,error_status(TOP_Recaptcha::verify($token))===400&&count($http)===0);
    }
    $cases=[
        ['transport',new WP_Error('timeout'),503],
        ['server status',['code'=>500,'body'=>'{}'],503],
        ['redirect',['code'=>302,'body'=>'{}'],503],
        ['malformed json',['code'=>200,'body'=>'not-json'],503],
        ['missing success',['code'=>200,'body'=>'{}'],503],
        ['nonboolean success',['code'=>200,'body'=>'{"success":"true","hostname":"new.thai-online.org"}'],503],
        ['invalid secret',['code'=>200,'body'=>'{"success":false,"error-codes":["invalid-input-secret"]}'],503],
        ['missing secret',['code'=>200,'body'=>'{"success":false,"error-codes":["missing-input-secret"]}'],503],
        ['invalid response',['code'=>200,'body'=>'{"success":false,"error-codes":["invalid-input-response"]}'],400],
        ['expired or reused',['code'=>200,'body'=>'{"success":false,"error-codes":["timeout-or-duplicate"]}'],400],
        ['missing hostname',['code'=>200,'body'=>'{"success":true}'],400],
        ['invalid hostname',['code'=>200,'body'=>'{"success":true,"hostname":[]}'],400],
        ['wrong hostname',['code'=>200,'body'=>'{"success":true,"hostname":"attacker.example"}'],400],
        ['hostname suffix attack',['code'=>200,'body'=>'{"success":true,"hostname":"new.thai-online.org.attacker.example"}'],400],
    ];
    foreach($cases as [$name,$result,$status]) {
        reset_case($result);check($name,error_status(TOP_Recaptcha::verify('fixture-token'))===$status);
    }
    reset_case();check('verified response accepted',TOP_Recaptcha::verify('fixture-token')===true);
    [$url,$args]=$http[0];
    check('fixed HTTPS verifier',$url==='https://www.google.com/recaptcha/api/siteverify'&&$args['sslverify']===true);
    check('bounded verifier request',$args['timeout']===10&&$args['redirection']===0&&$args['limit_response_size']===16384);
    check('secret only in POST body',$args['body']===['secret'=>'fixture-private-secret','response'=>'fixture-token']);
    check('optional visitor IP omitted',!isset($args['body']['remoteip']));
    reset_case(['code'=>200,'body'=>'{"success":true,"hostname":"NEW.THAI-ONLINE.ORG."}']);
    check('hostname normalized',TOP_Recaptcha::verify('fixture-token')===true);
    $home='https://different.example';reset_case();
    check('request Host cannot override home hostname',error_status(TOP_Recaptcha::verify('fixture-token'))===400);
    $home='https://new.thai-online.org';
    reset_case();ob_start();TOP_Recaptcha::widget();$html=ob_get_clean();
    check('public widget key rendered',str_contains($html,'data-sitekey="6LesJOMtAAAAACLMzyJ18Z4MMAeqVGxCY71YzxNW"'));
    check('private key absent from widget',!str_contains($html,'fixture-private-secret'));
    foreach([['missing','',null,400],['invalid','fixture-token',['code'=>200,'body'=>'{"success":false}'],400],['network','fixture-token',new WP_Error('timeout'),503],['wrong host','fixture-token',['code'=>200,'body'=>'{"success":true,"hostname":"attacker.example"}'],400]] as [$name,$token,$result,$status]) {
        reset_case($result);$_POST['g-recaptcha-response']=$token;
        try {TOP_Community::submit_found_cheaper();check('rejection '.$name,false);} catch(RecaptchaTestDie $e) {check('rejection '.$name,$e->status===$status);}
        check('no writes or mail '.$name,!in_array('write-rate',$events,true)&&!$insertions&&!$mails);
    }
    foreach([['nonce',403],['honeypot',400],['rate',429]] as [$kind,$status]) {
        reset_case();
        if($kind==='nonce')$nonce=false;
        if($kind==='honeypot')$_POST['website']='spam';
        if($kind==='rate')$limited=true;
        try {TOP_Community::submit_found_cheaper();check('existing guard '.$kind,false);}catch(RecaptchaTestDie $e){check('existing guard '.$kind,$e->status===$status);}
        check('guard before network '.$kind,count($http)===0&&!$insertions&&!$mails&&!in_array('write-rate',$events,true));
    }
    reset_case();
    try {TOP_Community::submit_found_cheaper();check('valid form redirects',false);}catch(RecaptchaTestRedirect $e){check('valid form redirects',true);}
    check('valid form stores one private message',count($insertions)===1&&$insertions[0]['post_status']==='private'&&$insertions[0]['meta_input']['_thai_type']==='found_cheaper');
    check('valid form mails once',count($mails)===1);
    check('verification precedes all writes',array_search('http',$events,true)<array_search('write-rate',$events,true)&&array_search('http',$events,true)<array_search('insert',$events,true));
    $validation=new ReflectionMethod('TOP_Community','validate_submission');
    reset_case();$_POST['name']='Fixture';$_POST['message']='Fixture message';unset($_POST['g-recaptcha-response']);
    $validation->invoke(null,'thai_contact');
    check('other forms unchanged',count($http)===0&&in_array('write-rate',$events,true));
    $result=['checks'=>count($checks),'pass'=>count(array_filter($checks,fn($x)=>$x['pass'])),'failed'=>array_values(array_filter($checks,fn($x)=>!$x['pass'])),'real_db_connections'=>0,'real_http_requests'=>0,'real_emails'=>0];
    echo json_encode($result,JSON_PRETTY_PRINT)."\n";
} catch(Throwable $e) {
    fwrite(STDERR,json_encode(['failed'=>$e->getMessage(),'checks'=>$checks])."\n");exit(1);
}
