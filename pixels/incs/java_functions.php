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

//-->
</script>