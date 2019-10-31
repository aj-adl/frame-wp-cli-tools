<?php

class Output
{
/*
 *   local RED=`tput setaf 1`
  local GREEN=`tput setaf 2`
  local BLUE=`tput setaf 6`
  local YELLOW=`tput setaf 3`
  local PURPLE=`tput setaf 5`
  local NC=`tput sgr0` # No Color
  local TAG="${BLUE}FRAME TOOLSET ${NC}:::: "
  local NL=`tput il 1`
  local SEP="${NC}................................";
  local BLOCK="+-------------------------------------------------------------+";
 */

	public static function seperator(){
		WP_CLI::log( WP_CLI::colorize( PHP_EOL . '%n................................' . PHP_EOL ) );
	}

	public static function tag( $string = '' ){
		WP_CLI::log( WP_CLI::colorize( '%bF/R/A/M/E %n:: ' . $string . PHP_EOL ) );
	}

	public static function title( $string ){
		$output = '%cF/R/A/M/E %n:: ' . $string . PHP_EOL;
		$output .= '%n................................' . PHP_EOL;

		WP_CLI::log( WP_CLI::colorize( $output ) );
	}

	public static function header( $string ){

		$output = '';
		$output .= '%n+-------------------------------------------------------------+' . PHP_EOL;
		$output .= '%n+ ' . $string . self::pad( ( 61 - 1 - strlen( $string ) ) ) .'+' . PHP_EOL;
		$output .= '%n+-------------------------------------------------------------+' . PHP_EOL;

		WP_CLI::log( WP_CLI::colorize( $output ) );
	}

	public static function pad( $number = 0, $char = ' '){
		$string = '';
		for( $i = 0; $i < $number; $i++ ){
			$string .=$char;
		}

		return $string;
	}


}
