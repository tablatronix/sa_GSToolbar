<?php

/*
	fix for hovers popping up on load
	fix for "debug_mode" i18n text
	changed targets to new		
*/

/*
* @Plugin Name: sa_toolbar
* @Description: Admin toolbar
* @Version: 0.1.4
* @Author: Shawn Alverson
* @Author URI: http://tablatronix.com/getsimple-cms/sa-toolbar/
*/


$SATB['PLUGIN_ID'] = "sa_toolbar";
$SATB['PLUGIN_PATH'] = $SITEURL.'plugins/'.$SATB['PLUGIN_ID'].'/';
$SATB['PLUGIN_URL'] = "http://tablatronix.com/getsimple-cms/sa-toolbar-plugin/";
$SATB['DEBUG'] = false;
$SATB['owner'] = '';
$SATB['gsback'] = true;


define('SATB_DEBUG',$SATB['DEBUG']);
# define('GS_DEV',false); // global development constant

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");			// Plugin File
$satb_pname = 	  'SA Toolbar';    	    	//Plugin name
$satb_pversion =	'0.1.4'; 		       	     	//Plugin version
$satb_pauthor = 	'Shawn Alverson';      	//Plugin author
$satb_purl = 			$SATB['PLUGIN_URL'];		//author website
$satb_pdesc =			'SA Toolbar';					 	//Plugin description
$satb_ptype =			'';                 	  //page type - on which admin tab to display
$satb_pfunc =			'';                     //main function (administration)
	
# register plugin
register_plugin($thisfile,$satb_pname,$satb_pversion,$satb_pauthor,$satb_purl,$satb_pdesc,$satb_ptype,$satb_pfunc);

function satb_debugLog_wrapper(){
	satb_deeper_debuglog(func_get_args());
}

function satb_debugLog(){
	GLOBAL $debugLogFunc;
	
	// define your debug enviroment, put this somewhere global
	// define('SATB_DEBUG',true);
	
	if(!defined('SATB_DEBUG') or SATB_DEBUG != true) return;
	if(function_exists('_debugLog')){
		$debugLogFunc = __FUNCTION__;
		_debugLog($args = func_get_args());
	} else {
		$args = func_get_args();
		$args = is_array($args) ? print_r($args,true) : $args;
		debugLog($args);
	}	
}
  
if(defined('SATB_DEBUG') and SATB_DEBUG == true){
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

// INIT
if(sa_tb_user_is_admin()){
	add_action('theme-footer', 'sa_toolbar');
	add_action('index-pretemplate', 'sa_init_i18n');
	if($SATB['gsback'] = true){
		add_action('footer', 'sa_toolbar');
		add_action('admin-pre-header', 'sa_init_i18n');
	}	
}

// asset queing
// use header hook if older than 3.1
if(floatval(GSVERSION) < 3.1){
	add_action('header', 'sa_tb_executeheader');
	$SATB['owner'] = "SA_tb_";
}  
else{ sa_tb_executeheader(); }


// FUNCTIONS

function sa_init_i18n(){
	global $LANG;
		i18n_merge('anonymous_data') || i18n_merge('anonymous_data', 'en_US');
}

function sa_toolbar(){
	
  GLOBAL $SATB,$SITEURL,$LANG,$USR;
	
	$editpath = $SITEURL.'admin/edit.php?id=';
	
	if(function_exists('return_page_slug')){
		$pageslug = return_page_slug();
	} else {
		$pageslug = '';
	}	
	
	$tm = array(); // holds tabs
	$sm = array(); // holds submenus	
	$psidemenus = get_PluginMenus(); // hold plugin sidemenus
	$ptabs = get_PluginTabs();	// hold plugin tabs
	
	satb_automerge(array_merge($psidemenus,$ptabs));
	
	// tabs
	$tm = addMenu($tm,'pages','TAB_PAGES','admin/pages.php');
	$tm = addMenu($tm,'files','TAB_FILES','admin/upload.php');
	$tm = addMenu($tm,'theme','TAB_THEME','admin/theme.php');
	$tm = addMenu($tm,'backups','TAB_BACKUPS','admin/backups.php');
	$tm = addMenu($tm,'plugins','PLUGINS_NAV','admin/plugins.php');

	// merge plugin nav-tabs
	$tm = array_merge($tm,$ptabs);
	
	$tm = addMenu($tm,'support','TAB_SUPPORT','admin/support.php');
	$tm = addMenu($tm,'settings','TAB_SETTINGS','admin/settings.php');
	$tm = addMenu($tm,'logs','LOGS','admin/log.php');
	
	# satb_debugLog($ptabs);
	# satb_debugLog($tm);
	
	// default sidemenus
	$sm = addMenu($sm,'pages','SIDE_VIEW_PAGES','admin/pages.php');
	$sm = addMenu($sm,'pages','SIDE_CREATE_NEW','admin/edit.php');
	$sm = addMenu($sm,'pages','MENU_MANAGER','admin/menu-manager.php');
	$sm = addMenu($sm,'files','FILE_MANAGEMENT','admin/upload.php');
	$sm = addMenu($sm,'theme','SIDE_CHOOSE_THEME','admin/theme.php');
	$sm = addMenu($sm,'theme','SIDE_EDIT_THEME','admin/theme-edit.php');
	$sm = addMenu($sm,'theme','SIDE_COMPONENTS','admin/components.php');
	$sm = addMenu($sm,'theme','SIDE_VIEW_SITEMAP','../sitemap.xml');
	$sm = addMenu($sm,'backups','SIDE_PAGE_BAK','admin/backups.php');
	$sm = addMenu($sm,'backups','SIDE_WEB_ARCHIVES','admin/archive.php');
	$sm = addMenu($sm,'plugins','SHOW_PLUGINS','admin/plugins.php');
	$sm = addMenu($sm,'plugins','anonymous_data/ANONY_TITLE','admin/load.php?id=anonymous_data');
	$sm = addMenu($sm,'plugins','GET_PLUGINS_LINK','http://get-simple.info/extend');
	$sm = addMenu($sm,'support','SUPPORT','admin/support.php');
	$sm = addMenu($sm,'support','WEB_HEALTH_CHECK','admin/health-check.php');
	$sm = addMenu($sm,'settings','GENERAL_SETTINGS','admin/settings.php');
	$sm = addMenu($sm,'settings','SIDE_USER_PROFILE','admin/settings.php#profile');
	$sm = addMenu($sm,'settings','TAB_LOGOUT','admin/logout.php');
	$sm = addMenu($sm,'logs','VIEW_FAILED_LOGIN','admin/log.php?log=failedlogins.log');
	
	$logoutitem = $sm['settings'][2];
	$profileitem = $sm['settings'][1];
	

	// define menu parts

	// link target
	$target = '_blank'; 
	
	// init master admin menu
	$menu = '<li><ul class="satb_nav">
	<li class="satb_menu"><a href="#">Admin &#9662;</a>
	<ul>
	';
	
	// logo	
	$logo  = '<li><ul class="satb_nav"><li class="satb_menu satb_icon"><a class="satb_logo" title="GetSImple CMS ver. '.GSVERSION.'" href="#"><img src="'.$SATB['PLUGIN_PATH'].'assets/img/gsicon.png"></a><ul>';
	$logo .= '<li class=""><a href="http://get-simple.info" target="'.$target.'">GetSimple CMS</a></li>';
	$logo .= '<li class=""><a href="http://get-simple.info/forum/" target="'.$target.'">Forums<span class="iconright">&#9656;</span></a>';
	$logo .= '<ul><li class=""><a href="http://get-simple.info/forum/search/new/" target="'.$target.'">New Posts</a></li>';
	$logo .= '</ul></li>';
	$logo .= '<li class=""><a href="http://get-simple.info/extend/" target="'.$target.'">Extend</a></li>';
	$logo .= '<li class=""><a href="http://get-simple.info/wiki/" target="'.$target.'">Wiki</a></li>';
	$logo .= '<li class=""><a href="http://code.google.com/p/get-simple-cms" target="'.$target.'">SVN</a></li>';
	$logo .= '<li class=""><a href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'">About SA_toolbar</a></li>';
	$logo .= '</ul></ul></li>';	
	
	// global buttons
	
	// edit button
	$edit = '';
	if(function_exists('return_page_slug')){
		$edit = '<li class="satb_menu"><a href="'.$editpath.return_page_slug().'" target="'.$target.'">'.satb_cleanStr(satb_geti18n('EDIT')).'</a></li>';
	}
	
	$new	= '<li class="satb_menu"><a href="'.$editpath.'" target="'.$target.'">+ '.satb_cleanStr(satb_geti18n('NEW_PAGE')).'</a></li>';
	$separator = '<li class="separator"></li>';
	
	// debug mode indicator
	$debugicon = '<li class="satb_icon" title="'.ucwords(satb_cleanStr(satb_geti18n('DEBUG_MODE'))).' ON"><img src="'.$SATB['PLUGIN_PATH'].'assets/img/sa_tb_debugmode.png"></li>';	
	
	// welcome user
	$sig  = '<ul class="satb_nav"><li class="satb_menu"><a href="#">'.i18n_r('WELCOME').', <strong>'.$USR.'</strong></a><ul>';
	$sig .= '<li class=""><a href="'.$SITEURL.$logoutitem['func'].'" target="'.$target.'">'.satb_cleanStr(satb_geti18n($logoutitem['title'])).'</a></li>';
	$sig .= '<li class=""><a href="'.$SITEURL.$profileitem['func'].'" target="'.$target.'">'.satb_cleanStr(satb_geti18n($profileitem['title'])).'</a></li>';
	$sig .= '</ul></li>';
		
	foreach($tm as $key=>$page){
		// tabs
		
		// check if tab is plugin tab
		// note: built in tabs all use the first level sidemenu item as the default action, all plugins should follow this, arghhh		
		if( isset($ptabs[$key]) ){
			$tablink = 'admin/load.php?id=' . sa_tb_array_index(sa_tb_array_index($ptabs[$key],0),'func');
			$tablink .=  '&amp;' . sa_tb_array_index(sa_tb_array_index($ptabs[$key],0),'action');
			$title_i18n = true;
		} else {
			$tablink = sa_tb_array_index(sa_tb_array_index($page,0),'func');
			$title_i18n = false;
		}
		
		$menu.= '<li' . (isset($ptabs[$key])? ' class="plugin" ':'') . '><a href="'.$SITEURL.$tablink.'" target="'.$target.'">'.satb_cleanStr(satb_geti18n($tm[$key][0]['title'],$title_i18n));
		$menu.= (count($page) > 0) ? '<span class="iconright">&#9656;</span></a><ul>' : '</a><ul>';
		// default sidemenus
		if(isset($sm[$key])){
			foreach($sm[$key] as $submenu){
				$title = satb_cleanStr(satb_geti18n($submenu['title']));	
				$menu.='<li><a href="'.$SITEURL.$submenu['func'].'" target="'.$target.'">'.$title.'</a></li>';
			}
		}
		// plugin sidemenus
		if(isset($psidemenus[$key])){
			foreach($psidemenus[$key] as $submenu){
				$title = satb_cleanStr(satb_geti18n($submenu['title'],true));				
				$menu.='<li class="plugin"><a href="'.$SITEURL.'admin/load.php?id='.$submenu['func'].(isset($submenu['action']) ? '&amp;'.$submenu['action'] : '').'" target="'.$target.'">'.$title.'</a></li>';
			}
		}
		
		$menu.='</ul></li>';
	}
	
	$menu.='</ul></li></ul>';
	
	echo '<div id="sa_toolbar">
	<ul class="">';
	echo $logo;
	echo $menu;
	echo $separator;
	echo $new;
	echo $separator;
	if(isset($pageslug)) echo $edit;
	echo $separator;	
	echo '</ul>';

	echo '<ul class="right">'.$sig.'</ul>';
	
	// debug indicator logic
	if((defined('GSDEBUG') and GSDEBUG == 1)){
		echo '<ul class="right">';
		echo $debugicon;
		echo '</ul>';
	}
	echo '</div>';
	
	
	?>
	
	<script type="text/javascript">
		$(document).ready(function() {

			// $('body').append($('#sa_toolbar'));
			
			if ( $('#sa_toolbar').length > 0 ) {

				$('body').addClass('gs-toolbar'); // for special theme styling when toolbar is present, body.gs-toolbar elements{}
			
				// add margin to body to push down content
				bodytop = $('body').csspixels('margin-top');
				$('body').css('margin-top', (bodytop+28)+'px'); //todo: make the height dynamic based on navbar css
				
				// move background
				// $('body').css('background-position', '0px 28px');	
				
				// assign body z-index in case its auto
				// console.log($('body').css('z-index'));
				// $('body').css('z-index', 9998);	
			}

		});
		
		$.fn.csspixels = function(property) {
				return parseInt(this.css(property).slice(0,-2));
		};		
		
	</script>

<?php
	
}

function satb_cleanStr($str){
	return ucwords(strip_tags($str));
}

function satb_automerge($array){
	global $LANG;
	
	// loop plugins for i18n text {}
	// index unique for single merge
	// merge plugin lang files in string for posible lang combinations ( which is inefficient )
	
	$i18n_merges = array();
		
	foreach($array as $menu){
		satb_debugLog($menu);
		foreach($menu as $item){
		satb_debugLog($item);			
			if(preg_match('/^\{(.*)\/(.*)\}$/',$item['title'],$matches)){
				satb_debugLog($item);			
				if(isset($matches[1]) and isset($matches[2])){
					$i18n_merges[] = trim($matches[1]);
				}	
			}	
		}	
	}

	$i18n_merges = array_unique($i18n_merges);
	
	foreach($i18n_merges as $merge){
		satb_debugLog('satb_automerge_custom',$merge); // the first argument is not in backtrace
		# _debugLog("satb_automerge_default",$merge);
		i18n_merge($merge, $LANG);		
		i18n_merge($merge, substr($LANG,0,2));					
		# i18n_merge($matches[1],'en_US');		
	}
	
}

function satb_geti18n($str,$intags=false){
		if($intags == false){
			return i18n_r($str);	
		}
		else if(preg_match('/^\{(.*\/.*)\}$/',$str,$matches) ){
			return i18n_r($matches[1]);		
		}	
		else return $str;
}

function addMenu($array,$page,$title,$func){
	$array[$page][] = array('func'=>$func,'title'=>$title);
	return $array;
}


function get_PluginTabs(){
	global $plugins;	
  $sa_plugins = $plugins;
	$plugintabs = array();
	
  foreach ($sa_plugins as $hook)	{
		if($hook['hook'] == 'nav-tab' and (isset($hook['args']) and isset($hook['args'][1]) and isset($hook['args'][2])) ){
			# $plugintabs[$hook['args'][1]] = $hook['args'][2];
			$plugintabs[$hook['args'][1]][] = array('title'=>$hook['args'][2],'func'=>$hook['args'][0],'action'=> isset($hook['args'][3]) ? $hook['args'][3] : null );
		}	
	}
	return $plugintabs;
}

function get_PluginMenus($page = null){
	global $plugins,$tabs;
	# satb_debuglog($plugins);
  $sa_plugins = $plugins;
	$pluginsidemenus = array();
	
  foreach ($sa_plugins as $hook)	{
    if(substr($hook['hook'],-8,9) == '-sidebar'){
			# satb_debuglog($hook);		
			$tab = str_replace('-sidebar','',$hook['hook']);
			if(isset($hook['args']) and isset($hook['args'][0]) and isset($hook['args'][1])){
				$allowAll = true; // allow plugins that use their own callbacks instead of createSideMenu, even though it is a terrible idea
				if($hook['function'] == 'createSideMenu' or $allowAll){
					$pluginsidemenus[$tab][] = array('title'=>$hook['args'][1],'func'=>$hook['args'][0],'action'=> isset($hook['args'][2]) ? $hook['args'][2] : null );
				}
			}
		}	
	}
	
	# satb_debuglog($pluginsidemenus);
	return $pluginsidemenus;
}

function sa_tb_executeheader(){ // assigns assets to queue or header
  GLOBAL $SATB;

  # debugLog("sa_dev_executeheader");

	$PLUGIN_ID = $SATB['PLUGIN_ID'];
	$PLUGIN_PATH = $SATB['PLUGIN_PATH'];
	$owner = $SATB['owner'];
  
  $regscript = $owner."register_script";
  $regstyle  = $owner."register_style";
  $quescript = $owner."queue_script";
  $questyle  = $owner."queue_style";

  $regstyle($PLUGIN_ID, $PLUGIN_PATH.'assets/css/sa_toolbar.css', '0.1', 'screen');
  $questyle($PLUGIN_ID,GSBOTH);   
}

function SA_tb_register_style($handle, $src, $ver){echo '<link rel="stylesheet" href="'.$src.'" type="text/css" charset="utf-8" />'."\n";}
function SA_tb_queue_style($name,$where){}
function SA_tb_register_script($handle, $src, $ver, $in_footer=FALSE){echo '<script type="text/javascript" src="'.$src.'"></script>'."\n";}
function SA_tb_queue_script($name,$where){}


function sa_tb_user_is_admin(){
  GLOBAL $USR;
    
  if (isset($USR) && $USR == get_cookie('GS_ADMIN_USERNAME')) {
    return true;
  }
}

function sa_tb_array_index($ary,$idx){ // handles all the isset error avoidance bullshit when checking an array for a key that might not exist
  if( isset($ary) and isset($idx) and isset($ary[$idx]) ) return $ary[$idx];
}

?>