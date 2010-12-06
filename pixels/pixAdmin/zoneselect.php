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
var gwidth  = <?=$gwidth?>;
var gheight = <?=$gheight?>;
var xpl = <?PHP print $x_plus ? $x_plus : 0; ?>;
var ypl = <?PHP print $y_plus ? $y_plus : 0; ?>;
var pX,pY;
f  = new Array();

<?PHP
    // Geblockte Felder lesen
    if(is_array($f_hidden)) {
        while(list(,$val) = each($f_hidden))
            print 'f['.$val.'] = 2;';
        reset($f_hidden);
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

    if(clickx > gwidth-1 || clicky > gheight-1) return;

    fX     = Math.floor( (clickx - xpl + bsx) / bsx );
    fY     = Math.floor( (clicky - ypl) / bsy );
    Feld   = fX + (fY * 100);
    pX     = fX * bsx + xpl - bsx;
    pY     = fY * bsy + ypl;

    window.status = Feld;
    if(drawing && !f[Feld]) {
        f[Feld] = 1;
        document.spi.ms.value = document.spi.ms.value ? document.spi.ms.value + '-' + Feld : Feld;
        <?PHP
            if($zoneselect_color) {
                print 'jg.setColor("'.$zoneselect_color.'");';
                print 'jg.fillRect(pX, pY, bsx, bsy);';
            } else {
                print 'jg.drawImage(img, pX, pY, bsx, bsy);';
            }
        ?>
        jg.paint();

    } else if(drawing && f[Feld]==2) {
        f[Feld]=3;
        document.spi.md.value = document.spi.md.value ? document.spi.md.value + '-' + Feld : Feld;
        jg.setColor("<?=$GRID['unselection_color']?>");
        jg.fillRect(pX, pY, bsx, bsy);
        jg.paint();
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
