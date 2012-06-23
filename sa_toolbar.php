<?php

/*
Added parse error catching regardless of error reporting level
Fixed issue with backtracing object classes
*/

/*
* @Plugin Name: sa_toolbar
* @Description: Admin toolbar
* @Version: 0.1
* @Author: Shawn Alverson
* @Author URI: http://tablatronix.com/getsimple-cms/sa-toolbar/
*/

define('SATB_DEBUG',true); // sa dev plugin debug
# define('GS_DEV',false); // global development constant

$SA_TB_PLUGIN_ID = 'sa_toolbar';
$SA_TB_PLUGINPATH = $SITEURL.'plugins/sa_toolbar/';
$sa_url = 'http://tablatronix.com/getsimple-cms/sa-toolbar/';

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");			// Plugin File
$sa_pname = 	  'SA Toolbar';           	//Plugin name
$sa_pversion =	'0.1'; 		       	      	//Plugin version
$sa_pauthor = 	'Shawn Alverson';       	//Plugin author
$sa_purl = 			$sa_url;									//author website
$sa_pdesc =			'SA Toolbar';						 	//Plugin description
$sa_ptype =			'';                       //page type - on which admin tab to display
$sa_pfunc =			'';                       //main function (administration)
	
# register plugin
register_plugin($thisfile,$sa_pname,$sa_pversion,$sa_pauthor,$sa_url,$sa_pdesc,$sa_ptype,$sa_pfunc);

// INCLUDES
require_once($SA_TB_PLUGIN_ID.'/inc/sa_common.php');

function satb_debugLog(){
	_debuglog(func_get_args());
}
  
if(SATB_DEBUG==true){
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

// INIT
if(sa_tb_user_is_admin()){
	add_action('theme-footer', 'sa_toolbar');
	add_action('index-pretemplate', 'sa_init_i18n');
}

$owner = '';

// asset queing
// use header hook if older than 3.1
if(floatval(GSVERSION) < 3.1){
	add_action('header', 'sa_tb_executeheader');
	$owner = "SA_tb_";
}  
else{ sa_tb_executeheader(); }


// FUNCTIONS

function sa_init_i18n(){
	global $LANG;
	  satb_debuglog('i18mmerge active');
		i18n_merge('anonymous_data') || i18n_merge('anonymous_data', 'en_US');

		i18n_merge('i18n_search', substr($LANG,0,2));
		i18n_merge('i18n_search', 'en');
}

function sa_toolbar(){
	
  GLOBAL $SA_TB_PLUGINPATH,$SITEURL,$LANG,$USR;
	
	$editpath = $SITEURL.'admin/edit.php?id=';
	$pageslug = return_page_slug();
	
	$tm = array(); // holds tabs
	$sm = array(); // holds submenus	
	$psidemenus = get_PluginMenus(); // hold plugin sidemenus
	$ptabs = get_PluginTabs();	// hold plugin tabs
	
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
	
	_debugLog($ptabs);
	_debugLog($tm);
	
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
	$target = 'gsadmin'; 
	
	// init master admin menu
	$menu = '<li><ul class="satb_nav">
	<li class="menu"><a href="#">Admin &#9662;</a>
	<ul>
	';
	
	// logo
	$logo = '<li>
	<ul class="satb_nav">
		<li class="icon" title="GetSImple CMS ver. '.GSVERSION.'">
		<a href=""><img src="'.$SA_TB_PLUGINPATH.'assets/img/gsicon.png"></a>
			<ul>
				<li><a href="">test</a></li>
			</ul>
		</li>
	</ul>
	';
	
	$logo  = '<li><ul class="satb_nav"><li class="icon"><a href="#"><img src="'.$SA_TB_PLUGINPATH.'assets/img/gsicon.png"></a><ul>';
	$logo .= '<li class="menu"><a href="">GetSimple CMS</a></li>';
	$logo .= '<li class="menu"><a href="">Forums</a></li>';
	$logo .= '<li class="menu"><a href="">Wiki</a></li>';
	$logo .= '</ul></ul></li>';	
	
	// global buttons
	$edit = '<li class="menu"><a href="'.$editpath.return_page_slug().'" target="_blank">'.satb_cleanStr(satb_geti18n('EDIT')).'</a></li>';
	$new	= '<li class="menu"><a href="'.$editpath.'" target="_blank">+ '.satb_cleanStr(satb_geti18n('NEW_PAGE')).'</a></li>';
	$separator = '<li class="separator"></li>';
	
	// debug mode indicator
	$debugicon = '<li class="icon" title="'.ucwords(strip_tags('DEBUG_MODE')).' ON"><img src="'.$SA_TB_PLUGINPATH.'assets/img/sa_tb_debugmode.png"></li>';	
	
	// welcome user
	$sig  = '<ul class="satb_nav"><li class="menu"><a href="#">'.i18n_r('WELCOME').', <strong>'.$USR.'</strong></a><ul>';
	$sig .= '<li class="menu"><a href="'.$logoutitem['func'].'">'.satb_cleanStr(satb_geti18n($logoutitem['title'])).'</a></li></li>';
	$sig .= '<li class="menu"><a href="'.$profileitem['func'].'">'.satb_cleanStr(satb_geti18n($profileitem['title'])).'</a></li></li>';
	$sig .= '</ul>';
		
	foreach($tm as $key=>$page){
		// tabs
		
		// check if tab is plugin tab
		if( isset($ptabs[$key]) ){
			$tablink = 'admin/load.php?id=' . sa_tb_array_index(sa_tb_array_index($ptabs[$key],0),'func');
			$tablink .=  '&amp;' . sa_tb_array_index(sa_tb_array_index($ptabs[$key],0),'action');
		} else {
			// built in stuff all uses first level sidemenu as index for tabs, all plugins should follow this, arghhh!
			$tablink = sa_tb_array_index(sa_tb_array_index($page,0),'func');
		}
		
		# $tablink.= (isset($submenu['action']) ? '&amp;'.$submenu['action'] : '')
		$menu.= '<li' . (isset($ptabs[$key])? ' class="plugin" ':'') . '><a href="'.$tablink.'" target="'.$target.'">'.satb_cleanStr(satb_geti18n($tm[$key][0]['title']));
		$menu.= (count($page) > 0) ? '<span class="iconright">&#9656;</span></a><ul>' : '</a><ul>';
		// default sidemenus
		if(isset($sm[$key])){
			foreach($sm[$key] as $submenu){
				$title = satb_cleanStr(satb_geti18n($submenu['title']));	
				$menu.='<li><a href="'.$submenu['func'].'" target="'.$target.'">'.$title.'</a></li>';
			}
		}
		// plugin sidemenus
		if(isset($psidemenus[$key])){
			foreach($psidemenus[$key] as $submenu){
				$title = satb_cleanStr(satb_geti18n($submenu['title']));				
				$menu.='<li class="plugin"><a href="admin/load.php?id='.$submenu['func'].(isset($submenu['action']) ? '&amp;'.$submenu['action'] : '').'" target="'.$target.'">'.$title.'</a></li>';
			}
		}
		
		$menu.='</ul></li>';
	}
	
	$menu.='</ul></li></ul>';
	
	echo '<div id="sa_toolbar">
	<ul class="left">';
	echo $logo;
	echo $menu;
	echo $separator;
	echo $new;
	echo $separator;
	if(isset($pageslug)) echo $edit;
	echo $separator;	
	echo '</ul>';
	
	// debug indicator logic
	if((defined('GSDEBUG') and GSDEBUG == 1)){
		echo '<ul class="right">';
		echo $sig;
		echo $debugicon;
		echo '</ul>';
	}
	echo '</div>';
	
	
	?>
	
	<script type="text/javascript">
		$(document).ready(function() {

			$('body').append($('#sa_toolbar'));
			
			if ( $('#sa_toolbar').length > 0 ) {

				$('body').addClass('gs-toolbar'); // for special theme styling when toolbar is present, body.gs-toolbar elements{}
			
				// add margin to body to push down content
				bodytop = $('body').csspixels('margin-top');
				$('body').css('margin-top', (bodytop+28)+'px');
				
				// move background
				// $('body').css('background-position', '0px 28px');	
				
				// assign body z-index in case its auto
				// console.log($('body').css('z-index'));
				// $('body').css('z-index', 9998);	

				
				// move our toolbar to the top
				// $('.navbar-fixed-top').css('margin-top', '28px');				
			} else {
				$('body').css('margin-top', '0px');
			}

		});
		
		$.fn.csspixels = function(property) {
				return parseInt(this.css(property).slice(0,-2));
		};		
		
	</script>

<?php
	
}

function satb_automerge_i18n(){
	// loop plugins for i18n text {}
	// index for single merge
	// merge plugin lang files in string
	
}

function satb_cleanStr($str){
	return ucwords(strip_tags($str));
}

function satb_geti18n($str){
	// todo make this a parser, and dynamically load i18n_merge for plugins
		$str = str_replace('{','',$str);
		$str = str_replace('}','',$str);
		$str = i18n_r($str);		
		$str = str_replace('{','',$str);
		$str = str_replace('}','',$str);		
		return $str;
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

function bounceBack(){
	return func_get_args();
}

function sa_tb_executeheader(){ // assigns assets to queue or header
  GLOBAL $PLUGIN_ID, $SA_TB_PLUGINPATH, $owner;

  # debugLog("sa_dev_executeheader");
  
  $regscript = $owner."register_script";
  $regstyle  = $owner."register_style";
  $quescript = $owner."queue_script";
  $questyle  = $owner."queue_style";

  $regstyle($PLUGIN_ID, $SA_TB_PLUGINPATH.'assets/css/sa_toolbar.css', '0.1', 'screen');
  $questyle($PLUGIN_ID,GSFRONT);   
}

?>