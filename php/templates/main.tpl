<!doctype html><head>
<meta charset="utf-8">
<title>Boxshop Online</title>
<link href="{dir}css/style.css" rel="stylesheet" type="text/css">
<link href="{dir}css/nav.css" rel="stylesheet" type="text/css">
<link media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" href="{dir}css/mobile.css" type="text/css" rel="stylesheet"> 

<link rel="stylesheet" href="{dir}css/ui-darkness/jquery-ui-1.8.16.custom.css" type="text/css" media="screen">
<script type="text/javascript" src="{dir}scripts/jquery.js"></script>
	<script type="text/javascript">
        $(function() {
          if ($.browser.msie && $.browser.version.substr(0,1)<7)
          {
			$('li').has('ul').mouseover(function(){
				$(this).children('ul').show();
				}).mouseout(function(){
				$(this).children('ul').hide();
				})
          }
        });        
    </script>
    
    {scripts}
{wikiscripts}
	<script type="text/javascript">
			
			function deleteSection(id){
				if (confirm("Delete the WHOLE SECTION?")){
					$('#deletesection').submit();
				}
			}
			function changeVersion(){
				obj = document.getElementsByName("versionId");
				str="wiki_getversion.php?id=" + getCheckedValue(obj);
				$('#historybox').load(str);
			}
			function getCheckedValue(radioObj) {
				if(!radioObj)
					return "";
				var radioLength = radioObj.length;
				if(radioLength == undefined)
					if(radioObj.checked)
						return radioObj.value;
					else
						return "";
				for(var i = 0; i < radioLength; i++) {
					if(radioObj[i].checked) {
						return radioObj[i].value;
					}
				}
				return "";
				
			}
			function dropVersion(){
				var theform=document.forms["deleteVersion"];
				var answer = confirm  ("Delete this version?");
				if (answer){
					theform.submit();
				}
			}
			
			</script>';
{/wikiscripts}		
{codescripts}


<link rel="stylesheet" href="css/ui-darkness/jquery-ui-1.8.16.custom.css" type="text/css" media="screen">

<script type="text/javascript" src="scripts/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript">

	  function changeInfoView(){
		 pageName = $('#infoView').val() + '.php';
	  	$('#eventlogcontainer').load(pageName);	   	
	  }	
	  
	  function getRandomCode(inputId){
		$.get("randomcode.php",
		function(data){
			$(inputId).val(data.code);
		}, "json");
   	  }
	  function toggleCodeForms(id){
		  str = '#' + id;
		  $(str).css('display', 'block');
		  str = str + 'Btn';
		  $(str).css('display', 'none');
	  }
		  
		  
		  
		  
		  
</script>

<script type="text/javascript">
	$(function() {
		$( "#codeStartDate" ).datepicker();
	});

   
</script>


{/codescripts}




</head>
<body>
<div id="header">
<h1><a href="index.php">Boxshop Online</a></h1>
<p class="welcome">{welcome}</p>
{nav}

</div>
<div style="height:180px;">&nbsp;</div>
{login}
        <form action="process.php" method="post">
            <p>Username:
            <input name="user" type="text" value="{formLoginUser}" class="formfield">
            {formLoginUserError}
            Password:<input type="password" name="pass" maxlength="30" value="{formLoginPass}" class="formfield">{formLoginPassError}
          <input type="submit" value="Login" class="submitbtn"><input type="checkbox" name="remember">Remember me next time</p>
            <input type="hidden" name="sublogin" value="1">
            <a href="forgotpass.php">Forgot Password</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="register.php">New Account(Boxshop Members only)</a>

        </form>
{/login}
<div class="contentContainer">
{content}
</div>
</body>

</html>

