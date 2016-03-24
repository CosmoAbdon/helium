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
// Last modified 23/03/2016 by MVprobr

ob_start();
/* now I include html headers
header ("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");*/
session_start();
$_SESSION["loc"] = dirname($_SERVER['PHP_SELF']);
if($_SESSION["loc"]=="/") $_SESSION["loc"] = "";
$_SESSION["locr"] = dirname(__FILE__);
if($_SESSION["locr"]=="/") $_SESSION["locr"] = "";

require_once("globals.php");
require_once("db.php");

if (!isset($_GET["name"])) {
	if (ValidSession())
		DBLogOut($_SESSION["usertable"]["contestnumber"], 
				 $_SESSION["usertable"]["usersitenumber"], $_SESSION["usertable"]["usernumber"],
				 $_SESSION["usertable"]["username"]=='admin');
	session_unset();
	session_destroy();
	session_start();
	$_SESSION["loc"] = dirname($_SERVER['PHP_SELF']);
	if($_SESSION["loc"]=="/") $_SESSION["loc"] = "";
	$_SESSION["locr"] = dirname(__FILE__);
	if($_SESSION["locr"]=="/") $_SESSION["locr"] = "";
}
if(isset($_GET["getsessionid"])) {
	echo session_id();
	exit;
}
ob_end_flush();

require_once('version.php');

?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<link rel="icon" type="image/png" href="../assets/img/favicon.ico">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<title>Helium <?php echo $BOCAVERSION; ?> Login - <?php /*echo $TITULO CUSTOMIZAVEL PELA UNIVERSIDADE;*/ ?></title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    
     <!--  Social tags      -->
    <meta name="keywords" content="html dashboard, html css dashboard, web dashboard, bootstrap dashboard, bootstrap, css3 dashboard, bootstrap admin,frontend, responsive bootstrap dashboard">
    <meta name="description" content="Forget about boring dashboards, get an admin template designed to be simple and beautiful.">
    
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="Helium - Online Contest Administrator">
    <meta itemprop="description" content="Forget about boring dashboards, get an admin template designed to be simple and beautiful.">
    <meta itemprop="image" content="../assets/img/social/opt_lbd_pro_thumbnail.jpg">
    
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@heliumsgcp">
    <meta name="twitter:title" content="Helium - Online Contest Administrator">
    
    <meta name="twitter:description" content="Forget about boring dashboards, get an admin template designed to be simple and beautiful.">
    <meta name="twitter:creator" content="@heliumsgcp">
    <meta name="twitter:image" content="../assets/img/social/opt_lbd_pro_thumbnail.jpg">
    <meta name="twitter:data1" content="Helium - Online Contest Administrator">
    <meta name="twitter:label1" content="Product Type">
    
    <!-- Open Graph data -->
    <meta property="og:title" content="Helium - Online Contest Administrator" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="../dashboard.html" />
    <meta property="og:image" content="../assets/img/social/opt_lbd_pro_thumbnail.jpg"/>
    <meta property="og:description" content="Forget about boring dashboards, get an admin template designed to be simple and beautiful." />
    <meta property="og:site_name" content="Helium" />

    <!-- Bootstrap core CSS     -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
        
    <!--  Light Bootstrap Dashboard core CSS    -->
    <link href="../../assets/css/light-bootstrap-dashboard.css" rel="stylesheet"/>
    
    <!--  CSS for Demo Purpose, don't include it in your project     -->
    <link href="../../assets/css/demo.css" rel="stylesheet" />
    
    <!--     Fonts and icons     -->
    <link href="../bower_components/font-awesome/font-awesome.min.css" rel="stylesheet">
    <link href='../bower_components/google-fonts/css?family=Roboto:400,700,300' rel='stylesheet' type='text/css'>
    <link href="../assets/css/pe-icon-7-stroke.css" rel="stylesheet" />

	<?php
	if(function_exists("globalconf") && function_exists("sanitizeVariables")) {
	  if(isset($_GET["name"]) && $_GET["name"] != "" ) {
		$name = $_GET["name"];
		$password = $_GET["password"];
		$usertable = DBLogIn($name, $password);
		if(!$usertable) {
			ForceLoad("index.php");
		}
		else {
			if(($ct = DBContestInfo($_SESSION["usertable"]["contestnumber"])) == null)
				ForceLoad("index.php");
			if($ct["contestlocalsite"]==$ct["contestmainsite"]) $main=true; else $main=false;
			if(isset($_GET['action']) && $_GET['action'] == 'scoretransfer') {
				echo "SCORETRANSFER OK";
			} else {
				if($main && $_SESSION["usertable"]["usertype"] == 'site') {
					MSGError('Direct login of this user is not allowed');
					unset($_SESSION["usertable"]);
					ForceLoad("index.php");
					exit;
				}
				echo "<script language=\"javascript\">\n";
				echo "document.location='" . $_SESSION["usertable"]["usertype"] . "/index.php';\n";
				echo "</script>\n";
			}
			exit;
		}
	  }
	} else {
	  echo "<script language=\"javascript\">\n";
	  echo "alert('Unable to load config files. Possible file permission problem in the BOCA directory.');\n";
	  echo "</script>\n";
	}
	?>
</head>
<body onload="document.form1.name.focus()">
	<nav class="navbar navbar-transparent navbar-absolute">
    	<div class="container">    
	        <div class="navbar-header">
	            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigation-example-2">
	                <span class="sr-only">Toggle navigation</span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
		            </button>
		            <a class="navbar-brand" href="../dashboard.html">Light Bootstrap Dashboard PRO</a>
		     </div>
		     <div class="collapse navbar-collapse">       
		        <ul class="nav navbar-nav navbar-right">
					<li>
						<a href="register.html">
							Register
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<div class="wrapper wrapper-full-page">
	    <div class="full-page login-page" data-color="orange" data-image="../../assets/img/full-screen-image-1.jpg">   
	        
	    <!--   you can change the color of the filter page using: data-color="blue | azure | green | orange | red | purple"
	    ** could be choose by university in settings ** -->
	        <div class="content">
	            <div class="container">
	                <div class="row">                   
	                    <div class="col-md-4 col-sm-6 col-md-offset-4 col-sm-offset-3">
	      					<form name="form1" action="javascript:computeHASH()">
	        <div align="center"> 
	          <!--   if you want to have the card without animation please remove the ".card-hidden" class   -->
	                            <div class="card card-hidden">
	                                <div class="header text-center">heLogin</div>
	                                <div class="content">
	                                    <div class="form-group">
	                                        <label>Nome de usuário / código</label>
	                                        <input type="text" placeholder="" name="name" class="form-control">
	                                    </div>
	                                    <div class="form-group">
	                                        <label>Senha</label>
	                                        <input type="password" placeholder="" name="password" class="form-control">
	                                    </div>
	                    		</div>
	                            <div class="footer text-center">
	                            	<button type="submit" class="btn btn-fill btn-warning btn-wd">Login</button>
	                            </div>
	                                
	                        </form>
	                                
	                    </div>                    
	                </div>
	            </div>
	        </div>
	    	<footer class="footer footer-transparent">
	            <div class="container">
	                <nav class="pull-left">
	                    <ul>
	                        <li>
	                            <a href="#">
	                                Home
	                            </a>
	                        </li>
	                        <li>
	                            <a href="#">
	                                <!--github icon--> GitHub
	                            </a>
	                        </li>
	                        <li>
	                            <a href="#">
	                                Support
	                            </a>
	                        </li>
	                        <li>
	                            <a href="#">
	                               Talk to Us
	                            </a>
	                        </li>
	                    </ul>
	                </nav>
	                <p class="copyright pull-right">
	                    <a href="#">&copy;Copyleft</a> 2016 <a href="#">Laboratório de Ideias</a>, made with love for better programathon's
	                </p>
	            </div>
	        </footer>
	    </div>  
	</div>
</body>
    
    <!--   Core JS Files   -->
    <script src="../assets/js/jquery.min.js" type="text/javascript"></script>
    <script src="../../assets/js/jquery-ui.min.js" type="text/javascript"></script> 
	<script src="../../assets/js/bootstrap.min.js" type="text/javascript"></script>
	
	
	<!--  Forms Validations Plugin -->
	<script src="../../assets/js/jquery.validate.min.js"></script>
	
	<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
	<script src="../../assets/js/moment.min.js"></script>
	
    <!--  Date Time Picker Plugin is included in this js file -->
    <script src="../../assets/js/bootstrap-datetimepicker.js"></script>
    
    <!--  Select Picker Plugin -->
    <script src="../../assets/js/bootstrap-selectpicker.js"></script>
    
	<!--  Checkbox, Radio, Switch and Tags Input Plugins -->
	<script src="../../assets/js/bootstrap-checkbox-radio-switch-tags.js"></script>
	
	<!--  Charts Plugin -->
	<script src="../../assets/js/chartist.min.js"></script>

    <!--  Notifications Plugin    -->
    <script src="../../assets/js/bootstrap-notify.js"></script>
    
    <!-- Sweet Alert 2 plugin -->
	<script src="../../assets/js/sweetalert2.js"></script>
        
    <!-- Vector Map plugin -->
	<script src="../../assets/js/jquery-jvectormap.js"></script>
	
    <!--  Google Maps Plugin    -->
    <script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
	
	<!-- Wizard Plugin    -->
    <script src="../../assets/js/jquery.bootstrap.wizard.min.js"></script>

    <!--  Datatable Plugin    -->
    <script src="../../assets/js/bootstrap-table.js"></script>
    
    <!--  Full Calendar Plugin    -->
    <script src="../../assets/js/fullcalendar.min.js"></script>
    
    <!-- Light Bootstrap Dashboard Core javascript and methods -->
	<script src="../../assets/js/light-bootstrap-dashboard.js"></script>
	
	<!--   Sharrre Library    -->
    <script src="../../assets/js/jquery.sharrre.js"></script>
	
	<!-- Light Bootstrap Dashboard DEMO methods, don't include it in your project! -->
	<script src="../../assets/js/demo.js"></script>

	<!-- password login?! -->
	<script language="javascript" src="../assets/js/sha256.js"></script>


    <script type="text/javascript">
        $().ready(function(){
            lbd.checkFullPageBackgroundImage();
            
            setTimeout(function(){
                // after 1000 ms we add the class animated to the login/register card
                $('.card').removeClass('card-hidden');
            }, 700)
        });
    </script>
    
	<!-- password login?! -->
	<script language="javascript">
		function computeHASH()
		{
			var userHASH, passHASH;
			userHASH = document.form1.name.value;
			passHASH = js_myhash(js_myhash(document.form1.password.value)+'<?php echo session_id(); ?>');
			document.form1.name.value = '';
			document.form1.password.value = '                                                                                 ';
			document.location = 'index.php?name='+userHASH+'&password='+passHASH;
		}
	</script>

    <!-- Google Analytics
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','www.google-analytics.com/analytics.js','ga');
    
      ga('create', 'UA-00000000-1', 'auto');
      ga('send', 'pageview');
    </script> -->


    

</html>