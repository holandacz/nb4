<?
/******************************************************************************************
*   PHP MillionPixel Script (C)2005 by texmedia.de
*   All Rights Reserved.
*
*   This program is not for free, you have to buy a copy-license for your domain.
*   This copyright notice and the header above have to remain intact.
*   You do not have the permission to sell the code or parts of this code or chanced
*   parts of this code for this program.
*   This program is distributed "as is" and without warranty of any
*   kind, either express or implied.
*
*   Please check
*   http://www.texmedia.de
*   for Bugfixes, Updates and Support.
******************************************************************************************/
    print '<script type="text/javascript">'."\n";
    print '<!--'."\n";
    if($Focusfeld && $Focusform)
        print ' document.'.$Focusform.'.elements["'.$Focusfeld.'"].focus();'."\n\n";
    print '//-->'."\n";
    print '</script>'."\n";
?>
