<?php # -*- coding: utf-8 -*-

namespace RESTAdminBar;

class RESTEndpointsToAdminBar {

	/**
	 * @wp-hook wp_loaded
	 */
	public function run() {

		add_action( 'wp_before_admin_bar_render', [ $this, 'updade_admin_bar' ] );
	}

	/**
	 * @wp-hook wp_before_admin_bar_render
	 */
	public function updade_admin_bar() {

		$URI_builder = new Core\JSONNonceURIBuilder( 'wp_json' );
		$nodes = [];

		/* /wp-json */
		$nodes[ 'json' ] = new AdminBarRESTNode\JSON(
			$GLOBALS[ 'wp_admin_bar' ],
			$URI_builder
		);

		if ( is_admin() ) {
			/* current screen */
			$screen = get_current_screen();
			if ( is_a( $screen, '\WP_Screen' ) ) {
				$nodes[ 'current' ] = new AdminBarRESTNode\WPScreenObject(
					$screen,
					$GLOBALS[ 'wp_admin_bar' ],
					$URI_builder,
					$nodes[ 'json' ]
				);
			}
		} else {
			$nodes[ 'current' ] = new AdminBarRESTNode\QueriedObject(
				get_queried_object(),
				$GLOBALS[ 'wp_admin_bar' ],
				$URI_builder,
				$nodes[ 'json' ]
			);
		}

		/* /wp-json/posts */
		$nodes[ 'json/posts' ] = new AdminBarRESTNode\Posts(
			$GLOBALS[ 'wp_admin_bar' ],
			$URI_builder,
			$nodes[ 'json' ]
		);

		/* /wp-json/users */
		$nodes[ 'json/users' ] = new AdminBarRESTNode\Users(
			$GLOBALS[ 'wp_admin_bar' ],
			$URI_builder,
			$nodes[ 'json' ]
		);

		/* /wp-json/users/me */
		$nodes[ 'json/users/me' ] = new AdminBarRESTNode\UsersMe(
			$GLOBALS[ 'wp_admin_bar' ],
			$URI_builder,
			$nodes[ 'json/users' ]
		);

		/* /wp-json/taxonomies */
		$nodes[ 'json/taxonomies' ] = new AdminBarRESTNode\Taxonomies(
			$GLOBALS[ 'wp_admin_bar' ],
			$URI_builder,
			$nodes[ 'json' ]
		);

		foreach ( get_taxonomies( [ 'public' => TRUE ] ) as $tax ) {
			$nodes[ 'json/taxonomies/' . $tax ] = new AdminBarRESTNode\SingleTaxonomy(
				$tax,
				$GLOBALS[ 'wp_admin_bar' ],
				$URI_builder,
				$nodes[ 'json/taxonomies' ]
			);
			$nodes[ 'json/taxonomies/' . $tax . '/terms' ] = new AdminBarRESTNode\Terms(
				$tax,
				$GLOBALS[ 'wp_admin_bar' ],
				$URI_builder,
				$nodes[ 'json/taxonomies/' . $tax ]
			);
		}

		foreach ( $nodes as $node )
			$node->register();
	}
} 