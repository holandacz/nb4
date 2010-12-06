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
var vB_TabCtrls=new Array();vBulletin.events.systemInit.subscribe(function(){if(vBulletin.elements.vB_TabCtrl){var B,A;for(B=0;B<vBulletin.elements.vB_TabCtrl.length;B++){A=vBulletin.elements.vB_TabCtrl[B];vB_TabCtrls[A[0]]=new vB_TabCtrl(A[0],A[1],A[2],A[3])}vBulletin.elements.vB_TabCtrl=null}});function vB_TabCtrl(L,H,J,K){var D,F,M,C,I,A,B;console.log("Init vB_TabCtrl for %s with initial tab id = %s",L,H);this.tab_container=YAHOO.util.Dom.get(L).parentNode;this.selected_tab_id=H;if(K){this.ajax_load_url=K.split("?");this.ajax_load_url[1]=this.ajax_load_url[1].replace(/\{(\d+)(:\w+)?\}/gi,"%$1$s")}this.tab_count=0;F=YAHOO.util.Dom.getElementsByClassName("content_block","div",L);if(F.length){console.log("vB_TabCtrl :: Found %d tab content blocks",F.length);this.tab_element=document.createElement("ul");YAHOO.util.Dom.addClass(this.tab_element,"tab_list");I=-1;for(D=0;D<F.length;D++){this.tab_count++;C=F[D].getAttribute("id");A=YAHOO.util.Dom.get("collapseobj_"+C);if(A){YAHOO.util.Dom.setStyle(A,"display","block")}M=this.tab_element.appendChild(document.createElement("li"));M.id=C+"_tab";M.innerHTML=YAHOO.util.Dom.getElementsByClassName("block_name","span",F[D].getElementsByTagName("h4")[0])[0].innerHTML;YAHOO.util.Dom.addClass(YAHOO.util.Dom.getElementsByClassName("block_row","div",F[D])[0],"block_title");M.setAttribute("tab_id",C);YAHOO.util.Dom.addClass(M,"tborder tcat");if(C==this.selected_tab_id){I=D}}F[0].parentNode.insertBefore(this.tab_element,F[0]);B=F[0].parentNode.insertBefore(document.createElement("div"),F[0]);YAHOO.util.Dom.addClass(B,"tborder tcat tab_header")}this.tabs=this.tab_element.getElementsByTagName("li");YAHOO.util.Dom.setStyle(this.tab_element,"height",this.tabs[0].offsetHeight+"px");for(D=0;D<this.tab_count;D++){this.init_tab(this.tabs[D],false);this.tabs[D].setAttribute("fixed_width",this.tabs[D].offsetWidth);YAHOO.util.Dom.setStyle(this.tabs[D],"width",this.tabs[D].getAttribute("fixed_width")+"px");YAHOO.util.Dom.setStyle(this.tabs[D],"display","none")}this.overflow_tab=this.tab_element.appendChild(document.createElement("li"));this.overflow_tab.setAttribute("dir","ltr");YAHOO.util.Dom.addClass(this.overflow_tab,"tborder thead overflow_tab");YAHOO.util.Event.on(this.overflow_tab,"click",this.show_menu,this,true);if(J){this.overflow_tab.innerHTML=J}else{this.overflow_tab.appendChild(document.createTextNode("�"))}if(I==-1){I=0;this.selected_tab_id=F[0].getAttribute("id")}this.switch_tab(this.selected_tab_id,true);var G=YAHOO.util.Dom.get(this.selected_tab_id+"_tab").offsetHeight;var E=0;for(D=0;D<this.tabs.length;D++){if(this.tabs[D].getAttribute("tab_id")!=this.selected_tab_id){E=this.tabs[D].offsetHeight;break}}var N=document.createElement("style");N.type="text/css";if(N.styleSheet&&is_ie){N.styleSheet.cssText="ul.tab_list li.thead { top:"+(G-E)+"px; } div.tab_header { background-position:1px -"+(G-2)+"px; }"}else{N.appendChild(document.createTextNode("ul.tab_list li.thead { top:"+(G-E)+"px; } div.tab_header { background-position:0px -"+(G-1)+"px; }"))}document.getElementsByTagName("head")[0].appendChild(N);YAHOO.util.Event.on(window,"resize",this.resize,this,true);YAHOO.util.Event.on(document,"click",this.hide_menu,this,true)}vB_TabCtrl.prototype.click_tab=function(B){var A=YAHOO.util.Event.getTarget(B);while(A.getAttribute("tab_id")==null&&A.tagName.toUpperCase()!="HTML"){A=A.parentNode}this.switch_tab(A.getAttribute("tab_id"));YAHOO.util.Event.stopEvent(B)};vB_TabCtrl.prototype.switch_tab=function(D,E){if(this.selected_tab_id!=D||E){var C,B,A;this.hide_menu();this.selected_tab_id=D;this.selected_tab=YAHOO.util.Dom.get(this.selected_tab_id+"_tab");for(C=0;C<this.tab_count;C++){B=this.tabs[C].getAttribute("tab_id");YAHOO.util.Dom[(B==this.selected_tab_id?"addClass":"removeClass")](this.tabs[C],"tcat");YAHOO.util.Dom[(B!=this.selected_tab_id?"addClass":"removeClass")](this.tabs[C],"thead");A=YAHOO.util.Dom.get(B);YAHOO.util.Dom.setStyle(A,"display",(B==this.selected_tab_id?"block":"none"));if(B==this.selected_tab_id&&YAHOO.util.Dom.get("collapseobj_"+B).innerHTML==""){this.load_tab_content(this.selected_tab_id)}}console.log("vB_TabCtrl :: Switched to '%s' tab",this.selected_tab_id);this.resize()}return false};vB_TabCtrl.prototype.resize=function(F){this.hide_menu();var H,D,C,I,A,B,E,G,J;YAHOO.util.Dom.setStyle(this.overflow_tab,"display","block");H=this.overflow_tab.offsetWidth+10;YAHOO.util.Dom.setStyle(this.overflow_tab,"display","none");I=0;for(D=0;D<this.tab_count;D++){YAHOO.util.Dom.setStyle(this.tabs[D],"display","block");H+=Math.max(parseInt(this.tabs[D].getAttribute("fixed_width")),this.tabs[D].offsetWidth);if(this.tab_container.offsetWidth>H){I++}else{YAHOO.util.Dom.setStyle(this.tabs[D],"display","none")}A=this.tabs[D].getAttribute("tab_id");if(A==this.selected_tab_id&&YAHOO.util.Dom.getStyle(this.tabs[D],"display")=="none"){console.info("vB_TabCtrl :: Moving selected tab... Found the selected tab: %d, %s",D,A);B=this.tabs[D];for(C=D;C>=0;C--){if(YAHOO.util.Dom.getStyle(this.tabs[C],"display")!="none"){console.log("vB_TabCtrl :: Replace tab %d (%s) with %d (%s)",C,this.tabs[C].getAttribute("tab_id"),D,A);E=this.tabs[C];G=this.tab_element.insertBefore(B.cloneNode(true),E);YAHOO.util.Event.removeListener(B,"click",this.click_tab,this,true);YAHOO.util.Event.removeListener(B.firstChild,"click",this.click_tab,this,true);B.parentNode.removeChild(B);YAHOO.util.Dom.setStyle(G,"display","block");this.init_tab(G,false);return this.resize()}}}}this.display_tab_count=I;J=new Array();for(D=this.display_tab_count;D<this.tabs.length;D++){if(this.tabs[D]!=this.overflow_tab){J.push(this.tabs[D].innerHTML)}}this.overflow_tab.setAttribute("title",J.join(",\n"));YAHOO.util.Dom.setStyle(this.overflow_tab,"display",(this.tab_count>I?"block":"none"));for(D=0;D<this.tabs.length;D++){YAHOO.util.Dom.setStyle(this.tabs[D],"backgroundPosition",(this.tabs[D].getAttribute("tab_id")==this.selected_tab_id?(this.tabs[D].offsetLeft*-1)+"px 0px":"0px 0px"))}};vB_TabCtrl.prototype.show_menu=function(D){YAHOO.util.Event.stopEvent(D);if(this.menu_open){this.hide_menu();return }var C,B,E,A,F;this.menu=document.createElement("ul");this.menu.setAttribute("id",this.tab_element.id+"menu");YAHOO.util.Dom.addClass(this.menu,"vbmenu_popup");YAHOO.util.Dom.addClass(this.menu,"tab_popup");menu_element_names=new Array();B=0;for(C=this.display_tab_count;C<this.tab_count;C++){E=this.menu.appendChild(this.tabs[C].cloneNode(true));YAHOO.util.Dom.setStyle(E,"display","block");YAHOO.util.Dom.setStyle(E,"width","auto");YAHOO.util.Dom.removeClass(E,"tborder");YAHOO.util.Dom.removeClass(E,"tcat");YAHOO.util.Dom.removeClass(E,"thead");YAHOO.util.Dom.addClass(E,"vbmenu_option");if(B++<1){YAHOO.util.Dom.addClass(E,"first")}this.init_tab(E,true)}this.tab_container.appendChild(this.menu);A=YAHOO.util.Dom.getXY(this.overflow_tab);F=A[0]-E.offsetWidth+this.overflow_tab.offsetWidth;F=(F<fetch_viewport_info()["x"])?A[0]:F;YAHOO.util.Dom.setX(this.menu,F);YAHOO.util.Dom.setY(this.menu,A[1]+this.overflow_tab.offsetHeight-1);this.menu_open=true};vB_TabCtrl.prototype.hide_menu=function(){try{this.menu.parentNode.removeChild(this.menu)}catch(A){}this.menu_open=false};vB_TabCtrl.prototype.init_tab=function(A,B){YAHOO.util.Event.on(A,"click",this.click_tab,this,true);content_element=YAHOO.util.Dom.get(A.getAttribute("tab_id"));YAHOO.util.Dom.setStyle(YAHOO.util.Dom.getElementsByClassName("block_title","h4",content_element)[0],"display","none")};vB_TabCtrl.prototype.is_block_title=function(A){return(A.getAttribute("rel")=="Block")};vB_TabCtrl.prototype.handle_ajax_error=function(A){vBulletin_AJAX_Error_Handler(A)};