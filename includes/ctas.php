<?php

// Display a callout after the entry content.
add_action( 'genesis_after_entry_content', function() {

	// Bail if not a single post.
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	if ( ! class_exists( 'Mai_Callout' ) ) {
		return;
	}

	// CTAs.
	$ctas = get_the_terms( get_the_ID(), 'coupon_cta' );
	if ( ! $ctas ) {
		return;
	}

	foreach ( $ctas as $cta ) {
		$content = $cta->description;
		$section = new Mai_Callout( array(
			'bg'    => '',
			'class' => sprintf( 'cta cta-%s', $cta->slug ),
			'id'    => sprintf( 'cta-%s', $cta->slug ),
			'style' => '',
		), $content );
		echo $section->render();
	}

});
