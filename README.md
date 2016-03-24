## helium (pt-BR)
### sistema de apoio a competicões de programação

Desenvolvido com base no [BOCA](ime.usp.br/~cassio/boca/) de [cassiopc](https://github.com/cassiopc/) o helium vem corrigir praticas de programação hoje já obsoletas e trazer uma nova interface visando usabilidade e acessibilidade. O helium está sendo construido para um trabalho da disciplica SIN 422 - Qualidade de Software no qual será avaliado a [ISO/IEC 9126](https://pt.wikipedia.org/wiki/ISO/IEC_9126).

runing boca: http://www.maratona.dacc.unir.br/ (open to register)

### versão:
#### branches:
```
- master: original boca 1.5.9 stable
- support-python: boca 1.5.8 + support py
- unstable: boca 1.5.10 unstable + assets to new layout (keep going here)
```

### TODO:
```
- all hole software except judge system :(
```

olhe isso:
helium/src/team/option.php
```
<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
// Last modified 05/aug/2012 by cassio@ime.usp.br
require('header.php');
require('../optionlower.php');
?>
```
helium/src/team/header.php
```
<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
// Last modified 21/jul/2012 by cassio@ime.usp.br
ob_start();
header ("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");
session_start();
ob_end_flush();
require_once('../version.php');
require_once("../globals.php");
require_once("../db.php");
$runteam='run.php';
echo "<html><head><title>Team's Page</title>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
echo "<link rel=stylesheet href=\"../Css.php\" type=\"text/css\">\n";
//echo "<meta http-equiv=\"refresh\" content=\"60\" />"; 
if(!ValidSession()) {
	InvalidSession("team/index.php");
        ForceLoad("../index.php");
}
if($_SESSION["usertable"]["usertype"] != "team") {
	IntrusionNotify("team/index.php");
        ForceLoad("../index.php");
}
echo "<script language=\"javascript\" src=\"../reload.js\"></script>\n";
echo "</head><body onload=\"Comecar()\" onunload=\"Parar()\"><table border=1 width=\"100%\">\n";
echo "<tr><td nowrap bgcolor=\"#aaaaee\" align=center>";
echo "<img src=\"../images/smallballoontransp.png\" alt=\"\">";
echo "<font color=\"#000000\">BOCA</font>";
echo "</td><td bgcolor=\"#aaaaee\" width=\"99%\">\n";
echo "Username: " . $_SESSION["usertable"]["userfullname"] . " (site=".$_SESSION["usertable"]["usersitenumber"].")\n";
$ds = DIRECTORY_SEPARATOR;
if($ds=="") $ds = "/";
$runtmp = $_SESSION["locr"] . $ds . "private" . $ds . "runtmp" . $ds . "run-contest" . $_SESSION["usertable"]["contestnumber"] . 
	"-site". $_SESSION["usertable"]["usersitenumber"] . "-user" . $_SESSION["usertable"]["usernumber"] . ".php";
$doslow=true;
if(file_exists($runtmp)) {
	if(($strtmp = file_get_contents($runtmp,FALSE,NULL,-1,1000000)) !== FALSE) {
		$postab=strpos($strtmp,"\t");
		$conf=globalconf();
		$strcolors = decryptData(substr($strtmp,$postab+1,strpos($strtmp,"\n")-$postab-1),$conf['key'],'');
		$doslow=false;
		$rn=explode("\t",$strcolors);
		$n=count($rn);
		for($i=1; $i<$n-1;$i++) {
			echo "<img alt=\"".$rn[$i]."\" width=\"10\" ".
				"src=\"" . balloonurl($rn[$i+1]) . "\" />\n";
			$i++;
		}
	} else unset($strtmp);
}
if($doslow) {
	$run = DBUserRunsYES($_SESSION["usertable"]["contestnumber"],
						 $_SESSION["usertable"]["usersitenumber"],
						 $_SESSION["usertable"]["usernumber"]);
	$n=count($run);
	for($i=0; $i<$n;$i++) {
		echo "<img alt=\"".$run[$i]["colorname"]."\" width=\"10\" ".
			"src=\"" . balloonurl($run[$i]["color"]) . "\" />\n";
	}
}
if(!isset($_SESSION["popuptime"]) || $_SESSION["popuptime"] < time()-120) {
	$_SESSION["popuptime"] = time();
	if(($st = DBSiteInfo($_SESSION["usertable"]["contestnumber"],$_SESSION["usertable"]["usersitenumber"])) != null) {
		$clar = DBUserClars($_SESSION["usertable"]["contestnumber"],
							$_SESSION["usertable"]["usersitenumber"],
							$_SESSION["usertable"]["usernumber"]);
		for ($i=0; $i<count($clar); $i++) {
			if ($clar[$i]["anstime"]>$_SESSION["usertable"]["userlastlogin"]-$st["sitestartdate"] && 
				$clar[$i]["anstime"] < $st['siteduration'] &&
				trim($clar[$i]["answer"])!='' && !isset($_SESSION["popups"]['clar' . $i . '-' . $clar[$i]["anstime"]])) {
				$_SESSION["popups"]['clar' . $i . '-' . $clar[$i]["anstime"]] = "(Clar for problem ".$clar[$i]["problem"]." answered)\n";
			}
		}
		$run = DBUserRuns($_SESSION["usertable"]["contestnumber"],
						  $_SESSION["usertable"]["usersitenumber"],
						  $_SESSION["usertable"]["usernumber"]);
		for ($i=0; $i<count($run); $i++) {
			if ($run[$i]["anstime"]>$_SESSION["usertable"]["userlastlogin"]-$st["sitestartdate"] && 
				$run[$i]["anstime"] < $st['sitelastmileanswer'] &&
				$run[$i]["ansfake"]!="t" && !isset($_SESSION["popups"]['run' . $i . '-' . $run[$i]["anstime"]])) {
				$_SESSION["popups"]['run' . $i . '-' . $run[$i]["anstime"]] = "(Run ".$run[$i]["number"]." result: ".$run[$i]["answer"] . ')\n';
			}
		}
	}
	$str = '';
	if(isset($_SESSION["popups"])) {
		foreach($_SESSION["popups"] as $key => $value) {
			if($value != '') {
				$str .= $value;
				$_SESSION["popups"][$key] = '';
			}
		}
		if($str != '') {
			MSGError('YOU GOT NEWS:\n' . $str . '\n');
		}
	}
}
list($clockstr,$clocktype)=siteclock();
echo "</td><td bgcolor=\"#aaaaee\" align=center nowrap>&nbsp;".$clockstr."&nbsp;</td></tr>\n";
echo "</table>\n";
echo "<table border=0 width=\"100%\" align=center>\n";
echo " <tr>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=problem.php>Problems</a></td>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=run.php>Runs</a></td>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=score.php>Score</a></td>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=clar.php>Clarifications</a></td>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=task.php>Tasks</a></td>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=files.php>Backups</a></td>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=option.php>Options</a></td>\n";
echo "  <td align=center width=\"12%\"><a class=menu style=\"font-weight:bold\" href=../index.php>Logout</a></td>\n";
echo " </tr>\n"; 
echo "</table>\n";
?>
```

helium/src/optionlower.php **voltou uma pasta wtf?!**
```
<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
// Last modified 05/aug/2012 by cassio@ime.usp.br
//optionlower.php: parte de baixo da tela de option.php, que eh igual para
//			todos os usuarios
require_once("globals.php");
if(!ValidSession()) {
        InvalidSession("scoretable.php");
        ForceLoad("index.php");
}
$loc = $_SESSION['loc'];
if (isset($_GET["username"]) && isset($_GET["userfullname"]) && isset($_GET["userdesc"]) && 
    isset($_GET["passwordo"]) && isset($_GET["passwordn"])) {
	$username = myhtmlspecialchars($_GET["username"]);
	$userfullname = myhtmlspecialchars($_GET["userfullname"]);
	$userdesc = myhtmlspecialchars($_GET["userdesc"]);
	$passwordo = myhtmlspecialchars($_GET["passwordo"]);
	$passwordn = myhtmlspecialchars($_GET["passwordn"]);
	DBUserUpdate($_SESSION["usertable"]["contestnumber"],
				 $_SESSION["usertable"]["usersitenumber"],
				 $_SESSION["usertable"]["usernumber"],
				 $_SESSION["usertable"]["username"], // $username, but users should not change their names
				 $userfullname,
				 $userdesc,
				 $passwordo,
				 $passwordn);
	ForceLoad("option.php");
}
$a = DBUserInfo($_SESSION["usertable"]["contestnumber"],
                $_SESSION["usertable"]["usersitenumber"],
                $_SESSION["usertable"]["usernumber"]);
?>

<script language="JavaScript" src="<?php echo $loc; ?>/sha256.js"></script>
<script language="JavaScript" src="<?php echo $loc; ?>/hex.js"></script>
<script language="JavaScript">
function computeHASH()
{
	var username, userdesc, userfull, passHASHo, passHASHn1, passHASHn2;
	if (document.form1.passwordn1.value != document.form1.passwordn2.value) return;
	username = document.form1.username.value;
	userdesc = document.form1.userdesc.value;
	userfull = document.form1.userfull.value;
	passMDo = js_myhash(js_myhash(document.form1.passwordo.value)+'<?php echo session_id(); ?>');
	passMDn = bighexsoma(js_myhash(document.form1.passwordn2.value),js_myhash(document.form1.passwordo.value));
	document.form1.passwordo.value = '                                                         ';
	document.form1.passwordn1.value = '                                                         ';
	document.form1.passwordn2.value = '                                                         ';
	document.location='option.php?username='+username+'&userdesc='+userdesc+'&userfullname='+userfull+'&passwordo='+passMDo+'&passwordn='+passMDn;
}
</script>

<br><br>
<form name="form1" action="javascript:computeHASH()">
  <center>
    <table border="0">
      <tr> 
        <td width="35%" align=right>Username:</td>
        <td width="65%">
	  <input type="text" readonly name="username" value="<?php echo $a["username"]; ?>" size="20" maxlength="20" />
        </td>
      </tr>
      <tr> 
        <td width="35%" align=right>User Full Name:</td>
        <td width="65%">
	  <input type="text" readonly name="userfull" value="<?php echo $a["userfullname"]; ?>" size="50" maxlength="50" />
        </td>
      </tr>
      <tr> 
        <td width="35%" align=right>User Description:</td>
        <td width="65%">
	  <input type="text" name="userdesc" value="<?php echo $a["userdesc"]; ?>" size="50" maxlength="250" />
        </td>
      </tr>
      <tr> 
        <td width="35%" align=right>Old Password:</td>
        <td width="65%">
	  <input type="password" name="passwordo" size="20" maxlength="20" />
        </td>
      </tr>
      <tr> 
        <td width="35%" align=right>New Password:</td>
        <td width="65%">
	  <input type="password" name="passwordn1" size="20" maxlength="20" />
        </td>
      </tr>
      <tr> 
        <td width="35%" align=right>Retype New Password:</td>
        <td width="65%">
	  <input type="password" name="passwordn2" size="20" maxlength="20" />
        </td>
      </tr>
    </table>
  </center>
  <center>
      <input type="submit" name="Submit" value="Send">
  </center>
</form>

</body>
</html>
```

### Licença:
[GPL-3](https://tldrlegal.com/license/gnu-general-public-license-v3-(gpl-3))

## helium (en-US)
### online content administrator

soon as possible
