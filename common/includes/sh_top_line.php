<?php
$spot = 'adb';
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
		    <a class='ab-item' role="menuitem" aria-expanded="false" href='/sh_imac/'>
			<img class="site-icon" src="/sh/site/assets/files/0000/sh_logo50.png" alt="" width="20" height="20" />Sweet Home
		    </a>
		</li>
		<li role='group' id='wp-admin-bar-wd_top_button'><a class='ab-item' role="menuitem" href='/<?=$spot?>/stat/' title='Статистика'><span class="ab-icon"></span>Статистика</a></li>
	    </ul>
	    <ul role='menu' id='wp-admin-bar-top-secondary' class="ab-top-secondary ab-top-menu">
		<li role='group' id='wp-admin-bar-my-account' class="menupop with-avatar">
		    <a class='ab-item' role="menuitem" aria-expanded="false" href='/<?=$spot?>/wp-admin/profile.php'>Howdy,
			<span class="display-name">yb</span>
			<img alt='' src='https://secure.gravatar.com/avatar/b1cc58a4ec77ae5c3fbacdf46cd2c3f5f6f1e886348935a6770e0bef3958f222?s=28&#038;d=mm&#038;r=g' srcset='https://secure.gravatar.com/avatar/b1cc58a4ec77ae5c3fbacdf46cd2c3f5f6f1e886348935a6770e0bef3958f222?s=56&#038;d=mm&#038;r=g 2x' class='avatar avatar-28 photo' height='28' width='28' loading='lazy' decoding='async'/>
		    </a>
		    <div class="ab-sub-wrapper">
			<ul role='menu' aria-label='Howdy, yb' id='wp-admin-bar-user-actions' class="ab-submenu">
			    <li role='group' id='wp-admin-bar-user-info'>
				<a class='ab-item' role="menuitem" href='/<?=$spot?>/wp-admin/profile.php'>
				    <img alt='' src='https://secure.gravatar.com/avatar/b1cc58a4ec77ae5c3fbacdf46cd2c3f5f6f1e886348935a6770e0bef3958f222?s=64&#038;d=mm&#038;r=g' srcset='https://secure.gravatar.com/avatar/b1cc58a4ec77ae5c3fbacdf46cd2c3f5f6f1e886348935a6770e0bef3958f222?s=128&#038;d=mm&#038;r=g 2x' class='avatar avatar-64 photo' height='64' width='64' loading='lazy' decoding='async'/>
				    <span class='display-name'>yb</span>
				    <span class='display-name edit-profile'>Edit Profile</span>
				</a>
			    </li>
			    <li role='group' id='wp-admin-bar-logout'><a class='ab-item' role="menuitem" href='/<?=$spot?>/wp-login.php?action=logout&#038;_wpnonce=7952a461c8'>Log Out</a></li>
			</ul>
		    </div>
		</li>
		<li role='group' id='wp-admin-bar-search' class="admin-bar-search"><div class="ab-item ab-empty-item" tabindex="-1" role="menuitem">
		    <form action="/<?=$spot?>/" method="get" id="adminbarsearch">
			<input class="adminbar-input" name="s" id="adminbar-search" type="text" value="" maxlength="150" />
			<label for="adminbar-search" class="screen-reader-text">Search</label>
			<input type="submit" class="adminbar-button" value="Search" />
		    </form>
		</div>
		</li>
	    </ul>
	</div>
    </div>
</span>
