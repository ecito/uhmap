<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php if(is_home()) { echo bloginfo('name'); } else { wp_title(''); } ?></title>

<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="all" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/print.css" type="text/css" media="print" />

<!-- Sidebar docking boxes (dbx) by Brothercake - http://www.brothercake.com/ -->
<script type="text/javascript" src="<?php bloginfo('stylesheet_directory'); ?>/dbx.js"></script>
<script type="text/javascript" src="<?php bloginfo('stylesheet_directory'); ?>/dbx-key.js"></script>

<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_directory'); ?>/dbx.css" media="screen, projection" />

<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<!--[if lt IE 8]>
<script src="http://ie7-js.googlecode.com/svn/version/2.0(beta3)/IE8.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>
<body>
<div style="display:none;"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/agradient-30medium.gif" alt="preload" /></div>
	
<div id="page" class="fix">
  <div id="wrapper" class="fix">
    <div id="header" class="fix">
      		<h1 class="blogtitle"><a href="<?php echo get_settings('home'); ?>"><?php bloginfo('name'); ?></a></h1>
      		<div class="description"><?php bloginfo('description'); ?></div>
    </div><!-- /header -->

	<div id="nav" class="fix">
		<ul class="fix">
			<li class="page_item <?php if ( is_home() ) { ?>current_page_item<?php } ?>"><a class="home" href="<?php echo get_settings('home'); ?>/" title="Home"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/home-icon.png" alt="home icon"/></a></li>
			<?php wp_list_pages('sort_column=menu_order&depth=1&title_li=');?>

		</ul>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>
		
	</div><!-- /nav -->

<div id="container" class="fix">
    <div id="left-col">
