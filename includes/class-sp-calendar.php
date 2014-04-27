<?php
/**
 * Calendar Class
 *
 * The SportsPress calendar class handles individual calendar data.
 *
 * @class 		SP_Calendar
 * @version		0.8
 * @package		SportsPress/Classes
 * @category	Class
 * @author 		ThemeBoy
 */
class SP_Calendar extends SP_Custom_Post {

	/** @var array The events status. */
	public $status;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $post
	 */
	public function __construct( $post ) {
		if ( $post instanceof WP_Post || $post instanceof SP_Custom_Post ):
			$this->ID   = absint( $post->ID );
			$this->post = $post;
		else:
			$this->ID  = absint( $post );
			$this->post = get_post( $this->ID );
		endif;

		$this->status = $this->__get( 'status' );

		if ( ! $this->status )
			$this->status = 'any';
	}

	/**
	 * Returns formatted data
	 *
	 * @access public
	 * @return array
	 */
	public function data() {
		global $pagenow;

		$args = array(
			'post_type' => 'sp_event',
			'numberposts' => -1,
			'posts_per_page' => -1,
			'orderby' => 'post_date',
			'order' => 'ASC',
			'post_status' => $this->status,
			'tax_query' => array(
				'relation' => 'AND'
			),
		);

		if ( $pagenow != 'post-new.php' ):
			if ( $this->ID ):
				$leagues = get_the_terms( $this->ID, 'sp_league' );
				$seasons = get_the_terms( $this->ID, 'sp_season' );
				$venues = get_the_terms( $this->ID, 'sp_venue' );
				$team = get_post_meta( $this->ID, 'sp_team', true );

				if ( $leagues ):
					$league_ids = array();
					foreach( $leagues as $league ):
						$league_ids[] = $league->term_id;
					endforeach;
					$args['tax_query'][] = array(
						'taxonomy' => 'sp_league',
						'field' => 'id',
						'terms' => $league_ids
					);
				endif;

				if ( $seasons ):
					$season_ids = array();
					foreach( $seasons as $season ):
						$season_ids[] = $season->term_id;
					endforeach;
					$args['tax_query'][] = array(
						'taxonomy' => 'sp_season',
						'field' => 'id',
						'terms' => $season_ids
					);
				endif;

				if ( $venues ):
					$venue_ids = array();
					foreach( $venues as $venue ):
						$venue_ids[] = $venue->term_id;
					endforeach;
					$args['tax_query'][] = array(
						'taxonomy' => 'sp_venue',
						'field' => 'id',
						'terms' => $venue_ids
					);
				endif;

				if ( $team ):
					$args['meta_query']	= array(
						array(
							'key' => 'sp_team',
							'value' => $team,
						),
					);
				endif;

			endif;
			
			$events = get_posts( $args );

		else:
			$events = array();
		endif;

		return $events;

	}
}