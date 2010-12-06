<?php
/*

Development Tool: Global Array Examiner for vBulletin.
Copyright (c) 2008 ReCom (http://www.recom.org)

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT 
NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES 
OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Portions of codes are copyrighted to Jelsoft Enterprises Ltd.

*/

define('EXAMINE_VERSION', '1.0');

$globaltemplates = array('GENERIC_SHELL');
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions.php');

/* List of examinable array variables. Format:
 *  'key' => array( 'dropdown text', &$thearray, $securitylevel )
 */
$examinetargets = array(
  'server' => array('$_SERVER', &$_SERVER, 3),
  'cookie' => array('$_COOKIE', &$_COOKIE),
  'userinfo' => array('$vbulletin->userinfo', &$vbulletin->userinfo),
  'options' => array('$vbulletin->options', &$vbulletin->options, 3),
  'bfugp' => array('$vbulletin->bf_ugp', &$vbulletin->bf_ugp),
  'config' => array('$vbulletin->config', &$vbulletin->config, 4),
  'stylevar' => array('$stylevar', &$stylevar),
  'phrase' => array('$vbphrase', &$vbphrase, 2),
);

/* Security level
 * 0 / null => everybody can view
 * 1 => registered only
 * 2 => mod only
 * 3 => admin only
 * 4 => superadmin only 
 */ 
$security_unlock_bool = array(
  0 => true,
  1 => ($vbulletin->userinfo['userid'] > 0),
  2 => can_moderate(),
  3 => can_administer(),
  4 => in_array($vbulletin->userinfo['userid'], preg_split('#\s*,\s*#s', $vbulletin->config['SpecialUsers']['superadministrators'], -1, PREG_SPLIT_NO_EMPTY)),
);

/* You won't probably need to modify the following codes */

$vbulletin->input->clean_array_gpc('g', array(
  'examine' => TYPE_NOHTML,
  'sort' => TYPE_BOOL,
  'nonempty' => TYPE_BOOL,
));

$examine = $vbulletin->GPC['examine'];
$sort = $vbulletin->GPC['sort'];
$nonempty = $vbulletin->GPC['nonempty'];

$options = '';
foreach ($examinetargets as $k => $v) {
  if (empty($v[2]) OR $security_unlock_bool["$v[2]"]) {
    $options .= '<option'.($k == $examine? ' selected="selected"':'').' value="'.$k.'">'.htmlentities($v[0]).'</option>';
  }
}

$HTML .= '<form method="get" action="" class="tborder smallfont" style="padding: 1em;">';
$HTML .= '<input type="submit" class="button" value="Examine:" /><select name="examine" style="width: 25em;">'.$options.'</select> ';
$HTML .= '<label for="sort"><input type="checkbox" name="sort" id="sort" value="1"'.($sort? ' checked="checked"':'').' />Sort by keys</label> ';
$HTML .= '<label for="nonempty"><input type="checkbox" name="nonempty" id="nonempty" value="1"'.($nonempty? ' checked="checked"':'').' />Filter out empty strings/arrays</label> ';
$HTML .= '</form><br />';

$_navbits = array( $_SERVER['PHP_SELF'] => 'Development Tool: Global Array Examiner');

if ($examine == 'post')
  $toexamine = array('$_POST', &$_POST);
else
  $toexamine = $examinetargets["$examine"];

if (!empty($toexamine)) {
  $_navbits[''] = $toexamine[0];
  if (empty($toexamine[2]) OR $security_unlock_bool["$toexamine[2]"])
    $HTML .= print_variable($toexamine[1], $toexamine[0], true);
  else
    $HTML .= '<h2>Permission denied</h2>';
}

$HTML .= '<p align="center" class="smallfont">Global Array Examiner v'.EXAMINE_VERSION.' by <a href="http://www.vbulletin.org/forum/member.php?u=243622">ReCom</a></p>';
$navbits = construct_navbits($_navbits);

function print_variable($var, $varname, $showheader = false)
{
  global $sort, $nonempty, $stylevar;
  $out = '';
  if (is_array($var)) {
    if ($showheader AND !empty($varname))
      $out .= '<div class="tborder thead" style="font-weight: bold;">'.htmlentities($varname).'</div>';
    $out .= '<table class="tborder" cellspacing="1" cellpadding="3" border="0" style="width: 100%; table-layout: fixed;"><tbody>';
    $rc = 1;
    
    if ($sort)
      ksort($var);
    foreach ($var as $name => $val) {
      $thisname = $varname.'[\''.$name.'\']';
      $thisname_html = htmlentities($thisname);
      if (is_array($val)) {
        if (!empty($val) OR !$nonempty) {
          $out .= '<tr class="alt'.$rc.'"><td colspan="2" style="vertical-align: top;"><div title="'.$thisname_html.'"><a href="#" onclick="javascript:prompt(\'Copy variable name\', \''.addslashes($thisname_html).'\'); return false;">'.htmlentities($name).'</a></div>';
          $out .= '<div style="margin-top: 10px; margin-'.$stylevar['left'].': 25px;"><div style="width:100%; overflow: auto;">'. print_variable($val, $thisname) .'</div></div></td></tr>';
          $rc = 3 - $rc;
        }
      }
      elseif ($val != "" OR !$nonempty) {
        $out .= '<tr class="alt'.$rc.'" title="'.$thisname_html.'"><td style="width: 20em; vertical-align: top;"><div style="overflow: auto;"><a href="#" onclick="javascript:prompt(\'Copy variable name\', \''.addslashes($thisname_html).'\'); return false;">'.$name.'</a></div></td>';
        $out .= '<td style="width: 100%"><div style="overflow: auto;">'. print_variable($val, $thisname) .'</div></td></tr>';
        $rc = 3 - $rc;
      }
    }
    $out .= '</tbody></table>';
  }
  else
    $out = htmlentities(print_r($var, true));
  return $out;
}

eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('GENERIC_SHELL') . '");');

?>