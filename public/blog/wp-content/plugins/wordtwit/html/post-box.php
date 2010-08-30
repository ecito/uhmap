<?php global $post; ?>
<?php $settings = wordtwit_get_settings(); ?>

<div id="wordtwit-post-widget">
	<div class="misc-pub-section wt-status-bar">
		Status: <span class="wt-preview">
			<?php 
				$was_tweeted = get_post_meta( $post->ID, 'has_been_twittered', true );
				switch ( $was_tweeted ) { 
					case 'yes':
						echo "<strong class='tweeted'>" . __( "Tweeted", "wordtwit" ) . "</strong>"; 
						break;
					case 'pending':
						echo "<strong class='pending'>" . __( "Pending", "wordtwit" ) . "</strong>";
						break;
					case 'previously':
						echo "<strong class='tweeted'>" . __( "Tweeted previously", "wordtwit" ) . "</strong>";
						break;
					default:
						echo "<strong class='not-tweeted'>" . __( "Not Tweeted", "wordtwit" ) . "</strong>";
						break;
				} 
				
				?></strong></span>
		<div class="retweet-clear"></div>
	</div>
	<input type="hidden" name="wordtwit_nonce" id="wordtwit_nonce" value="<?php echo wp_create_nonce( 'WordTwit' ); ?>" />		
	<?php if ( get_the_title() ) { ?>
	<div class="misc-pub-section" class="wordtwit-preview">
		Preview: <span class="wt-preview"><?php $msg = wordtwit_get_message( $_GET['post'] ); echo $msg; ?></span>
		<div class="retweet-clear"></div>
	</div>
	
		<?php if ( wordtwit_is_bitly() ) { ?>
		<div class="misc-pub-section">
		Views: <span class="wt-preview"><?php echo twit_get_bitly_views( $msg ); ?></span>
		</div>
		<?php } ?>
	
		<?php if ( get_post_meta( $post->ID, 'has_been_twittered', true ) ) { ?>
		<div id="retweet-button">
			<a class="button" href="<?php wordtwit_retweet_link(); ?>"><?php _e( "Retweet Post", "wordtwit" ); ?></a>
		</div>
		<?php } else if ( $post->post_status == 'publish' ) { ?>
		<div id="retweet-button">
			<a class="button" href="<?php wordtwit_retweet_link(); ?>"><?php _e( "Force Tweet", "wordtwit" ); ?></a>
		</div>	
		<?php } ?>
		
	<?php } ?>
	<div class="retweet-clear"></div>

</div>
