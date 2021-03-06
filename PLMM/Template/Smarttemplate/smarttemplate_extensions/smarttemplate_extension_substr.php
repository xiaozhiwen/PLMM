<?php

	/**
	* SmartTemplate Extension substr
	* Insert specific part of a string
	*
	* Usage Example:
	* Content:  $template->assign('HEADLINE', 'My Title');
	* Template: <font size=4>{substr:HEADLINE,0,1}</font>{substr:HEADLINE,1}
	* Result:   <font size=4>M</font>y Title
	*
	* @author Philipp v. Criegern philipp@criegern.de
	*/
	function smarttemplate_extension_substr ( $param, $lim0 = 0, $lim1 = 0 )
	{
		if ($lim1) {
			return substr( $param, $lim0, $lim1 );
		} else {
			return substr( $param, $lim0 );
		}
	}

?>