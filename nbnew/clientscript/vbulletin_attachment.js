/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.7.4
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
function vB_Attachment(A,B){this.attachments=new Array();this.menu_contents=new Array();this.windows=new Array();this.listobjid=A;if(B==""){for(B in vB_Editor){if(YAHOO.lang.hasOwnProperty(vB_Editor,B)){this.editorid=B;break}}}else{this.editorid=(B?B:null)}}vB_Attachment.prototype.popup_exists=function(){if(this.editorid&&((typeof vB_Editor[this.editorid].popups.attach!="undefined"&&vB_Editor[this.editorid].popups.attach!=null)||(!vB_Editor[this.editorid].popupmode&&typeof vB_Editor[this.editorid].buttons.attach!="undefined"&&vB_Editor[this.editorid].buttons.attach!=null))){return true}else{return false}};vB_Attachment.prototype.add=function(D,B,C,A){this.attachments[D]=new Array();this.attachments[D]={filename:B,filesize:C,imgpath:A};this.update_list()};vB_Attachment.prototype.remove=function(A){if(typeof this.attachments[A]!="undefined"){this.attachments[A]=null;this.update_list()}};vB_Attachment.prototype.has_attachments=function(){for(var A in this.attachments){if(YAHOO.lang.hasOwnProperty(this.attachments,A)&&this.attachments[A]!=null){return true}}return false};vB_Attachment.prototype.reset=function(){this.attachments=new Array();this.update_list()};vB_Attachment.prototype.build_list=function(A){var B=fetch_object(A);if(A!=null){while(B.hasChildNodes()){B.removeChild(B.firstChild)}for(var D in this.attachments){if(!YAHOO.lang.hasOwnProperty(this.attachments,D)){continue}var C=document.createElement("div");if(typeof newpost_attachmentbit!="undefined"){C.innerHTML=construct_phrase(newpost_attachmentbit,this.attachments[D]["imgpath"],SESSIONURL,D,Math.ceil((new Date().getTime())/1000),this.attachments[D]["filename"],this.attachments[D]["filesize"])}else{C.innerHTML='<div style="margin:2px"><img src="'+this.attachments[D]["imgpath"]+'" alt="" class="inlineimg" /> <a href="attachment.php?'+SESSIONURL+"attachmentid="+D+"&stc=1&d="+Math.ceil((new Date().getTime())/1000)+'" target="_blank" />'+this.attachments[D]["filename"]+"</a> ("+this.attachments[D]["filesize"]+")</div>"}B.appendChild(C)}}};vB_Attachment.prototype.update_list=function(){this.build_list(this.listobjid);if(this.popup_exists()){vB_Editor[this.editorid].build_attachments_popup(vB_Editor[this.editorid].popupmode?vB_Editor[this.editorid].popups.attach:vB_Editor[this.editorid].buttons.attach,vB_Editor[this.editorid].buttons.attach)}};vB_Attachment.prototype.open_window=function(B,C,A,D){if(typeof (this.windows[D])!="undefined"&&this.windows[D].closed==false){this.windows[D].focus()}else{this.windows[D]=openWindow(B,C,A,"Attach"+D)}return this.windows[D]};