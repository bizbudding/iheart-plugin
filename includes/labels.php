<?php

// Body classes for CSS.
add_filter( 'body_class', function( $classes ) {
	if ( is_home() || is_category() || is_tag() || is_search() ) {
		$classes[] = 'coupon-archive';
	}
	if ( is_singular( 'post' ) && iheart_post_is_expired() ) {
		$classes[] = 'is-expired';
	}
	return $classes;
});

// Post classes for CSS.
add_filter( 'post_class', function( $classes ) {
	if ( iheart_post_has_labels() ) {
		$classes[] = 'has-labels';
	}
	if ( iheart_post_is_expired() ) {
		$classes[] = 'is-expired';
	}
	return $classes;
});

add_filter( 'mai_entry_image_link', function( $html, $args, $original_args ) {
	if ( ! ( isset( $args['content'] ) || in_array( 'post', $args['content'] ) ) ) {
		return $html;
	}
	$labels = iheart_get_category_labels_html();
	if ( ! $labels ) {
		return $html;
	}
	return $html . $labels;
}, 10, 3 );

// Add labels to archive entries.
add_filter( 'genesis_markup_entry-image-link_close', function( $open ) {
	if ( ! ( is_home() || is_category() || is_tag() || is_search() ) ) {
		return $open;
	}
	$html = iheart_get_category_labels_html();
	if ( ! $html ) {
		return $open;
	}
	return $open . $html;
});

// Add labels to single featured image.
add_filter( 'genesis_get_image', function( $output, $args, $id, $html, $url, $src ) {

	if ( ! is_singular() ) {
		return $output;
	}

	if ( ! is_main_query() ) {
		return $output;
	}

	if ( get_post_thumbnail_id() !== $id ) {
		return $output;
	}

	$html = iheart_get_category_labels_html();
	if ( ! $html ) {
		return $output;
	}

	return $output . $html;

}, 10, 6 );

// Add category labels to single entry header.
add_action( 'genesis_entry_header', function() {

	// Bail if not a single post.
	if ( ! is_singular() ) {
		return;
	}

	// Bail if we have a featured image, the labels will show there instead.
	if ( get_post_thumbnail_id() ) {
		return;
	}

	echo iheart_get_category_labels_html();

}, 8 );

/**
 * Get all the category label ids.
 *
 * @since  0.1.0
 */
function iheart_get_category_labels_html() {

	$labels = iheart_get_category_labels();

	// Bail if no labels.
	if ( ! $labels ) {
		return '';
	}

	// If on category archive.
	if ( is_category() ) {

		$cat_id = get_queried_object_id();

		// Unset the current category. We don't want to show "Hot Deals" label on the Hot Deals category archive.
		if ( isset( $labels[ $cat_id ] ) ) {
			unset( $labels[ $cat_id ] );
		}
	}

	$html = '';

	$html .= '<ul class="category-labels">';

	foreach ( $labels as $label ) {

		$html .= '<li class="category-label">';
			$html .= sprintf( '<a href="%s">%s</a>', get_term_link( $label, 'category' ), $label->name );
		$html .= '</li>';
	}

	$html .= '</ul>';

	return $html;
}

/**
 * Check if a post is expired.
 *
 * @since  0.1.0
 */
function iheart_post_is_expired() {
	$labels = iheart_get_category_labels();
	$slugs  = wp_list_pluck( $labels, 'name', 'slug' );
	return isset( $slugs['expired'] );
}

/**
 * Check if a post has any labels.
 *
 * @since  0.1.0
 */
function iheart_post_has_labels() {
	return iheart_get_category_labels() ? true : false;
}

/**
 * Get all category labels.
 *
 * @since  0.1.0
 */
function iheart_get_category_labels() {

	$labels = array();

	// Get all labels.
	$label_ids = iheart_get_all_category_label_ids();
	if ( ! $label_ids ) {
		return $labels;
	}

	// Categories.
	$categories = get_the_terms( get_the_ID(), 'category' );
	if ( ! $categories ) {
		return $labels;
	}

	foreach ( $categories as $category ) {

		// Skip if not a label.
		if ( ! in_array( $category->term_id, $label_ids ) ) {
			continue;
		}

		// Add to list.
		$labels[ $category->term_id ] = $category;
	}


	return $labels;
}

/**
 * Get all the category label ids.
 *
 * @since  0.1.0
 */
function iheart_get_all_category_label_ids() {
	// If transient isn't set.
	if ( false === ( $labels = get_transient( 'iheart_category_label_ids' ) ) ) {
		// Get the labels. Also sets transient.
		$labels = iheart_get_labels_set_transient();
	}
	return $labels;
}

/**
 * Get all the category labels.
 * Set the transient in the process.
 *
 * @since  0.1.0
 */
function iheart_get_labels_set_transient() {
	// Get labels.
	$labels = array();
	$terms  = get_terms( array(
		'taxonomy'   => 'category',
		'fields'     => 'ids',
		'hide_empty' => false,
		'meta_key'   => 'iheart_label',
		'value'      => 'on',
	) );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term_id ) {
			$labels[] = $term_id;
		}
	}
	// Set transient, and expire after 8 hours.
	set_transient( 'iheart_category_label_ids', $labels, 8 * HOUR_IN_SECONDS );
	return $labels;
}

/**
 * Set the transient when a category is edited.
 *
 * @param   int     $term_id  Term ID
 * @param   string  $taxonomy Taxonomy slug.
 *
 * @return  void
 */
add_action( 'created_category', function( $term_id, $tt_id ) {
	$labels = iheart_get_labels_set_transient();
}, 10, 2 );
add_action( 'edited_category', function( $term_id, $tt_id ) {
	$labels = iheart_get_labels_set_transient();
}, 10, 2 );
