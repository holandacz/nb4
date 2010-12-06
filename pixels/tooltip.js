/******************************************************************************************
*   Million Pixel Script (R)
*   (C) 2005-2006 by texmedia.de, all rights reserved.
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
too = tooo = bi = an = null; document.onmousemove = document.onmouseover = toolt;
function toolt(a) {
    if(bi) {
        y = bi.offsetTop+bi.height-20;
        x = bi.offsetLeft+bi.width-12;
    } else {
        y = (document.all) ? window.event.y + document.body.scrollTop  : a.pageY;
        x = (document.all) ? window.event.x + document.body.scrollLeft : a.pageX;
    }
    a = window.innerHeight ? window.innerHeight : document.body.offsetHeight;
    b = window.innerWidth ? window.innerWidth : document.body.offsetWidth;
    if (too != null) {
        if(too.offsetWidth+x+35-document.body.scrollLeft > b) x=document.body.scrollLeft+b-too.offsetWidth-35; too.style.left = (x+12)+"px";
        if(too.offsetHeight+y+40-document.body.scrollTop > a) y=y-too.offsetHeight-30; too.style.top = (y+20)+"px"; }
}
function htoo(i) { if(too)  too.style.display  = "none"; if(bi) bi.style.display = an ? "block" : "none"; tooo = false;}
function stoo(i) { if(tooo) tooo.style.display = "none"; if(bi) bi.style.display = an ? "block" : "none"; too = tooo = document.getElementById(i); too.style.display = "block"; if(bi = document.getElementById(i+"b")) bi.style.display="block"; }
