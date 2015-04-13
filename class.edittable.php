<?php
/*
	Class CEditTable 0.0.26
	Update 0.0.26: 2015-03-21
	Update 0.0.25: 2014-04-27
	Update 0.0.24: 2013-10-06
	Update 0.0.23: 2013-09-26
	Update 0.0.22: 2013-09-26
	Update 0.0.21: 2013-08-02
	Update 0.0.20: 2013-08-26
	Update 0.0.19: 2013-08-14
	Update 0.0.18: 2013-08-03
	Update 0.0.17: 2013-07-11
	Update 0.0.16: 2013-07-10
	Update 0.0.15: 2013-01-05
	Update 0.0.14: 2012-12-19
	Update 0.0.13: 2012-10-17
	Update 0.0.12: 2012-09-27
	Update 0.0.11: 2012-09-21
	Update 0.0.10: 2012-05-26
	Update 0.0.9: 2012-05-19
	Update 0.0.8: 2012-04-30
	Date start: 2011-12-01
*/
/*
	form_fieldType:
		- checkbox: 'values'=>array('0','1') - имеет всегда два значения. Второе значение всегда "true", оно и выводится в параметре value в тэге input.
	tr_param:
		если массив, то key - имя поля из таблицы; value - массив значений (где key - значение; value - на что заменять)
*/
define('UPDATE_ALL_FIELDS_OF_TABLE', true);
define('DONT_UPDATE_ALL_FIELDS_OF_TABLE', false);

require_once(SITE_ROOT.'/assets/modules/seagulllibrary/seagull_library.php');

class CEditTable {
	var $table_hidden;
	var $table_columns_count;

	function __construct($tablename, $columns) { //------------------------------------------------------
		$this->tablename = $GLOBALS['table_prefix'].$tablename;
		$this->table = '`'.$this->tablename.'`';
		$this->tablename_i18n = $this->tablename.'_i18n';
		$this->table_i18n = '`'.$this->tablename.'_i18n`';
		$this->columns = $columns;
		$this->table_param = 'class="b-table" cellpadding="0" cellspacing="0"';
		$this->sort_col = 'id';
		$this->sort_direct = 'ASC';
		$this->id_col = 'id';
		$this->id_col_i18n = 'id';
		$this->rows_per_col = 0;
		$this->tag_begin = '<p>';
		$this->tag_end = '</p>';
		$this->label_begin = '<label>';
		$this->label_end = '</label>';
		$this->multilang = false;
		$this->page = 1;
		$this->paginatorRowsByPage = 10;
		$this->paginatorAdvLinks = 2;

		$this->init();
	}

	function init() { //------------------------------------------------------

		foreach ($this->columns as $col=>$column) {
			if (!isset($this->table_mysql_select)) {
				if (!$column['table_hidden']) {
					$table_columns[] = $col;
					$table_mysql_select[] = $column['table_mysql_mask'] ? $column['table_mysql_mask'] : '`'.$col.'`';
				}
			}
			if ((!$column['form_hidden'] and !$column['non-exist']) or $this->id_col === $col) {
				$form_columns[] = $col;
				$form_mysql_select[] = $column['form_mysql_mask'] ? $column['form_mysql_mask'] : '`'.$col.'`';
			}
		}
		if ($table_columns) {
			$this->table_mysql_select = implode(',', $table_mysql_select);
			$this->table_columns_count = count($table_columns);
		}

		$this->form_mysql_select = implode(',', $form_mysql_select);
		$this->form_columns_count = count($this->form_columns);
		return 1;
	}

	function setConfig($var, $val) { //-----------------------------------------------------------------
		$this->{$var} = $val;
	}

	function getRows($select, $where=NULL, $order=NULL, $limit=NULL) { //-------------------------------
//	Получить список строк таблицы из БД 	// DO IT: использовать LIMIT и ORDER BY

		$select = empty($select) ? $this->table_mysql_select : $select;
		$where = is_null($where) ? '' : ' WHERE '.$where;
		$order = is_null($order) ? ' ORDER BY '.$this->sort_col.' '.$this->sort_direct : ' ORDER BY '.$order;
		$limit = is_null($limit) ? '' : ' LIMIT '.$limit;

//echo 'SELECT '.$select.' FROM '.$this->table.$where.$order.$limit;
		if ($this->multilang)
			$rows = sql2table('SELECT '.$select.' FROM '.$this->table.' LEFT JOIN '.$this->table_i18n.' ON `id`=`event_id` '.$where.$order.$limit);
		else
			$rows = sql2table('SELECT '.$select.' FROM '.$this->table.$where.$order.$limit);

		return $rows;
	}

	function getRow($id, $select=NULL, $oneCol=false) { //-------------------------------------------------------------
//	Получить список строк таблицы из БД 	// DO IT: использовать LIMIT и ORDER BY
//		add_log("SELECT $this->form_mysql_select FROM ".$this->tablename." WHERE `$this->id_col`='$id'", 'upload.log');
		$select = is_null($select) ? $this->table_mysql_select : $select;
		$row = retr_sql('SELECT '.$select.' FROM '.$this->table.' WHERE `'.$this->id_col.'`=\''.$id.'\'', MYSQL_ASSOC, $oneCol);

		return $row;
	}

	function getRowLang($id, $select=NULL) { //---------------------------------------------------------
//	Получить список строк таблицы из БД 	// DO IT: использовать LIMIT и ORDER BY
//		add_log("SELECT $this->form_mysql_select FROM ".$this->tablename." WHERE `$this->id_col`='$id'", 'upload.log');
		$row = retr_sql('SELECT * FROM `'.$this->tablename.'_i18n` WHERE `event_id`=\''.$id.'\'', MYSQL_ASSOC, true);

		return $row;
	}

	function renderTable($page=NULL, $where=NULL, $order=NULL, $limit=NULL) { //------------------------

//	Постройка заголовка таблицы
		$output = '<table '.$this->table_param.'>';
		$output .= $this->renderTableHead();

//	Получить список строк таблицы из БД 	// DO IT: использовать LIMIT и ORDER BY
		$output .= '<tbody>';
		$output .= $this->renderTableBody($page, $where, $order, $limit);
		$output .= '</tbody></table>';
		return $output;
	}

	function renderTableHead() { //---------------------------------------------------------------------
		$output = '<thead><tr>';

		foreach ($this->columns as $col=>$column) {
			if (!$column['table_hidden']) {
				$output .= '<td'.($column['table_theadParam']?' '.$column['table_theadParam']:'').'>'.($column['table_title_hidden'] ? '' : $column['title']).'</td>';
			}
		}
		$output .= '</tr></thead>';
		return $output;
	}

	function renderTableBody($page=NULL, $where=NULL, $order=NULL, $limit=NULL) { //--------------------

//	Получить список строк таблицы из БД 	// DO IT: использовать LIMIT и ORDER BY
		if (isset($page)) {
			$this->page = $page;
			if (isset($limit) and !strpos($limit, ','))
				$limit = ($page-1) * $limit.','.$limit;
			elseif (isset($this->paginatorRowsByPage))
				$limit = ($page-1) * $this->paginatorRowsByPage.','.$this->paginatorRowsByPage;
		}
		$rows = $this->getRows($this->table_mysql_select, $where, $order, $limit);

		$c = count($rows);
		if ($c) {
			for ($i=0; $i<$c; $i++) {
				$output .= $this->renderTableRow($rows[$i]);
			}
		}
		else
			$output .= '<tr id="js-no-records"><td style="text-align:center" colspan="'.$this->table_columns_count.'">Нет записей</td></tr>';
		return $output;
	}

	function renderTableRow($row) { //------------------------------------------------------------------
		$output = '';
//		add_log(count($row).'|'.count($this->columns), 'gallery.log');

		foreach ($this->columns as $col=>$column) {
			if (!$column['table_hidden']) {
				$value = '';
				if (!$column['table_td_hidden']) {
					switch ($column['form_fieldType']) {
						case 'date':
							$value = $column['table_td_link2edit'] ? '<a href="#" onclick="postForm(\''.$column['table_td_link2edit'].'\', '.$row[$this->id_col].'); return false;">'.$row[$col].'</a>' : (empty($row[$col]) ? '-' : $row[$col]);
						break;

						case 'textarea':
							$value = $column['table_td_nl2br'] ? nl2br($row[$col]) : $row[$col];
						break;

						case 'arr_checkbox':
							$aRow = explode(',', $row[$col]);
							foreach ($aRow as $key=>$checkbox) {
								$aRow[$key] = $column['values'][$checkbox];
							}
							$value = implode(', ', $aRow);
	//						$value = $column['table_td_link2edit'] ? '<a href="#" onclick="postForm(\''.$column['table_td_link2edit'].'\', '.$row[$this->id_col].'); return false;">'.$row[$col].'</a>' : (empty($row[$col]) ? '-' : $row[$col]);
						break;

						default:
							if ($column['table_td_content']) {
								$value = $this->fieldValueReplace($column['table_td_content'], $row);
							}
							else
								$value = $column['table_td_link2edit'] ? '<a href="#" onclick="postForm(\''.$column['table_td_link2edit'].'\', '.$row[$this->id_col].'); return false;">'.$row[$col].'</a>' : $row[$col];
						break;
					}
					if (is_callable($column['table_callback'])) {
						$value = call_user_func($column['table_callback'], $row);
					}
					$value = $column['table_value2key'] ? $column['values'][$value] : $value;
	//				$value = $column['table_td_nowrap'] ? '<span>'.$value.'</span>' : $value;
				}

				$column['table_td_param'] = $this->fieldParamReplace($column['table_td_param'], $row);

				$output .= '<td'.$column['table_td_param'].'>'.$value.'</td>';
			}
		}

		if (isset($this->tr_param)) {
/*
array (
	'state' => array (
					0 => 'class="b-row_yellow"',
					1 => 'class="b-row_red"',
					2 => 'class="b-row_green"'
	),
	'viewed' => array (
					0 => '',
					1 => 'class="b-viewed"',
	),
	'id' => 'id="img%id%"'
)
*/
			$tr_param = $this->fieldParamReplace($this->tr_param, $row);
		}
		return '<tr'.$tr_param.'>'.$output.'</tr>';
	}

	function fieldValueReplace($param, $row) { //-------------------------------------------------------
//	ea($row);
		if (is_array($param)) {
			reset($param);
			while (list($field, $val) = each($param)) {
				if (is_array($val)) {
					if (isset($val[$row[$field]])) {
						$value = $val[$row[$field]];
						break;
					}
				}
				else
					$value .= ' '.str_replace('%'.$field.'%', $row[$field], $val);
			}
		}
		else {
			$value = $param;
		}
		return $value;
	}

	function fieldParamReplace($param, $row) { //-------------------------------------------------------
		if (is_array($param)) {
			reset($param);
			while (list($field, $val) = each($param)) {
				if (is_array($val)) {
					if (isset($val[$row[$field]]))
						$tr_class[] = $val[$row[$field]];
				}
				else
					$tr_param .= ' '.str_replace('%'.$field.'%', $row[$field], $val);
			}
			$tr_param = sizeof($tr_class) ? $tr_param.' class="'.implode(' ', $tr_class).'"' : $tr_param;
		}
		else {
			$tr_param = ' '.$param;
		}
		return $tr_param;
	}

	function renderForm($id=NULL, $lang=NULL) { //------------------------------------------------------
		$output = '';
		$this->init();

		if ($id!=NULL) {
			$row = $this->getRow($id, $this->form_mysql_select);
		}

		foreach ($this->columns as $col=>$column) {
			if (!$column['form_hidden'] and !$column['non-exist']) {
				$field = '';
				if ($column['form_content']) {
					$field = $column['form_content'];
				}
				else {
					if ($column['multilang'] and isset($lang)) {
						$field_name = $lang.'['.$col.']';
						$field_id = $lang.'_'.$col;
					}
					else {
						$field_name = $field_id = $col;
					}

					if (is_callable($column['form_callback'])) {
						$row[$col] = call_user_func($column['form_callback'], $row);
					}

					switch ($column['form_fieldType']) {
						case 'input':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;

						case 'number':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="number" name="'.$field_name.'" value="'.$row[$col].'" />';
						break;

						case 'textarea':
							$field = '<textarea id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' name="'.$field_name.'" row="15" col="20">'.htmlspecialchars($row[$col]).'</textarea>';
						break;

						case 'select':
							foreach ($column['values'] as $value=>$radio) {
								$field .= '<option value="'.$value.'" '.(((empty($row[$col]) and $column['value_default']===$value) or $row[$col]==$value) ? 'selected="selected"':'').'>'.$radio.'</option>';
							}
							$field = '<select id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' name="'.$field_name.'">'.$field.'</select>';
						break;

						case 'checkbox':
							if ($column['table_value2key']) {
								end($column['values']);
								$val = key($column['values']);
								$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="'.$val.'" '.(($row[$col] == $val) ? 'checked="checked"':'').' />';
							}
							elseif (is_array($column['values']))
								$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="'.$column['values'][1].'" '.(($row[$col] == $column['values'][1]) ? 'checked="checked"':'').' />';
							else
								$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="1" '.(($row[$col] == 1) ? 'checked="checked"':'').' />';
						break;

						case 'arr_checkbox':
							$aRow = explode(',', $row[$col]);
							foreach ($column['values'] as $key=>$checkbox) {
								$field .= '<label style="margin-left:150px;display:block"><input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$col.'['.$key.']" value="1" '.(in_array($key, $aRow) ? 'checked="checked"':'').' />'.$checkbox.'</label>';
							}
						break;

						case 'radio':
							foreach ($column['values'] as $value=>$radio) {
								$field .= '<label><input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="radio" name="'.$field_name.'" value="'.$value.'" '.(((empty($row[$col]) and $column['value_default']===$value) or $row[$col]===$value) ? 'checked="checked"':'').' />'.$radio.'</label>';
							}
						break;

						case 'date':
						case 'time':
						case 'datetime':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.$row[$col].'" />';
						break;

						case 'file':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="file" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;

						case 'hidden':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="hidden" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;

						default:
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;
					}
				}
				$fieldButton = $column['form_fieldButton'] ? $column['form_fieldButton'] : '';

				$output .= $this->tag_begin.($column['form_fieldLabelTag']?$column['form_fieldLabelTag']:$this->label_begin).$column['title'].$this->label_end.($column['form_fieldHidden'] ? '' : $field).$fieldButton.$column['form_info'].$this->tag_end;
			}
		}

		return $output;
	}

	function renderField($nameField, $labelShow=false, $lang=NULL) { //------------------------------------------------------
		$output = '';
//		$this->init();

/*		if ($id!=NULL) {
			$row = $this->getRow($id, $this->form_mysql_select);
		}
*/
		$column = &$this->columns[$nameField];
		$col = $nameField;
		if ($column['form_content']) {
			$field = $column['form_content'];
		}
		else {
			if ($column['multilang'] and isset($lang)) {
				$field_name = $lang.'['.$col.']';
				$field_id = $lang.'_'.$col;
			}
			else {
				$field_name = $field_id = $col;
			}
			switch ($column['form_fieldType']) {
				case 'input':
					$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
				break;

				case 'number':
					$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="number" name="'.$field_name.'" value="'.$row[$col].'" />';
				break;

				case 'textarea':
					$field = '<textarea id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' name="'.$field_name.'" row="15" col="20">'.htmlspecialchars($row[$col]).'</textarea>';
				break;

				case 'select':
					foreach ($column['values'] as $value=>$radio) {
						$field .= '<option value="'.$value.'" '.(((empty($row[$col]) and $column['value_default']===$value) or $row[$col]===$value) ? 'selected="selected"':'').'>'.$radio.'</option>';
					}
//					$field = '<select id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' name="'.$field_name.'">'.$field.'</select>';
				break;

				case 'checkbox':
					if ($column['table_value2key']) {
						end($column['values']);
						$val = key($column['values']);
						$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="'.$val.'" '.(($row[$col] == $val) ? 'checked="checked"':'').' />';
					}
					elseif (is_array($column['values']))
						$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="'.$column['values'][1].'" '.(($row[$col] == $column['values'][1]) ? 'checked="checked"':'').' />';
					else
						$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="1" '.(($row[$col] == 1) ? 'checked="checked"':'').' />';
				break;

				case 'arr_checkbox':
					$aRow = explode(',', $row[$col]);
					foreach ($column['values'] as $key=>$checkbox) {
						$field .= '<label style="margin-left:150px;display:block"><input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$col.'['.$key.']" value="1" '.(in_array($key, $aRow) ? 'checked="checked"':'').' />'.$checkbox.'</label>';
					}
				break;

				case 'radio':
					foreach ($column['values'] as $value=>$radio) {
						$field .= '<label><input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="radio" name="'.$field_name.'" value="'.$value.'" '.(((empty($row[$col]) and $column['value_default']===$value) or $row[$col]===$value) ? 'checked="checked"':'').' />'.$radio.'</label>';
					}
				break;

				case 'date':
				case 'time':
				case 'datetime':
					$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.$row[$col].'" />';
				break;

				case 'file':
					$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="file" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
				break;

				case 'hidden':
					$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="hidden" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
				break;

				default:
					$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
				break;
			}
		}
		$fieldButton = $column['form_fieldButton'] ? $column['form_fieldButton'] : '';
		if ($labelShow)
			$output .= $this->tag_begin.($column['form_fieldLabelTag']?$column['form_fieldLabelTag']:$this->label_begin).$column['title'].$this->label_end.($column['form_fieldHidden'] ? '' : $field).$fieldButton.$column['form_info'].$this->tag_end;
		else
			$output .= $field.$fieldButton.$column['form_info'];

		return $output;
	}

	function renderFormLang($id=NULL, $lang=NULL) { //--------------------------------------------------
		$output = '';
		$this->init();

		if ($id!=NULL) {
			$row = $this->getRowLang($id);
		}
//ea($row);
		foreach ($this->columns as $col=>$column) {
			if (!$column['form_hidden'] and $column['multilang']) {
				$field = '';
				if ($column['form_content']) {
					$field = $column['form_content'];
				}
				else {
					if ($column['multilang'] and isset($lang)) {
						$field_name = $col.'_'.$lang;
						$field_id = $lang.'_'.$col;
						$col = $field_id = $field_name;
					}
					else {
						$col = $field_id = $field_name;
					}

					if (is_callable($column['form_callback'])) {
						$row[$col] = call_user_func($column['form_callback'], $row);
					}

					switch ($column['form_fieldType']) {
						case 'input':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;

						case 'number':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="number" name="'.$field_name.'" value="'.$row[$col].'" />';
						break;

						case 'textarea':
							$field = '<textarea id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' name="'.$field_name.'" row="15" col="20">'.htmlspecialchars($row[$col]).'</textarea>';
						break;

						case 'select':
							foreach ($column['values'] as $value=>$radio) {
								$field .= '<option value="'.$value.'" '.(((empty($row[$col]) and $column['value_default']===$value) or $row[$col]===$value) ? 'selected="selected"':'').'>'.$radio.'</option>';
							}
							$field = '<select id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' name="'.$field_name.'">'.$field.'</select>';
						break;

						case 'checkbox':
							if ($column['table_value2key']) {
								end($column['values']);
								$val = key($column['values']);
								$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="'.$val.'" '.(($row[$col] == $val) ? 'checked="checked"':'').' />';
							}
							elseif (is_array($column['values']))
								$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="'.$column['values'][1].'" '.(($row[$col] == $column['values'][1]) ? 'checked="checked"':'').' />';
							else
								$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$field_name.'" value="1" '.(($row[$col] == 1) ? 'checked="checked"':'').' />';
						break;

						case 'arr_checkbox':
							$aRow = explode(',', $row[$col]);
							foreach ($column['values'] as $key=>$checkbox) {
								$field .= '<label style="margin-left:150px;display:block"><input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="checkbox" name="'.$col.'['.$key.']" value="1" '.(in_array($key, $aRow) ? 'checked="checked"':'').' />'.$checkbox.'</label>';
							}
						break;

						case 'radio':
							foreach ($column['values'] as $value=>$radio) {
								$field .= '<label><input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="radio" name="'.$field_name.'" value="'.$value.'" '.(((empty($row[$col]) and $column['value_default']===$value) or $row[$col]===$value) ? 'checked="checked"':'').' />'.$radio.'</label>';
							}
						break;

						case 'date':
						case 'time':
						case 'datetime':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.$row[$col].'" />';
						break;

						case 'file':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="file" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;

						case 'hidden':
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="hidden" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;

						default:
							$field = '<input id="ff-'.$field_id.'" '.$column['form_fieldParam'].(($column['form_dontEdit'] OR $column['form_readOnly'])?' disabled="disabled"':'').' type="text" name="'.$field_name.'" value="'.htmlspecialchars($row[$col]).'" />';
						break;
					}
				}
				$fieldButton = $column['form_fieldButton'] ? $column['form_fieldButton'] : '';

				$output .= $this->tag_begin.($column['form_fieldLabelTag']?$column['form_fieldLabelTag']:$this->label_begin).$column['title'].$this->label_end.($column['form_fieldHidden'] ? '' : $field).$fieldButton.$column['form_info'].$this->tag_end;
			}
		}

		return $output;
	}

	function renderPaginator($page=NULL, $cfg=NULL) { //------------------------------------------------
		$output = '<div '.(isset($cfg['param']) ? ' id="paginator-'.$cfg['param'].'"' : '').' class="paginator">';
		$output .= $this->renderPaginatorLinks($page, $cfg);
		$output .= '</div>';

		return $output;
	}

	function renderPaginatorLinks($page=NULL, $cfg=NULL) { //-------------------------------------------
		$cfg['tableName'] = isset($cfg['tableName']) ? $cfg['tableName'] : $this->tablename;
		$cfg['limit'] = isset($cfg['limit']) ? $cfg['limit'] : $this->paginatorRowsByPage;
		$cfg['advLinks'] = isset($cfg['advLinks']) ? $cfg['advLinks'] : $this->paginatorAdvLinks;
//ea($cfg);
		$output = paginator($page, $cfg);

		return '<span class="paginator-loading" style="display:none">Загрузка...</span>'.$output;
	}

	function saveForm($id=NULL, $aData=NULL) { //-------------------------------------------------------
		if ($id!=NULL) {
			$row = $this->updateRow($id, $aData);
		}
		else {
			$row = $this->insertRow($aData);
		}
		return $row;
	}

	function insertRow($arr=NULL, $updateAllFields=DONT_UPDATE_ALL_FIELDS_OF_TABLE) { //---------------------
		if (is_null($arr))
			$arr = &$_POST;

		$values = array();

		foreach ($this->columns as $col=>$column) {
			if (!$updateAllFields and ($column['form_dontEdit'] or $column['non-exist']))
				continue;
			$value = &$arr[$col];
//			if (array_key_exists($col, $this->columns) and !$this->columns[$col]['form_dontEdit']) {
			switch ($column['form_fieldType']) {
				case 'area':
				case 'time':
					$value = empty($value) ? 'NULL' : '\''.$value.'\'';
				break;

				case 'number':
					$value = ($value==='') ? 'NULL' : '\''.$value.'\'';
				break;

				case 'select':
					$value = '\''.$value.'\'';
				break;

				case 'checkbox':
					if ($column['table_value2key'])
						$value = empty($value) ? '\''.key($column['values']).'\'' : '\''.$value.'\'';
					elseif (is_array($column['values']))
						$value = empty($value) ? '\''.$column['values'][0].'\'' : '\''.$value.'\'';
					else
						$value = isset($value) ? '\''.$value.'\'' : '\'0\'';
				break;

				case 'arr_checkbox':
					$aKeys = array();
					if ($value) {
						foreach ($value as $key=>$checkbox) {
							$aKeys[] = $key;
						}
						$value = '\''.implode(',', $aKeys).'\'';
					}
					else
						$value = '\'\'';
				break;

				case 'radio':
					$value = "'$value'";
				break;

				case 'date':
					if (empty($value))
						$value = 'NULL';
					else
						$value = empty($column['values']) ? (is_string($value) ? date2int($value) : $value) : $column['values'];
				break;

				case 'datetime':
					if (empty($value))
						$value = 'NULL';
					else
						$value = empty($column['values']) ? (is_string($value) ? datetime2int($value) : $value) : $column['values'];
				break;

				default:
					$value = '\''.(get_magic_quotes_gpc() ? $value : mysql_real_escape_string($value)).'\'';
				break;
			}

			$into[] = "`$col`";
			$values[] = $value;
		}
		$into = implode(',', $into);
		$values = implode(',', $values);

//		echo "INSERT INTO $this->table ($into) VALUES ($values)";
		$r = run_sql('INSERT INTO '.$this->table.' ('.$into.') VALUES ('.$values.')');
		if ($r)
			return mysql_insert_id();

		return 0;
	}

	function updateRow($id, $arr=NULL, $updateAllFields=UPDATE_ALL_FIELDS_OF_TABLE) { //----------------

		if ($updateAllFields === DONT_UPDATE_ALL_FIELDS_OF_TABLE) {
//ea($arr);
			foreach ($arr as $col=>$value) {
				if (!$this->columns[$col]['non-exist'] and array_key_exists($col, $this->columns)) {
					$column = &$this->columns[$col];

					switch ($column['form_fieldType']) {
						case 'area':
						case 'number':
						case 'time':
							$value = empty($value) ? 'NULL' : '\''.$value.'\'';
						break;

						case 'select':
							$value = '\''.$value.'\'';
						break;

						case 'checkbox':
							if ($column['table_value2key'])
								$value = empty($value) ? '\''.key($column['values']).'\'' : '\''.$value.'\'';
							elseif (is_array($column['values']))
								$value = empty($value) ? '\''.$column['values'][0].'\'' : '\''.$value.'\'';
							else
								$value = isset($value) ? '\''.$value.'\'' : '\'0\'';
						break;

						case 'arr_checkbox':
							$aKeys = array();
							if ($value) {
								foreach ($value as $key=>$checkbox) {
									$aKeys[] = $key;
								}
								$value = '\''.implode(',', $aKeys).'\'';
							}
							else
								$value = '\'\'';
						break;

						case 'radio':
							$value = "'$value'";
						break;

						case 'date':
							if (empty($value))
								$value = 'NULL';
							else
								$value = empty($column['values']) ? (is_string($value) ? date2int($value) : $value) : $column['values'];
						break;

						case 'datetime':
							if (empty($value))
								$value = 'NULL';
							else
								$value = empty($column['values']) ? (is_string($value) ? datetime2int($value) : $value) : $column['values'];
						break;

						default:
							$value = '\''.(get_magic_quotes_gpc() ? $value : mysql_real_escape_string($value)).'\'';
						break;
					}
					$values[] = '`'.$col.'`='.$value;
				}
			}// foreach
		}
		else {
			if (is_null($arr))
				$arr = &$_POST;

			foreach ($this->columns as $col=>$column) {
				if ($column['form_dontEdit'] or $column['non-exist'])
					continue;
				$value = &$arr[$col];

				switch ($column['form_fieldType']) {
					case 'area':
					case 'number':
					case 'time':
						$value = empty($value) ? 'NULL' : '\''.$value.'\'';
					break;

					case 'select':
						$value = '\''.$value.'\'';
					break;

					case 'checkbox':
						if ($column['table_value2key'])
							$value = empty($value) ? '\''.key($column['values']).'\'' : '\''.$value.'\'';
						elseif (is_array($column['values']))
							$value = empty($value) ? '\''.$column['values'][0].'\'' : '\''.$value.'\'';
						else
							$value = isset($value) ? '\''.$value.'\'' : '\'0\'';
					break;

					case 'arr_checkbox':
						$aKeys = array();
						if ($value) {
							foreach ($value as $key=>$checkbox) {
								$aKeys[] = $key;
							}
							$value = '\''.implode(',', $aKeys).'\'';
						}
						else
							$value = '\'\'';
					break;

					case 'radio':
						$value = "'$value'";
					break;

					case 'date':
						if (empty($value))
							$value = 'NULL';
						else
							$value = empty($column['values']) ? date2int($value) : $column['values'];
					break;

					case 'datetime':
						if (empty($value))
							$value = 'NULL';
						else
							$value = empty($column['values']) ? datetime2int($value, '.') : $column['values'];
					break;

					default:
							$value = '\''.(get_magic_quotes_gpc() ? $value : mysql_real_escape_string($value)).'\'';
					break;
				}

				$values[] = '`'.$col.'`='.$value;
			}// foreach colums
		}
		$values = implode(',', $values);

//		echo 'UPDATE '.$this->table.' SET '.$values.' WHERE `'.$this->id_col.'`='.$id;
//		add_log("UPDATE ".$this->tablename." SET $values WHERE `$this->id_col`='$id'", 'upload.log');
		$r = run_sql('UPDATE '.$this->table.' SET '.$values.' WHERE `'.$this->id_col.'`='.$id);
		if ($r)
			return 1;

		return 0;
	}

	function saveRowLang($id, $arr, $langs=NULL, $lang_default=NULL) { //-------------------------------

		foreach ($this->columns as $col=>$column) {
			if (!$column['multilang'])
				continue;

			foreach ($langs as $lang=>$text) {
				if ($lang_default !== $lang) {
					$colname = $col.'_'.$lang;

					$value = &$arr[$colname];

					switch ($column['form_fieldType']) {
						case 'area':
						case 'number':
							$value = empty($value) ? 'NULL' : '\''.$value.'\'';
						break;

						case 'select':
							$value = '\''.$value.'\'';
						break;

						case 'checkbox':
							if ($column['table_value2key'])
								$value = empty($value) ? '\''.key($column['values']).'\'' : '\''.$value.'\'';
							elseif (is_array($column['values']))
								$value = empty($value) ? '\''.$column['values'][0].'\'' : '\''.$value.'\'';
							else
								$value = isset($value) ? '\''.$value.'\'' : '\'0\'';
						break;

						case 'arr_checkbox':
							$aKeys = array();
							if ($value) {
								foreach ($value as $key=>$checkbox) {
									$aKeys[] = $key;
								}
								$value = '\''.implode(',', $aKeys).'\'';
							}
							else
								$value = '\'\'';
						break;

						case 'radio':
							$value = "'$value'";
						break;

						case 'date':
							if (empty($value))
								$value = 'NULL';
							else
								$value = empty($column['values']) ? date2int($value) : $column['values'];
						break;

						case 'datetime':
							if (empty($value))
								$value = 'NULL';
							else
								$value = empty($column['values']) ? datetime2int($value, '.') : $column['values'];
						break;

						default:
								$value = '\''.(get_magic_quotes_gpc() ? $value : mysql_real_escape_string($value)).'\'';
						break;
					}
//echo $col.'('.$colname.', '.$column['form_fieldType'].') = '.$value.'<br>';
					$values["`$colname`"] = $value;
				}
			}
		}// foreach colums

		$result = mysql_query('SELECT `event_id` FROM '.$this->table_i18n.' WHERE `event_id`='.$id);
		if (mysql_num_rows($result)==0) {
			foreach ($values as $key=>$value) {
				$into[] = $key;
				$values[$key] = $value;
			}
			$into[] = '`event_id`';
			$values['`event_id`'] = $id;

			$into = implode(',', $into);
			$values = implode(',', $values);
//			echo 'INSERT INTO '.$this->table_i18n.' ('.$into.') VALUES ('.$values.')';
			$r = run_sql('INSERT INTO '.$this->table_i18n.' ('.$into.') VALUES ('.$values.')');
			if ($r)
				return mysql_insert_id();
		}
		else {
			foreach ($values as $key=>$value) {
				$values[$key] = $key.'='.$value;
			}
			$values = implode(',', $values);
//			echo 'UPDATE '.$this->table_i18n.' SET '.$values.' WHERE `event_id`='.$id;
	//		add_log("UPDATE ".$this->tablename." SET $values WHERE `$this->id_col`='$id'", 'upload.log');
			$r = run_sql('UPDATE '.$this->table_i18n.' SET '.$values.' WHERE `event_id`='.$id);
		}
		if ($r)
			return 1;

		return 0;
	}

	function del($id, $i18n = false) {
		if (isset($id)) {
			if ($i18n)
				$r = run_sql('DELETE FROM '.$this->tablename_i18n.' WHERE `'.$this->id_col_i18n.'`='.$id);
			$r = run_sql('DELETE FROM '.$this->tablename.' WHERE `'.$this->id_col.'`='.$id);
			if ($r)
				return 1;
		}
		return 0;
	}
}

?>
