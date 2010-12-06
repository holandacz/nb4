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
error_reporting(0);
$VERSIONS[basename(__FILE__)] = "3.0";

if(eregi(basename(__FILE__), $HTTP_SERVER_VARS[REQUEST_URI]))
    die ("You can't access this file directly! Please go to the startpage!");

    @ini_set('include_path',".");

    if($Ext=='.gif')        $bild = imagecreatefromgif($image);
    elseif($Ext=='.jpg')    $bild = imagecreatefromjpeg($image);
    elseif($Ext=='.png')    $bild = imagecreatefrompng($image);

    // Maße des Bildes bestimmen
    $b_x = imagesx($bild);
    $b_y = imagesy($bild);

    //-------------------------------------------------------------------
    // Bild als Originalgröße in Session schreiben
    // um es später auf Platte abzulegen.
    if($GRID[(int)$_REQUEST['gr']]['image_saveorig']) {

        //-> Anpassung auf Größe
        $smallwidth  = (int)$GRID[(int)$_REQUEST['gr']]['image_resize_x'];
        $smallheight = (int)$GRID[(int)$_REQUEST['gr']]['image_resize_y'];
        if($smallwidth && $smallheight) {

            $orig_height    = round($b_y/($b_x/$smallwidth));
            $orig_width     = $smallwidth;
            if($orig_height > $smallheight) {
                $orig_width  = round($b_x/($b_y/$smallheight));       // ... je nach längerer Seite
                $orig_height = $smallheight;
            }
            $xcenter = round(($smallwidth-$orig_width)/2);
            $ycenter = round(($smallheight-$orig_height)/2);

            // GD-Lib 2.0.1 oder < 2  ??
            if(!$dest_small = @imagecreatetruecolor($smallwidth,$smallheight))
                $dest_small  = imagecreate($smallwidth,$smallheight);

            if(!$transparenter_farbwert = imagecolorsforindex($bild,imagecolortransparent($bild))) {
                $colorTransparent = imagecolorallocate($dest_small, 255,42,212);
                imagefill($dest_small, 0,0,$colorTransparent);
                $transparenter_farbwert = array(255,42,212);
            }

            if(!@imagecopyresampled($dest_small,$bild,$xcenter,$ycenter,0,0,$orig_width,$orig_height,$b_x,$b_y))
                imagecopyresized($dest_small,$bild,$xcenter,$ycenter,0,0,$orig_width,$orig_height,$b_x,$b_y);

            imagetruecolortopalette($dest_small, false, 256);
            imagecolortransparent($dest_small, imagecolorclosest($dest_small,$transparenter_farbwert[0],$transparenter_farbwert[1],$transparenter_farbwert[2]));

            ob_start();
            if($Ext=='.gif')        imagegif($dest_small);
            else {                  imagepng($dest_small); $Ext = '.png'; }
            //-> Hinweis: jpg rausgenommen, da keine Transparenz unterstützt für Anpassung der Größe

            $_SESSION['origimg'] = base64_encode(ob_get_contents());
            ob_end_clean();
            imagedestroy($dest_small);

        } else {
            // Keine Größenanpassung
            if($adminprocess || $logineditprocess) {
                $move_uploaded_file = true;

            } else {
                if(move_uploaded_file($image,'incs/temp/u'.Session_id())) {
                    $_SESSION['origimg'] = base64_encode(@file_get_contents('incs/temp/u'.Session_id()));
                    @unlink('incs/temp/u'.Session_id());
                } else {
                    $_SESSION['origimg'] = base64_encode(@file_get_contents($image));
                }

            }
        }
    }
    $_SESSION['bildext'] = $Ext;
    //-------------------------------------------------------------------

    // Bild erstellen
    if(!$dest = @imagecreatetruecolor($breite+$BLOCKSIZE_X,$hoehe+$BLOCKSIZE_Y))
        $dest = imagecreate($breite+$BLOCKSIZE_X,$hoehe+$BLOCKSIZE_Y);


    if(!$transparenter_farbwert = imagecolorsforindex($bild,imagecolortransparent($bild))) {
        $colorTransparent = imagecolorallocate($dest, 255,42,212); // Pinke Farbe
        imagefill($dest,0,0,$colorTransparent);
        $transparenter_farbwert = array(255,42,212);
    }

    // Originalbild verkleinern
    if(!$src = @imagecreatetruecolor($breite+$BLOCKSIZE_X,$hoehe+$BLOCKSIZE_Y))
        $src = imagecreate($breite+$BLOCKSIZE_X,$hoehe+$BLOCKSIZE_Y);

    if(!@imagecopyresampled($src,$bild,0,0,0,0,$breite+$BLOCKSIZE_X,$hoehe+$BLOCKSIZE_Y,$b_x,$b_y))
        imagecopyresized($src,$bild,0,0,0,0,$breite+$BLOCKSIZE_X,$hoehe+$BLOCKSIZE_Y,$b_x,$b_y);

    imagedestroy($bild);

    while(list(,$v) = each($xy)) {
        if(!@imagecopyresized($dest,$src,$v['x'],$v['y'],$v['x'],$v['y'],$BLOCKSIZE_X,$BLOCKSIZE_Y,$BLOCKSIZE_X,$BLOCKSIZE_Y))
            imagecopyresized($dest,$src,$v['x'],$v['y'],$v['x'],$v['y'],$BLOCKSIZE_X,$BLOCKSIZE_Y,$BLOCKSIZE_X,$BLOCKSIZE_Y);
    }

    imagetruecolortopalette($dest, false, 256);
    if(imagecolorstotal($dest)>1)
        imagecolortransparent($dest, imagecolorclosest($dest,$transparenter_farbwert[0],$transparenter_farbwert[1],$transparenter_farbwert[2]));

    ob_start();
        imagepng($dest);
    $temp_pic = base64_encode(ob_get_contents());
    ob_end_clean();
    imagedestroy($dest);
    imagedestroy($src);


    return $temp_pic;

?>
