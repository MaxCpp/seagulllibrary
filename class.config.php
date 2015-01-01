<?
/*	Class CConfig 0.0.12
	Update 0.0.12: 2014-04-24
	Update 0.0.11: 2013-10-02
	Update 0.0.10: 2013-02-23
	Update 0.0.9: 2013-02-20
	Update 0.0.8: 2013-01-07
	Update 0.0.7: 2012-12-20
	Update 0.0.6: 2012-09-30
	Update 0.0.5: 2012-09-19
	Update 0.0.4: 2012-04-30
	Update 0.0.3: 2012-04-23
	Update 0.0.2: 2012-04-19
	Update 0.0.1: 2012-03-26

	Example:
		$this->config->setVariable('text', 'richard_kb@mail.ru', $this->nameModule, NULL, 'T', 'Текст');
		$this->config->setVariable('password', 'фыв', $this->nameModule, NULL, 'P', 'Пароль');
		$view_default = array(
			array('name'=>'images','title'=>'Большие изображения','val'=>'1'),
			array('name'=>'thumbs','title'=>'Миниатюры','val'=>'0'),
			array('name'=>'image_and_thumbs','title'=>'Большое изображение и миниатюры','val'=>'0')
		);
		$this->config->setVariable('select', $view_default, $this->nameModule, NULL, 'S', 'Список');
		$view_default = array(
			array('name'=>'images','title'=>'Большие изображения','val'=>'0'),
			array('name'=>'thumbs','title'=>'Миниатюры','val'=>'1'),
			array('name'=>'image_and_thumbs','title'=>'Большое изображение и миниатюры','val'=>'0')
		);
		$this->config->setVariable('radio', $view_default, $this->nameModule, NULL, 'R', 'Переключатели');
		$this->config->setVariable('checkbox', 1, $this->nameModule, NULL, 'C', 'Флажок');
		$this->config->setVariable('textarea', 'Тег <input> является одним из разносторонних элементов формы и позволяет создавать разные элементы интерфейса и обеспечить взаимодействие с пользователем.', $this->nameModule, NULL, 'TA', 'Многострочный текст');

*/
require_once(SITE_ROOT.'/assets/modules/seagulllibrary/class.edittable.php');

class CConfig {
	var $tables = array();
	var $labelParam = 'class="b-form__label"';
	var $inputParam = '';

	function __construct() { //---------------------------------------
		$args = func_get_args();
		if (isset($args[0])) {
			$this->msg = $args[0];
		}

// Уровень вывода ошибок PHP
		$this->php_error_reporting = E_ALL & ~E_NOTICE;
		$this->mysql_error = true;

		error_reporting($this->php_error_reporting);

		$columns = array();
		$columns['id'] = array(
					'title'=>'ID',
					'form_hidden'=>true,
					'form_dontEdit'=>true
					);

		$columns['title'] =	array(
					'title'=>'Название поля',
					'form_param'=>'style="width:60%"'
					);

		$columns['name'] = array(
					'title'=>'Имя переменной',
					'td_link2edit'=>true
					);

		$columns['value'] = array(
					'title'=>'Значение'
					);

		$columns['type'] = array(
					'title'=>'Тип'
					);

		$columns['module'] = array(
					'title'=>'Модуль',
					'table_hidden'=>true
					);

		$this->tables['config'] = new CEditTable('seagull_config', $columns);
		$this->tables['config']->setConfig('table_param', 'id="js-table" class="b-table" cellpadding="0" cellspacing="0"');
		$this->tables['config']->setConfig('label_begin', '<label style="width:130px; display:block; float:left">');
	}

	function getVariables($module=NULL, $fieldset=NULL) { //---------------------------------------

		$where = isset($module) ? '`module`=\''.$module.'\' ' : '';
		$where .= isset($fieldset) ? 'AND `fieldset`='.$fieldset : 'AND `fieldset` IS NULL';
		$aVars = sql2table('SELECT `id`, `name`, `value`, `type` FROM '.$this->tables['config']->table.' WHERE '.$where);

		if ($aVars) {
			$arr = array();
			$cv = count($aVars);
			if ($cv)
				for ($i=0; $i<$cv; $i++) {
					$var = &$aVars[$i];
					if (isset($fieldset)) {
						if ($var['type']==='R' or $var['type']==='S') {
							$var['value'] = json_decode($var['value'], true);
							if ($var['value'])
								foreach ($var['value'] as $optio) {
									if (($optio['val'] == '1'))
										$arr[$var['name']] = $optio['name'];
								}
						}
						elseif ($var['type']==='FIELDSET') {
							if ($var['name'])
								$arr[$var['name']] = (object)$this->getVariables($module, $var['id']);
						}
						else {
							if ($var['name'])
								$arr[$var['name']] = $var['value'];
//								$arr[$var['name']] = htmlspecialchars($var['value']);
							else
								echo 'Ошибка: пустое имя в поле "name" таблицы "seagull_gonfig"';
						}
					}
					else {
						if ($var['type']==='R' or $var['type']==='S') {
							$var['value'] = json_decode($var['value'], true);
							if ($var['value'])
								foreach ($var['value'] as $optio) {
									if (($optio['val'] == '1'))
										$this->{$var['name']} = $optio['name'];
								}
						}
						elseif ($var['type']==='FIELDSET') {
							if ($var['name'])
								$this->{$var['name']} = (object)$this->getVariables($module, $var['id']);
						}
						else {
							if ($var['name'])
								$this->{$var['name']} = $var['value'];
//								$this->{$var['name']} = htmlspecialchars($var['value']);
							else
								echo 'Ошибка: пустое имя в поле "name" таблицы "seagull_gonfig"';
						}
					}
				}
		}
		elseif (mysql_errno() == 1146) {
			$this->msg->setError('Необходимо установить модуль');
		}
		return $arr;
	}

	function renderForm($module=NULL, $fieldset=NULL) { //---------------------------------------

//		$where = isset($module) ? '`module`=\''.$module.'\'' : '';
//		$where .= isset($fieldset) ? ' AND `fieldset`='.$fieldset : 'AND `fieldset` IS NULL';

		$aVars = sql2table('SELECT `id`, `title`, `name`, `value`, `type`, `widthField`, `classField`, `info`, `advElement` FROM '.$this->tables['config']->table.' WHERE `module`=\''.$module.'\' AND `fieldset`'.(isset($fieldset) ? '='.$fieldset : ' IS NULL'));

		$arr = array();
		$cv = count($aVars);
		if ($cv) {
			$output = '';

			for ($i=0; $i<$cv; $i++) {
				$field = '';
				$var = &$aVars[$i];

				$widthField = empty($var['widthField']) ? '' : ' style="width:'.$var['widthField'].'"';
				$info = empty($var['advElement']) ? '' : $var['advElement'];
				$info = empty($var['info']) ? $info : $info.'<span class="b-info"><span class="b-info__text">'.$var['info'].'</span></span>';
				$var['title'] = htmlentities($var['title'], ENT_NOQUOTES, "UTF-8");

				switch ($var['type']) {
					case 'FIELDSET':
						$class = empty($var['classField']) ? '' : ' '.$var['classField'];
						$field = $this->renderForm($module, $var['id']);
						$field = '<fieldset class="b-form__fieldset'.$class.'" ><legend>'.$var['title'].'</legend>'.$field.'</fieldset>';
					break;

					case 'T':
						$class = empty($var['classField']) ? '' : ' '.$var['classField'];
						$var['value'] = htmlspecialchars($var['value']);
						$field = '<label '.$this->labelParam.' for="ff-config-id'.$var['id'].'">'.$var['title'].'</label><input class="b-form__input'.$class.'" '.$widthField.' id="ff-config-id'.$var['id'].'" type="text" name="config['.$var['id'].']" value="'.$var['value'].'" />'.$info;
					break;

					case 'N':
						$class = empty($var['classField']) ? '' : 'class="'.$var['classField'].'"';
						$var['value'] = htmlspecialchars($var['value']);
						if (empty($widthField)) $widthField = 'style="width:50px"';
						$field = '<label '.$this->labelParam.' for="ff-config-id'.$var['id'].'">'.$var['title'].'</label><input '.$class.$widthField.' id="ff-config-id'.$var['id'].'" type="number" name="config['.$var['id'].']" value="'.$var['value'].'" />'.$info;
					break;

					case 'R':
						$class = empty($var['classField']) ? '' : 'class="'.$var['classField'].'"';
						$var['value'] = json_decode($var['value'], true);
						foreach ($var['value'] as $optio) {
							$field .= '<label><input '.$class.' type="radio" name="config['.$var['id'].']" value="'.$optio['name'].'" '.(($optio['val'] == '1') ? 'checked="checked"':'').' />'.$optio['title'].'</label><br />';
						}
						$field = '<span '.$this->labelParam.'>'.$var['title'].'</span><span style="display:inline-block">'.$field.'</span>'.$info;
					break;

					case 'S':
						$class = empty($var['classField']) ? '' : 'class="'.$var['classField'].'"';
						$var['value'] = json_decode($var['value'], true);
						if ($var['value'])
							foreach ($var['value'] as $optio) {
								$field .= '<option value="'.$optio['name'].'" '.(($optio['val'] == '1') ? 'selected="selected"':'').'>'.$optio['title'].'</option>';
							}
						else $this->msg->setError('Пустое поле "value" в таблице "config"');
						$field = '<span '.$this->labelParam.'>'.$var['title'].'</span><select '.$class.$widthField.' id="ff-config-id'.$var['id'].'" name="config['.$var['id'].']">'.$field.'</select>'.$info;
					break;

					case 'C':
						$class = empty($var['classField']) ? '' : 'class="'.$var['classField'].'"';
						$field = '<label '.$this->labelParam.' for="ff-config-id'.$var['id'].'">'.$var['title'].'</label><input '.$class.' id="ff-config-id'.$var['id'].'" type="checkbox" name="config['.$var['id'].']" value="1" '.(($var['value'] === '1') ? 'checked="checked"':'').' />'.$info;
					break;

					case 'TA':
						$var['value'] = htmlspecialchars($var['value']);
						$class = empty($var['classField']) ? '' : 'class="'.$var['classField'].'"';
						$field = '<label '.$this->labelParam.' for="ff-config-id'.$var['id'].'">'.$var['title'].'</label><textarea '.$class.$widthField.' id="ff-config-id'.$var['id'].'" name="config['.$var['id'].']">'.$var['value'].'</textarea>'.$info;
					break;

					case 'P':
						$var['value'] = htmlspecialchars($var['value']);
						$class = empty($var['classField']) ? '' : 'class="'.$var['classField'].'"';
						$field = '<label '.$this->labelParam.' for="ff-config-id'.$var['id'].'">'.$var['title'].'</label><input '.$class.$widthField.' id="ff-config-id'.$var['id'].'" type="password" name="config['.$var['id'].']" value="'.$var['value'].'" />'.$info;
					break;

					case 'F':
						$var['value'] = htmlspecialchars($var['value']);
						$class = empty($var['classField']) ? '' : 'class="'.$var['classField'].'"';
						$field = '<label '.$this->labelParam.' for="ff-config-id'.$var['id'].'">'.$var['title'].'</label><input '.$class.$widthField.' id="ff-config-id'.$var['id'].'" type="file" name="config['.$var['id'].']" value="'.$var['value'].'" />'.$info;
					break;
				}
				$output .= '<p class="b-form__2cols">'.$field.'</p>';
			}
		}

		return $output;
	}

	function saveForm($aData, $module=NULL) { //---------------------------------------

		if (isset($module))
			$arr = sql2array('SELECT `id`, `name`, `value`, `type` FROM '.$this->tables['config']->table.' WHERE `module`=\''.$module.'\'');
		else {
			$keys = array_keys($aData);
			$keys = implode(',', $keys);
			$arr = sql2array('SELECT `id`, `name`, `value`, `type` FROM '.$this->tables['config']->table.' WHERE `id` IN ('.$keys.')');
		}
		if ($arr)
		foreach ($arr as $key=>$var) {
			switch ($var['type']) {
				case 'R':
					$obj = json_decode($var['value'], true);
					$c = count($obj);
					for ($i=0; $i<$c; $i++) {
						if ($obj[$i]['name']==$aData[$key])
							$obj[$i]['val'] = 1;
						else
							$obj[$i]['val'] = 0;
					}
					$r = $this->setVariable($key, $obj, NULL, NULL, 'R');
				break;

				case 'S':
					$obj = json_decode($var['value'], true);
					$c = count($obj);
					for ($i=0; $i<$c; $i++) {
						if ($obj[$i]['name']==$aData[$key])
							$obj[$i]['val'] = 1;
						else
							$obj[$i]['val'] = 0;
					}
					$r = $this->setVariable($key, $obj, NULL, NULL, 'S');
				break;

				case 'C':
					$value = isset($aData[$key]) ? '1' : '0';
					$r = $this->setVariable($key, $value, NULL, NULL, 'C');
				break;

				case 'T':
				case 'N':
				case 'TA':
				case 'P':
				case 'F':
					$r = $this->setVariable($key, $aData[$key]);
				break;
			}
		}
		if ($r)
			return 1;
	}

	function setVariable($name, $value, $module=NULL, $fieldset=NULL, $type=NULL, $title=NULL, $width=NULL, $info=NULL, $class=NULL, $advElement=NULL) { //---------------------------------------

		if ($type=='S' or $type=='R') {
			$value = json_encode($value);
		}
		$value = isset($value) ? '\''.mysql_real_escape_string($value).'\'' : 'NULL';
		$title = isset($title) ? mysql_real_escape_string($title) : NULL;
		$info = isset($info) ? mysql_real_escape_string($info) : NULL;

		if (isset($module)) {
			if (!$this->existModule($module)) {
				$this->addModule($module);
			}
		}

		if (is_numeric($name)) {
			$r = run_sql('UPDATE '.$this->tables['config']->table.' SET `value`='.$value.' WHERE `id`='.$name);
			if ($r)
				return 2;
		}
		else {
//			Условия для поиска принадлежности переменной module и fieldset
			$where = isset($module) ? ' AND `module`=\''.$module.'\'' : '';
			if (isset($fieldset) and $fieldset !== 'NULL') {
				$fsID = is_numeric($fieldset) ? $fieldset : $this->getFieldsetID($fieldset, $module);
				$where .= isset($fsID) ? ' AND `fieldset`='.$fsID : '';
			}
			else
				$where .= ' AND `fieldset` IS NULL';

//			echo 'SELECT `id` FROM '.$this->tables['config']->table." WHERE `name`='".$name."'".$where.'<br>';
			if ($varID = retr_sql('SELECT `id` FROM '.$this->tables['config']->table." WHERE `name`='".$name."'".$where)) {
				$set = isset($type) ? ', `type`=\''.$type.'\'' : '';
				$set .= isset($title) ? ', `title`=\''.$title.'\'' : '';
				$set .= isset($info) ? ', `info`=\''.$info.'\'' : '';
				$set .= isset($width) ? ', `widthField`=\''.$width.'\'' : '';
				$set .= isset($class) ? ', `classField`=\''.$class.'\'' : '';
				$set .= isset($advElement) ? ', `advElement`=\''.$advElement.'\'' : '';
//				echo 'UPDATE '.$this->tables['config']->table.' SET `value`='.$value.$set.' WHERE `id`='.$varID.'<br><br>';
				$r = run_sql('UPDATE '.$this->tables['config']->table.' SET `value`='.$value.$set.' WHERE `id`='.$varID);
				if ($r)
					return 2;
			}
			else {
				$fsID = isset($fsID) ? $fsID : 'NULL';
				$info = isset($info) ? '\''.$info.'\'' : 'NULL';
				$width = isset($width) ? '\''.$width.'\'' : 'NULL';
				$class = isset($class) ? '\''.$class.'\'' : 'NULL';
				$advElement = isset($advElement) ? '\''.$advElement.'\'' : 'NULL';
//				echo 'REPLACE INTO '.$this->tables['config']->table.' (`fieldset`, `title`, `name`, `value`, `type`, `module`, `widthField`, `info`) VALUES ('.$fsID.', \''.$title."', '".$name."', ".$value.", '".$type."', '".$module."', ".$width.', '.$info.')'.'<br><br>';
				$r = run_sql('REPLACE INTO '.$this->tables['config']->table.' (`fieldset`, `title`, `name`, `value`, `type`, `module`, `classField`, `widthField`, `info`, `advElement`) VALUES ('.$fsID.', \''.$title."', '".$name."', ".$value.", '".$type."', '".$module."', ".$class.', '.$width.', '.$info.', '.$advElement.')');
				if ($r)
					return 1;
			}
		}
		return 0;
	}

	function getFieldsetID($fieldset, $module) { //-----------------------------------------------------
		return retr_sql('SELECT `id` FROM '.$this->tables['config']->table.' WHERE `type`=\'FIELDSET\' AND `module`=\''.$module.'\' AND `name`=\''.$fieldset.'\'');
	}

	function existModule($name) { //---------------------------------------
		$r = retr_sql('SELECT count(`id`) FROM '.$this->tables['config']->table.' WHERE `module`=\''.$name.'\'');
		return $r;
	}

	function addModule($name) { //---------------------------------------

		$arr = retr_sql('DESCRIBE '.$this->tables['config']->table.' `module`');

		if (!strpos($arr['Type'], $name)) {
			$arr = preg_match('/^enum\((.+)\)$/', $arr['Type'], $match);
			$arr = $match[1] ? ($match[1].",'$name'") : "'$name'";
			$r = run_sql("ALTER TABLE ".$this->tables['config']->table." CHANGE `module` `module` ENUM($arr) CHARSET utf8 COLLATE utf8_general_ci NULL");

			if ($r)
				return 1;
		}

		return 0;
	}

	function addVariable($data) { //---------------------------------------

		if (($data['type']=='S' or $data['type']=='R') and strpos($data['value'], '|')) {

			$arr = explode("\n", $data['value']);
			$data['value'] = array();
			foreach ($arr as $option) {
				$arr_option = explode('|', $option);

				$item['title'] = $arr_option[0];
				$item['name'] = $arr_option[1];
				$item['val'] = $arr_option[2];
				$data['value'][] = $item;
			}
//				ea($data['value']);
		}

		if (empty($data['title'])) {
			$this->msg->setHighlight('title');
			$this->msg->setError('Введите title');
		}

		if (empty($data['name'])) {
			$this->msg->setHighlight('name');
			$this->msg->setError('Введите name');
		}

		if (empty($data['value'])) {
			$this->msg->setHighlight('value');
			$this->msg->setError('Введите value');
		}

		if (empty($data['namemodule'])) {
			$this->msg->setHighlight('namemodule');
			$this->msg->setError('Введите namemodule');
		}

		if (empty($data['fieldset'])) {
			$this->msg->setHighlight('fieldset');
			$this->msg->setError('Введите fieldset');
		}

		if (!$this->msg->keep) {
			switch ($this->setVariable($data['name'], $data['value'], $data['namemodule'], $data['fieldset'], $data['type'], $data['title'])) {
				case 1: $this->msg->setOk('Переменная добавлена'); break;
				case 2: $this->msg->setInfo('Переменная обновлена'); break;
				case 0: $this->msg->setError('Ошибка при сохранении'); break;
			}
		}
	}

	function install() { //---------------------------------------
		global $dbase;

		$r = retr_sql("SHOW TABLE STATUS FROM ".$dbase." LIKE '".$this->tables['config']->tablename."'");

		if (!$r) {
			$r = run_sql('CREATE TABLE '.$this->tables['config']->table." (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`module` enum('seagullgallery') DEFAULT NULL,
				`fieldset` int(10) unsigned DEFAULT NULL,
				`name` varchar(64) NOT NULL,
				`value` text,
				`type` enum('T','N','R','C','S','TA','F','P','FIELDSET') NOT NULL DEFAULT 'T',
				`title` varchar(255) NOT NULL,
				`classField` varchar(255) DEFAULT NULL,
				`widthField` varchar(255) DEFAULT NULL,
				`info` text,
				`advElement` text,
				PRIMARY KEY (`id`)
				) ENGINE=MYISAM DEFAULT CHARSET=utf8");
			if ($r)	$this->msg->setOk('Таблица "'.$this->tables['config']->tablename.'" создана');
			else	$this->msg->setError('Таблица "'.$this->tables['config']->tablename.'" не создана (ошибка #'.mysql_errno().' '.mysql_error().')');
		} else
			$this->msg->setWarning('Таблица "'.$this->tables['config']->tablename.'" уже создана');

		if (!$this->msg->keep_error)
			return 1;

		return 0;
	}
}
?>
