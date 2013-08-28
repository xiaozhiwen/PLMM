<?php

	/**
	* SmartTemplate Extension mailto
	* creates Mailto-Link from email address
	*
	* Usage Example:
	* Content:  $template->assign('CONTACT', 'philipp@criegern.de' );
	* Template: Mail to Webmaster: {mailto:CONTACT}
	* Result:   Mail to Webmaster: <a href="mailto:philipp@criegern.de">philipp@criegern.de</a>
	*
	* @author Philipp v. Criegern philipp@criegern.de
	*/
	function smarttemplate_extension_mailto ( $param )
	{
		return "<a href=\"mailto:$param\">$param</a>";
	}

?>