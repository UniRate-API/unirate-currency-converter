<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UniRate_Exception extends RuntimeException {}

class UniRate_Authentication_Exception extends UniRate_Exception {}

class UniRate_Currency_Exception extends UniRate_Exception {}

class UniRate_Rate_Limit_Exception extends UniRate_Exception {}
