<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Leads_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function returns the Number of Leads created per week.
	 *
	 * @param type $data
	 *
	 * @return <Array>
	 */
	public function getLeadsCreated($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();
		$module = $this->getName();
		$securityParameter = \App\PrivilegeQuery::getAccessConditions($module);
		if (!empty($owner)) {
			$ownerSql = ' && smownerid = ' . $owner;
		}

		$params = [];
		if (!empty($dateFilter)) {
			$dateFilterSql = ' && createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$sql = sprintf('SELECT COUNT(*) AS count, date(createdtime) AS time FROM vtiger_leaddetails
		INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
		WHERE deleted = 0 %s %s %s', $ownerSql, $dateFilterSql, $securityParameter);
		$sql .= ' && converted = 0 GROUP BY week(createdtime)';
		$result = $db->pquery($sql, $params);

		$response = [];
		while ($row = $db->getRow($result)) {
			$response[$i][0] = $row['count'];
			$response[$i][1] = $row['time'];
		}

		return $response;
	}

	/**
	 * Function returns Leads grouped by Status.
	 *
	 * @param type $data
	 *
	 * @return array
	 */
	public function getLeadsByStatusConverted($owner, $dateFilter)
	{
		$module = $this->getName();
		$query = new \App\Db\Query();
		$query->select([
				'count' => new \yii\db\Expression('COUNT(*)'),
				'leadstatusvalue' => 'vtiger_leadstatus.leadstatus', ])
				->from('vtiger_leaddetails')
				->innerJoin('vtiger_crmentity', 'vtiger_leaddetails.leadid = vtiger_crmentity.crmid')
				->innerJoin('vtiger_leadstatus', 'vtiger_leaddetails.leadstatus = vtiger_leadstatus.leadstatus')
				->where(['deleted' => 0]);
		if (!empty($owner)) {
			$query->andWhere(['smownerid' => $owner]);
		}
		if (!empty($dateFilter)) {
			$query->andWhere(['between', 'createdtime', $dateFilter['start'] . ' 00:00:00', $dateFilter['end'] . ' 23:59:59']);
		}
		\App\PrivilegeQuery::getConditions($query, $module);
		$query->groupBy(['leadstatusvalue', 'vtiger_leadstatus.sortorderid'])->orderBy('vtiger_leadstatus.sortorderid');
		$dataReader = $query->createCommand()->query();
		$i = 0;
		$response = [];
		while ($row = $dataReader->read()) {
			$response[$i][0] = $row['count'];
			$leadStatusVal = $row['leadstatusvalue'];
			if ($leadStatusVal == '') {
				$leadStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = \App\Language::translate($leadStatusVal, $module);
			$response[$i][2] = $leadStatusVal;
			++$i;
		}
		$dataReader->close();

		return $response;
	}

	/**
	 * Function to get Converted Information for selected records.
	 *
	 * @param array $recordIdsList
	 *
	 * @return array converted Info
	 */
	public static function getConvertedInfo($recordIdsList = [])
	{
		$convertedInfo = [];
		if ($recordIdsList) {
			$convertedInfo = (new App\Db\Query())->select(['leadid', 'converted'])
				->from('vtiger_leaddetails')
				->where(['leadid' => $recordIdsList])
				->createCommand()->queryAllByGroup(0);
		}

		return $convertedInfo;
	}

	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		if (!empty($record) && in_array($sourceModule, ['Campaigns', 'Products', 'Services'])) {
			switch ($sourceModule) {
				case 'Campaigns':
					$tableName = 'vtiger_campaign_records';
					$fieldName = 'crmid';
					$relatedFieldName = 'campaignid';
					break;
				case 'Products':
					$tableName = 'vtiger_seproductsrel';
					$fieldName = 'crmid';
					$relatedFieldName = 'productid';
					break;
			}

			if ($sourceModule === 'Services') {
				$subQuery = (new App\Db\Query())
					->select(['relcrmid'])
					->from('vtiger_crmentityrel')
					->where(['crmid' => $record]);
				$secondSubQuery = (new App\Db\Query())
					->select(['crmid'])
					->from('vtiger_crmentityrel')
					->where(['relcrmid' => $record]);
				$condition = ['and', ['not in', 'vtiger_leaddetails.leadid', $subQuery], ['not in', 'vtiger_leaddetails.leadid', $secondSubQuery]];
			} else {
				$condition = ['not in', 'vtiger_leaddetails.leadid', (new App\Db\Query())->select([$fieldName])->from($tableName)->where([$relatedFieldName => $record])];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}

	/**
	 * Function to search accounts.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function searchAccountsToConvert(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Start ' . __METHOD__);
		if ($recordModel) {
			$mappingFields = Vtiger_Processes_Model::getConfig('marketing', 'conversion', 'mapping');
			$mappingFields = \App\Json::decode($mappingFields);
			$query = (new App\Db\Query())->select(['vtiger_account.accountid'])
				->from('vtiger_account')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_account.accountid')
				->where(['vtiger_crmentity.deleted' => 0]);
			$joinTable = ['vtiger_account', 'vtiger_crmentity'];
			$moduleModel = Vtiger_Module_Model::getInstance('Accounts');
			$focus = $moduleModel->getEntityInstance();
			foreach ($mappingFields as $leadFieldName => $accountFieldName) {
				$fieldModel = $moduleModel->getField($accountFieldName);
				if (!$fieldModel) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				}
				$tableName = $fieldModel->get('table');
				if (!in_array($tableName, $joinTable)) {
					$query->innerJoin($tableName, "{$tableName}.{$focus->tab_name_index[$tableName]} = vtiger_account.accountid");
					$joinTable[] = $tableName;
				}
				$query->andWhere(["{$tableName}.{$fieldModel->getColumnName()}" => $recordModel->get($leadFieldName)]);
			}
			$query->limit(2);
			$dataReader = $query->createCommand()->query();
			$numberRows = $dataReader->count();
			if ($numberRows > 1) {
				$dataReader->close();
				\App\Log::trace('End ' . __METHOD__);

				return false;
			} elseif ($numberRows === 1) {
				\App\Log::trace('End ' . __METHOD__);

				return (int) $dataReader->readColumn(0);
			}
		}
		\App\Log::trace('End ' . __METHOD__);

		return true;
	}

	/**
	 * Function that returns status that allow to convert Lead.
	 *
	 * @return <Array> array of statuses
	 */
	public static function getConversionAvaibleStatuses()
	{
		$leadConfig = Settings_MarketingProcesses_Module_Model::getConfig('lead');

		return $leadConfig['convert_status'];
	}

	/**
	 * Function that checks if lead record can be converted.
	 *
	 * @param string $status - lead status
	 *
	 * @return <boolean> if or not allowed to convert
	 */
	public static function checkIfAllowedToConvert($status)
	{
		$leadConfig = Settings_MarketingProcesses_Module_Model::getConfig('lead');

		if (empty($leadConfig['convert_status'])) {
			return true;
		} else {
			return in_array($status, $leadConfig['convert_status']);
		}
	}
}
