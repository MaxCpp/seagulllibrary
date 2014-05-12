<?
/*	Class CSeagullMessage 0.0.4
	Update 0.0.4: 2012-10-15
	Update 0.0.3: 2012-09-19
	Update 0.0.2: 2012-03-19
*/
class CMessage {
	var $arr_error = array();
	var $arr_info = array();
	var $arr_warning = array();
	var $arr_ok = array();
	var $arr_highlight = array();
	var $response = array();
	var $keep = 0;
	var $refresh = 0;
	var $reload = false;
	var $animated = 0;

//	----------------------------------------------
	function __construct() {
	}

//	----------------------------------------------
//	Указывает какую ошибку и как показывать. Первый параметр может быть как числом (id сообщения из глобального массива $this->info[]), либо строкой (содержащая текст сообщения). Второй параметр равен 0 - сразу показывать, 1 - показывать после refresh'а.
	function setError($p_error, $p_refresh=0) {
		global $_lang;
//ea($_lang);
//var_dump(isset($_lang[$p_error]));
		if (isset($_lang[$p_error])) {
//			if (!array_key_exists($p_error, $this->arr_error))
//				$this->arr_error[$p_error] = $_lang[$p_error];
			$this->arr_error[] = $_lang[$p_error];
		}
		else {
			if (!in_array($p_error, $this->arr_error))
				$this->arr_error[] = $p_error;
		}
		$this->refresh = $p_refresh;
		$this->keep = 1;
		$this->keep_error = 1;
	}

//	Указывает какое сообщение и как показывать. Первый параметр может быть как числом (id сообщения из глобального массива $this->info[]), либо строкой (содержащая текст сообщения). Второй параметр равен 0 - сразу показывать, 1 - показывать после refresh'а. Третий параметр равен 1 - анимированное соощение (прячется через некоторое время), 0 - не аннимированное.
	function setOk($p_msg, $p_refresh=0, $p_animated=1) { //----------------------------------------------
		global $_lang;

		if (isset($_lang[$p_msg])) {
			$this->arr_ok[] = $_lang[$p_msg];
		}
		else {
			$this->arr_ok[] = $p_msg;
		}
		$this->refresh = $p_refresh;
		$this->keep = 1;
		$this->keep_ok = 1;
		$this->animated = $p_animated;
	}

//	Указывает какое сообщение и как показывать. Первый параметр может быть как числом (id сообщения из глобального массива $this->info[]), либо строкой (содержащая текст сообщения). Второй параметр равен 0 - сразу показывать, 1 - показывать после refresh'а. Третий параметр равен 1 - анимированное соощение (прячется через некоторое время), 0 - не аннимированное.
	function setInfo($p_msg, $p_refresh=0, $p_animated=1) { //----------------------------------------------
		global $_lang;

		if (isset($_lang[$p_msg])) {
			$this->arr_info[] = $_lang[$p_msg];
		}
		else {
			$this->arr_info[] = $p_msg;
		}
		$this->refresh = $p_refresh;
		$this->keep = 1;
		$this->keep_info = 1;
		$this->animated = $p_animated;
	}

//	Указывает какое сообщение и как показывать. Первый параметр может быть как числом (id сообщения из глобального массива $this->info[]), либо строкой (содержащая текст сообщения). Второй параметр равен 0 - сразу показывать, 1 - показывать после refresh'а. Третий параметр равен 1 - анимированное соощение (прячется через некоторое время), 0 - не аннимированное.
	function setWarning($p_msg, $p_refresh=0, $p_animated=1) { //----------------------------------------------
		global $_lang;

		if (isset($_lang[$p_msg])) {
			$this->arr_warning[] = $_lang[$p_msg];
		}
		else {
			$this->arr_warning[] = $p_msg;
		}
		$this->refresh = $p_refresh;
		$this->keep = 1;
		$this->keep_warning = 1;
		$this->animated = $p_animated;
	}

	function setHighlight($input, $msg='') { //----------------------------------------------
		$this->arr_highlight[$input] = $msg;
	}

	function setVar($var, $val='') {
		$this->response[$var] = $val;
	}

//	Указывает какое сообщение и как показывать. Первый параметр может быть как числом (id сообщения из глобального массива $this->info[]), либо строкой (содержащая текст сообщения). Второй параметр равен 0 - сразу показывать, 1 - показывать после refresh'а. Третий параметр равен 1 - анимированное соощение (прячется через некоторое время), 0 - не аннимированное.
	function setReload($url=NULL) { //----------------------------------------------
		if (isset($url))
			$this->reload = $url;
		else
			$this->reload = true;
	}

//	Указывает какое сообщение и как показывать. Первый параметр может быть как числом (id сообщения из глобального массива $this->info[]), либо строкой (содержащая текст сообщения). Второй параметр равен 0 - сразу показывать, 1 - показывать после refresh'а. Третий параметр равен 1 - анимированное соощение (прячется через некоторое время), 0 - не аннимированное.
	function setRefresh() { //----------------------------------------------
		$this->refresh = 1;
	}

	function getError() { //----------------------------------------------
		return $this->arr_error;
	}

	function getOk() { //----------------------------------------------
		return $this->arr_ok;
	}

	function getInfo() { //----------------------------------------------
		return $this->arr_info;
	}

	function getWarning() { //----------------------------------------------
		return $this->arr_warning;
	}

	function getHighlight() { //----------------------------------------------
		return $this->arr_highlight;
	}

	function getType() { //	----------------------------------------------
		if (count($this->arr_error))
			return 'error';
		elseif (count($this->arr_ok))
			return 'ok';
		elseif (count($this->arr_info))
			return 'info';
		elseif (count($this->arr_warning))
			return 'warning';
		return '';
	}

	function get() { //	----------------------------------------------

		$response = $this->response;
		$response['msgType'] = $this->getType();

		if (count($this->arr_error))
			$response['error'] = $this->arr_error;

		if (count($this->arr_ok))
			$response['ok'] = $this->arr_ok;

		if (count($this->arr_info))
			$response['info'] = $this->arr_info;

		if (count($this->arr_warning))
			$response['warning'] = $this->arr_warning;
		
		if (count($this->arr_highlight))
			$response['highlight'] = $this->arr_highlight;

		if ($this->reload)
			$response['reload'] = $this->reload;

		return $response;
	}

	function renderArr($arr) {
		$output = '';
		$c = count($arr);

		if ($c == 1) {
			$output = $arr[0];
		}
		elseif ($c > 1) {
			for ($i=0; $i<$arr; $i++) {
				$output .= '<li>'.$arr[$i].'</li>';
			}
			$output = '<ul>'.$output.'</ul>';
		}

		return $output;
	}

	function render($redirect='') { //----------------------------------------------
		global $_SESSION, $_GET;

		$output = '';
		$str = '';

//ea($_SESSION);
		if ($this->refresh) {
			if (session_id() == '')
				session_start();
			$_SESSION['msg'] = ea($this);
			header("HTTP/1.1 301 Moved Permanently");
			if ($redirect)
				header('Location: '.$redirect);
			else
				header('Location: '.$_SERVER['HTTP_REFERER']);
		}

		if ($output = $this->renderArr($this->arr_error))
			return $output;
		elseif ($output = $this->renderArr($this->arr_warning))
			return $output;
		elseif ($output = $this->renderArr($this->arr_ok))
			return $output;
		elseif ($output = $this->renderArr($this->arr_info)) {
			return $output;
		}

		return $output;
	}

	function renderAll() {
		$output = '';

		foreach ($this->arr_error as $item) {
			$output .= '<li style="color:#800">'.$item.'</li>';
		}
		foreach ($this->arr_warning as $item) {
			$output .= '<li style="color:#AA0">'.$item.'</li>';
		}
		foreach ($this->arr_ok as $item) {
			$output .= '<li style="color:#080">'.$item.'</li>';
		}
		foreach ($this->arr_info as $item) {
			$output .= '<li style="color:#00A">'.$item.'</li>';
		}

		$output = '<ul>'.$output.'</ul>';

		return $output;
	}

	function check_session() { //----------------------------------------------
		global $_SESSION, $tpl;
//ea($_SESSION);
		if (isset($_SESSION['msg_animated'])) {
			$tpl->assign('msg_animated', $_SESSION['msg_animated']);
			$this->animated = $_SESSION['msg_animated'];
			if (!headers_sent()) {
				unset($_SESSION['msg_animated']);
			}
		}

		if (isset($_SESSION['error_refresh']) and !empty($_SESSION['error_refresh'])) {
			$this->setError($_SESSION['error_refresh']);
			if (!headers_sent())
				unset($_SESSION['error_refresh']);
//			$this->error_hold = 1;
//			$this->hold = 1;
			$tpl->assign('msg_refresh', 1);
		}
		elseif (isset($_SESSION['info_refresh']) and !empty($_SESSION['info_refresh'])) {
			$this->setInfo($_SESSION['info_refresh']);
			if (!headers_sent())
				unset($_SESSION['info_refresh']);
//			$this->info_hold = 1;
//			$this->hold = 1;
			$tpl->assign('msg_refresh', 1);
		}

//add_log(print_r($_SESSION, 1), "msg.log");
//ea($_SESSION);

	}

}

$msg = new CMessage();
?>