var dhtmlwindow={

minimizeorder: 0,
zIndexvalue:100,
tobjects: [], //object to contain references to dhtml window divs, for cleanup purposes
lastactivet: {}, //reference to last active DHTML window

init:function(t){
	var domwindow=document.createElement("div") //create dhtml window div
	domwindow.id=t
	domwindow.className="dhtmlwindow"
	var domwindowdata=''
	domwindowdata='<table class="tborder" cellpadding="0" cellspacing="0" border="1">'
	domwindowdata+='<tr><td><table width="100%" cellpadding="6" cellspacing="0"border="0"><tr><td class="tcat">'
	domwindowdata+='<div class="drag-handle">DHTML Window <div class="drag-controls"><img src="images/misc/close.gif" title="Close" /></div></div>'
	domwindowdata+='</td></tr></table><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td class="alt1">'
	domwindowdata+='<div class="drag-contentarea"></div>'
	domwindowdata+='<div class="drag-statusarea"><div class="drag-resizearea">&nbsp;</div></div>'
	domwindowdata+='</td></tr></table></td></tr></table>'
	domwindowdata+='</div>'
	domwindow.innerHTML=domwindowdata
	document.getElementById("dhtmlwindowholder").appendChild(domwindow)
	//this.zIndexvalue=(this.zIndexvalue)? this.zIndexvalue+1 : 100 //z-index value for DHTML window: starts at 0, increments whenever a window has focus
	var t=document.getElementById(t)
	var divs=t.getElementsByTagName("div")
	for (var i=0; i<divs.length; i++){ //go through divs inside dhtml window and extract all those with class="drag-" prefix
		if (/drag-/.test(divs[i].className))
			t[divs[i].className.replace(/drag-/, "")]=divs[i] //take out the "drag-" prefix for shorter access by name
	}
	//t.style.zIndex=this.zIndexvalue //set z-index of this dhtml window
	t.handle._parent=t //store back reference to dhtml window
	t.resizearea._parent=t //same
	t.controls._parent=t //same
	t.onclose=function(){return true} //custom event handler "onclose"
	t.onmousedown=function(){dhtmlwindow.setfocus(this)} //Increase z-index of window when focus is on it
	t.handle.onmousedown=dhtmlwindow.setupdrag //set up drag behavior when mouse down on handle div
	t.resizearea.onmousedown=dhtmlwindow.setupdrag //set up drag behavior when mouse down on resize div
	t.controls.onclick=dhtmlwindow.enablecontrols
	t.show=function(){dhtmlwindow.show(this)} //public function for showing dhtml window
	t.hide=function(){dhtmlwindow.hide(this)} //public function for hiding dhtml window
	t.close=function(){dhtmlwindow.close(this)} //public function for closing dhtml window (also empties DHTML window content)
	t.setSize=function(w, h){dhtmlwindow.setSize(this, w, h)} //public function for setting window dimensions
	t.moveTo=function(x, y){dhtmlwindow.moveTo(this, x, y)} //public function for moving dhtml window (relative to viewpoint)
	t.isResize=function(bol){dhtmlwindow.isResize(this, bol)} //public function for specifying if window is resizable
	t.isScrolling=function(bol){dhtmlwindow.isScrolling(this, bol)} //public function for specifying if window content contains scrollbars
	t.load=function(contenttype, contentsource, title){dhtmlwindow.load(this, contenttype, contentsource, title)} //public function for loading content into window
	this.tobjects[this.tobjects.length]=t
	return t //return reference to dhtml window div
},

open:function(t, contenttype, contentsource, title, attr, recalonload){
	var d=dhtmlwindow //reference dhtml window object
	function getValue(Name){
		var config=new RegExp(Name+"=([^,]+)", "i") //get name/value config pair (ie: width=400px,)
		return (config.test(attr))? parseInt(RegExp.$1) : 0 //return value portion (int), or 0 (false) if none found
	}
	if (document.getElementById(t)==null) //if window doesn't exist yet, create it
		t=this.init(t) //return reference to dhtml window div
	else
		t=document.getElementById(t)
	this.setfocus(t)
	t.setSize(getValue(("width")), (getValue("height"))) //Set dimensions of window
	var xpos=getValue("center")? "middle" : getValue("left") //Get x coord of window
	var ypos=getValue("center")? "middle" : getValue("top") //Get y coord of window
	//t.moveTo(xpos, ypos) //Position window
	if (typeof recalonload!="undefined" && recalonload=="recal" && this.scroll_top==0){ //reposition window when page fully loads with updated window viewpoints?
		if (window.attachEvent && !window.opera) //In IE, add another 400 milisecs on page load (viewpoint properties may return 0 b4 then)
			this.addEvent(window, function(){setTimeout(function(){t.moveTo(xpos, ypos)}, 400)}, "load")
		else
			this.addEvent(window, function(){t.moveTo(xpos, ypos)}, "load")
	}
	t.isResize(getValue("resize")) //Set whether window is resizable
	t.isScrolling(getValue("scrolling")) //Set whether window should contain scrollbars
	t.style.visibility="visible"
	t.style.display="block"
	t.contentarea.style.display="block"
	t.moveTo(xpos, ypos) //Position window 
	t.load(contenttype, contentsource, title)
	return t
},

setSize:function(t, w, h){ //set window size (min is 150px wide by 100px tall)
	t.style.width=Math.max(parseInt(w), 150)+"px"
	t.contentarea.style.height=Math.max(parseInt(h), 100)+"px"
},

moveTo:function(t, x, y){ //move window. Position includes current viewpoint of document
	this.getviewpoint() //Get current viewpoint numbers
	t.style.left=(x=="middle")? this.scroll_left+(this.docwidth-t.offsetWidth)/2+"px" : this.scroll_left+parseInt(x)+"px"
	t.style.top=(y=="middle")? this.scroll_top+(this.docheight-t.offsetHeight)/2+"px" : this.scroll_top+parseInt(y)+"px"
},

isResize:function(t, bol){ //show or hide resize inteface (part of the status bar)
	t.statusarea.style.display=(bol)? "block" : "none"
	t.resizeBool=(bol)? 1 : 0
},

isScrolling:function(t, bol){ //set whether loaded content contains scrollbars
	t.contentarea.style.overflow=(bol)? "auto" : "hidden"
},

load:function(t, contenttype, contentsource, title){ //loads content into window plus set its title
	var contenttype=contenttype.toLowerCase() //convert string to lower case
	if (typeof title!="undefined")
		t.handle.firstChild.nodeValue=title
	if (contenttype=="div"){
		var inlinedivref=document.getElementById(contentsource)
		t.contentarea.innerHTML=(inlinedivref.defaultHTML || inlinedivref.innerHTML) //Populate window with contents of inline div on page
		if (!inlinedivref.defaultHTML)
			inlinedivref.defaultHTML=inlinedivref.innerHTML //save HTML within inline DIV
		inlinedivref.innerHTML="" //then, remove HTML within inline DIV (to prevent duplicate IDs, NAME attributes etc in contents of DHTML window
		inlinedivref.style.display="none" //hide that div
	}
	t.contentarea.datatype=contenttype //store contenttype of current window for future reference
},

setupdrag:function(e){
	var d=dhtmlwindow //reference dhtml window object
	var t=this._parent //reference dhtml window div
	d.etarget=this //remember div mouse is currently held down on ("handle" or "resize" div)
	var e=window.event || e
	d.initmousex=e.clientX //store x position of mouse onmousedown
	d.initmousey=e.clientY
	d.initx=parseInt(t.offsetLeft) //store offset x of window div onmousedown
	d.inity=parseInt(t.offsetTop)
	d.width=parseInt(t.offsetWidth) //store width of window div
	d.contentheight=parseInt(t.contentarea.offsetHeight) //store height of window div's content div
	if (t.contentarea.datatype=="iframe"){ //if content of this window div is "iframe"
		t.style.backgroundColor="#F8F8F8" //colorize and hide content div (while window is being dragged)
		t.contentarea.style.visibility="hidden"
	}
	document.onmousemove=d.getdistance //get distance travelled by mouse as it moves
	document.onmouseup=function(){
		if (t.contentarea.datatype=="iframe"){ //restore color and visibility of content div onmouseup
			t.contentarea.style.backgroundColor="white"
			t.contentarea.style.visibility="visible"
		}
		d.stop()
	}
	return false
},

getdistance:function(e){
	var d=dhtmlwindow
	var etarget=d.etarget
	var e=window.event || e
	d.distancex=e.clientX-d.initmousex //horizontal distance travelled relative to starting point
	d.distancey=e.clientY-d.initmousey
	if (etarget.className=="drag-handle") //if target element is "handle" div
		d.move(etarget._parent, e)
	else if (etarget.className=="drag-resizearea") //if target element is "resize" div
		d.resize(etarget._parent, e)
	return false //cancel default dragging behavior
},

getviewpoint:function(){ //get window viewpoint numbers
	var ie=document.all && !window.opera
	var domclientWidth=document.documentElement && parseInt(document.documentElement.clientWidth) || 100000 //Preliminary doc width in non IE browsers
	this.standardbody=(document.compatMode=="CSS1Compat")? document.documentElement : document.body //create reference to common "body" across doctypes
	this.scroll_top=(ie)? this.standardbody.scrollTop : window.pageYOffset
	this.scroll_left=(ie)? this.standardbody.scrollLeft : window.pageXOffset
	this.docwidth=(ie)? this.standardbody.clientWidth : (/Safari/i.test(navigator.userAgent))? window.innerWidth : Math.min(domclientWidth, window.innerWidth-16)
	this.docheight=(ie)? this.standardbody.clientHeight: window.innerHeight
},

move:function(t, e){
	t.style.left=dhtmlwindow.distancex+dhtmlwindow.initx+"px"
	t.style.top=dhtmlwindow.distancey+dhtmlwindow.inity+"px"
},

enablecontrols:function(e){
	var d=dhtmlwindow
	var sourceobj=window.event? window.event.srcElement : e.target //Get element within "handle" div mouse is currently on (the controls)
	if (/Minimize/i.test(sourceobj.getAttribute("title"))) //if this is the "minimize" control
		d.minimize(sourceobj, this._parent)
	else if (/Restore/i.test(sourceobj.getAttribute("title"))) //if this is the "restore" control
		d.restore(sourceobj, this._parent)
	else if (/Close/i.test(sourceobj.getAttribute("title"))) //if this is the "close" control
		d.close(this._parent)
	return false
},

close:function(t){
	try{
		var closewinbol=t.onclose()
	}
	catch(err){ //In non IE browsers, all errors are caught, so just run the below
		var closewinbol=true
 }
	finally{ //In IE, not all errors are caught, so check if variable isn't defined in IE in those cases
		if (typeof closewinbol=="undefined"){
			alert("An error has occured somwhere inside your \"onclose\" event handler")
			var closewinbol=true
		}
	}
	if (closewinbol){ //if custom event handler function returns true
		t.contentarea.innerHTML=""
		t.style.display="none"
		t.isClosed=true //tell script this window is closed (for detection in t.show())
	}
	return closewinbol
},

setopacity:function(targetobject, value){ //Sets the opacity of targetobject based on the passed in value setting (0 to 1 and in between)
	if (!targetobject)
		return
	if (targetobject.filters && targetobject.filters[0]){ //IE syntax
		if (typeof targetobject.filters[0].opacity=="number") //IE6
			targetobject.filters[0].opacity=value*100
		else //IE 5.5
			targetobject.style.filter="alpha(opacity="+value*100+")"
		}
	else if (typeof targetobject.style.MozOpacity!="undefined") //Old Mozilla syntax
		targetobject.style.MozOpacity=value
	else if (typeof targetobject.style.opacity!="undefined") //Standard opacity syntax
		targetobject.style.opacity=value
},

setfocus:function(t){ //Sets focus to the currently active window
	this.zIndexvalue++
	t.style.zIndex=this.zIndexvalue
	t.isClosed=false //tell script this window isn't closed (for detection in t.show())
	this.setopacity(this.lastactivet.handle, 0.5) //unfocus last active window
	this.setopacity(t.handle, 1) //focus currently active window
	this.lastactivet=t //remember last active window
},

stop:function(){
	dhtmlwindow.etarget=null //clean up
	document.onmousemove=null
	document.onmouseup=null
},

addEvent:function(target, functionref, tasktype){ //assign a function to execute to an event handler (ie: onunload)
	var tasktype=(window.addEventListener)? tasktype : "on"+tasktype
	if (target.addEventListener)
		target.addEventListener(tasktype, functionref, false)
	else if (target.attachEvent)
		target.attachEvent(tasktype, functionref)
},

cleanup:function(){
	for (var i=0; i<dhtmlwindow.tobjects.length; i++){
		dhtmlwindow.tobjects[i].handle._parent=dhtmlwindow.tobjects[i].resizearea._parent=dhtmlwindow.tobjects[i].controls._parent=null
	}
	window.onload=null
}

} //End dhtmlwindow object

document.write('<div id="dhtmlwindowholder"><span style="display:none">.</span></div>') //container that holds all dhtml window divs on page
window.onunload=dhtmlwindow.cleanup
