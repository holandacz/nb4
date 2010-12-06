<?PHP
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
?>
<script type="text/javascript">

var jg = new jsGraphics("pixfl");
var drawing = false;

var img  = '<?=$blockimg?>';
var bsx  = <?=$BLOCKSIZE_X?>;
var bsy  = <?=$BLOCKSIZE_Y?>;
var maxf = <?=$max_felder?>;
var gwidth  = <?=$gwidth?>;
var gheight = <?=$gheight?>;
var xpl = <?PHP print $x_plus ? $x_plus : 0; ?>;
var ypl = <?PHP print $y_plus ? $y_plus : 0; ?>;
var x1 = <?PHP print $x1 ? $x1 : 0; ?>;
var y1 = <?PHP print $y1 ? $y1 : 0; ?>;
var x2 = <?PHP print $x2 ? $x2 : 0; ?>;
var y2 = <?PHP print $y2 ? $y2 : 0; ?>;
var BANNER = <?PHP print $BANNER ? 'true' : 'false'; ?>;
var pX,pY;
f  = new Array();
fe = new Array();

<?PHP
    // Besetzte Felder in JavaArray
    if(count($spi_felder_array)>0) {
        for($i=0;$i<count($spi_felder_array);$i++)
            print 'f['.$spi_felder_array[$i].'] = 1;';
    }
    // geblockte Felder in JavaArray
    if(count($blocked_felder)>0) {
        for($i=0;$i<count($blocked_felder);$i++)
            print 'f['.$blocked_felder[$i].'] = 1;';
    }
    // Ausgewählte Felder in JavaArray
    for($i=0;$i<count($f_hidden);$i++) {
        print 'fe['.$i.'] = '.$f_hidden[$i].';';
        print 'f['.$f_hidden[$i].'] = 2;';
    }

?>

i = document.getElementById('pixb');
i.onmousemove = i.onmouseover = selpix;

function selpix(a) {
    y = (document.all) ? window.event.y  : a.pageY;
    x = (document.all) ? window.event.x  : a.pageX;
    a = window.innerHeight ? window.innerHeight : document.body.offsetHeight;
    b = window.innerWidth ? window.innerWidth : document.body.offsetWidth;

    clickx = (document.all) ? i.offsetLeft+x : x-Gridpos(i).x;
    clicky = (document.all) ? i.offsetTop+y  : y-Gridpos(i).y;

    if(clickx > gwidth-1 || clicky > gheight-1 || (BANNER && clickx>=x1 && clickx<=x2 && clicky>=y1 && clicky<=y2) ) return;

    fX     = Math.floor( (clickx - xpl + bsx) / bsx );
    fY     = Math.floor( (clicky - ypl) / bsy );
    Feld   = fX + (fY * 100);
    pX     = fX * bsx + xpl - bsx;
    pY     = fY * bsy + ypl;

    window.status = Feld;
    if(drawing && !f[Feld] && checknb(Feld) && (!maxf || fe.length < maxf)) {
        f[Feld] = 1;
        fe[fe.length] = Feld;
        document.spi.ms.value = document.spi.ms.value ? document.spi.ms.value + '-' + Feld : Feld;
        <?PHP
            if(!$GRID[(int)$_REQUEST['gr']]['selection_color']) {
                print 'jg.drawImage(img, pX, pY, bsx, bsy);';
            } else {
                print 'jg.setColor("'.$GRID[(int)$_REQUEST['gr']]['selection_color'].'");';
                print 'jg.fillRect(pX, pY, bsx, bsy);';
            }
        ?>
        jg.paint();

    } else if(drawing && f[Feld]==2) {
        f[Feld]=3;
        document.spi.md.value = document.spi.md.value ? document.spi.md.value + '-' + Feld : Feld;
        jg.setColor("<?=$GRID[(int)$_REQUEST['gr']]['unselection_color']?>");
        jg.fillRect(pX, pY, bsx, bsy);
        jg.paint();
    }
}

function checknb(Feld) {
    if(fe.length < 1) return true;
    for(e=0;e<fe.length;e++) {
        if(fe[e]+100 == Feld ||
           fe[e]-100 == Feld ||
           fe[e]+1 == Feld ||
           fe[e]-1 == Feld)
            return true;
    }
}

function startdraw() {
    drawing = true;
}

function stopdraw() {
    drawing = false;
}

function Gridpos(grid) {
    var pos = new Object;
    if (document.getBoxObjectFor) {
        var bo = document.getBoxObjectFor(grid);
        pos.x = bo.x;
        pos.y = bo.y;
        pos.w = bo.width;
        pos.h = bo.height;
    } else if (grid.getBoundingClientRect) {
        var sct, scl;
        if (document.documentElement && document.documentElement.scrollTop) {
            sct = document.documentElement.scrollTop;
            scl = document.documentElement.scrollTop;
        } else {
            sct = document.body.scrollTop;
            scl = document.body.scrollLeft;
        }
        var rect = grid.getBoundingClientRect();
        pos.x = rect.left + scl;
        pos.y = rect.top + sct;
        pos.w = rect.right - rect.left;
        pos.h = rect.bottom - rect.top;
    }
    return pos;
}

</script>
