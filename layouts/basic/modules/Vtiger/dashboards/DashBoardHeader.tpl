{*<!--
/************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************/
-->*}
{strip}
	<nav class="widget_header d-flex">
		{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		<div class="btn-group listViewMassActions modOn_{$MODULE} float-left paddingRight10">
			{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] BTN_GROUP=false CLASS=buttonTextHolder}
		</div>
	</nav>
{/strip}
