<?php


class Frame_CLI_Setup {

	/**
	 * Runs the setup necessary for Frame CLI.
	 *
	 * ## OPTIONS
	 *
	 * [--folder=<folder>]
	 * : Whether or not to greet the person with success or error.
	 *
	 * ## EXAMPLES
	 *
	 *     wp frame setup --folder="code"
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args = [], $assoc_args = []  ){

		$input = [];

		foreach( $assoc_args as $key => $value ) {
			$input[] = [
				'key' => $key,
				'value' => $value,
			];
		}

		WP_CLI\Utils\format_items( 'table', $input, [ 'key', 'value' ] );
	}

}
