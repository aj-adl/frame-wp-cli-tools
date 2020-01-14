<?php


class Frame_CLI_ChangeVersion {

	protected $package;

	protected $version_string;

	protected $section_key;

	/**
	 * Update a dependency's version constraint in composer.json
	 *
	 * ## OPTIONS
	 * <package>
	 * : The composer package to be updated
	 *
	 * <version>
	 * : The new version string, any valid composer version string is valid
	 *
	 * [--dev]
	 * : Update a devDependency instead of a normal one
	 * ## EXAMPLES
	 *
	 *     wp frame change_version "roots/wordpress" "5.3.2"
	 *
	 *     wp frame change_version "wpackagist-plugin/wordpress-seo" "^12.0"
	 *
	 * @when before_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function __invoke( $args, $assoc_args )
	{
		frame_cli_includes();

		$this->package = $args[0];
		$this->version_string = $args[1];
		$this->section_key = ( isset( $assoc_args['dev'] ) && $assoc_args['dev'] ) ? 'require-dev' : 'require';

		$slashed_path = getcwd() . DIRECTORY_SEPARATOR;

		if ( ! file_exists( $slashed_path . 'composer.json' ) ){
			WP_CLI::error( 'There is no composer.json in this directory' );
		}

		WP_CLI::debug( 'file found at '. $slashed_path . 'composer.json, decoding...' );

		$composer_info = json_decode( file_get_contents($slashed_path . 'composer.json' ), true );

		$packages = $composer_info[ $this->section_key ];

		$package_names = array_keys( $packages );

		if ( array_search( $this->package, $package_names ) === false ){
			WP_CLI::error( "Package '$this->package' does not exist within section '$this->section_key' in ${slashed_path}composer.json " );
		}

		$composer_info[ $this->section_key ][ $this->package ] = $this->version_string;

		// Remove any empty sections from composer.json, as they will get output in the wrong format.
		foreach( $composer_info as $key => $value ){

			if ( ! empty( $value ) ) continue;

			unset( $composer_info[ $key ] );
		}

		$result = file_put_contents( $slashed_path . 'composer.json', json_encode( $composer_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

		if ( $result === false ){
			WP_CLI::error( "Error replacing file in ${slashed_path}composer.json, changes not saved" );
		}

		$colorized_version = WP_CLI::colorize( "%G$this->version_string%n" );

		WP_CLI::success( "Package '$this->package' in section '$this->section_key' is now set to ${colorized_version} in ${slashed_path}composer.json " );
	}

}
