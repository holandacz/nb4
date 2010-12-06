
		function emailCheck (emailStr) {
			var checkTLD=1;
			var knownDomsPat=/^(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum)$/;
			var emailPat=/^(.+)@(.+)$/;
			var specialChars="\\(\\)><@,;:\\\\\\\"\\.\\[\\]";
			var validChars="\[^\\s" + specialChars + "\]";
			var quotedUser="(\"[^\"]*\")";
			var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
			var atom=validChars + '+';
			var word="(" + atom + "|" + quotedUser + ")";
			var userPat=new RegExp("^" + word + "(\\." + word + ")*$");
			var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$");
			var matchArray=emailStr.match(emailPat);
			if (matchArray==null) {
				LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_1);
				return false;
			}
			var user=matchArray[1];
			var domain=matchArray[2];
			for (i=0; i<user.length; i++) {
				if (user.charCodeAt(i)>127) {
					LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_2);
					return false;
			   }
			}
			if (emailStr.match(/\'/)) {
				LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_2);
				return false;
			}
			for (i=0; i<domain.length; i++) { 
				if (domain.charCodeAt(i)>127) {
					LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_3);
					return false;
				}
			}
			if (user.match(userPat)==null) {
				LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_4);
				return false;
			}
			var IPArray=domain.match(ipDomainPat);
			if (IPArray!=null) {
				for (var i=1;i<=4;i++) {
					if (IPArray[i]>255) {
						LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_5);
						return false;
					}
				}
				return true;
			}
			var atomPat=new RegExp("^" + atom + "$");
			var domArr=domain.split(".");
			var len=domArr.length;
			for (i=0;i<len;i++) {
				if (domArr[i].search(atomPat)==-1) {
					LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_6);
					return false;
				}
			}
			if (checkTLD && domArr[domArr.length-1].length!=2 && 
			domArr[domArr.length-1].search(knownDomsPat)==-1) {
				LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_7)
				return false;
			}
			if (len<2) {
				 LoginStatus('email_1','email_1_status',null,'0', ajaxreg_email_error_8);
				return false;
			}
			return true;
		}
		function isInt (str)
		{
			var i = parseInt (str);
			if (isNaN (i))
				return false;
		
			i = i . toString ();
			if (i != str)
				return false;
		
			return true;
		}
		var isWorking = false;
		function handleHttpResponse_2() {
		  if (http.readyState == 4) {
			if (http.responseText.indexOf('invalid') == -1) {
			  // Split the comma delimited response into an array
			  results = http.responseText.split(",");
			  if (results[1] == "Invalid") {
			  	document.getElementById('username').value = '';
				LoginStatus('username','username_status',null,'0', results[0] + ajaxreg_name_inuse);
				isWorking = false;
			  } else if (results[1] == "IllegalUsername") {
			  	document.getElementById('username').value = '';
				LoginStatus('username','username_status',null,'0', results[0] + ajaxreg_name_illegal);
				isWorking = false;
			  } else {
			  	document.getElementById('username').value = unescape(results[0]);
				LoginStatus('username','username_status',null,'1', results[0] + ajaxreg_name_ok);
				isWorking = false;
			  }
			}
		  }
		}
		function CheckUserName() {
			var form = document.register;
			var username = escape(stripslashes(form.username.value));	
			var usernameregex = new RegExp(ajaxreg_name_usernameregex);
				if (username.length < ajaxreg_name_minuserlength) {
				 	LoginStatus('username','username_status',null,'0', ajaxreg_name_error_1);
				 	isWorking = false;
				} else if ((username.match(usernameregex)==null) && (usernameregex!="")) {
					LoginStatus('username','username_status',null,'0', ajaxreg_name_error_2);
					return false;
				} else {
				if (!isWorking && http) {
					document.getElementById('username').value = ajaxreg_name_checking;
					http.open("GET", 'ajax.php?do=CheckUsername&param=' + username, true);
					http.onreadystatechange = handleHttpResponse_2;
					isWorking = true;
					http.send(null);
				}
			}
		}
		function handleHttpResponse() {
		  if (http.readyState == 4) {
			if (http.responseText.indexOf('invalid') == -1) {
			  // Split the comma delimited response into an array
			  results = http.responseText.split(",");
			  if (results[1] == "Working") {
			  	document.getElementById('email_1').value = unescape(results[0]);
				LoginStatus('email_1','email_1_status',null,'1', results[0] + ajaxreg_name_ok);
				isWorking = false;
			  } else {
			  	document.getElementById('email_1').value = '';
				LoginStatus('email_1','email_1_status',null,'0', results[0] + ajaxreg_name_inuse);
				isWorking = false;
			  }
			}
		  }
		}
		function CheckEmail() {
			var form = document.register;
			var email = form.email_1.value;	
				if (!isWorking && http) {
					document.getElementById('email_1').value = ajaxreg_name_checking;
					http.open("GET", 'ajax.php?do=CheckEmail&param=' + email, true);
					http.onreadystatechange = handleHttpResponse;
					isWorking = true;
					http.send(null);
				}
		}

		function getHTTPObject() {
		  var xmlhttp;
		  /*@cc_on
		  @if (@_jscript_version >= 5)
			try {
			  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
			  try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			  } catch (E) {
				xmlhttp = false;
			  }
			}
		  @else
		  xmlhttp = false;
		  @end @*/
		  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
			try {
			  xmlhttp = new XMLHttpRequest();
			} catch (e) {
			  xmlhttp = false;
			}
		  }
		  return xmlhttp;
		}
		function LoginStatus(ID,ID2,parentID,sText,reason) {
			var inputboxElem = document.getElementById(ID);
			var ID2 = ID + "_status";
			if (typeof reason == "undefined") {
				reason = "";
			}
			if (sText == 0) {
				var sText = "&nbsp;<img src='./images/misc/bad.gif'>&nbsp;";
				inputboxElem.setAttribute("class", "inputbad");
				inputboxElem.setAttribute("className", "inputbad");
			} else if (sText == 1) {
				var sText = "&nbsp;<img src='./images/misc/good.gif'>&nbsp;";
				inputboxElem.setAttribute("class", "inputgood");
				inputboxElem.setAttribute("className", "inputgood");
			}
			if (document.layers) {
				var oLayer = (parentID)? eval('document.' + parentID + '.document.' + ID2 + '.document') : document.layers[ID2].document;
				oLayer.open();
				oLayer.write(sText + reason + progressbar);
				oLayer.close();
			}
			else if (document.all) document.all[ID2].innerHTML = sText + reason
			else if (parseInt(navigator.appVersion)>=5&&navigator.appName=="Netscape") {
				document.getElementById(ID2).innerHTML = sText + reason;
			}
		}
		function varfield(field) {
			var form = document.register;
			var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");
			if (field == 1) {
				if (form.password_1.value.length < 6) {
					LoginStatus('password_1','password_1_status',null,'0', ajaxreg_password_error_1);
				} else {
					LoginStatus('password_1','password_1_status',null,'1', ajaxreg_password_ok);
				}
			} else if (field == 2) {
				if ((form.password.value == "") || (form.password.value !== form.passwordconfirm.value)){
					LoginStatus('password_2','password_2_status',null,'0', ajaxreg_password_nomatch);
				} else if (form.passwordconfirm.value.length > 5) {
					LoginStatus('password_2','password_2_status',null,'1', ajaxreg_password_match);
				}
			} else if (field == 3) {
				if (emailCheck(form.email.value) == false) {
				} else {
					CheckEmail();
				}
			} else if (field == 4) {
				if (form.email.value !== form.emailconfirm.value) {
					LoginStatus('email_2','email_2_status',null,'0', ajaxreg_email_nomatch);
				} else if (form.email.value.length > 4) {
					 LoginStatus('email_2','email_2_status',null,'1', ajaxreg_email_match);
				}
			}
		}
		function stripslashes(str) {
			str=str.replace(/\\/g,'');
			str=str.replace(/\//g,'');
			return str;
		}
		var http = getHTTPObject(); // We create the HTTP Object