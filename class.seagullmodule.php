<?php
/*
	Class CSeagullModule 0.0.4
	Update 0.0.4: 2015-01-10
	 - $this::$nameModule в $this::nameModule
	Update 0.0.3: 2013-01-26
	Update 0.0.2: 2012-11-22
*/

if(!defined('SITE_ROOT'))
	define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once(SITE_ROOT.'/assets/modules/seagulllibrary/class.edittable.php');
require_once(SITE_ROOT.'/assets/modules/seagulllibrary/class.config.php');
require_once(SITE_ROOT.'/assets/modules/seagulllibrary/class.message.php');

class CSeagullModule {
	var $modx = null;
	var $ph = array();
	var $theme = '';
	var $tables = array();

	function renderPaginator($page=NULL) { //------------------------------------------------------
		$output = '<div class="paginator">';
		$output .= $this->renderPaginatorLinks($page);
		$output .= '</div>';

		return $output;
	}

	function renderPaginatorLinks($page=NULL) { //------------------------------------------------------
		$cfg['tableName'] = $this->tablename;
		$cfg['limit'] = $this->config->paginatorFrontend->rowsByPage;
		$cfg['advLinks'] = $this->config->paginatorFrontend->advLinks;
		$output = paginator($page, $cfg);

		return '<span class="paginator-loading" style="display:none">Загрузка...</span>'.$output;
	}

	function del($id) { //------------------------------------------------------

		if (isset($id)) {
			$r = run_sql("DELETE ".$this->tables[($this->nameModule ? $this->nameModule : $this::nameModule)]." WHERE `id`=".$id);
			if ($r)
				return 1;
			else
				return 0;
		}
	}

	function getTheme() { //------------------------------------------------------
		if (isset($this->modx)) {
			$theme = $this->modx->db->select('setting_value', $this->modx->getFullTableName('system_settings'), 'setting_name=\'manager_theme\'', '');
			if ($this->modx->db->getRecordCount($theme)) {
				$theme = $this->modx->db->getRow($theme);
				$this->theme = ($theme['setting_value'] <> '') ? '/' . $theme['setting_value'] : '';
				return $this->theme;
			}
		}
		return '';
	}

	function getTpl($tpl) {
		global $modx;

		$template = '';
		if (isset($this->modx) and $this->modx->getChunk($tpl) != '') {
			$template = $this->modx->getChunk($tpl);
		}
/*		else if(substr($tpl, 0, 6) == '@FILE:') {
			$template = $this->file_get_contents(substr($tpl, 6));
		}*/
		else {
			$tpl = SITE_ROOT.'/assets/modules/'.($this->nameModule ? $this->nameModule : $this::nameModule).'/templates/'.$tpl.'.html';
			if (file_exists($tpl)) {
				$template = file_get_contents($tpl);
			}
		}
		return $template;
	}

	function parseTemplate($tpl, $values = array(), $arrTpl = array()) { //--------------------------------
		$arrTpl = array('css', 'js', 'addvariable');

		$file = SITE_ROOT.'/assets/modules/'.($this->nameModule ? $this->nameModule : $this::nameModule).'/templates/'.$tpl.'.html';

		if (file_exists($file)) {
			$tpl = file_get_contents($file);

			if ($tpl) {
				foreach ($arrTpl as $item) {
					$file = SITE_ROOT.'/assets/modules/seagulllibrary/tpl/'.$item.'.html';
					if (file_exists($file)) {
						$values[$item] = file_get_contents($file);
						foreach ($values as $key => $value) {
							$values[$item] = str_replace('[+'.$key.'+]', $value, $values[$item]);
						}
					}
					else echo 'Нет файла-шаблона: ',$file;
				}
				if ($values) {
					if (empty($values['theme']))
						$values['theme'] = $this->getTheme();

					foreach ($values as $key => $value) {
						$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
					}
				}
				$tpl = preg_replace('/(\[\+.*?\+\])/' ,'', $tpl);
				return $tpl;
			} else {
				return '';
			}
		}
		else
			echo 'Нет файла-шаблона: ',$file;
	}

	function parseContent($content, $values = array(), $beginTag='[+', $endTag='+]') { //--------------------------------
		if ($values) {
			foreach ($values as $key => $value) {
				$content = str_replace($beginTag.$key.$endTag, $value, $content);
			}
		}
		$content = preg_replace('/(\[\+.*?\+\])/' ,'', $content);
		return $content;
	}
}
?>
