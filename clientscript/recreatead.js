recreatead = new vB_AJAX_Handler(true)

// http://lawrence.ecorp.net/inet/samples/js-getelementsbyclassname.shtml
function cstmGetElementsByClassName(class_name)
{
	var docList = this.all || this.getElementsByTagName('*');
	var re = new RegExp("(?:^|\\s)"+class_name+"(?:\\s|$)");
	var matchArray = new Array();
	for (var i = 0; i < docList.length; i++)
	{
		if (re.test(docList[i].className))
		{
			matchArray[matchArray.length] = docList[i];
		}
	}
	return matchArray;
}
document.getElementsByClassName = cstmGetElementsByClassName;

function recreatead_header()
{
	recreatead.onreadystatechange(recreatedad_header)
	recreatead.send('ajax.php?do=createad&adcode=header_adcode')
	function recreatedad_header()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			fetch_object('header_adcode').innerHTML = recreatead.handler.responseText
		}
	}
}
function recreatead_navbar()
{
	recreatead.onreadystatechange(recreatedad_navbar)
	recreatead.send('ajax.php?do=createad&adcode=navbar_adcode')
	function recreatedad_navbar()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			fetch_object('navbar_adcode').innerHTML = recreatead.handler.responseText
		}
	}
}
function recreatead_leftcolumn()
{
	recreatead.onreadystatechange(recreatedad_leftcolumn)
	recreatead.send('ajax.php?do=createad&adcode=leftcolumn_adcode')
	function recreatedad_leftcolumn()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			fetch_object('leftcolumn_adcode').innerHTML = recreatead.handler.responseText
		}
	}
}
function recreatead_rightcolumn()
{
	recreatead.onreadystatechange(recreatedad_rightcolumn)
	recreatead.send('ajax.php?do=createad&adcode=rightcolumn_adcode')
	function recreatedad_rightcolumn()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			fetch_object('rightcolumn_adcode').innerHTML = recreatead.handler.responseText
		}
	}
}
function recreatead_postbit()
{
	recreatead.onreadystatechange(recreatedad_postbit)
	recreatead.send('ajax.php?do=createad&adcode=postbit_adcode')
	function recreatedad_postbit()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			var list = document.getElementsByClassName('postbit_adcode')
			for (var i = 0; i < list.length; i++)
			{
				list[i].innerHTML = recreatead.handler.responseText
           	}
		}
	}
}
function recreatead_forumbit()
{
	recreatead.onreadystatechange(recreatedad_forumbit)
	recreatead.send('ajax.php?do=createad&adcode=forumbit_adcode')
	function recreatedad_forumbit()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			var list = document.getElementsByClassName('forumbit_adcode')
			for (var i = 0; i < list.length; i++)
			{
				list[i].innerHTML = recreatead.handler.responseText
           	}
		}
	}
}
function recreatead_threadbit()
{
	recreatead.onreadystatechange(recreatedad_threadbit)
	recreatead.send('ajax.php?do=createad&adcode=threadbit_adcode')
	function recreatedad_threadbit()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			var list = document.getElementsByClassName('threadbit_adcode')
			for (var i = 0; i < list.length; i++)
			{
				list[i].innerHTML = recreatead.handler.responseText
           	}
		}
	}
}
function recreatead_footer()
{
	recreatead.onreadystatechange(recreatedad_footer)
	recreatead.send('ajax.php?do=createad&adcode=footer_adcode')
	function recreatedad_footer()
	{
		if (recreatead.handler.readyState == 4 && recreatead.handler.status == 200)
		{
			fetch_object('footer_adcode').innerHTML = recreatead.handler.responseText
		}
	}
}