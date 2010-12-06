<?PHP
/******************************************************************************************
*   Million Pixel Script (R)
*   (C) 2006 by texmedia.de, all rights reserved.
*   "Million Pixel Script" and "Pixel Script" is a registered Trademark of texmedia.
*
*   This script code is protected by international Copyright Law.
*   Any violations of copyright will be dealt with seriously,
*   and offenders will be prosecuted to the fullest extent of the law.
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
$popupwidth  = 800;
$popupheight = 600;
$VERSIONS[basename(__FILE__)] = "3.0";
?>
<script type="text/javascript">
<!--

var dragobjekt = null;
var dragx = 0;
var dragy = 0;
var posx = 0;
var posy = 0;
var bodytest = "no"

var agt=navigator.userAgent.toLowerCase();
var is_ie = (agt.indexOf("msie") != -1);
var is_nn = (agt.indexOf("netscape") != -1);
var is_op = (agt.indexOf("opera") != -1);
var is_ff = (agt.indexOf("firefox") != -1);
    if(is_op) {
        is_ie = false;
        is_nn = false;
        is_ff = false;
        var basewidth = eval(<? print $popupwidth; ?>+4);
        var baseheight = eval(<? print $popupheight; ?>);
        var baseiframe = eval(<? print $popupheight; ?>);
        var basepatch = 0;
    }
    if(is_ie) {
        var basewidth = eval(<? print $popupwidth; ?>+4);
        var baseheight = eval(<? print $popupheight; ?>+4);
        var baseiframe = "100%";
        var basepatch = 2;
    }
    if(is_nn) {
        var basewidth = eval(<? print $popupwidth; ?>);
        var baseheight = eval(<? print $popupheight; ?>+20);
        var baseiframe = eval(<? print $popupheight; ?>);
        var basepatch = 0;
    }
    if(is_ff) {
        var basewidth = eval(<? print $popupwidth; ?>);
        var baseheight = eval(<? print $popupheight; ?>+20);
        var baseiframe = eval(<? print $popupheight; ?>);
        var basepatch = 0;
    }
function draginit() {
  document.onmousemove = drag;
  document.onmouseup = dragstop;
}
function dragstart(element) {
  dragobjekt = element;
  dragx = posx - dragobjekt.offsetLeft;
  dragy = posy - dragobjekt.offsetTop;
}
function dragstop() {
  if(dragobjekt != null) {
  dragobjekt=null;
  }
}
function drag(ereignis) {
  posx = document.all ? window.event.clientX : ereignis.pageX;
  posy = document.all ? window.event.clientY : ereignis.pageY;
  if(dragobjekt != null) {
    dragobjekt.style.left = (posx - dragx) + "px";
    dragobjekt.style.top = (posy - dragy) + "px";
  }
}
function minimieren() {
    document.getElementById('popup<? print $popupID?>').style.display='none';
    var PopupFenster<? print $popupID?> = window.open("<? print $popupurl; ?>","PopupFenster<? print $popupID?>","left=20,top=100,width=<? print $popupwidth; ?>,height=<? print $popupheight; ?>,toolbar=no,menubar=no,status=no,scrollbars=<?PHP print $POPUP_NOSCROLLING ? 'no' : 'yes';?>,resizable=no,location=no");
    PopupFenster<? print $popupID?>.blur();
}
 if (is_op) {
  var PopupFenster<? print $popupID?> = 0;
 } else {
  var PopupFenster<? print $popupID?> = window.open("<? print $popupurl; ?>","PopupFenster<? print $popupID?>","left=20,top=100,width=<? print $popupwidth; ?>,height=<? print $popupheight; ?>,toolbar=no,menubar=no,status=no,scrollbars=<?PHP print $POPUP_NOSCROLLING ? 'no' : 'yes';?>,resizable=no,location=no");
 }
 if (PopupFenster<? print $popupID?>) {
   PopupFenster<? print $popupID?>.focus();
 } else {
        try {
        if (document.getElementsByTagName("body")[0].clientWidth) {
          throw "BodyTestOkay";
        }
      }
      catch(e) {
        if (e == "BodyTestOkay") {
            if(is_nn) {
                var hoehe = window.innerHeight;
            } else {
              var hoehe = document.getElementsByTagName("body")[0].clientHeight;
            }
            if (document.getElementsByTagName("body")[0].clientWidth >= eval(<? print $popupwidth; ?>+40-basepatch) && hoehe >= eval(<? print $popupheight; ?>+40-basepatch)) {
                bodytest = "okay";
            }
        }
      }
      if(bodytest == "okay" || top.frames.length == "0") {
        document.write("<div id=\"popup<? print $popupID?>\" style=\"background-color:white; border-width:2px; border-color:silver; border-style:solid; width:"+basewidth+"px; height:"+baseheight+"px; position:absolute; left:20px; top:100px; z-index:10;\" onmousedown=\"dragstart(this);\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" height=\"20\"><tr><td align=\"center\" valign=\"middle\" style=\"background-color:navy;\"><span style=\"font-family:Arial,sans-serif; font-weight:bold; font-size:12px; color:white; cursor:pointer; float:left; margin-left:4px; letter-spacing:4px;\"><\/span><span style=\"font-family:Arial,sans-serif; font-weight:bold; font-size:12px; color:black; background-color:silver; padding-right:4px; padding-left:4px; margin-right:2px; margin-left:2px; border-width:1px; border-top-color:white; border-right-color:black; border-bottom-color:black; border-left-color:white; border-style:solid; cursor:pointer; float:right;\" onclick=\"document.getElementById('popup<? print $popupID?>').style.display='none';document.getElementById('popupiframe<? print $popupID?>').src='';\">X<\/span><span style=\"font-family:Arial,sans-serif; font-weight:bold; font-size:12px; color:black; background-color:silver; padding-right:4px; padding-left:4px; border-width:1px; border-top-color:white; border-right-color:black; border-bottom-color:black; border-left-color:white; border-style:solid; cursor:pointer; float:right;\" onclick=\"minimieren();\">_<\/span><\/td><\/tr><\/table><iframe src=\"<? print $popupurl; ?>\" name=\"popupiframe<? print $popupID?>\" width=\"100%\" height=\""+baseiframe+"\" align=\"center\" scrolling=\"<?PHP print $POPUP_NOSCROLLING ? 'no' : 'yes';?>\" marginheight=\"0\" marginwidth=\"0\" style=\"padding-right:5;padding-bottom:5\" frameborder=\"0\"><\/iframe><\/div>");
      }
 }
draginit();
//-->
</script>

