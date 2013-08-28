<?php

	/**
	* SmartTemplate Extension config
	* Print Content of Configuration Parameters
	*
	* Usage Example:
	* Content:  $_CONFIG['webmaster']  =  'philipp@criegern.de';
	* Template: Please Contact Webmaster: {config:"webmaster"}
	* Result:   Please Contact Webmaster: philipp@criegern.de
	*
	* @author Philipp v. Criegern philipp@criegern.de
	*/
	function smarttemplate_extension_config ( $param )
	{
		global $_CONFIG;

		return $_CONFIG[$param];
	}

?>