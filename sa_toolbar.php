<?php

/*
	revs:
	
	added login toolbar

	todo:
	
	cache menus on backend, so they are all available on the front end always
	do something about session timing out on front end when doing nothing on back end, dev testing etc.
	blind logout, using redirect for now.
	icons line wrap text on IOS, why

*/

/*
* @Plugin Name: sa_toolbar
* @Description: Admin toolbar
* @Version: 0.1.6
* @Author: Shawn Alverson
* @Author URI: http://tablatronix.com/getsimple-cms/sa-toolbar/
*
* @hook callouts: satb_toolbar_disp
*/


$SATB = array();
$SATB['PLUGIN_ID'] = "sa_toolbar";
$SATB['PLUGIN_PATH'] = $SITEURL.'plugins/'.$SATB['PLUGIN_ID'].'/';
$SATB['PLUGIN_URL'] = "http://tablatronix.com/getsimple-cms/sa-toolbar-plugin/";
$SATB['owner'] = '';
$SATB['gsback'] = true;


// DEBUGGING GLOBAL
// ----------------------
$SATB['DEBUG'] = false;
// ----------------------

define('SATB_DEBUG',$SATB['DEBUG']);

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");			// Plugin File
$satb_pname = 	  'SA Toolbar';    	    	//Plugin name
$satb_pversion =	'0.1.6'; 		       	     	//Plugin version
$satb_pauthor = 	'Shawn Alverson';      	//Plugin author
$satb_purl = 			$SATB['PLUGIN_URL'];		//author website
$satb_pdesc =			'SA Toolbar';					 	//Plugin description
$satb_ptype =			'';                 	  //page type - on which admin tab to display
$satb_pfunc =			'';                     //main function (administration)
	
# register plugin
register_plugin($thisfile,$satb_pname,$satb_pversion,$satb_pauthor,$satb_purl,$satb_pdesc,$satb_ptype,$satb_pfunc);

  
if(defined('SATB_DEBUG') and SATB_DEBUG == true){
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

// INIT
add_action('logout','satb_logout');

if(sa_tb_user_is_admin()){

	satb_setTbCookie();
	
	$SATB_MENU_ADMIN = array(); // global admin menu
	$SATB_MENU_STATIC = array(); // global toolbar menu
	
	add_action('theme-footer', 'sa_toolbar');
	add_action('index-pretemplate', 'sa_init_i18n');
	if($SATB['gsback'] = true){
		add_action('footer', 'sa_toolbar');
		add_action('admin-pre-header', 'sa_init_i18n');
	}	

	add_action('sa_toolbar_disp','satb_hook_test');
	
	// asset queing
	// use header hook if older than 3.1
	if(floatval(GSVERSION) < 3.1){
		add_action('header', 'sa_tb_executeheader');
		$SATB['owner'] = "SA_tb_";
	}  
	else{ sa_tb_executeheader(); }

} 
else if (satb_checkTbCookie()){	
	if(floatval(GSVERSION) < 3.1){
		add_action('header', 'sa_tb_executeheader');
		$SATB['owner'] = "SA_tb_";
	}  
	else{ sa_tb_executeheader(); }
	
	add_action('theme-footer', 'sa_toolbar',array(true));
}

// FUNCTIONS
// ----------------------------------------------------------------------------

function satb_logout(){
	// On logout redirect to page before logout redirects to index
	// requires get[toolbar'] presence
	if(isset($_GET['toolbar']) and isset($_GET['close']))	satb_clearTbCookie();
	if(isset($_GET['toolbar']) and isset($_SERVER['HTTP_REFERER'])) redirect($_SERVER['HTTP_REFERER']);
}

function satb_checkTbCookie(){
	satb_debugLog(isset($_COOKIE['GS_ADMIN_TOOLBAR']) and $_COOKIE['GS_ADMIN_TOOLBAR'] == '1');	
	return isset($_COOKIE['GS_ADMIN_TOOLBAR']) and $_COOKIE['GS_ADMIN_TOOLBAR'] == '1';
}

function satb_setTbCookie(){
	setcookie('GS_ADMIN_TOOLBAR', 1, time() + 86400,'/');
}

function satb_clearTbCookie(){
 	setcookie('GS_ADMIN_TOOLBAR', 'null', time() - 3600,'/');	
}


function satb_debugLog(){
	GLOBAL $debugLogFunc;
		
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


function sa_init_i18n(){
	global $LANG;
		// PRELOAD DEFAULT LANG FILES HERE
		// i18n_merge('anonymous_data') || i18n_merge('anonymous_data', 'en_US'); 
}

function sa_toolbar($login=null){
	
	// todo : refactor this a bit, whew
	
  GLOBAL $SATB,$SITEURL,$LANG,$USR,$SATB_MENU_ADMIN,$SATB_MENU_STATIC;

	$gstarget = '_blank'; 

	// logo	
	$logo  = '<li><ul class="satb_nav"><li class="satb_menu satb_icon"><a class="satb_logo" title="GetSImple CMS ver. '.GSVERSION.'" href="#"><img src="'.$SATB['PLUGIN_PATH'].'assets/img/gsicon.png"></a><ul id="satb_logo_sub">';
	$logo .= '<li class=""><a href="http://get-simple.info" target="'.$gstarget.'">GetSimple CMS</a></li>';
	$logo .= '<li class=""><a href="http://get-simple.info/forum/" target="'.$gstarget.'">Forums<span class="iconright">&#9656;</span></a>';
	$logo .= '<ul><li class=""><a href="http://get-simple.info/forum/search/new/" target="'.$gstarget.'">New Posts</a></li>';
	$logo .= '</ul></li>';
	$logo .= '<li class=""><a href="http://get-simple.info/extend/" target="'.$gstarget.'">Extend</a></li>';
	$logo .= '<li class=""><a href="http://get-simple.info/wiki/" target="'.$gstarget.'">Wiki</a></li>';
	$logo .= '<li class=""><a href="http://code.google.com/p/get-simple-cms" target="'.$gstarget.'">SVN</a></li>';
	$logo .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$gstarget.'"><i class="cssicon info"></i>About SA_toolbar</a></li>';

	// icon test
	/*	
	$test = '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon info"></i>Info Icon</a></li>';
	$test .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon help"></i>Help Icon</a></li>';
	$test .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon success"></i>Success Icon</a></li>';
	$test .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon success-alt"></i>Success Alt Icon</a></li>';
	$test .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon alert"></i>Alert Icon</a></li>';
	$test .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon warning"></i>Warning Icon</a></li>';
	$test .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon denied"></i>Denied Icon</a></li>';
	$test .= '<li class=""><a class="" href="http://get-simple.info/forum/topic/4141/sa-gs-admin-toolbar/" target="'.$target.'"><i class="cssicon ribbon"></i>Ribbon Icon</a></li>';
	*/
	
	$logo .= '</ul></ul></li>';	
	
	// login form
	if($login){
		echo '<div id="sa_toolbar"><ul class="">'.$logo.'</ul>
			<ul class="right">
			<ul class="satb_nav">
				<li id="satb_login" class="satb_menu">
				<a id="satb_login_link" href="#">'.i18n_r('LOGIN').'</a>
					<ul id="satb_login_menu">			
						<form action="/getsimple_dev/admin/index.php?redirect='.$_SERVER['REQUEST_URI'].'" method="post">
							<b>Username:</b><input type="text" id="userid" name="userid">
							<b>Password:</b><input type="password" id="pwd" name="pwd">
							<input class="submit" id="satb_login_submit" type="submit" name="submitted" value="Login">
						</form>
					</ul>
				</li>
			<li class="satb_menu tb_close"><a href="'.$SITEURL.'/admin/logout.php?toolbar&close" title="Remove Bar"><strong>&times;</strong></a></li>				
			</ul>
			</ul>
		</div>';
		satb_jsOutput();		
		return;	
	}
	
	$editpath = $SITEURL.'admin/edit.php';
	
	if(function_exists('return_page_slug')){
		$pageslug = return_page_slug();
	} else {
		$pageslug = '';
	}	
	
	$tm = array(); // holds all tabs
	$sm = array(); // holds all sidemenus	
	
	$ptabs = sa_tb_get_PluginTabs();	// hold plugin tabs
		
	// tabs
	$tm = sa_tb_addMenu($tm,'pages','TAB_PAGES','admin/pages.php');
	$tm = sa_tb_addMenu($tm,'files','TAB_FILES','admin/upload.php');
	$tm = sa_tb_addMenu($tm,'theme','TAB_THEME','admin/theme.php');
	$tm = sa_tb_addMenu($tm,'backups','TAB_BACKUPS','admin/backups.php');
	$tm = sa_tb_addMenu($tm,'plugins','PLUGINS_NAV','admin/plugins.php');

	// merge in plugin nav-tabs
	$tm = array_merge($tm,$ptabs);	
		
	$tm = sa_tb_addMenu($tm,'support','TAB_SUPPORT','admin/support.php'); // custom
	$tm = sa_tb_addMenu($tm,'settings','TAB_SETTINGS','admin/settings.php'); // custom
	$tm = sa_tb_addMenu($tm,'logs','LOGS','admin/log.php'); // custom
	
	# satb_debugLog($ptabs);
	# satb_debugLog($tm);
	
	// default sidemenus
	$sm = sa_tb_addMenu($sm,'pages','SIDE_VIEW_PAGES','admin/pages.php');
	$sm = sa_tb_addMenu($sm,'pages','SIDE_CREATE_NEW','admin/edit.php');
	$sm = sa_tb_addMenu($sm,'pages','MENU_MANAGER','admin/menu-manager.php');
	$sm = sa_tb_addMenu($sm,'files','FILE_MANAGEMENT','admin/upload.php');
	$sm = sa_tb_addMenu($sm,'theme','SIDE_CHOOSE_THEME','admin/theme.php');
	$sm = sa_tb_addMenu($sm,'theme','SIDE_EDIT_THEME','admin/theme-edit.php');
	$sm = sa_tb_addMenu($sm,'theme','SIDE_COMPONENTS','admin/components.php');
	$sm = sa_tb_addMenu($sm,'theme','SIDE_VIEW_SITEMAP','../sitemap.xml');
	$sm = sa_tb_addMenu($sm,'backups','SIDE_PAGE_BAK','admin/backups.php');
	$sm = sa_tb_addMenu($sm,'backups','SIDE_WEB_ARCHIVES','admin/archive.php');
	$sm = sa_tb_addMenu($sm,'plugins','SHOW_PLUGINS','admin/plugins.php');
	// $sm = sa_tb_addMenu($sm,'plugins','anonymous_data/ANONY_TITLE','admin/load.php?id=anonymous_data'); // oops, forgot this was a plugin
	$sm = sa_tb_addMenu($sm,'support','SUPPORT','admin/support.php');
	$sm = sa_tb_addMenu($sm,'support','WEB_HEALTH_CHECK','admin/health-check.php');
	$sm = sa_tb_addMenu($sm,'settings','GENERAL_SETTINGS','admin/settings.php');
	$sm = sa_tb_addMenu($sm,'settings','SIDE_USER_PROFILE','admin/settings.php#profile');
	$sm = sa_tb_addMenu($sm,'logs','VIEW_FAILED_LOGIN','admin/log.php?log=failedlogins.log'); // custom
	
	$sm = sa_tb_get_PluginMenus($sm); // add plugin sidemenus to core sidemenus

	// these core sidemenus go at bottom
	$sm = sa_tb_addMenu($sm,'plugins','GET_PLUGINS_LINK','http://get-simple.info/extend');
	$sm = sa_tb_addMenu($sm,'settings','TAB_LOGOUT','admin/logout.php?toolbar'); // logout for convienence

	$logoutitem = $sm['settings'][count($sm['settings'])-1]; // logout is always last item
	$profileitem = $sm['settings'][1];	
	
	satb_automerge(array_merge($sm,$ptabs)); // auto load language files for found lang tokens
		
	// define menu parts

	// link target
	$target = satb_is_frontend() ? '_blank' : '_self'; 
	
	// init master admin menu
	$menu = '<li><ul class="satb_nav">
	<li class="satb_menu"><a href="#">Admin &#9662;</a>
	<ul>
	';
		
	// DO HOOKS
	$SATB_MENU_ADMIN = $sm; // assign to global
	
	$SATB_MENU_STATIC['new'] = array('title'=>satb_cleanStr(satb_geti18n('NEW_PAGE')),'url'=>$editpath);	
	if(function_exists('return_page_slug')){	
		$SATB_MENU_STATIC['edit'] = array('title'=>satb_cleanStr(satb_geti18n('EDIT')),'url'=>$editpath.'?id='.return_page_slug());	
	}
	
	exec_action('sa_toolbar_disp'); // call hook		
	
	$sm = $SATB_MENU_ADMIN; // set back from global
		
	// edit button
	$edit = '';
	if(function_exists('return_page_slug')){
		$edit = '<li class="satb_menu"><a href="'.$SATB_MENU_STATIC['edit']['url'].'" target="'.$target.'">'.$SATB_MENU_STATIC['edit']['title'].'</a></li>';
	}
	
	$new	= '<li class="satb_menu"><a href="'.$SATB_MENU_STATIC['new']['url'].'" target="'.$target.'">+ '.$SATB_MENU_STATIC['new']['title'].'</a></li>';
	
	$separator = '<li class="separator"></li>';
	
	// debug mode indicator
	$debugicon = '<li class="satb_icon" title="'.ucwords(satb_cleanStr(satb_geti18n('DEBUG_MODE'))).' ON"><img src="'.$SATB['PLUGIN_PATH'].'assets/img/sa_tb_debugmode.png"></li>';	
	
	// welcome user
	$sig  = '<ul class="satb_nav"><li class="satb_menu"><a href="#">'.i18n_r('WELCOME').', <strong>'.$USR.'</strong></a><ul>';
	$sig .= '<li class=""><a href="'.$SITEURL.$profileitem['func'].'" target="'.$target.'">'.satb_cleanStr(satb_geti18n($profileitem['title'])).'</a></li>';
	$sig .= '<li class=""><a href="'.$SITEURL.$logoutitem['func'].'"><i class="cssicon alert"></i>'.satb_cleanStr(satb_geti18n($logoutitem['title'])).'</a></li>';
	$sig .= '</ul></li>';
		
	$tm = satb_update_tabs($tm); // handle any empty or new tabs

	satb_debugLog('tabs array',$tm);
	satb_debugLog('sidemenus array',$sm);
	
	foreach($tm as $key=>$page){
		// loop tabs array
		
		$iscustomtab = sa_tb_array_index(sa_tb_array_index($page,0),'iscustom');
		
		// check if tab is plugin tab
		// picky note: built in tabs all use the first level sidemenu item as the default action, all plugins should follow this, arghhh		
		if( isset($ptabs[$key]) ){
			// tab is plugin, so convert lang wrapped titles only and set func and action url parts
			$tablink = 'admin/load.php?id=' . sa_tb_array_index(sa_tb_array_index($ptabs[$key],0),'func');
			$tablink .=  '&amp;' . sa_tb_array_index(sa_tb_array_index($ptabs[$key],0),'action');
			$title_i18n = true;
		} else {
			// tab is core
			$tablink = sa_tb_array_index(sa_tb_array_index($page,0),'func');
			// is tab custom
			if($iscustomtab){
				$title_i18n = true;
			} else {
				$title_i18n = false;
			}			
		}
		
		if($key != 'link'){
			$menu.= '<li' . (isset($ptabs[$key])? ' class="plugin" ':'') . '><a href="'.$SITEURL.$tablink.'" target="'.$target.'">'.satb_cleanStr(satb_geti18n($tm[$key][0]['title'],$title_i18n));
			$menu.= (count($page) > 0) ? '<span class="iconright">&#9656;</span></a><ul>' : '</a><ul>';
		}
		
		// loop sidemenus for page
		if(isset($sm[$key])){
				
			foreach($sm[$key] as $submenu){
			
				$iscustomsm = sa_tb_array_index($submenu,'iscustom');
				$ispluginsm = sa_tb_array_index($submenu,'isplugin');			
			
				if( (isset($submenu['isplugin']) and $submenu['isplugin'] == true) or $iscustomtab or $iscustomsm) {
					$title = satb_cleanStr(satb_geti18n($submenu['title'],true));				
					$class = $iscustomtab || $iscustomsm ? 'custom' : 'plugin';
					
					if($iscustomtab and $key == 'link'){
						$menu.='<li class="'.$class.'"><a href="'.$SITEURL.'admin/load.php?id='.$submenu['func'].(isset($submenu['action']) ? '&amp;'.$submenu['action'] : '').'" target="'.$target.'">'.$title.'</a></li>';										
					} else {
						$menu.='<li class="'.$class.'"><a href="'.$SITEURL.'admin/load.php?id='.$submenu['func'].(isset($submenu['action']) ? '&amp;'.$submenu['action'] : '').'" target="'.$target.'">'.$title.'</a></li>';					
					}	
				} else {
					$title = satb_cleanStr(satb_geti18n($submenu['title']));	
					$menu.='<li><a href="'.$SITEURL.$submenu['func'].'" target="'.$target.'">'.$title.'</a></li>';
			 }
			}
		}		
		
		if($key == 'link'){
			$menu.='</li>';
		} else {
			$menu.='</ul></li>';
		}
		
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
	
	satb_jsOutput();
	
}
	
function satb_jsOutput(){
?>
	
	<script type="text/javascript">
		$(document).ready(function() {

			$('body').append($('#sa_toolbar')); // prevents inheriting styles from #footer
			$('ul#pill').hide(); // hide backend header
			
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
		# satb_debugLog($menu);
		foreach($menu as $item){
		# satb_debugLog($item);			
			if(preg_match('/^\{(.*)\/(.*)\}$/',$item['title'],$matches)){
				# satb_debugLog($item);			
				if(isset($matches[1]) and isset($matches[2])){
					$i18n_merges[] = trim($matches[1]);
				}	
			}	
		}	
	}

	$i18n_merges = array_unique($i18n_merges);
	
	foreach($i18n_merges as $merge){
		satb_debugLog('satb_automerge_custom',$merge);
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

function sa_tb_addMenu($array,$page,$title,$func){
	$array[$page][] = array('func'=>$func,'title'=>$title);
	return $array;
}


function satb_update_tabs($tm){
	// adds or removes tabs as needed
	
	GLOBAL $SATB_MENU_ADMIN;
	$smkeys = array_keys($SATB_MENU_ADMIN);
	$tmkeys = array_keys($tm);
		
	// new tabs
	foreach(array_diff($smkeys,$tmkeys) as $tab){
		# $tm = sa_tb_addMenu($tm,$tab,$tab,'');
		$tm[$tab][] = array('func'=>$tab,'title'=>$tab,'iscustom'=>true);
	}

	// empty tabs
	foreach(array_diff($tmkeys,$smkeys) as $tab){
		unset($tm[$tab]);
	}
	
	return $tm;	
}

function sa_tb_get_PluginTabs(){
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

function sa_tb_get_PluginMenus($pluginsidemenus = array(),$page = null){
	global $plugins;
  $sa_plugins = $plugins;

	# satb_debuglog($sa_plugins);
	
  foreach ($sa_plugins as $hook)	{
    if(substr($hook['hook'],-8,9) == '-sidebar'){
			# satb_debuglog($hook);		
			$tab = str_replace('-sidebar','',$hook['hook']);
			if(isset($hook['args']) and isset($hook['args'][0]) and isset($hook['args'][1])){
				$allowAll = true; // allow plugins that use their own callbacks instead of createSideMenu, even though it is a terrible idea
				if($hook['function'] == 'createSideMenu' or $allowAll){
					$pluginsidemenus[$tab][] = array('title'=>$hook['args'][1],'func'=>$hook['args'][0],'action'=> isset($hook['args'][2]) ? $hook['args'][2] : null,'isplugin' => true,'file' => $hook['file'] );
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

function satb_is_frontend() {
  GLOBAL $base;
        if(isset($base)) {
                return true;
        } else {
                return false;
        }
}


//	add_action('sa_toolbar_disp','satb_hook_test');

function satb_hook_test(){
	GLOBAL $SATB,$SATB_MENU_ADMIN,$SATB_MENU_STATIC;
	if(SATB_DEBUG == false) return;
	
	// known issues / limitations
	// If you do not specify isplugin or iscustom, your string will be i18n decoded and wrapped in {} at this time
	
	// To add a link to an existing sub menu, use the pages name or plugin name as the arrays key
	$SATB_MENU_ADMIN['pages'][] = array('title'=>'my custom pages item','func'=>'#','iscustom'=>true);
	$SATB_MENU_ADMIN['backups'][] = array('title'=>'my custom backup item','func'=>'#','iscustom'=>true);
	
	// To add a link to a new sub menu, use the arkey for the menu name eg. "custom"
	$SATB_MENU_ADMIN['custom'][] = array('title'=>'my custom sub menu item','func'=>'#','iscustom'=>true);
	
	// TO add a single menu item link use 'link' as the page
	$SATB_MENU_ADMIN['link'][] = array('title'=>'my custom menu item link','func'=>'#','iscustom'=>true);
	
	// To remove an entire menu, remove it from the array
	unset($SATB_MENU_ADMIN['settings']); // remove settings entirely
	
	// To remove specific sub-menu items, you will have to loop through the array and remove items based on your criteria
	// * menu items contain a 'file' attribute which can help identify a specific plugins submenus
	
	// To change the edit button
	$SATB_MENU_STATIC['edit'] = array('title'=> 'Custom Edit','url'=>'javascript:alert(\'javacript example\');');
	
	// To change the new page button
	$SATB_MENU_STATIC['new'] = array('title'=> 'Custom New','url'=>'admin/load.php?id=blog&create_post');
	
	// There is no way to add new top buttons yet, but its in the works.
	
	satb_debugLog($SATB_MENU_ADMIN);
}

?>