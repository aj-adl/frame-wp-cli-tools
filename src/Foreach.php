<?php


class Frame_CLI_Foreach {

	protected $type;

	protected $excludes;

	protected $command;

	protected $command_prefix;

	protected $starting_path;

	protected $is_composer_command;

	protected $is_git_command;

	protected $is_yarn_command;

	/**
	 * Runs the setup necessary for Frame CLI.
	 *
	 * ## OPTIONS
	 * <command>...
	 * :The command to be executed in each valid directory
	 *
	 * [--exclude=<folder>]
	 * : A string referencing one or more folders to exclude, multiples should be comma seperated
	 *
	 * [--type=<project-type>]
	 * : A project type to test for
	 * ---
	 * default: all
	 * options:
	 *   - all
	 *   - wordpress
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp frame foreach "ls -al"
	 *
	 *     wp frame foreach "ls -al" --exclude="project1, project4"
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args , $assoc_args ){

		frame_cli_includes();

		// Parse options

		// Parse command to look for git, composer, yarn

		$this->parse_args( $args, $assoc_args );

		//$this->load_environment();

		// Get all the possible subdirectories
		$dirs = $this->get_sub_directories();
		WP_CLI::debug(  ' ALL DIRS: ' . json_encode( $dirs ) );

		// Check requirements vs type and commands above
		$valid = $this->get_valid_dir_names();
		WP_CLI::debug(  'VALID DIRS: ' . json_encode( array_values( $valid ) ) );

		// Execute


		foreach ( $valid as $dirname ){

			$path =  getcwd() . DIRECTORY_SEPARATOR . $dirname;

			$command = "cd $path && " . $this->command;

			if( $this->command_prefix ){
				$command = trim( $this->command_prefix  ) . ' ' . $command;
			}

			Output::title( $dirname );

			WP_CLI::debug( 'Running ' . $command );

			$result = shell_exec( $command );

			if ( $result ){
				echo $result;
				WP_CLI::line();
			} else {
				WP_CLI::warning( WP_CLI::colorize( '%yno result for command ' . $command ) );
				WP_CLI::line();
			}

		}

	}

	protected function load_environment(){

	}

	protected function parse_args( $args, $assoc_args ){

		$this->starting_path = getcwd();

		$this->command = $args[0];

		WP_CLI::debug( 'COMMAND: ' . $this->command );

		$this->excludes = ['logs', 'log', 'vendor', 'ignore', 'tmp' ];

		if ( isset( $assoc_args['exclude'] ) ){

			$excludes = explode( ',', $assoc_args['exclude'] );

			$this->excludes = array_merge( $this->excludes, array_map( function( $s ){ return trim( $s ); }, $excludes ) );

			WP_CLI::debug( 'EXCLUDES: ' . join( ', ', $this->excludes ) );
		}

		if ( isset( $assoc_args['type'] ) ){
			$this->type = $assoc_args['type'];

			WP_CLI::debug( 'TYPE: ' . $this->type );
		}

		if ( stristr(  $this->command, 'composer' ) && ! ( stristr( 'composer init', $this->command ) ) ){
			$this->is_composer_command = true;
			WP_CLI::debug( 'IS COMPOSER COMMAND' );
		}

		if ( stristr( $this->command, 'git' ) ){
			$this->is_git_command = true;
			WP_CLI::debug( 'IS GIT COMMAND' );
		}

		if ( stristr( $this->command, 'yarn' ) ){
			$this->is_yarn_command = true;
			WP_CLI::debug( 'IS YARN COMMAND' );
		}

	}

	protected function get_valid_dir_names(){

		return array_filter( $this->get_sub_directories(), [ $this, 'is_valid_project' ] );
	}

	protected function get_sub_directories(){

		return array_map( function( $d ){ return basename( $d ); }, glob(getcwd() . '/*', GLOB_ONLYDIR) );

	}

	protected function is_valid_project( $dirname ){

		if( substr( $dirname, 0, 1 ) === '_'){
			WP_CLI::debug( $dirname . ' will be excluded  [ Condition: dirname starts with _ ]' );
			return false;
		}

		if( array_search( $dirname, $this->excludes ) !== false ){
			WP_CLI::debug( $dirname . ' will be excluded  [ Condition: dirname in excludes list ]' );
			return false;
		}

		$path = $slashed_path = getcwd() . DIRECTORY_SEPARATOR . $dirname;
		$slashed_path = $path . DIRECTORY_SEPARATOR;

		if( $this->is_composer_command && ! file_exists( $slashed_path . 'composer.json' ) ){
			WP_CLI::warning( $dirname . ' will be excluded  [ Condition: no composer.json when running composer commands ]' );
			return false;
		}

		if( $this->is_git_command && ! is_dir( $slashed_path . '.git' ) && ! stristr( $this->command, 'git init' ) ){
			WP_CLI::warning( $dirname . ' will be excluded  [ Condition: not a valid git repository when running git commands ]' );
			return false;
		}

		if( $this->is_yarn_command && ! file_exists( $slashed_path . 'package.json' ) ){
			WP_CLI::warning( $dirname . ' will be excluded [ Condition: no package.json when running yarn commands ]' );
			return false;
		}

		if ( $this->type === 'wordpress' ){
			return $this->is_wordpress_project( $dirname );
		}

		return true;

	}

	protected function is_wordpress_project( $dirname ){

		$path = $slashed_path = getcwd() . DIRECTORY_SEPARATOR . $dirname;
		$slashed_path = $path . DIRECTORY_SEPARATOR;

		if ( file_exists( $slashed_path . 'composer.json' ) ){

			$composer_info = json_decode( file_get_contents($slashed_path . 'composer.json' ), true );

			$packages = array_keys( $composer_info['require'] );

			$wordpress_package_names = [ 'johnpbloch/wordpress-core', 'johnpbloch/wordpress', 'roots/wordpress'];

			$core_packages_for_poject = array_intersect( $wordpress_package_names, $packages );

			if( count( $core_packages_for_poject ) ) {
				WP_CLI::debug( $dirname . ' was matched as a WordPress project. [ Condition: package in composer.json ]' );
				return true;
			}
		}

		if ( file_exists( $slashed_path . 'wp-config.php' ) ) {
			WP_CLI::debug( $dirname . ' was matched as a WordPress project. [ Condition: ' . $slashed_path . 'wp-config.php' .' exists ]' );
			return true;
		}

		if ( file_exists( $slashed_path . 'dist' . DIRECTORY_SEPARATOR . 'wp-config.php' ) ) {
			WP_CLI::debug( $dirname . ' was matched as a WordPress project. [ Condition: ' . $slashed_path . 'dist/wp-config.php' .' exists ]' );
			return true;
		}
		if ( file_exists( $slashed_path . 'site' . DIRECTORY_SEPARATOR . 'wp-config.php' ) ) {
			WP_CLI::debug( $dirname . ' was matched as a WordPress project. [ Condition: ' . $slashed_path . 'site/wp-config.php' .' exists ]' );
			return true;
		}
		if ( file_exists( $slashed_path . 'public' . DIRECTORY_SEPARATOR . 'wp-config.php' ) ) {
			WP_CLI::debug( $dirname . ' was matched as a WordPress project. [ Condition: ' . $slashed_path . 'public/wp-config.php' .' exists ]' );
			return true;
		}

		WP_CLI::debug( $dirname . ' was excluded [ Condition: Not a WordPress project ]' );

		return false;

	}

}
