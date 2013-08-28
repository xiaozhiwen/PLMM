<?php

	/**
	* SmartTemplate Extension uppercase
	* Converts String to uppercase
	*
	* Usage Example:
	* Content:  $template->assign('NAME', 'John Doe');
	* Template: Username: {uppercase:NAME}
	* Result:   Username: JOHN DOE
	*
	* @author Philipp v. Criegern philipp@criegern.de
	*/
	function smarttemplate_extension_uppercase ( $param )
	{
		return strtoupper( $param );
	}

?>