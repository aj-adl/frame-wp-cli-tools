<?php


class Frame_CLI_Setup {

	/**
	 * Runs the setup necessary for Frame CLI.
	 *
	 * ## OPTIONS
	 * <command>
	 * :The command to be executed in each valid directory
	 *
	 * [--exclude=<folder>]
	 * : Whether or not to greet the person with success or error.
	 *
	 * ## EXAMPLES
	 *
	 *     wp frame setup --folder="code"
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args = [], $assoc_args = []  ){

		WP_CLI\Utils\format_items( 'table', $assoc_args, [] );
	}

}
