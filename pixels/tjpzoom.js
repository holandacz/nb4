//
// tjpzoom 2.0 2006.0221.
// details/usage: http://valid.tjp.hu/zoom2/
//

var zoomw=220;
var zoomh=180;
var defzoomamount=2;

var zoomamountstep=1.2;
var zoomsizemin=1000;
var zoomsizemax=100000;
var zoomsizestep=1.2;
var zoomamountmin=1;
var zoomamountmax=12;
var zoomborder=2;
var zoomborderoricolor='#000000';
var zoomborderhotcolor='#ffeb00';
var tooltipstyle='background-color:#ffffe1; border: 1px solid #000000; padding:2px; font-size: 70%; font-family: verdana,arial;';
//if you don't want to show the 'Loading...' tooltip, uncomment the following:
// var tooltipstyle='display:none';


function zoom_click() {
 if(overhotspot!=0) {
  todo=hotspots[overhotspot][4];
  if(todo.indexOf('http://') == 0) { document.location=todo}
  else {eval(todo);}
 }
 return false;
}

function zoom_hidone() {
 hidone=1;
 document.getElementById(zoomid+'_container').style.cursor='default';
 document.getElementById(zoomid+'_tooltip').style.display='none';
}

function zoom_set(evt) {
 var evt = evt?evt:window.event?window.event:null; if(!evt){ return;}
 if(zoomid=='' || parseInt(document.getElementById(zoomid+'_container').style.width) == 0) {return true;}
 //left:
 if(evt.keyCode==37 || evt.keyCode==100) {zoomw/=zoomsizestep; zoomh/=zoomsizestep; if(zoomw*zoomh<zoomsizemin) {zoomh=Math.sqrt(zoomsizemin/zoomratio); zoomw=zoomh*zoomratio;} zoom_init(); zoom_move(); return;}
 //right:
 if(evt.keyCode==39 || evt.keyCode==102) {
  zoomw*=zoomsizestep; zoomh*=zoomsizestep;
  if(zoomw*zoomh>zoomsizemax) {zoomh=Math.sqrt(zoomsizemax/zoomratio); zoomw=zoomh*zoomratio;}
  if(zoomw>objw) {zoomw=objw; zoomh=objw/zoomratio;}
  else if(zoomh>objh) {zoomh=objh; zoomw=objh*zoomratio}
  zoom_init(); zoom_move(); return;
 }
 //down:
 if(evt.keyCode==40 || evt.keyCode==98)  {zoomamount/=zoomamountstep; if(zoomamount<zoomamountmin) {zoomamount=zoomamountmin;} zoom_init(); zoom_move(); return;}
 //up:
 if(evt.keyCode==38 || evt.keyCode==104) {zoomamount*=zoomamountstep; if(zoomamount>zoomamountmax) {zoomamount=zoomamountmax;} zoom_init(); zoom_move(); return;}
 return;
}

function zoom_init() {
 document.getElementById(zoomid+'_clip').style.width=objw*zoomamount+'px';
 document.getElementById(zoomid+'_clip').style.height=objh*zoomamount+'px';
 eval("document.getElementById('"+zoomid+"_trigger').focus();");
}

function zoom_move(evt) {
 if(typeof(evt) == 'object') {
  var evt = evt?evt:window.event?window.event:null; if(!evt){ return;}
  if(evt.pageX) {
   xx=evt.pageX - ffox;
   yy=evt.pageY - ffoy;
  } else {
   if(typeof(document.getElementById(zoomid)+1) == 'number') {return true;} //mert az ie egy ocska kurva
   xx=evt.clientX - ieox;
   yy=evt.clientY - ieoy;
  }
 } else {
  xx=lastxx; yy=lastyy;
 }
 lastxx=xx; lastyy=yy;

 if(hidone == 1) {
  if(overhotspot == 0) {
   for(key in hotspots) {
    if(xx>=hotspots[key][0] && yy >= hotspots[key][1] && xx <= hotspots[key][2] && yy <= hotspots[key][3]) {
     overhotspot=key;
     document.getElementById(zoomid+'_container').style.cursor='pointer';
     if(!isopera) {
      document.getElementById(zoomid+'_tooltip').style.display='block';
      document.getElementById(zoomid+'_tooltip').innerHTML=key;
     }
     zoombordercolor=zoomborderhotcolor;
    }
   }
  } else {
   os=0;
   for(key in hotspots) {
    if(xx>=hotspots[key][0] && yy >= hotspots[key][1] && xx <= hotspots[key][2] && yy <= hotspots[key][3]) { os=1;}
   }
   if(os == 0) {
    overhotspot=0;
    zoombordercolor=zoomborderoricolor;
    document.getElementById(zoomid+'_container').style.cursor='default';
    document.getElementById(zoomid+'_tooltip').style.display='none';
   }
  }
 }

 if(zoomborder>0) {
  zbl=zoomborder;
  zbt=zoomborder;
  zbl=(Math.abs(zoomw/2-xx)-Math.abs(zoomw/2-xx+zoomborder)+zoomborder)/2;
  zbt=(Math.abs(zoomh/2-yy)-Math.abs(zoomh/2-yy+zoomborder)+zoomborder)/2;
  zbr=(Math.abs((xx+zoomw/2+zbl)-objw-zoomborder)-Math.abs((xx+zoomw/2+zbl)-objw)+zoomborder)/2;
  zbb=(Math.abs((yy+zoomh/2+zbt)-objh-zoomborder)-Math.abs((yy+zoomh/2+zbt)-objh)+zoomborder)/2;

  document.getElementById(zoomid+'_clipborder').style.borderLeft=zbl+'px solid '+zoombordercolor;
  document.getElementById(zoomid+'_clipborder').style.borderTop=zbt+'px solid '+zoombordercolor;
  document.getElementById(zoomid+'_clipborder').style.borderBottom=zbb+'px solid '+zoombordercolor;
  document.getElementById(zoomid+'_clipborder').style.borderRight=zbr+'px solid '+zoombordercolor;
 } else {
  zbt=zbr=zbb=zbl=0;
 }

 document.getElementById(zoomid+'_clip').style.margin=(((yy-zoomh/2 > 0)?(zoomh/2-yy*zoomamount):(yy*(1-zoomamount)))+zbt)+'px 0px 0px '+(((xx-zoomw/2 > 0)?(zoomw/2-xx*zoomamount):(xx*(1-zoomamount)))+zbl)+'px';
 document.getElementById(zoomid+'_container').style.margin=(((yy-zoomh/2 > 0)?(yy-zoomh/2):(0))-zbt)+'px 0px 0px '+(((xx-zoomw/2 > 0)?(xx-zoomw/2):(0))-zbl)+'px';


 document.getElementById(zoomid+'_padder').style.height=((Math.abs(yy)-Math.abs(yy-zoomh/2)+zoomh/2)/2+zoomh/2)+'px';
 document.getElementById(zoomid+'_padder').style.width=((Math.abs(xx)-Math.abs(xx-zoomw/2)+zoomw/2)/2+zoomw/2)+'px';
 w2=((xx+zoomw/2<objw)?((zoomw/2<xx)?(zoomw):(zoomw/2+xx)):(zoomw/2+objw-xx)); if(w2<0) {w2=0;}
 document.getElementById(zoomid+'_container').style.width=(w2+zbl+zbr)+'px';

 h2=((yy+zoomh/2<objh)?((zbt+zoomh/2<yy)?(zoomh):(zoomh/2+yy)):(zoomh/2+objh-yy)); if(h2<0) {h2=0;}
 document.getElementById(zoomid+'_container').style.height=(h2+zbt+zbb)+'px';
 return false;
}

function zoom_off() {
 if(zoomid != '') {
  document.getElementById(zoomid+'_container').style.width='0px';
  document.getElementById(zoomid+'_container').style.height='0px';
 }
 zoomid='';
 hotspots={};
}

function countoffset() {
 ieox=0;ieoy=0;
 for(zmi=0;zmi<50;zmi++) {
  zme='document.getElementById(zoomid)';
  for(zmj=1;zmj<=zmi;zmj++) {
   zme+='.offsetParent';
  }
  if(eval(zme)+1 == 1) {zmi=100} else {ieox+=eval(zme+'.offsetLeft'); ieoy+=eval(zme+'.offsetTop');}
 }
 ffox=ieox;
 ffoy=ieoy;
 ieox-=document.body.scrollLeft;
 ieoy-=document.body.scrollTop;
}

function zoom_on(evt,ow,oh,lowres,highres) {
 var evt = evt?evt:window.event?window.event:null; if(!evt){ return;}
 thisid=lowres+highres+ow+oh;
 thisid='zoom_'+thisid.replace(/[^a-z0-9]/ig,'')
 if(zoomid==thisid) {return;}
 if(lastover == thisid) {zoomid=thisid; countoffset(); zoom_move(); return;}
 if(typeof(highres) == typeof(nemistudom)) {highres=lowres;}
 zoomamount=defzoomamount;
 objw=ow;
 objh=oh;
 zoomid=thisid;
 if(evt.pageX) {
  evt.target.parentNode.id=thisid;
  countoffset();
  lastxx=evt.pageX - ffox;
  lastyy=evt.pageY - ffoy;
 } else {
  evt.srcElement.parentNode.id=thisid;
  countoffset();
  lastxx=evt.clientX - ieox;
  lastyy=evt.clientY - ieoy;
 }
 lastover=thisid;

 document.getElementById(thisid).style.width=ow+'px';
 document.getElementById(thisid).style.height=(oh+((isopera)?(5):(0)))+'px';
 document.getElementById(thisid).style.overflow='hidden';
 document.getElementById(thisid).innerHTML=
 '<textarea id="'+thisid+'_trigger"  style="font-size:1px;position:absolute;width:100px;height:'+((isopera)?('4px'):('0; filter:alpha(opacity=0);'))+';border:0;margin:0;padding:0;overflow:hidden;z-index:0;"/></textarea>'+
 '<div style="text-align:left;position:absolute; overflow:hidden; width:0; height:0; border:0;" id="'+thisid+'_container" onmousemove="zoom_move(event);" onmouseout="zoom_off()">'+
 '<div id="'+thisid+'_clipborder" style="position:absolute;overflow:hidden;border:'+zoomborder+'px solid '+zoombordercolor+';">'+
 '<div style="width:0;height:0;overflow:hidden;" id="'+thisid+'_padder"></div>'+
 '</div>'+
 '<img src="'+highres+'" alt="" id="'+thisid+'_clip" style="padding:0;margin:0;border:0;" onload="zoom_hidone();"/></div>'+
 '<div style="text-align:left;position:absolute; z-index:2;float:left; '+tooltipstyle+'" id="'+thisid+'_tooltip">Loading...</div>'+
 '<img src="'+lowres+'" id="'+thisid+'_pic" alt="" style="padding:0;margin:0;border:0;z-index:10;width:'+ow+'px; height: '+oh+'px"/>'
;
 if(zoomw>objw) {zoomw=objw; zoomh=objw/zoomratio;}
 else if(zoomh>objh) {zoomh=objh; zoomw=objh*zoomratio}
 zoom_init();
 zoom_move('');
 document.getElementById(zoomid+'_container').style.cursor='wait'; hidone=0;
 if(isopera) {
  document.onkeypress=zoom_set;
 } else {
  if(document.all) {
   document.onkeydown=zoom_set;
  } else {
   window.captureEvents(Event.KEYDOWN);
   window.onkeydown = zoom_set;
  }
 }
 return false;
}

var zoomamount=defzoomamount; var objw;var objh;var zoomid=''; var zoomratio=zoomw/zoomh; var ieox=0; var ieoy=0; var ffox=0; var ffoy=0; var hidone=0; var overhotspot=0; var lastover=''; var zoombordercolor=zoomborderoricolor; if(navigator.userAgent.indexOf('Firefox/1.0') != -1)  {zoomborder=0;}
var isopera=(navigator.userAgent.toLowerCase().indexOf('opera') != -1); if(isopera) { defzoomamount=2; zoomamountmax=3; tooltipstyle='display:none';} var hotspots;