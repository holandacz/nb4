/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.7.3 Patch Level 1
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
function vB_AJAX_ThreadRate_Init(D){var C=fetch_object(D);if(AJAX_Compatible&&(typeof vb_disable_ajax=="undefined"||vb_disable_ajax<2)&&C){for(var B=0;B<C.elements.length;B++){if(C.elements[B].type=="submit"){var E=C.elements[B];var A=document.createElement("input");A.type="button";A.className=E.className;A.value=E.value;A.onclick=vB_AJAX_ThreadRate.prototype.form_click;E.parentNode.insertBefore(A,E);E.parentNode.removeChild(E)}}}}function vB_AJAX_ThreadRate(A){this.formobj=A;this.pseudoform=new vB_Hidden_Form("threadrate.php");this.pseudoform.add_variable("ajax",1);this.pseudoform.add_variables_from_object(this.formobj);this.output_element_id="threadrating_current"}vB_AJAX_ThreadRate.prototype.handle_ajax_response=function(C){if(C.responseXML){var A=C.responseXML.getElementsByTagName("error");if(A.length){if(vBmenu.activemenu=="threadrating"){vBmenu.hide()}alert(A[0].firstChild.nodeValue)}else{var D=C.responseXML.getElementsByTagName("voteavg");if(D.length&&D[0].firstChild&&D[0].firstChild.nodeValue!=""){fetch_object(this.output_element_id).innerHTML=D[0].firstChild.nodeValue}if(vBmenu.activemenu=="threadrating"){vBmenu.hide()}var B=C.responseXML.getElementsByTagName("message");if(B.length){alert(B[0].firstChild.nodeValue)}}}};vB_AJAX_ThreadRate.prototype.rate=function(){if(this.pseudoform.fetch_variable("vote")!=null){YAHOO.util.Connect.asyncRequest("POST","threadrate.php?t="+threadid+"&vote="+PHP.urlencode(this.pseudoform.fetch_variable("vote")),{success:this.handle_ajax_response,failure:this.handle_ajax_error,timeout:vB_Default_Timeout,scope:this},SESSIONURL+"securitytoken="+SECURITYTOKEN+"&"+this.pseudoform.build_query_string())}};vB_AJAX_ThreadRate.prototype.handle_ajax_error=function(A){vBulletin_AJAX_Error_Handler(A);this.formobj.submit()};vB_AJAX_ThreadRate.prototype.form_click=function(){var A=new vB_AJAX_ThreadRate(this.form);A.rate();return false};