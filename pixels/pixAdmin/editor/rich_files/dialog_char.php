<?php

//language class
require_once('lang/class.rich_lang.php');

	//extract variables submitted to this page
	@extract($_GET);
	if (!isset($browser)) $browser = '';

$rich_lang = new rich_lang($lang); //text data in current language
$text = $rich_lang->item('CharacterMap');

$chars[1] = array(
 'nbsp','euro','fnof','permil','Scaron','OElig','#381','trade','scaron','oelig',
 '#382','Yuml','cent','pound','curren','yen','brvbar','sect','uml','copy',
 'ordf','laquo','not','nbsp; &shy','reg','macr','deg','plusmn','sup2','sup3',
 'acute','micro','para','middot','cedil','sup1','ordm','raquo','frac14','frac12',
 'frac34','iquest','Agrave','Aacute','Acirc','Atilde','Auml','Aring','AElig','Ccedil',
 'Egrave','Eacute','Ecirc','Euml','Igrave','Iacute','Icirc','Iuml','ETH','Ntilde',
 'Ograve','Oacute','Ocirc','Otilde','Ouml','times','Oslash','Ugrave','Uacute','Ucirc',
 'Uuml','Yacute','THORN','szlig','agrave','aacute','acirc','atilde','auml','aring',
 'aelig','ccedil','egrave','eacute','ecirc','euml','igrave','iacute','icirc','iuml',
 'eth','ntilde','ograve','oacute','ocirc','otilde','ouml','divide','oslash','ugrave',
 'uacute','ucirc','uuml','yacute','thorn','yuml'
);

$chars[2] = array(
 'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta', 'Iota', 'Kappa',
 'Lambda', 'Mu', 'Nu', 'Xi', 'Omicron', 'Pi', 'Rho', 'Sigma', 'Tau', 'Upsilon',
 'Phi', 'Chi', 'Psi', 'Omega', 'there4', 'perp', 'alpha', 'beta', 'gamma', 'delta',
 'epsilon', 'zeta', 'eta', 'theta', 'iota', 'kappa', 'lambda', 'mu', 'nu', 'xi',
 'omicron', 'pi', 'rho', 'sigmaf', 'sigma', 'tau', 'upsilon', 'phi', 'chi', 'psi',
 'omega', 'oline', 'le', 'frasl', 'infin', 'int', 'clubs', 'diams', 'hearts', 'spades',
 'harr', 'larr', 'rarr', 'uarr', 'darr', 'ldquo', 'rdquo', 'bdquo', 'ge', 'prop',
 'part', 'bull', 'ne', 'equiv', 'asymp', 'hellip', 'mdash', 'cap', 'cup', 'sup',
 'supe', 'sub', 'sube', 'isin', 'ni', 'forall', 'exist', 'lsquo', 'rsquo', 'iexcl',
 'ang', 'nabla', 'prod', 'radic', 'and', 'or', 'hArr', 'rArr', 'loz', 'sum'

);

?><html>
<head>
<title><?php echo $text['Title']; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<link rel="StyleSheet" type="text/css" href="rich<?php echo $browser=='ns'?'_ns':''; ?>.css">

<style type="text/css">
td.re_char{
  cursor: pointer;
}
</style>

<script language="JavaScript">
var parent_win = <?php
	if ($browser == 'ns') echo 'opener';
		else echo 'window.dialogArguments'; ?>;
var active_rich = parent_win.active_rich;

//mouse over/out handler
function char_over(obj, mode){
//	obj.style.cursor = mode?'hand':'default';
}

//mouse click handler
function char_click(obj){
//delete text making borders of cell with &shy; visible in Mozilla-based
var s_char = String(obj.innerHTML).replace("&nbsp; ", "");

<?php if ($browser == 'ns') : ?>

	opener.paste_html(s_char);

<?php else: ?>

var sel = active_rich.document.selection.createRange();

	if (sel.parentElement) {
		sel.pasteHTML(s_char); //insert the character in editor
	}

<?php endif; ?>

	window.close();
}


</script>

</head>

<body topmargin="0" leftmargin="0" rightmargin="0" class="re_dialog">
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr class="re_dialog"><td align="center">

<table border="0" cellspacing="0" cellpadding="0">
<?php

	foreach ($chars as $key=>$value) {
		foreach ($value as $k=>$val) {

			if ($k%20 == 0) {
				echo '<tr>';
			}

			echo '<td class="re_char" width="20" height="20" align="center" vAlign="center" onclick="char_click(this);">&'.$val.';</td>';
// onmouseover="char_over(this,true);" onmouseout="char_over(this,false);"
			echo "\n";

			if (($k+1)%20 == 0) {
				echo '</tr>';

			}
		}

		if ($k%20) {
			echo '<td colspan="'.(20-$k%20-1).'">&nbsp;</td></tr>';
		}
	}
?>
</table>

<table border="0" cellspacing="0" cellpadding="0" align="center">
<form onsubmit="return false;">

<tr><td><img src="images/space.gif" width="1" height="4"></td></tr>
<tr><td align="center" vAlign="center">
<input type="button" value="<?php echo $rich_lang->item('Cancel'); ?>" onclick="window.close();">
</td></tr>

</form>
</table>

</td></tr></table>
</body>
</html>