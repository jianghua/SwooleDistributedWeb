<?php


$_config = array();

// ----------------------------  CONFIG DB  ----------------------------- //
$_config['db']['1']['dbhost'] = '172.19.2.48';
$_config['db']['1']['dbuser'] = 'dzwclub';
$_config['db']['1']['dbpw'] = 'M3stark9x03etp5la7c1e';
$_config['db']['1']['dbcharset'] = 'utf8';
$_config['db']['1']['pconnect'] = '0';
$_config['db']['1']['dbname'] = 'discuzx';
$_config['db']['1']['tablepre'] = 'pre_';


$_config['db']['common'] = array();
$_config['db']['common']['slave_except_table'] = 'common_session,common_member, forum_attachment, forum_thread, forum_post, forum_attachment_0, forum_attachment_1, forum_attachment_2, forum_attachment_3, forum_attachment_4, forum_attachment_5, forum_attachment_6, forum_attachment_7, forum_attachment_8, forum_attachment_9';

$_config['db']['slave'] = array();


/*
$_config['db']['slave']['1']['dbhost'] = '172.19.2.100';
$_config['db']['slave']['1']['dbuser'] = 'dzwclub';
$_config['db']['slave']['1']['dbpw'] = '70d7a33539c27df0';
$_config['db']['slave']['1']['dbcharset'] = 'utf8';
$_config['db']['slave']['1']['pconnect'] = '0';
$_config['db']['slave']['1']['dbname'] = 'discuzx';
$_config['db']['slave']['1']['tablepre'] = 'pre_';
*/

$_config['db']['slave']['2']['dbhost'] = '172.19.2.24';
$_config['db']['slave']['2']['dbuser'] = 'dzwclub';
$_config['db']['slave']['2']['dbpw'] = '70d7a33539c27df0';
$_config['db']['slave']['2']['dbcharset'] = 'utf8';
$_config['db']['slave']['2']['pconnect'] = '0';
$_config['db']['slave']['2']['dbname'] = 'discuzx';
$_config['db']['slave']['2']['tablepre'] = 'pre_';



// --------------------------  CONFIG MEMORY  --------------------------- //
$_config['memory']['prefix'] = 'OfBWK3_';
$_config['memory']['eaccelerator'] = 1;
$_config['memory']['apc'] = 1;
$_config['memory']['xcache'] = 1;
$_config['memory']['memcache']['server'] = '172.19.2.37';//172.19.2.37
$_config['memory']['memcache']['port'] = 11211;
$_config['memory']['memcache']['pconnect'] = 1;
$_config['memory']['memcache']['timeout'] = 1;
// --------------------------  CONFIG SERVER  --------------------------- //
$_config['server']['id'] = 1;

// -------------------------  CONFIG DOWNLOAD  -------------------------- //
$_config['download']['readmod'] = 2;
$_config['download']['xsendfile']['type'] = '0';
$_config['download']['xsendfile']['dir'] = '/down/';

// ---------------------------  CONFIG CACHE  --------------------------- //
$_config['cache']['type'] = 'sql';

// --------------------------  CONFIG OUTPUT  --------------------------- //
$_config['output']['charset'] = 'utf-8';
$_config['output']['forceheader'] = 1;
$_config['output']['gzip'] = '0';
$_config['output']['tplrefresh'] = 1;
$_config['output']['language'] = 'zh_cn';
$_config['output']['staticurl'] = 'static/';
$_config['output']['ajaxvalidate'] = '0';
$_config['output']['iecompatible'] = '0';

// --------------------------  CONFIG COOKIE  --------------------------- //
$_config['cookie']['cookiepre'] = '17mm_';
$_config['cookie']['cookiedomain'] = 'dzwww.com';
$_config['cookie']['cookiepath'] = '/';

// -------------------------  CONFIG SECURITY  -------------------------- //
$_config['security']['authkey'] = 'f02b2cSO5zPK2RNp';
$_config['security']['urlxssdefend'] = 1;
$_config['security']['attackevasive'] = '0';
$_config['security']['querysafe']['status'] = 1;
$_config['security']['querysafe']['dfunction']['0'] = 'load_file';
$_config['security']['querysafe']['dfunction']['1'] = 'hex';
$_config['security']['querysafe']['dfunction']['2'] = 'substring';
$_config['security']['querysafe']['dfunction']['3'] = 'if';
$_config['security']['querysafe']['dfunction']['4'] = 'ord';
$_config['security']['querysafe']['dfunction']['5'] = 'char';
$_config['security']['querysafe']['daction']['0'] = 'intooutfile';
$_config['security']['querysafe']['daction']['1'] = 'intodumpfile';
$_config['security']['querysafe']['daction']['2'] = 'unionselect';
$_config['security']['querysafe']['daction']['3'] = '(select';
$_config['security']['querysafe']['daction']['4'] = 'unionall';
$_config['security']['querysafe']['daction']['5'] = 'uniondistinct';
$_config['security']['querysafe']['dnote']['0'] = '/*';
$_config['security']['querysafe']['dnote']['1'] = '*/';
$_config['security']['querysafe']['dnote']['2'] = '#';
$_config['security']['querysafe']['dnote']['3'] = '--';
$_config['security']['querysafe']['dnote']['4'] = '"';
$_config['security']['querysafe']['dlikehex'] = 1;
$_config['security']['querysafe']['afullnote'] = '0';

// --------------------------  CONFIG ADMINCP  -------------------------- //
// -------- Founders: $_config['admincp']['founder'] = '1,2,3'; --------- //
$_config['admincp']['founder'] = '2,3248';
$_config['admincp']['forcesecques'] = '0';
$_config['admincp']['checkip'] = 1;
$_config['admincp']['runquery'] = 0;
$_config['admincp']['dbimport'] = 0;

// --------------------------  CONFIG REMOTE  --------------------------- //
$_config['remote']['on'] = '0';
$_config['remote']['dir'] = 'remote';
$_config['remote']['appkey'] = '62cf0b3c3e6a4c9468e7216839721d8e';
$_config['remote']['cron'] = '0';


// -------------------  THE END  -------------------- //
$_config['plugindeveloper'] = 1;

$_config['debug'] = 2; 
/*
n = 1，debug 标准模式 
   n = 2，debug E_ALL模式 
   n = 字串，当前 $_GET、$_POST 等 REQUEST 参数中包含 debug=字串 时显示
*/
?>