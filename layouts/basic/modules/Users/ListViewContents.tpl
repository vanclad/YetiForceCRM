{*<!--
/*+**********************************************************************************
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
	<input type="hidden" id="listViewEntriesCount" value="{$LISTVIEW_ENTRIES_COUNT}" />
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="pageNumberValue" value= "{$PAGE_NUMBER}" />
	<input type="hidden" id="pageLimitValue" value= "{$PAGING_MODEL->getPageLimit()}" />
	<input type="hidden" id="numberOfEntries" value= "{$LISTVIEW_ENTRIES_COUNT}" />
	<input type="hidden" id="alphabetSearchKey" value= "{$MODULE_MODEL->getAlphabetSearchField()}" />
	<input type="hidden" id="Operator" value="{$OPERATOR}" />
	<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" id="listMaxEntriesMassEdit" value="{\AppConfig::main('listMaxEntriesMassEdit')}" />
	<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">

	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $MODULE)}
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}&nbsp;{\App\Language::translate($MODULE ,$MODULE)}&nbsp;</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
	</div>
	<div class="listViewEntriesDiv" >
		<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
		<span class="listViewLoadingImageBlock hide modal" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<table class="table tableBorderHeadBody listViewEntriesTable {$WIDTHTYPE}">
			<thead>
				<tr class="listViewHeaders">
					<th width="2%" colspan="2">
						<input type="checkbox" id="listViewEntriesMainCheckBox" title="{\App\Language::translate('LBL_SELECT_ALL')}" />
					</th>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th class="noWrap {if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}columnSorted{/if}">
							<a href="javascript:void(0);" class="listViewHeaderValues" {if $LISTVIEW_HEADER->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}"{/if} data-columnname="{$LISTVIEW_HEADER->getColumnName()}">{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}
								&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}&nbsp;&nbsp;<span class="{$SORT_IMAGE}"></span>{/if}</a>
						</th>
					{/foreach}
					<th>{\App\Language::translate('LBL_ACTIONS')}</th>
				</tr>
			</thead>
			<tbody>
				{if $MODULE_MODEL->isQuickSearchEnabled()}
					<tr>
						<td class="listViewSearchTd" colspan="2">
							<div class="flexWrapper">
								<a class="btn btn-light" href="javascript:void(0);" data-trigger="listSearch">
									<span class="fas fa-search"></span>
								</a>
								<a class="btn btn-light float-right listRemoveBtn" href="index.php?module={$MODULE}&parent=Settings&view=List" >
									<span class="fas fa-times"></span>
								</a>
							</div>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS name=SEARCH_HEADERS}
							<td>
								{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
								{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME)
                    FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$USER_MODEL}
							</td>
						{/foreach}
						<td></td>
					</tr>
				{/if}
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
					<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}' data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
						<td  width="2%" class="{$WIDTHTYPE}">
							<input type="hidden" name="deleteActionUrl" value="{$LISTVIEW_ENTRY->getDeleteUrl()}">
							{if $LISTVIEW_ENTRY->isEditable()}
								<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" class="listViewEntriesCheckBox" />
							{/if}
						</td>
						<td width="5%" class="{$WIDTHTYPE}">
							<div class="row">
								{assign var=IMAGE_DETAILS value=$LISTVIEW_ENTRY->getImageDetails()}
								{foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
									{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
										<div class="col-md-6">
											<img class="list-user-img" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_INFO.path))}">
										</div>
									{/if}
								{/foreach}
								{if $IMAGE_DETAILS[0]['id'] eq null}
									<div class='col-md-6'>
										<img class="list-user-img" alt="" src="{\App\Layout::getImagePath('DefaultUserIcon.png')}">
									</div>
								{/if}
							</div>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
							<td class="{$WIDTHTYPE}" nowrap>
								{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
							</td>
						{/foreach}
						<td>
							{if $LISTVIEW_HEADER@last}
								<div class="float-right actions">
									<div class="actionImages flexWrapper">
										<a href='{$LISTVIEW_ENTRY->getDuplicateRecordUrl()}'><span title="{\App\Language::translate('LBL_DUPLICATE', $MODULE)}" class="fas fa-retweet alignMiddle"></span></a>&nbsp;
											{if $IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->get('status') eq 'Active'}
											<a id="{$MODULE}_LISTVIEW_ROW_{$LISTVIEW_ENTRY->getId()}_EDIT" href='{$LISTVIEW_ENTRY->getEditViewUrl()}'><span title="{\App\Language::translate('LBL_EDIT', $MODULE)}" class="fas fa-edit alignMiddle"></span></a>&nbsp;
											{/if}
											{if $IS_MODULE_DELETABLE && $LISTVIEW_ENTRY->getId() != $USER_MODEL->getId()}
												{if $LISTVIEW_ENTRY->get('status') eq 'Active'}
												<a id="{$MODULE}_LISTVIEW_ROW_{$LISTVIEW_ENTRY->getId()}_DELETE" class="deleteRecordButton"><span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt alignMiddle"></span></a>
												{else}
												<a onclick="Settings_Users_List_Js.restoreUser({$LISTVIEW_ENTRY->getId()}, event);"><span title="{\App\Language::translate('LBL_RESTORE', $MODULE)}" class="fas fa-sync-alt alignMiddle"></span></a>&nbsp;
												<a onclick="Settings_Users_List_Js.deleteUserPermanently({$LISTVIEW_ENTRY->getId()}, event);"><span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt alignMiddle"></span></a>
												{/if}
											{/if}
									</div>
								</div>
							{/if}
						</td>
					</tr>
				{/foreach}
			</tbody>
			<tfoot class="listViewSummation">
				<tr>
					<td></td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<td {if !empty($LISTVIEW_HEADER->get('maxwidthcolumn'))}style="width:{$LISTVIEW_HEADER->get('maxwidthcolumn')}%"{/if} {if $LISTVIEW_HEADER@last}colspan="2"{/if} class="noWrap {if !empty($LISTVIEW_HEADER->isCalculateField())}border{/if}" >
							{if !empty($LISTVIEW_HEADER->isCalculateField())}
								<button class="btn btn-sm btn-light popoverTooltip" data-operator="sum" data-field="{$LISTVIEW_HEADER->getName()}" data-content="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}">
									<span class="fas fa-signal"></span>
								</button>
								<span class="calculateValue"></span>
							{/if}
						</td>
					{/foreach}
				</tr>
			</tfoot>
		</table>
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
							{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')} <!--{if $IS_MODULE_EDITABLE} {\App\Language::translate('LBL_CREATE')} <a href="{$MODULE_MODEL->getCreateRecordUrl()}">{\App\Language::translate($SINGLE_MODULE, $MODULE)}</a>-->{/if}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
{/strip}
