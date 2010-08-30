/*
 * WordTwit -The WordTwit Admin Javascript File
 * This file holds all the default JS functions for the plugin
 * Copyright (c) 2008-2009 Duane Storey & Dale Mugford (BraveNewCode Inc.)
 * Licensed under GPL.
 *
 * Last Updated: September 6th, 2009
 */
			$j = jQuery.noConflict();
			$j(document).ready(function(){
				$j("a.wordtwit-fancy").fancybox({
				'padding':						6,
				'imageScale':					true,
				'zoomSpeedIn':				300, 
				'zoomSpeedOut':			300,
				'zoomOpacity':				true, 
				'overlayShow':				false,
				'hideOnContentClick': 	false
			});
				$j("a.wordtwit-ajax").fancybox({
				'padding':						6,
				'zoomSpeedIn':				200, 
				'zoomSpeedOut':			200,
				'zoomOpacity':				true, 
				'overlayShow':				false,
				'frameWidth':				700,
				'frameHeight':				575,
				'hideOnContentClick': 	false
			});		
		});