<?php namespace ProcessWire;
/**
 * Processwire plugin which imitatate WP admin toolbar
 *
   * Well, it is slightly "not perfect", will try better..
 */
if (defined('PROCESSWIRE')) {
    $spot = 'adb';
    echo "\n<!--  ------------------------------------------------------- ".basename(__file__)." ---------------------------------------- -->\n";
?>
<link rel='stylesheet' id='charts-css-css' href='/<?=$spot?>/wp-content/themes/twentytwentyfour-child/photoswipe/photoswipe.css?ver=7.1' media='all' />
<link rel='stylesheet' id='dashicons-css'  href='/<?=$spot?>/wp-includes/css/dashicons.min.css?ver=7.1' media='all' />
<link rel='stylesheet' id='admin-bar-css'  href='/<?=$spot?>/wp-includes/css/admin-bar.min.css?ver=7.1' media='all' />
<style id="admin-bar-inline-css">
 @media screen { html { margin-top: 32px !important; } }
 @media screen and ( max-width: 782px ) { html { margin-top: 46px !important; } }
 @media print { #wpadminbar { display:none; } }
</style>
<style id="wp-block-spacer-inline-css">
 .wp-block-spacer{clear:both}
</style>

<span class="home blog logged-in admin-bar no-customize-support wp-custom-logo wp-embed-responsive wp-theme-twentytwentyfour wp-child-theme-twentytwentyfour-child">
    <style>#wpadminbar #wp-admin-bar-wd_top_button .ab-icon:before {content: "\f239"; color: #FF9800; top: 3px;}</style>
    <div id="wpadminbar" class="nojq nojs">
	<div class="quicklinks" id="wp-toolbar" role="navigation" aria-label="Toolbar">
	    <ul role='menu' id='wp-admin-bar-root-default' class="ab-top-menu">
		<li role='group' id='wp-admin-bar-site-name' class="menupop has-site-icon">
		    <a class='ab-item' role="menuitem" aria-expanded="false" href="<?=SH?>">
			<img class="site-icon" src="<?=SH?>site/assets/files/0000/SH_logo_circle_28.jpg" alt="" width="20" height="20" />Sweet Home
		    </a>
		</li>
		<?php if (!empty($SPOT_id)) { ?>
		<li role='group' id='wp-admin-bar-wd_top_button'>
		    <a class='ab-item' role="menuitem" href='<?=SH?><?=$SPOT_id?>_spot/statistics/' title='Статистика'><span class="ab-icon"></span>Статистика</a>
		</li>
		<?php } ?>
	    </ul>
	    <ul role='menu' id='wp-admin-bar-top-secondary' class="ab-top-secondary ab-top-menu">
		<li role='group' id='wp-admin-bar-my-account' class="menupop with-avatar">
		    <a class='ab-item' role="menuitem" aria-expanded="false" href='#'>Howdy,
			<span class="display-name"><?=\get_WP_User()[1]?></span>
			<?=\get_WP_Avatar("",\get_WP_User()[0],28)?>
		    </a>
		    <div class="ab-sub-wrapper">
			<ul role='menu' aria-label='Howdy, '<?=\get_WP_User()[2]?> id='wp-admin-bar-user-actions' class="ab-submenu">
			    <li role='group' id='wp-admin-bar-user-info'>
				<?=\get_WP_Avatar("",\get_WP_User()[0],60)?><span class='display-name'><?=\get_WP_User()[2]?></span>
			    </li>
			    <li role='group' id='wp-admin-bar-logout'>
				<!-- <a class='ab-item' role="menuitem" href='/<?=$spot?>/wp-login.php?action=logout&#038;_wpnonce=7952a461c8'>Log Out</a> -->
				<?=x("form method='post' action='".SH."' style='display:contents'",
				     session()->CSRF->renderInput('logout') .
				     x("button type='submit' name='logout' value='1' style='display:contents'", x("span style='color:white;white-space:nowrap;'",'Log out')))?>
			    </li>
			</ul>
		    </div>
		</li>
<!--
		<li role='group' id='wp-admin-bar-search' class="admin-bar-search"><div class="ab-item ab-empty-item" tabindex="-1" role="menuitem">
		    <form action="/<?=$spot?>/" method="get" id="adminbarsearch">
			<input class="adminbar-input" name="s" id="adminbar-search" type="text" value="" maxlength="150" />
			<label for="adminbar-search" class="screen-reader-text">Search</label>
			<input type="submit" class="adminbar-button" value="Search" />
		    </form>
		</div>
-->
		</li>
	    </ul>
	</div>
    </div>
</span>
<?php }
echo "\n<!--  ------------------------------------------------------ /".basename(__file__)." ---------------------------------------- -->\n\n";
?>
