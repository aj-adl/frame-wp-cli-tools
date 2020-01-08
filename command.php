<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

function frame_cli_includes(){
	include_once ( 'src/Output.php' );
	include_once ( 'src/Setup.php' );
}

frame_cli_includes();

include_once ( 'src/Foreach.php' );
include_once ( 'src/ChangeVersion.php' );

WP_CLI::add_command( 'frame setup', Frame_CLI_Setup::class );
WP_CLI::add_command( 'frame foreach', Frame_CLI_Foreach::class );
WP_CLI::add_command( 'frame change_version', Frame_CLI_ChangeVersion::class );
