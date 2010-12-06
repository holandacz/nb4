<?php
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


if($logged_in) { ?>

<script type="text/javascript">
<!--

 fenster = new Array();
 function Fenster(url,name,option,width,height) {
     if(width)  var left = (screen.availWidth-width)/2;
     if(height) var top  = (screen.availHeight-height)/2;
     var scroll  = 'no';
     var resize  = 'no';
     var status  = 'no';
     var tool    = 'no';
     if(option) {
         if(option.search(/c/)!= -1)
             scroll = 'yes';
         if(option.search(/r/)!= -1)
             resize = 'yes';
         if(option.search(/s/)!= -1)
             status = 'yes';
         if(option.search(/t/)!= -1)
             tool   = 'yes';
     }
     var options = 'scrollbars='+scroll+',toolbar='+tool+',directories=no,Location=no,resizable='+resize+',status='+status;
     fens = window.open(url,name,options+',width='+width+',height='+height+',left='+left+',top='+top);
     fenster[name] = fens;
     fenster[name].focus();
 }



   function changeSize(gridtype) {
        if(gridtype>0) {
            document.all.xsize.setAttribute("readonly","readonly","false");
            document.all.ysize.setAttribute("readonly","readonly","false");
            gridedit.grid_template.options.value='';
            document.all.xsize.style.backgroundColor = "#D3D3D3";
            document.all.ysize.style.backgroundColor = "#D3D3D3";
            document.all.grid_template.style.backgroundColor = "#D3D3D3";
            document.all.grid_template.style.color = "#666666";
            document.all.xsize.style.color = "#666666";
            document.all.ysize.style.color = "#666666";
            gridedit.x.value='';
            gridedit.y.value='';
            document.all.pixel_x.innerText='';
            document.all.pixel_y.innerText='';
            document.all.blocksize_x.setAttribute("readonly","readonly","false");
            document.all.blocksize_y.setAttribute("readonly","readonly","false");
            document.all.blocksize_x.style.backgroundColor = "#D3D3D3";
            document.all.blocksize_y.style.backgroundColor = "#D3D3D3";
            document.all.blocksize_x.style.color = "#666666";
            document.all.blocksize_y.style.color = "#666666";
            gridedit.blocksize_x.value='10';
            gridedit.blocksize_y.value='10';
        } else {
            document.all.xsize.removeAttribute("readonly","false");
            document.all.ysize.removeAttribute("readonly","false");
            document.all.xsize.style.backgroundColor = "#FFFFFF";
            document.all.ysize.style.backgroundColor = "#FFFFFF";
            document.all.grid_template.style.backgroundColor = "#FFFFFF";
            document.all.grid_template.style.color = "#000000";
            document.all.xsize.style.color = "#000000";
            document.all.ysize.style.color = "#000000";
            gridedit.x.value=<?=(int)$grids[$_GET['grided']-1]['x']?>;
            gridedit.y.value=<?=(int)$grids[$_GET['grided']-1]['y']?>;
            document.all.pixel_x.innerText='<?=(int)$grids[$_GET['grided']-1]['x']*$grids[$_GET['grided']-1]['blocksize_x']?>';
            document.all.pixel_y.innerText='<?=(int)$grids[$_GET['grided']-1]['y']*$grids[$_GET['grided']-1]['blocksize_x']?>';
            document.all.blocksize_x.removeAttribute("readonly","false");
            document.all.blocksize_y.removeAttribute("readonly","false");
            document.all.blocksize_x.style.backgroundColor = "#FFFFFF";
            document.all.blocksize_y.style.backgroundColor = "#FFFFFF";
            document.all.blocksize_x.style.color = "#000000";
            document.all.blocksize_y.style.color = "#000000";
        }
   }


   function tpreview(template,type) {
       var x,y;
       x = gridedit.x.value*gridedit.blocksize_x.value;
       y = gridedit.y.value*gridedit.blocksize_y.value;

       var sizes = new Array(<?=count($gridsizes)?>);
       <?PHP while(list($key,$val) = each($gridsizes)) {
                print 'sizes['.$key.'] = new Array(2);'."\n";
                print 'sizes['.$key.']["x"] = "'.$val['x']."\";\n";
                print 'sizes['.$key.']["y"] = "'.$val['y']."\";\n";
             }
       ?>
       if(template=='' && type==0) {
           template = '_standard.png';
       } else if(!template && type>0) {
           x = sizes[type]["x"];
           y = sizes[type]["y"];
       }
       Fenster('?tpreview=' + template + '&type=' + type , 'tpreview' , '' , x , y );
   }

//-->
</script>

<?PHP
}
?>