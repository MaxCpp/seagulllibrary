<?php
/*	Seagull Library 0.0.9
	Update 0.0.9: 2015-11-20
	Update 0.0.8: 2013-10-01
	Update 0.0.7: 2013-10-01
	Update 0.0.6: 2013-10-01
	Update 0.0.5: 2013-09-28
	Update 0.0.4: 2013-09-25
	Update 0.0.3: 2013-08-28
	Update 0.0.2: 2013-08-05
	Update 0.0.1: 2013-07-28
*/
//date_default_timezone_get();
//date_default_timezone_set('Asia/Vladivostok');
define('MYSQL_ERROR_SHOW', true);
define('MYSQL_ERROR_LOG', false);
define('CLEAR_FILES', 1);
define('CLEAR_DIR', 2);
define('CLEAR_ALL', 3);

// Соединение с БД --------------------------------------------
function db_connect ($hostname, $username, $password ) {
	return  mysql_connect($hostname, $username, $password);
}

function db_select ($database, $connect) { // Выбор базы данных --------------------------------------------
	global $config;

	$_sql_queries_num = 0; $_sql_queries_time = 0;

	$db = mysql_select_db($database, $connect);

	if (!$db) return $db;

	mysql_query ('SET NAMES utf8');
	mysql_query ('SET CHARACTER SET utf8');
/*	mysql_query ("SET SESSION collation_connection = 'utf8_general_ci'");
	mysql_query ("SET character_set_client = utf8;");
	mysql_query ("SET character_set_results = utf8;");
	mysql_query ("SET character_set_connection = utf8;");
*/
	return $db;
}

function run_sql ($sql_query) { //--------------------------------------------
	global $config, $mysql_count, $mysql_querys;

//	add_log($sql_query, 'mysql_query.log');
	$query_result = mysql_query($sql_query);
//	echo $sql_query,'<br>';
	if ($query_result == 0) {
		if (MYSQL_ERROR_SHOW)
			echo('<span style="color:#00A">Ошибка MySQL (#'.mysql_error().'): '.$sql_query.'</span>');
		if (MYSQL_ERROR_LOG)
			add_log(mysql_error(), 'mysql_error.log');
//		echo 'Ошибка базы данных при выполнении запроса. Смотрите файл "mysql_error.log"';
	}
	elseif ($config->mysql_debug) {
		$mysql_count++;
		$mysql_querys .= '<br/>'.$sql_query;
	}

	return $query_result;
}

function sql2table ($sql_query) { //--------------------------------------------

	$query_result = run_sql($sql_query);
	$query_table = array();

	if ($query_result) {
		if (mysql_numrows($query_result) > 0) {
			while ($query_row = mysql_fetch_array($query_result, MYSQL_ASSOC)) {
				$query_table[] = $query_row;
			}
		}

		mysql_free_result ($query_result);
	}


	return $query_table;
}

function sql2array ($sql_query, $key='id', $value=NULL) { //--------------------------------------------

	$query_result = run_sql($sql_query);
	$query_table = array();

	if ($query_result and mysql_numrows($query_result) > 0) {
		if (isset($value)) {
			while ($query_row = mysql_fetch_array($query_result, MYSQL_ASSOC)) {
				$query_table[$query_row[$key]] = $query_row[$value];
			}
		}
		else {
			while ($query_row = mysql_fetch_array($query_result, MYSQL_ASSOC)) {
				$temp_id = $query_row[$key];
				unset($query_row[$key]);
				$query_table[$temp_id] = $query_row;
			}
		}

		mysql_free_result ($query_result);
	}


	return $query_table;
}

function sql2list ($sql_query, $key='name', $value='value') { //--------------------------------------------

	$query_result = run_sql($sql_query, $sql_connect);
	$query_table = array();

	if (mysql_numrows($query_result) > 0) {
		while ($query_row = mysql_fetch_array($query_result, MYSQL_ASSOC)) {
			$temp_id = $query_row[$key];
			$query_table[$temp_id] = $query_row[$value];
		}
	}

	mysql_free_result ($query_result);

	return $query_table;
}

function retr_sql ($sql_query, $result_type=MYSQL_ASSOC, $always_arr=false) { //--------------------------------------------

	$query_result = run_sql($sql_query);
/*	if (!$query_result)
		{
		echo '<pre>';
		print_r(debug_print_backtrace());
		print_r(debug_backtrace());
		echo '</pre>';
		exit;
		}*/
	if ($query_result) {
		$query_array = mysql_fetch_array($query_result, $result_type);
		mysql_free_result ($query_result);

//		if (!$always_arr and strlen($query_array)>0 and count($query_array)==1) {
		if (!$always_arr and $query_array!==false and count($query_array)===1) {
			$query_array = current($query_array);
		}
	}

	return $query_array;
}

function sendEmail($mailto='', $subject='', $body='', $MIME='html', $from='') { //------------------------------------------------------
//	Отправка на почту:
//	mail.ru - Content-Type: text/plain charset=utf-8
//	gmail.com - без кодировки
	switch ($MIME) {
		case 'attach': $MIME='multipart/mixed'; break; // для отправки письма с вложениями (тут описано как http://phpclub.ru/detail/article/mail)
		case 'text': $MIME='text/plain'; break;
		case 'html':
		default: $MIME='text/html'; break;
	}

	$subject = empty($subject) ? 'Тестовое письмо' : $subject;
	if (is_array($body)) {
		$body = arr2tableHTML($body);
	}

	if (strpos($mailto, ',')) {
		$arr = explode(',', $mailto);

		foreach ($arr as $email) {
			if (strpos($email, 'gmail.com'))
				$arr_gmail[] = $email;
			else
				$arr_mail[] = $email;
		}
	}
	else {
		$arr_mail = $mailto;
	}

//	Mail.ru
	if ($arr_mail) {
		if (is_array($arr_mail))
			$arr_mail = implode(',', $arr_mail);
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: $MIME; charset=utf-8 \r\n";
		$headers .= 'From: '.$from;
		$send_mail = mail($arr_mail, $subject, $body, $headers);
	}

//	Gmail.com
	if (sizeof($arr_gmail)) {
		$arr_gmail = implode(',', $arr_gmail);
		$headers = "MIME-Version: 1.0\r\n";
		if ($MIME!='text/plain')
			$headers .= "Content-Type: $MIME; charset=utf-8 \r\n";
		$headers .= 'From: '.$from;
		$send_mail = mail($arr_gmail, $subject, $body, $headers);
	}
	return $send_mail;
}

/* Масштабирование изображения
*
* Функция работает с PNG, GIF и JPEG изображениями.
* Масштабирование возможно как с указаниями одной стороны, так и двух, в процентах или пикселях.
*
* @param string Расположение исходного файла
* @param string Расположение конечного файла
* @param integer Ширина конечного файла
* @param integer Высота конечного файла
* @param bool Размеры даны в пискелях или в процентах
* @return bool
*/
function resize($file_input, $file_output, $w_o, $h_o, $percent = false) {
	list($w_i, $h_i, $type) = getimagesize($file_input);
	if (!$w_i || !$h_i) {
		echo 'Невозможно получить длину и ширину изображения';
		return;
    }
    $types = array('','gif','jpeg','png');
    $ext = $types[$type];
    if ($ext) {
    	$func = 'imagecreatefrom'.$ext;
    	$img = $func($file_input);
    } else {
    	echo 'Некорректный формат файла';
		return;
    }
	if ($percent) {
		$w_o *= $w_i / 100;
		$h_o *= $h_i / 100;
	}
	if (!$h_o) $h_o = $w_o/($w_i/$h_i);
	if (!$w_o) $w_o = $h_o/($h_i/$w_i);
	$img_o = imagecreatetruecolor($w_o, $h_o);
	imagecopyresampled($img_o, $img, 0, 0, 0, 0, $w_o, $h_o, $w_i, $h_i);
	if ($type == 2) {
		return imagejpeg($img_o,$file_output,100);
	} else {
		$func = 'image'.$ext;
		return $func($img_o,$file_output);
	}
}

/* Обрезка изображения
*
* Функция работает с PNG, GIF и JPEG изображениями.
* Обрезка идёт как с указанием абсоютной длины, так и относительной (отрицательной).
*
* @param string Расположение исходного файла
* @param string Расположение конечного файла
* @param array Координаты обрезки
* @param bool Размеры даны в пискелях или в процентах
* @return bool
*/
function crop($file_input, $file_output, $crop = 'square',$percent = false) {
	list($w_i, $h_i, $type) = getimagesize($file_input);
	if (!$w_i || !$h_i) {
		echo 'Невозможно получить длину и ширину изображения';
		return;
    }
    $types = array('','gif','jpeg','png');
    $ext = $types[$type];
    if ($ext) {
    	$func = 'imagecreatefrom'.$ext;
    	$img = $func($file_input);
    } else {
    	echo 'Некорректный формат файла';
		return;
    }
	if ($crop == 'square') {
		$min = $w_i;
		if ($w_i > $h_i) $min = $h_i;
		$w_o = $h_o = $min;
	} else {
		list($x_o, $y_o, $w_o, $h_o) = $crop;
		if ($percent) {
			$w_o *= $w_i / 100;
			$h_o *= $h_i / 100;
			$x_o *= $w_i / 100;
			$y_o *= $h_i / 100;
		}
    	if ($w_o < 0) $w_o += $w_i;
	    $w_o -= $x_o;
	   	if ($h_o < 0) $h_o += $h_i;
		$h_o -= $y_o;
	}
	$img_o = imagecreatetruecolor($w_o, $h_o);
	imagecopy($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o);
	if ($type == 2) {
		return imagejpeg($img_o,$file_output,100);
	} else {
		$func = 'image'.$ext;
		return $func($img_o,$file_output);
	}
}

function requireFiles($dir, $ext='php') { //--------------------------------------------
	$arr = scandir($dir);
	foreach ($arr as $file) {
		$file = $dir.'/'.$file;
		if (is_file($file) and substr(strrchr($file, '.'), 1)===$ext) {
			add_log('file:'.$file, 'active_gallery.log');
			include_once($file);
		}
	}
}

//	Очищает директорию от файлов и папок
function cleardir($directory, $del=CLEAR_ALL) {
	if (file_exists($directory)) {
		$dir = opendir($directory);
		while(($file = readdir($dir))) {
			if ( is_file($directory.'/'.$file) and $del!=CLEAR_DIR) {
				unlink($directory.'/'.$file);
			}
			else if ((is_dir($directory.'/'.$file) && ($file != '.') && ($file != '..')) and $del!=CLEAR_FILES) {
				removedir($directory.'/'.$file);
			}
		}
		closedir($dir);
	}
	return TRUE;
}

function removedir($path) {
	return is_file($path) ? @unlink($path) : array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}

function format_money($money) { //--------------------------------------------

	$l = strlen($money);
	$str = '';
	$i = $l;
	while ($i > 0) {
		$i = $i - 3;
		if ($i < 0) {
			$c = $i+3;
			$i = 0;
		}
		else {
			$c = 3;
		}
		$str = substr($money, $i, $c).' '.$str;
	}

	return $str;
}

function requestXML ($array_data, $url, $type='p') { //--------------------------------------------

//	Конвертор в XML ----------------------------------------------------------
	if ($type=='m')
		$type = 'merchant';
	elseif ($type=='p')
		$type = 'processing';

	$xml = "<?xml version=\"1.0\"?>";
	$xml .= "<".$type.".request>";
	foreach ($array_data as $key => $item) {
		$xml .= "<$key>$item</$key>";
	}
	$xml .= "</".$type.".request>";

//echo $xml;
//	Подготовка CURL
	$ch = curl_init();
//	curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
//	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
//	curl_setopt($ch, CURLOPT_VERBOSE, 1);
//	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);

//	Парсинг полученого XML ---------------------------------------------------
	if ($data) {
		$vals = _xml2array($data, 'windows-1251');
	}
	else
		$error_count++;
	curl_close($ch);

	return($vals);
}

function ea($mas, $inline=false) { //--------------------------------------------
	if ($inline) {
		return print_r($mas, true);
	}
	else {
		echo '<pre>';
		print_r($mas);
		echo '</pre>';
	}
}

function add_log($str, $filelog = 'log.log') { //--------------------------------------------
	$log = fopen($filelog, 'a');
	$date = date('Y.m.d H:i:s');
	fwrite($log, $date.': '.$str."\r\n");
	fclose($log);
}

function arr2tableHTML($arr) { //--------------------------------------------
	$output = '';

	foreach ($arr as $key=>$row) {
		$output .= '<tr><td>'.$key.'</td><td>'.$row.'</td></tr>';
	}
	return '<table>'.$output.'</table>';
}

function checkCharset($str) { //--------------------------------------------
	$tab = array('UTF-8', 'ASCII', 'Windows-1252', 'koir-8');
//	$tab = array("UTF-8", "ASCII", "Windows-1252", "ISO-8859-15", "ISO-8859-1", "ISO-8859-6", "CP1256", "koir-8");
	$chain = '';
	foreach ($tab as $i) {
		foreach ($tab as $j) {
			$chain .= "$i->$j:".iconv($i, $j, $str).'<br>';
		}
	}
	return $chain;
}

function add_file($str, $filelog = "test.txt") { //--------------------------------------------
	$f = fopen($filelog, 'w');
	fwrite($f, $str);
	fclose($f);
}

function set_location($url, $referer='') { //--------------------------------------------
	global $site;

	if (!empty($referer)) {
		$_SESSION['referer'] = $referer;
//		$_SESSION[$type] = true;
	}

	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$url);
//	exit();
}

function check_text($text, $spam=false) { //--------------------------------------------

	$text = trim($text);

	if (isset($text) and !empty($text)) {
		if (preg_match("/^[\W\w]+$/", $text)) {
			if ($spam) {
//				if (preg_match("/http:\/\/|\[url=|\[link=|<a /", $text))
				if (preg_match("/\[url=|\[link=|<a /", $text))
					return 3;
			}
			return 0;
		}
		else
			return 2;
	}
	return 1;
}

function check_number($num, $type='i') { //--------------------------------------------

	if (isset($num) and !empty($num)) {
		$num = trim($num);
		$regexp = ($type==='i') ? "/^[\d]+$/" : "/^[\d]+[.,]?[\d]*$/";

		if (preg_match($regexp, $num))
			return 0;
		else
			return 2;	// не соответствует формату
	}
	return 1;	// поле пустое
}

function check_email($email) { //--------------------------------------------
	global $msg;

	if (isset($email) and !empty($email)) {
		if (preg_match("/^[\w-]+(\.[\w-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)*?\.[a-z]{2,6}|(\d{1,3}\.){3}\d{1,3})(:\d{4})?$/", $email))
			return 0;
		else
			return 2;
	}
	return 1;
}

function check_password($pass, $error_on=true) { //--------------------------------------------
	global $msg;

	if (isset($pass) and !empty($pass)) {
		if (preg_match("/^([A-Za-z0-9]){6,14}$/", $pass))
			return 1;
		elseif ($error_on)
			$msg->setError('Пароль не соответствует формату');
	}
	elseif ($error_on)
		$msg->setError('Введите пароль');

	return 0;
}

function check_phone($phone, $with_null=0) { //--------------------------------------------
	if (isset($phone) and !empty($phone)) {
		if (preg_match("/^[0-9()+ -]+$/", $phone))
			return 0;
		else
			return 2;
	}
	return 1;
}

function check_date($date, $format=true) { //--------------------------------------------
	if (isset($date) and !empty($date)) {
		if (preg_match("/^[-.0-9\/ ]+$/", $date))
			return 0;
		else
			return 2;
	}
	return 1;
}

function check_year($year) { //--------------------------------------------
	if (isset($year) and !empty($year)) {
		if (preg_match("/^[0-9]{1,4}$/", $year))
			return 0;
		else
			return 2;
	}
	return 1;
}

function check_datetime($datetime) { //--------------------------------------------
	if (isset($datetime) and !empty($datetime)) {
		if (preg_match("/^[-:.0-9\/ ]+$/", $datetime))
			return 0;
		else
			return 2;
	}
	return 1;
}

function check_time($time) { //--------------------------------------------
	global $msg;

	if (isset($time) and !empty($time)) {
		if (preg_match("/^[:.0-9]+$/", $time))
			return 0;
		else
			return 2;
	}
	return 1;
}

function check_spam($spam) { //--------------------------------------------
	global $msg;

	if (isset($spam) and !empty($spam)) {
		if (preg_match("/http:\/\/|\[url=|\[link=|<a /", $spam))
			return 0;
		else
			return 2;
	}
	return 1;
}

function translit($str) { //--------------------------------------------
	static $LettersFrom = 'абвгдезиклмнопрстуфыэйхёьъАБВГДЕЗИКЛМНОПРСТУФЫЭЙХЁЬЪ';
	static $LettersTo   = "abvgdeziklmnoprstufyejxe''ABVGDEZIKLMNOPRSTUFYEJXE''";
	static $BiLetters = array(
		'ж' => 'zh', 'ц'=>'ts', 'ч' => 'ch',
		'ш' => 'sh', 'щ' => 'sch', 'ю' => 'ju', 'я' => 'ja',
		'Ж' => 'Zh', 'Ц'=>'Ts', 'Ч' => 'Ch',
		'Ш' => 'Sh', 'Щ' => 'Sch', 'Ю' => 'Ju', 'Я' => 'Ja'
	);

	$str = strtr($str, $LettersFrom, $LettersTo);
	$str = strtr($str, $BiLetters );
	return $str;
/*
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => "'",  'ы' => 'y',   'ъ' => "'",
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => "'",  'Ы' => 'Y',   'Ъ' => "'",
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($str, $converter);
*/
}

function translit2URL($str) { //--------------------------------------------

    $converter = array(
		'а' => 'a',		'б' => 'b',		'в' => 'v',
		'г' => 'g',		'д' => 'd',		'е' => 'e',
		'ё' => 'yo',	'ж' => 'zh',	'з' => 'z',
		'и' => 'i',		'й' => 'j',		'к' => 'k',
		'л' => 'l',		'м' => 'm',		'н' => 'n',
		'о' => 'o',		'п' => 'p',		'р' => 'r',
		'с' => 's',		'т' => 't',		'у' => 'u',
		'ф' => 'f',		'х' => 'h',		'ц' => 'c',
		'ч' => 'ch',	'ш' => 'sh',	'щ' => 'shh',
		'ь' => '',		'ы' => 'y',		'ъ' => '',
		'э' => 'e',		'ю' => 'yu',	'я' => 'ya',

/*		'А' => 'a',		'Б' => 'b',		'В' => 'v',
		'Г' => 'g',		'Д' => 'd',		'Е' => 'e',
		'Ё' => 'e',		'Ж' => 'zh',	'З' => 'z',
		'И' => 'i',		'Й' => 'y',		'К' => 'k',
		'Л' => 'l',		'М' => 'm',		'Н' => 'n',
		'О' => 'o',		'П' => 'p',		'Р' => 'r',
		'С' => 's',		'Т' => 't',		'У' => 'u',
		'Ф' => 'f',		'Х' => 'h',		'Ц' => 'c',
		'Ч' => 'ch',	'Ш' => 'sh',	'Щ' => 'sch',
		'Ь' => '',		'Ы' => 'y',		'Ъ' => '',
		'Э' => 'e',		'Ю' => 'yu',	'Я' => 'ya',
*/
		' '=> '-',		'.'=> '',		'/'=> '_',
		'\\'=> '',		':'=> '',
		'"'=> '',		"'"=> '',		"»"=> '',
		"«"=> ''
    );
    return strtr(mb_strtolower(trim($str), 'UTF-8'), $converter);
/*    $output = str_replace(
        array_keys($table),
        array_values($table),$str
    );

    return $output;
*/
}

function checkUkr($str) { //--------------------------------------------
	$str = preg_replace("/(&#1108;)/", "є", $str);
	$str = preg_replace("/(&#1110;)/", "i", $str);
	$str = preg_replace("/(&#1111;)/", "ї", $str);
	$str = preg_replace("/(&#1028;)/", "Є", $str);
	$str = preg_replace("/(&#1030;)/", "I", $str);
	$str = preg_replace("/(&#1031;)/", "Ї", $str);

	$str = preg_replace('/<([^>]|\n)*>/', "", $str);

	return($str);
}

function checkStr($str) { //--------------------------------------------
	$str = htmlspecialchars($str);
	$str = trim($str);
	return($str);
}

function getIPAddr($addip='') { //--------------------------------------------

	$headers = array(
		'HTTP_REMOTE_ADDR',     'REMOTE_ADDR',
		'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
		'HTTP_SP_HOST',         'HTTP_X_DELEGATE_REMOTE_HOST',
		'HTTP_FROM',            'HTTP_SP_CLIENT',
		'HTTP_CLIENTIP',        'HTTP_CLIENT',
		'HTTP_CLIENT_IP',       'CLIENT_IP'
	);
	$ips = '';
	$headerips = '';
	foreach ($headers as $header)
		if (isset($_SERVER[$header]) AND ($ip = $_SERVER[$header])) {
			$headerips .= ($headerips?', ':'').$header.': '.$ip;
			$ips .= ($ips?', ':'').$ip;
		}
	if ($addip) {
		$headerips .= ($headerips?', ':'').'FROM_LINK'.': '.$addip;
		$ips .= ($ips?', ':'').$addip;
	}
	if (empty($headerips)) $headerips = 'localhost';
	$allip = preg_split("/,\s*/",$ips);
	unset($ips);
	$ip = '';
	$ips = '';
	foreach ($allip as $aip) {
		if (preg_match('/(\d{1,3}).(\d{1,3}).(\d{1,3}).(\d{1,3})/', $aip, $matches) && $aip!='unknown' && $aip!='localhost') {
			if (($matches[1]==192) and ($matches[2]==168)) continue;
			$ip   = $aip;
			if ($matches[1]==127) $ip = $_SERVER['REMOTE_ADDR'];
			if ($matches[1]==10)  $ip = $_SERVER['REMOTE_ADDR'];
		//      if ( $matches[1]=='localhost') $ip = $_SERVER['REMOTE_ADDR'];
		}
	}
	if (empty($ip))
		$ip = $_SERVER['REMOTE_ADDR'];

	//	addons	(last update 2010-01-29) --------
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	return array('ip'=>$ip, 'real_ip'=>$_SERVER['REMOTE_ADDR'], 'headerip'=>$headerips);
}

function _xml2array($xml, $codec='') { //--------------------------------------------
	$p = xml_parser_create('UTF-8');
	xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 1);
	xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 0);
	xml_parse_into_struct($p, $xml, $values, $idx);
	xml_parser_free($p);

	// tracking used keys
	$usedKeys = array();
	$deepLevel = -1;

	// start a php array string (evaluated later)
	$forEvalPrefix = '$xml_array';

	// loop throught the value array
	foreach ($values as $key => $val) {
		$tagName = $val['tag']; // pass the key tag into a more friendly looking variable
		$level = $val['level']; // idem

		if($val['type'] == 'open') {
			$deepLevel++; // increase deep level
			$forEvalPrefix .= '[\''. $tagName .'\']';

			// begin used keys checks to allow multidimensionatity under the same tag
			(isset($usedKeys[$level][$tagName])) ? $usedKeys[$level][$tagName]++ : $usedKeys[$level][$tagName] = 0;
			$forEvalPrefix .= '['. $usedKeys[$level][$tagName] .']';

			if (!empty($val['attributes']) and is_array($val['attributes'])) {
				foreach ($val['attributes'] as $key2 => $item) {
					$item = addslashes($item); // format the value for evaluation as a string
					if (!is_numeric($item) and !empty($codec))
						$item = iconv('UTF-8', $codec, $item);
					$forEvalSuffix = '[\''. $key2 .'\'] = \''. $item .'\';'; // create a string to append to the current prefix
					$forEval = $forEvalPrefix . $forEvalSuffix; // (without "$php_used_prefix"...)
					eval($forEval); // write the string to the array structure
				}
			}
		}

		if($val['type'] == 'complete') {
			($level > $deepLevel) ? $deepLevel++ : ''; // increase $deepLevel only if current level is bigger
			$tagValue = addslashes($val['value'] ); // format the value for evaluation as a string
			if (!is_numeric($tagValue) and !empty($codec))
				$tagValue = iconv('UTF-8', $codec, $tagValue);
			$forEvalSuffix = '[\''. $tagName .'\'] = \''. $tagValue .'\';'; // create a string to append to the current prefix
			$forEval = $forEvalPrefix . $forEvalSuffix; // (without "$php_used_prefix"...)
			eval($forEval); // write the string to the array structure
		}

		if($val['type'] == 'close') {
			unset($usedKeys[$deepLevel]); // Suppress tagname's keys useless
			$deepLevel--;
			$forEvalPrefix = substr($forEvalPrefix, 0, strrpos($forEvalPrefix, '[')); // cut off the used keys node
			$forEvalPrefix = substr($forEvalPrefix, 0, strrpos($forEvalPrefix, '[')); // cut off the end level of the array string prefix
		}
	}

	return $xml_array;
}

function hex2rgb($hex_color) {
	return array_map('hexdec', str_split(str_replace('#', '', $hex_color), 2));
}

function utf2win ($text) { //--------------------------------------------
	global $c1, $c2, $w8;

	$u = false;
	$temp = '';
	for($i=0,$len=strlen($text); $i<$len; $i++) {
		$c = substr($text,$i,1);
		if ($u) {
			$c = $w8[$lc.$c];
			$temp .= isset($c) ? $c : '?';
			$u = false;
		}
		else if ($c==$c1 || $c==$c2) {
			$u = true;
			$lc = $c;
		}
		else
			$temp .= $c;
	}
	return $temp;
}

function session_kill() { //--------------------------------------------
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}

	// Finally, destroy the session.
	@session_destroy();
}

function date2int($str) {
	if ($str) {
		$mas = explode('.', $str);
		$date = mktime(0, 0, 0, $mas[1], $mas[0], $mas[2]);
		$date += (int)substr(date('O', $date),0,3)*3600;
	}
	else
		$date = mktime();

	return($date);
}

function datetime2int($str, $delimiter='.') {
	if ($str) {
		$date = explode(' ', $str);
		$time = explode(':', $date[1]);
		$date = explode($delimiter, $date[0]);
		$date = mktime( $time[0], $time[1], 0, $date[1], $date[0], $date[2]);
	}
	else
		$date = mktime();

	return($date);
}

function date2date($str, $delimiter='.') {
	$months = array(1=>'января', 2=>'февраля', 3=>'марта', 4=>'апреля', 5=>'мая', 6=>'июня', 7=>'июля', 8=>'августа', 9=>'сентября', 10=>'октября', 11=>'ноября', 12=>'декабря');
	if ($str) {
		$date = explode($delimiter, $str);
		$date = $date[0].' '.$months[(int)$date[1]].' '.$date[2];
	}
	return($date);
}

function date2daymonth($str, $delimiter='.') {
	$months = array(1=>'январь', 2=>'февраль', 3=>'март', 4=>'апрель', 5=>'май', 6=>'июнь', 7=>'июль', 8=>'август', 9=>'сентябрь', 10=>'октябрь', 11=>'ноябрь', 12=>'декабрь');
	if ($str) {
		$date = explode($delimiter, $str);
		$date = $date[0].' '.$months[(int)$date[1]];
	}
	return($date);
}

function date2format($str, $format='d m Y', $delimiter='.') {
	$months = array(1=>'январь', 2=>'февраль', 3=>'март', 4=>'апрель', 5=>'май', 6=>'июнь', 7=>'июль', 8=>'август', 9=>'сентябрь', 10=>'октябрь', 11=>'ноябрь', 12=>'декабрь');
	if ($str) {
		$datestr = '';
		$date = explode($delimiter, $str);
		$date = array('d'=>$date[0], 'm'=>$months[(int)$date[1]], 'Y'=>$date[2]);
		$date = strtr($format, $date);
	}
	return($date);
}

function month2str($num) {
	$months = array(1=>'январь', 2=>'февраль', 3=>'март', 4=>'апрель', 5=>'май', 6=>'июнь', 7=>'июль', 8=>'август', 9=>'сентябрь', 10=>'октябрь', 11=>'ноябрь', 12=>'декабрь');
	return $months[(int)$num];
}

function age($day, $month, $year){
	$age = date('Y')-$year;
	if (date('d')<$day && date('m')<=$month) { $age--; }
	return $age;
}

function compliteWord($str, $word1, $word2, $word5='', $wrod11='') {
	$substr = substr($str, -1);
	if ($substr == 1 ) {$term = $word1;}
	if ($substr > 1 ) {$term = $word2;}
	if ($substr > 4 or $substr == 0) {$term = $word5;}
	$substr = substr($str, -2);
	if ($substr == 11 or $substr == 12 or $substr == 13 or $substr == 14) {$term = $word5;}
	return $term;
}

//----------------------------------------------------------------------------
//	Число в словесной форме
function num2str($inn, $stripkop=false) {
	$nol = 'ноль';
	$str[100]= array("", "сто", "двісті", "триста", "чотириста", "п'ятсот", "шістсот", "сімсот", "вісімсот", "дев'ятсот");
	$str[11] = array("", "десять","одинадцять", "дванадцять", "тринадцять", "чотирнадцять", "п'ятнадцять", "шістнадцять", "сімнадцять", "вісімнадцять", "дев'ятнадцять", "двадцять");
	$str[10] = array("", "десять", "двадцять", "тридцять", "сорок", "п'ятдесят", "шістдесят", "сімдесят", "вісімдесят", "дев'яносто");
	$sex = array(
		array("","одна", "дві", "три", "чотири", "п'ять", "шість", "сім", "вісім", "дев'ять"),// m
		array("","один", "два", "три", "чотири", "п'ять", "шість", "сім", "вісім", "дев'ять") // f
	);
	$forms = array(
		array("копійка", "копійки", "копійок", 1), // 10-2
		array("гривня", "гривни", "гривень", 0),
		array("тисяча", "тисячі", "тисяч", 1), // 10 3
		array("мільйон", "мільйона", "мільйонів", 0), // 10 6
		array("мільярд", "мільярда", "мільярдів", 0), // 10 9
		array("трильйон", "трильйона", "трильйонів", 0), // 1012
	);
	$out = $tmp = array();
	// Поехали!
	$tmp = explode('.', str_replace(',','.', $inn));
	$rub = number_format($tmp[0],0,'','-');
	if ($rub==0) $out[] = $nol;
	// нормализация копеек
	$kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT),0,2) : '00';
	$segments = explode('-', $rub);
	$offset = sizeof($segments);
	if ((int)$rub==0) { // если 0 рублей
		$o[] = $nol;
		$o[] = morph(0, $forms[1][0],$forms[1][1],$forms[1][2]);
	}
	else {
		foreach ($segments as $k=>$lev) {
			$sexi= (int) $forms[$offset][3]; // определяем род
			$ri  = (int) $lev; // текущий сегмент
			if ($ri==0 && $offset>1) {// если сегмент==0 & не последний уровень(там Units)
				$offset--;
				continue;
			}
			// нормализация
			$ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
			// получаем циферки для анализа
			$r1 = (int)substr($ri,0,1); //первая цифра
			$r2 = (int)substr($ri,1,1); //вторая
			$r3 = (int)substr($ri,2,1); //третья
			$r22= (int)$r2.$r3; //вторая и третья
			// разгребаем порядки
			if ($ri>99) $o[] = $str[100][$r1]; // Сотни
			if ($r22>20) {// >20
				$o[] = $str[10][$r2];
				$o[] = $sex[ $sexi ][$r3];
			}
			else { // <=20
				if ($r22>9) $o[] = $str[11][$r22-9]; // 10-20
				elseif($r22>0)  $o[] = $sex[ $sexi ][$r3]; // 1-9
			}
			// Рубли
			$o[] = morph($ri, $forms[$offset][0],$forms[$offset][1],$forms[$offset][2]);
			$offset--;
		}
	}
	// Копейки
	if (!$stripkop) {
		$o[] = $kop;
		$o[] = morph($kop,$forms[0][0],$forms[0][1],$forms[0][2]);
	}
	return preg_replace("/\s{2,}/",' ',implode(' ',$o));
}

//----------------------------------------------------------------------------
// Многоуровневый массив в строку
function array2str($array, $nosub = true, $nodigitkeys = true, $indexes = array()) {
  $array_str = 'array (';

  $pos = 0;

  foreach ($array as $key => $value) {

    if (!($nosub && is_array($value)) && !($nodigitkeys && is_numeric($key)) && !($indexes &&
      !in_array($key, $indexes))) {
      $array_str .= ($pos != 0) ? ',':'';

      $array_str .= "'$key' => " . ((is_array($value)) ? array2str($value):"'$value'");

      $pos++;
    }
  }

  $array_str .= ')';

  return $array_str;
}

//----------------------------------------------------------------------------
// Параметры обращения к хосту
function get_remote_params(&$remote_ip, &$remote_host) {
  global $REMOTE_HOST, $REMOTE_ADDR;

  $ip = getenv('REMOTE_HOST');
  if (!$ip) {
    $ip = getenv('REMOTE_ADDR');
  }
  if (!$ip) {
    $ip = $REMOTE_ADDR;
  }
  if (!$ip) {
    $ip = $REMOTE_HOST;
  }

  $host = @GetHostByAddr($ip);

  $remote_ip = $ip;
  $remote_host = $host;
}

//----------------------------------------------------------------------------
// Строка в верхний регистр (для кирилицы)
function str_to_upper($Str) {
  $dictRusBig = "АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $dictRusSml = "абвгдежзийклмнопрстуфхцчшщъыьэюяabcdefghijklmnopqrstuvwxyz";
  return strtr($Str, $dictRusSml, $dictRusBig);
}

//----------------------------------------------------------------------------
// Подстрока от строки без разрыва слов
function substr_word($str, $min_len, $max_len) {
  if (strlen($str) <= $max_len)
    return $str;

  $break = $min_len;

  for ($i = $max_len; $i > $min_len; $i--) {
    if ($str[$i] == ' ' || $str[$i] == "\n") {
      $break = $i;
      break;
    }
  }

  return substr($str, 0, $break) . '...';
}

function substrByWord($text, $symbols = 100) {
	$symbols = (int)$symbols;

	if (strlen( $text) <= $symbols)
		return $text;

	$pos = strpos($text, ' ', $symbols);
	return substr($text, 0, (int)$pos).'...';
}
//----------------------------------------------------------------------------
// Выдача зависимого от врмени уникального значения
function getmicrotime() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

//----------------------------------------------------------------------------
// Преобразование из win1251 в UTF-8
function conv($str) {
  return iconv("windows-1251", "UTF-8", $str);
}

//----------------------------------------------------------------------------
// Преобразование из UTF-8 в win1251
function deconv($str) {
  return iconv("UTF-8", "windows-1251", $str);
}

//----------------------------------------------------------------------------
function php2js($a) {
	if (is_null($a)) return 'null';
	if ($a === false) return 'false';
	if ($a === true) return 'true';
	if (is_scalar($a)) {
		$a = addslashes($a);
		$a = str_replace("\n", '\n', $a);
		$a = str_replace("\r", '\r', $a);
		$a = preg_replace('{(</)(script)}i', '$1"+"$2', $a);
		return '"'.$a.'"';
	}
	$isList = true;
	for ($i=0, reset($a); $i<count($a); $i++, next($a))
		if (key($a) !== $i) { $isList = false; break; }
	$result = array();
	if ($isList) {
		foreach ($a as $v) $result[] = php2js($v);
		return '[' . join(', ', $result) . ']';
	} else {
		foreach ($a as $k=>$v)
			$result[] = php2js($k) . ':' . php2js($v);
		return '{' . join(', ', $result) . '}';
	}
}

/**
 * Склоняем словоформу
 */
function morph($n, $f1, $f2, $f5) {
	$n = abs($n) % 100;
	$n1= $n % 10;
	if ($n>10 && $n<20)	return $f5;
	if ($n1>1 && $n1<5)	return $f2;
	if ($n1==1)		return $f1;
	return $f5;
}

function arr2tree($parr) {
	if (!empty($parr)) {
		$arr = array();
		$parent = 0;

		foreach ($parr as $item) {
//$item['ID'] = -$item['ID'];
			if (!empty($item['PARENT_ID'])) {
				$arr[$parent]['CHILDREN'][] = $item;
			}
			elseif (!empty($item['ID'])) {
				$item['CHILDREN'] = array();
				$arr[] = $item;
				$parent = count($arr)-1;
			}
			else {
				$arr[] = $item;
			}
		}
		return $arr;
	}
	return 0;
}

//----------------------------------------------------------------------------
// Простейшая локализация. По значению переменной текущего языка выбирается массив строк и
// в случае наличия в строке соответсвия, выполняется подстановка
function __($str) {
	global $_lang;

	return ($_lang[$str] ? $_lang[$str] : $str);
}

function paginator($page=1, $cfg=NULL) { //------------------------------------------------

	if (is_null($cfg['btnNext'])) $cfg['btnNext'] = 'next';
	if (is_null($cfg['btnPrev'])) $cfg['btnPrev'] = 'prev';
	if (is_null($cfg['tableName'])) $cfg['tableName'] = 'seagull_reviews';
	if (is_null($cfg['targetPage'])) $cfg['targetPage'] = '#page';
	if (is_null($cfg['limit'])) $cfg['limit'] = 10;
	if (is_null($cfg['linkClass'])) $cfg['linkClass'] = 'b-paginator-link';
	if (is_null($cfg['linkDisabled'])) $cfg['linkDisabled'] = ' b-paginator-link_disabled';
	if (is_null($cfg['linkActive'])) $cfg['linkActive'] = ' b-paginator-link_active';
	if (is_null($cfg['query']))
		if (isset($cfg['tableName']))
			$cfg['query'] = 'SELECT COUNT(*) FROM `'.$cfg['tableName'].'`';
		else return 0;
	if (is_null($cfg['advLinks'])) $cfg['advLinks'] = 3;
	$cfg['advLinksX2'] = $cfg['advLinks'] * 2;
//ea($cfg);
	$total_pages = retr_sql($cfg['query']);
	$page = mysql_real_escape_string($page);

	// Initial page num setup
	if ($page == 0) {$page = 1;}
	$prev = $page - 1;
	$next = $page + 1;
	$lastpage = ceil($total_pages/$cfg['limit']);
	$output = '';

	if ($lastpage > 1) {
		// Previous
		if ($page > 1) {
			$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$prev.'">'.$cfg['btnPrev'].'</a>';
		} else {
			$output.= '<span class="'.$cfg['linkClass'].$cfg['linkDisabled'].'">'.$cfg['btnPrev'].'</span>';
		}

		// Pages
		if ($lastpage < 3 + $cfg['advLinksX2']) {	// Not enough pages to breaking it up
			for ($i = 1; $i <= $lastpage; $i++) {
				if ($i == $page) {
					$output.= '<span class="'.$cfg['linkClass'].$cfg['linkActive'].'">'.$i.'</span>';
				} else {
					$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$i.'">'.$i.'</a>';
				}
			}
		}
		elseif ($lastpage > 2 + $cfg['advLinksX2']) {	// Enough pages to hide a few?
			// Beginning only hide later pages
			if ($page < 1 + $cfg['advLinksX2']) {
				for ($i = 1; $i < $cfg['advLinks'] + $cfg['advLinksX2']; $i++) {
					if ($i == $page) {
						$output.= '<span class="'.$cfg['linkClass'].$cfg['linkActive'].'">'.$i.'</span>';
					} else {
						$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$i.'">'.$i.'</a>';
					}
				}
				$output.= ' ... <a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$lastpage.'">'.$lastpage.'</a>';
			}
			// Middle hide some front and some back
			elseif ($lastpage - $cfg['advLinksX2'] + 1 > $page && $page > $cfg['advLinksX2']) {
				$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].'1">1</a> ... ';
				for ($i = $page - $cfg['advLinks']; $i <= $page + $cfg['advLinks']; $i++) {
					if ($i == $page) {
						$output.= '<span class="'.$cfg['linkClass'].$cfg['linkActive'].'">'.$i.'</span>';
					} else {
						$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$i.'">'.$i.'</a>';
					}
				}
				$output.= ' ... <a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$lastpage.'">'.$lastpage.'</a>';
			}
			// End only hide early pages
			else {
				$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].'1">1</a> ... ';
				for ($i = $lastpage - $cfg['advLinksX2']; $i <= $lastpage; $i++) {
					if ($i == $page) {
						$output.= '<span class="'.$cfg['linkClass'].$cfg['linkActive'].'">'.$i.'</span>';
					} else {
						$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$i.'">'.$i.'</a>';
					}
				}
			}
		}

		// Next
		if ($page < $i - 1) {
			$output.= '<a class="'.$cfg['linkClass'].'" href="'.$cfg['targetPage'].$next.'">'.$cfg['btnNext'].'</a>';
		} else {
			$output.= '<span class="'.$cfg['linkClass'].$cfg['linkDisabled'].'">'.$cfg['btnNext'].'</span>';
		}
	}
	return $output;
}
?>
