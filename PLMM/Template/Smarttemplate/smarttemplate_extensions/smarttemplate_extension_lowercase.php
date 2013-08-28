<?php

	/**
	* SmartTemplate Extension lowercase
	* Converts String to lowercase
	*
	* Usage Example:
	* Content:  $template->assign('NAME', 'John Doe');
	* Template: Username: {lowercase:NAME}
	* Result:   Username: john doe
	*
	* @author Philipp v. Criegern philipp@criegern.de
	*/
	function smarttemplate_extension_lowercase ( $param )
	{
		return strtolower( $param );
	}

?>