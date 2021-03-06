<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

namespace vtlib;

/**
 * Provides basic API to work with vtiger CRM Fields.
 */
class FieldBasic
{
	/** ID of this field instance */
	public $id;
	public $name;
	public $tabid = false;
	public $label = false;
	public $table = false;
	public $column = false;
	public $columntype = false;
	public $helpinfo = '';
	public $summaryfield = 0;
	public $header_field = false;
	public $maxlengthtext = 0;
	public $maxwidthcolumn = 0;
	public $masseditable = 1; // Default: Enable massedit for field
	public $uitype = 1;
	public $typeofdata = 'V~O';
	public $displaytype = 1;
	public $generatedtype = 1;
	public $readonly = 1;
	public $presence = 2;
	public $defaultvalue = '';
	public $maximumlength = 100;
	public $sequence = false;
	public $quickcreate = 1;
	public $quicksequence = false;
	public $info_type = 'BAS';
	public $block;
	public $fieldparams = '';

	/**
	 * Initialize this instance.
	 *
	 * @param array        $valuemap
	 * @param mixed        $module        Mixed id or name of the module
	 * @param \vtlib\Block $blockInstance Instance of block to which this field belongs
	 */
	public function initialize($valuemap, $module = false, $blockInstance = false)
	{
		$this->id = (int) $valuemap['fieldid'];
		$this->tabid = (int) $valuemap['tabid'];
		$this->name = $valuemap['fieldname'];
		$this->label = $valuemap['fieldlabel'];
		$this->column = $valuemap['columnname'];
		$this->table = $valuemap['tablename'];
		$this->uitype = (int) $valuemap['uitype'];
		$this->typeofdata = $valuemap['typeofdata'];
		$this->helpinfo = $valuemap['helpinfo'];
		$this->masseditable = (int) $valuemap['masseditable'];
		$this->header_field = $valuemap['header_field'];
		$this->maxlengthtext = (int) $valuemap['maxlengthtext'];
		$this->maxwidthcolumn = (int) $valuemap['maxwidthcolumn'];
		$this->displaytype = (int) $valuemap['displaytype'];
		$this->generatedtype = (int) $valuemap['generatedtype'];
		$this->readonly = (int) $valuemap['readonly'];
		$this->presence = (int) $valuemap['presence'];
		$this->defaultvalue = $valuemap['defaultvalue'];
		$this->quickcreate = (int) $valuemap['quickcreate'];
		$this->sequence = (int) $valuemap['sequence'];
		$this->quicksequence = (int) $valuemap['quickcreatesequence'];
		$this->summaryfield = (int) $valuemap['summaryfield'];
		$this->fieldparams = $valuemap['fieldparams'];
		$this->block = $blockInstance ? $blockInstance : Block::getInstance($valuemap['block'], $module);
	}

	/** Cache (Record) the schema changes to improve performance */
	public static $__cacheSchemaChanges = [];

	/**
	 * Get unique id for this instance.
	 */
	public function __getUniqueId()
	{
		return \App\Db::getInstance()->getUniqueID('vtiger_field');
	}

	/**
	 * Get next sequence id to use within a block for this instance.
	 */
	public function __getNextSequence()
	{
		$maxSeq = (new \App\Db\Query())->from('vtiger_field')
			->where(['tabid' => $this->getModuleId(), 'block' => $this->getBlockId()])
			->max('sequence');
		if ($maxSeq) {
			return $maxSeq + 1;
		}

		return 0;
	}

	/**
	 * Get next quick create sequence id for this instance.
	 */
	public function __getNextQuickCreateSequence()
	{
		$maxSeq = (new \App\Db\Query())->from('vtiger_field')
			->where(['tabid' => $this->getModuleId()])
			->max('quickcreatesequence');
		if ($maxSeq) {
			return $maxSeq + 1;
		}

		return 0;
	}

	/**
	 * Create this field instance.
	 *
	 * @param vtlib\Block Instance of the block to use
	 */
	public function __create($blockInstance)
	{
		$db = \App\Db::getInstance();
		$this->block = $blockInstance;
		$moduleInstance = $this->getModuleInstance();
		$this->id = $this->__getUniqueId();
		if (!$this->sequence) {
			$this->sequence = $this->__getNextSequence();
		}
		if ($this->quickcreate != 1) { // If enabled for display
			if (!$this->quicksequence) {
				$this->quicksequence = $this->__getNextQuickCreateSequence();
			}
		} else {
			$this->quicksequence = null;
		}

		// Initialize other variables which are not done
		if (!$this->table) {
			$this->table = $moduleInstance->basetable;
		}
		if (!$this->column) {
			$this->column = strtolower($this->name);
		}
		if (!$this->columntype) {
			$this->columntype = 'string(100)';
		}
		if (!$this->label) {
			$this->label = $this->name;
		}
		$db->createCommand()->insert('vtiger_field', [
			'tabid' => $this->getModuleId(),
			'fieldid' => $this->id,
			'columnname' => $this->column,
			'tablename' => $this->table,
			'generatedtype' => (int) ($this->generatedtype),
			'uitype' => $this->uitype,
			'fieldname' => $this->name,
			'fieldlabel' => $this->label,
			'readonly' => $this->readonly,
			'presence' => $this->presence,
			'defaultvalue' => $this->defaultvalue,
			'maximumlength' => $this->maximumlength,
			'sequence' => $this->sequence,
			'block' => $this->getBlockId(),
			'displaytype' => $this->displaytype,
			'typeofdata' => $this->typeofdata,
			'quickcreate' => (int) ($this->quickcreate),
			'quickcreatesequence' => (int) ($this->quicksequence),
			'info_type' => $this->info_type,
			'helpinfo' => $this->helpinfo,
			'summaryfield' => (int) ($this->summaryfield),
			'fieldparams' => $this->fieldparams,
			'masseditable' => $this->masseditable,
		])->execute();
		Profile::initForField($this);
		if (!empty($this->columntype)) {
			Utils::addColumn($this->table, $this->column, $this->columntype);
			if ($this->uitype === 10) {
				$db->createCommand()->createIndex("{$this->table}_{$this->column}_idx", $this->table, $this->column)->execute();
			}
		}
		$this->createAdditionalField();
		\App\Log::trace("Creating field $this->name ... DONE", __METHOD__);
	}

	/**
	 * Create additional fields.
	 */
	public function createAdditionalField()
	{
		if ($this->uitype === 11) {
			$fieldInstance = new Field();
			$fieldInstance->name = $this->name . '_extra';
			$fieldInstance->table = $this->table;
			$fieldInstance->label = 'FL_PHONE_CUSTOM_INFORMATION';
			$fieldInstance->column = $this->column . '_extra';
			$fieldInstance->uitype = 1;
			$fieldInstance->displaytype = 3;
			$fieldInstance->typeofdata = 'V~O';
			$fieldInstance->save($this->block);
		}
	}

	public function __update()
	{
		\App\Log::trace("Updating Field $this->name ... DONE", __METHOD__);
	}

	/**
	 * Delete this field instance.
	 */
	public function __delete()
	{
		Profile::deleteForField($this);
		\App\Db::getInstance()->createCommand()->delete('vtiger_field', ['fieldid' => $this->id])->execute();
		if ($this->uitype === 10) {
			\App\Db::getInstance()->createCommand()->delete('vtiger_fieldmodulerel', ['fieldid' => $this->id])->execute();
		} elseif ($this->uitype === 11) {
			$rowExtra = (new \App\Db\Query())->from('vtiger_field')->where(['fieldname' => $this->name . '_extra'])->one();
			if ($rowExtra === false) {
				throw new \App\Exceptions\AppException('Extra field does not exist');
			}
			\App\Db::getInstance()->createCommand()->delete('vtiger_field', ['fieldid' => $rowExtra['fieldid']])->execute();
		}
		\App\Log::trace("Deleteing Field $this->name ... DONE", __METHOD__);
	}

	/**
	 * Get block id to which this field instance is associated.
	 */
	public function getBlockId()
	{
		return $this->block->id;
	}

	/**
	 * Get module id to which this field instance is associated.
	 */
	public function getModuleId()
	{
		if ($this->tabid) {
			return $this->tabid;
		}
		if (!empty($this->block)) {
			return $this->block->module->id;
		}

		return false;
	}

	/**
	 * Get module name to which this field instance is associated.
	 */
	public function getModuleName()
	{
		if ($this->tabid) {
			return \App\Module::getModuleName($this->tabid);
		}

		return $this->block->module->name;
	}

	/**
	 * Get module instance to which this field instance is associated.
	 */
	public function getModuleInstance()
	{
		return $this->block->module;
	}

	/**
	 * Save this field instance.
	 *
	 * @param vtlib\Block Instance of block to which this field should be added
	 */
	public function save($blockInstance = false)
	{
		if ($this->id) {
			$this->__update();
		} else {
			$this->__create($blockInstance);
		}

		return $this->id;
	}

	/**
	 * Delete this field instance.
	 */
	public function delete()
	{
		$this->__delete();
	}

	/**
	 * Set Help Information for this instance.
	 *
	 * @param string Help text (content)
	 */
	public function setHelpInfo($helptext)
	{
		// Make sure to initialize the core tables first
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['helpinfo' => $helptext], ['fieldid' => $this->id])
			->execute();
		\App\Log::trace("Updated help information of $this->name ... DONE", __METHOD__);
	}

	/**
	 * Set Masseditable information for this instance.
	 *
	 * @param int Masseditable value
	 */
	public function setMassEditable($value)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['masseditable' => $value], ['fieldid' => $this->id])
			->execute();
		\App\Log::trace("Updated masseditable information of $this->name ... DONE", __METHOD__);
	}

	/**
	 * Set Summaryfield information for this instance.
	 *
	 * @param int Summaryfield value
	 */
	public function setSummaryField($value)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['summaryfield' => $value], ['fieldid' => $this->id])
			->execute();
		\App\Log::trace("Updated summaryfield information of $this->name ... DONE", __METHOD__);
	}

	/**
	 * Get block name.
	 *
	 * @return string
	 */
	public function getBlockName()
	{
		if ($this->block) {
			return $this->block->label;
		}

		return '';
	}
}
