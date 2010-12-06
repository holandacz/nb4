<?php

$class_path = '../../';
//language class
require_once('class.rich_lang.php');

    //extract variables submitted to this page
    @extract($_GET);

    $rich_lang = new rich_lang($lang, false); //text data in current language

?><html>

<HEAD>
    <TITLE>Aide Rich Editor</TITLE>

    <META name="Copyright" content="Copyright (c) 2002-2004 Vyacheslav Smolin">
    <META http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<STYLE type="text/css">
.help_tips{
  font-family: "Verdana";
  font-size: xx-small;
}
.help_text p,div{
  font-family: "Verdana";
  font-size: x-small;
}
td.help_action{
  text-align: center;
  vertical-align: center;
}

a.rich_link {
  text-decoration:none;
  color:blue;
}
</STYLE>

</HEAD>

<BODY
BGCOLOR="#FFFFFF"
TEXT="#000000"
LINK="#0000FF"
VLINK="#840084"
ALINK="#0000FF"
>

<H1>Aide <a href="http://www.richarea.com/" target="_blank" class="rich_link" title="Rich Editor">Editor</a></H1>


<HR>

<!--
<P class="help_text">
<div>
To insert &lt;br&gt; instead of a new paragraph press Shift+Enter instead of Enter.
</div>
</P>
-->

<H2>Actions possibles</H2>

<P>
<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="0" WIDTH="100%" class="help_tips">
<TR BGCOLOR="#CCCCCC">
<TD class="help_action">
<B>Action</B>
</TD>
<TD ALIGN="CENTER" VALIGN="CENTER" WIDTH="100%">
<B>Description</B>
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('FullScreen'); ?>" src="../images/fullscreen.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Bascule l'&eacute;diteur en mode &quot;plein &eacute;cran&quot; (s'il n'y a pas d&eacute;j&agrave; d'autres &eacute;diteurs
  en plein &eacute;cran)<br>
<br>

Si ce mode est actif, le bouton est enfonc&eacute;<br>
<br>

<b>Note: </b>ce mode n'est valable que dans  MSIE 5.5+</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Preview'); ?>" src="../images/preview.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Affiche une pr&eacute;visualisation, dans une fen&ecirc;tre s&eacute;par&eacute;e, du contenu en cours d'&eacute;dition<br>
<br>

Vous permet de voir le contenu comme s'il s'afficherait dans votre navigateur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Find'); ?>" src="../images/find.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour un &quot;Rechercher/Remplacer&quot; dans l'&eacute;diteur<br>
<br>

Vous pouvez faire des recherches de texte sensibles &agrave; la casse, et/ou chercher
des mots entiers</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Cut'); ?>" src="../images/cut.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Coupe la portion de texte s&eacute;lectionn&eacute;e dans le presse-papier</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Copy'); ?>" src="../images/copy.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Copie la portion de texte s&eacute;lectionn&eacute;e dans le presse-papier</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Paste'); ?>" src="../images/paste.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Colle le contenu du presse-papier &agrave; l'endroit du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Undo'); ?>" src="../images/undo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Annule la derni&egrave;re modification faite sur le document</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Redo'); ?>" src="../images/redo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Refait sur le document la derni&egrave;re modification annul&eacute;e</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Bold'); ?>" src="../images/bold.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Met/retire le style gras au texte s&eacute;lectionn&eacute; ou &agrave; l'emplacement
du curseur  (retire le style gras si le texte &eacute;tait d&eacute;j&agrave; en
gras)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Italic'); ?>" src="../images/italic.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Met/retire le style italique au texte s&eacute;lectionn&eacute; ou &agrave; l'emplacement
du curseur  (retire le style italique si le texte &eacute;tait d&eacute;j&agrave; en italique)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Underline'); ?>" src="../images/underline.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Met/retire le style soulign&eacute; au texte s&eacute;lectionn&eacute; ou &agrave; l'emplacement
du curseur (retire le style soulign&eacute; si le texte &eacute;tait d&eacute;j&agrave; soulign&eacute;)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Strikethrough'); ?>" src="../images/strikethrough.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Met/retire le style barr&eacute; au texte s&eacute;lectionn&eacute; ou &agrave; l'emplacement
du curseur (retire le style barr&eacute; si le texte &eacute;tait d&eacute;j&agrave; barr&eacute;)
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SuperScript'); ?>" src="../images/superscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Met/retire le style exposant (indice haut) au texte s&eacute;lectionn&eacute; ou &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SubScript'); ?>" src="../images/subscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Met/retire le style indice (indice bas) au texte s&eacute;lectionn&eacute; ou &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyLeft'); ?>" src="../images/left.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Cadre &agrave; gauche le texte s&eacute;lectionn&eacute; ou le texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyCenter'); ?>" src="../images/center.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Centre le texte s&eacute;lectionn&eacute; ou le texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyRight'); ?>" src="../images/right.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Cadre &agrave; droite le texte s&eacute;lectionn&eacute; ou le texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyFull'); ?>" src="../images/justify.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Justifie le texte s&eacute;lectionn&eacute; ou le texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertOrderedList'); ?>" src="../images/numlist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Transforme le texte s&eacute;lectionn&eacute; ou le texte &agrave; la position du curseur en liste
  num&eacute;rot&eacute;e</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertUnorderedList'); ?>" src="../images/bullist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Transforme le texte s&eacute;lectionn&eacute; ou le texte &agrave; la position
du curseur en liste &agrave; puces</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Outdent'); ?>" src="../images/outdent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
D&eacute;cale &agrave; gauche l'indentation du texte</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Indent'); ?>" src="../images/indent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Indente le texte vers la droite</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertHorizontalRule'); ?>" src="../images/h_line.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ins&egrave;re une ligne horizontale &agrave; l'emplacement du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('RemoveFormat'); ?>" src="../images/rem_format.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Enl&egrave;ve toute mise en forme au texte s&eacute;lectionn&eacute; ou au texte &agrave; l'emplacement du
  curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTable'); ?>" src="../images/table.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er un tableau &agrave; l'emplacement du curseur,
  ou pour modifier les propri&eacute;t&eacute;s du tableau s&eacute;lectionn&eacute;<br>
<br>

Propri&eacute;t&eacute;s du tableau :<br>
Lignes -- nombre de lignes du tableau; colonnes -- nombre de colonnes du tableau;
largeur
-- largeur du tableau; hauteur -- hauteur du tableau; couleur de fond -- couleur
de fond du tableau;
image de fond -- image de fond du tableau; &eacute;cartement -- &eacute;cartement
entre cellules
du tableau (padding);
espacement
-- espace entre le texte et le bord de cellule (spacing); largeur de bordure
--
largeur (&eacute;paisseur) de la bordure autour de chaque cellule; couleur
&eacute;clair&eacute;e --
couleur des c&ocirc;t&eacute;s droit et bas de la bordure de chaque cellule (colorlight);
couleur enfonc&eacute;e --
couleur des c&ocirc;t&eacute;s gauche et haut de la bordure de chaque cellule
(colordark)<br>
<br>

Propri&eacute;t&eacute;s de cellule :<br>
largeur -- largeur de la cellule; hauteur -- hauteur de la cellule; Alignement
vertical
--
Cadrage en vertical du contenu de cellule; couleur -- couleur de fond de la cellule<br>
<br>

Pour &eacute;diter les propri&eacute;t&eacute;s d'un tableau, faire un clic droit sur une de ses bordures
ou s&eacute;lectionner ce tableau et cliquer sur le bouton correspondant dans la barre
de menus<br>
Pour &eacute;diter les propri&eacute;t&eacute;s d'une cellule -- faire un clic droit dans celle-ci<br>
<br>

Pour d&eacute;finir les couleurs, cliquer sur la case &agrave; cocher
correspondante
ou sur
le
carr&eacute;
de couleur &agrave; gauche de la case &agrave; cocher<br>
Pour d&eacute;finir l'image de fond, cliquer sur la case &agrave; cocher correspondante ou
dans
l'espace entre la case et le texte 'Image:'<br>
Pour retirer la couleur ou l'image, d&eacute;cocher la case correspondante<br>
<br>

<b>Note:</b> les valeurs 'lignes' et 'colonnes' ne peuvent &ecirc;tre modifi&eacute;es --
utiliser pour cel&agrave; les boutons d'ajout/suppression de ligne ou colonne</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertRow'); ?>" src="../images/insrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ajoute une ligne apr&egrave;s la ligne en cours</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteRow'); ?>" src="../images/delrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Supprime la ligne o&ugrave; se trouve le curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertColumn'); ?>" src="../images/inscol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ins&egrave;re une colonne apr&egrave;s la colonne en cours</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteColumn'); ?>" src="../images/delcol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Supprime la colonne o&ugrave; se trouve le curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertCell'); ?>" src="../images/inscell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ins&egrave;re une cellule apr&egrave;s la cellule en cours</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteCell'); ?>" src="../images/delcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Supprime la cellule o&ugrave; se trouve le curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('MergeCells'); ?>" src="../images/mergecells.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Fusionne la cellule en cours avec la suivante si elle existe</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SplitCell'); ?>" src="../images/splitcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Scinde la cellule en cours en deux si celle-ci &eacute;tait d&eacute;j&agrave; fusionn&eacute;e (c'est-&agrave;-dire
  si elle avait un &quot;colspan&quot; sup&eacute;rieur &agrave; 1)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateForm'); ?>" src="../images/form.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er un formulaire &agrave; l'emplacement
du curseur<br>
<br>

Propri&eacute;t&eacute;s du formulaire :<br>
Nom -- nom du formulaire; M&eacute;thode -- m&eacute;thode d'envoi des donn&eacute;es
(get/post);
Action -- URL de destination des donn&eacute;es<br>
<br>

Utiliser le bouton &quot;Afficher/Masquer les bordures&quot; pour
rendre visibles tous les formulaires du document<br>
<br>

Pour modier les propri&eacute;t&eacute;s d'un formulaire, cliquer droit sur celui-ci<br>
<br>

Pour &eacute;diter les propri&eacute;t&eacute;s des &eacute;l&eacute;ments du formulaire, cliquer droit sur une
de ses bordures ou s&eacute;lectionner l'&eacute;l&eacute;ment et cliquer sur le bouton correspondant
dans la barre de menus</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateText'); ?>" src="../images/text.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er un champ Texte ('TEXT') &agrave; l'emplacement
du curseur<br>
<br>

Propri&eacute;t&eacute;s du champ Texte :<br>
Nom -- nom du champ; valeur -- contenu initial du champ;
type -- type du champ (text/password); caract&egrave;res maximum --
nombre maximum de caract&egrave;res pouvant &ecirc;tre saisis;
largeur -- largeur du champ, compt&eacute;e en nombre de caract&egrave;res</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTextArea'); ?>" src="../images/textarea.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
  Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er un zone Texte ('TEXTAREA') &agrave; l'emplacement
  du curseur<br>
  <br>
  Propri&eacute;t&eacute;s de la zone Texte :<br>
  Nom -- nom de la zone; valeur -- contenu initial de la zone; lignes --hauteur
  de la zone, compt&eacute;e en nombre de lignes; colonnes -- largeur de la zone, compt&eacute;e
  en nombre de caract&egrave;res</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateButton'); ?>" src="../images/button.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er un bouton &agrave; l'emplacement
du curseur<br>
  <br>
Propri&eacute;t&eacute;s du bouton:<br>
Nom -- nom du bouton; valeur -- valeur initiale du bouton;
type -- type de bouton (button/reset/submit)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateSelect'); ?>" src="../images/select.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er une liste d&eacute;roulante de
valeurs &agrave; l'emplacement
du curseur<br>
<br>

Propri&eacute;t&eacute;s de la liste:<br>
Nom -- Nom de la liste; Taille -- indique le nombre de lignes visibles; Multiple
-- si coch&eacute;, autorise la s&eacute;lection multiple.
Si d&eacute;coch&eacute;, la liste ne permet la s&eacute;lection que d'une seule
valeur;
Choix -- Ensemble des valeurs possibles<br>
<br>

Propri&eacute;t&eacute;s de chaque valeur:<br>
Texte -- Contenu affich&eacute;; Valeur -- Valeur renvoy&eacute;e;
S&eacute;lectionn&eacute; -- Si coch&eacute;, la valeur est pr&eacute;-selectionn&eacute;e<br>
<br>

Boutons:<br>
Ajouter -- Ajoute une valeur avec ses propri&eacute;t&eacute;s; Supprimer -- Supprime la valeur
s&eacute;lectionn&eacute;e de la liste des valeurs;
Mettre &agrave; jour --
Modifie les propri&eacute;t&eacute;s de la valeur s&eacute;lectionn&eacute;e; /\ -- D&eacute;place la valeur s&eacute;lection&eacute;e
d'un cran vers le haut;
\/
--
D&eacute;place la valeur s&eacute;lectionn&eacute;e d'un cran vers le bas</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateHidden'); ?>" src="../images/hidden.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er un champ cach&eacute; &agrave; l'emplacement
du curseur<br>
<br>

Propri&eacute;t&eacute;s du champ cach&eacute;:<br>
Nom -- Nom du champ; Valeur -- Contenu initial du champ</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateCheckBox'); ?>" src="../images/checkbox.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er une case &agrave; cocher &agrave; l'emplacement
du curseur<br>
<br>

Propri&eacute;t&eacute;s de la case &agrave; cocher:<br>
Nom -- nom de la case; Valeur -- valeur renvoy&eacute;e si coch&eacute;e; Coch&eacute; -- Indique
si la case est coch&eacute;e ou non</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateRadio'); ?>" src="../images/radio.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour cr&eacute;er un bouton radio &agrave; l'emplacement
du curseur<br>
<br>

Propri&eacute;t&eacute;s du bouton radio:<br>
Nom -- nom du bouton radio; Valeur -- valeur renvoy&eacute;e si bouton s&eacute;lectionn&eacute;;
Coch&eacute; --
Indique si le bouton est s&eacute;lectionn&eacute; ou non</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FormatBlock'); ?>
</TD>
<TD VALIGN="TOP">
Place un style standard 'Paragraphe/Ent&ecirc;te' sur le texte s&eacute;lectionn&eacute; ou
sur le
texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontName'); ?>
</TD>
<TD VALIGN="TOP">
Change la police du texte s&eacute;lectionn&eacute; ou du texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('ClassName'); ?>
</TD>
<TD VALIGN="TOP">
Place un style de la feuille de style pour le texte s&eacute;lectionn&eacute;</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontSize'); ?>
</TD>
<TD VALIGN="TOP">
Change la taille de la police du texte s&eacute;lectionn&eacute; ou du texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('ForeColor'); ?>" src="../images/fgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Change la couleur du texte s&eacute;lectionn&eacute; ou du texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('BackColor'); ?>" src="../images/bgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Change la couleur de fond du texte s&eacute;lectionn&eacute; ou du texte &agrave; l'emplacement
du curseur</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateImage'); ?>" src="../images/image.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour ajouter une image &agrave; l'emplacement
du curseur ou pour changer les propri&eacute;t&eacute;s d'une image d&eacute;j&agrave; pr&eacute;sente<br>
<br>

La fen&ecirc;tre de dialogue est divis&eacute;e en quatre parties:<br>
le gestionnaire de fichiers (dans la partie gauche de la fen&ecirc;tre), la zone
de
pr&eacute;visualisation (dans le coin en haut &agrave; droite), la zone de propri&eacute;t&eacute;s
(en dessous
de la zone de pr&eacute;visualisation)
et le formulaire de t&eacute;l&eacute;chargement (upload) de fichier (tout &agrave; fait
en bas)<br>
<br>

Le gestionnaire de fichiers affiche une arborescence de r&eacute;pertoires depuis la
racine du r&eacute;pertoire de t&eacute;l&eacute;chargement (upload).<br>
Pour afficher les sous-r&eacute;pertoires et les fichiers d'un dossier, cliquer sur
le '+'. Pour fermer le contenu d'un dossier, cliquer sur le '-'.
<br>
Le nom du r&eacute;pertoire en cours, r&eacute;pertoire de d&eacute;p&ocirc;t des fichiers t&eacute;l&eacute;charg&eacute;s,
est de couleur rouge.
Pour changer ce r&eacute;pertoire, cliquer sur le nouveau nom.<br>
Pour cr&eacute;er un nouveau dossier, cliquer sur le lien 'cr&eacute;er dossier',
saisisser un nom et appuyer sur 'ok' pour confirmer ou 'annuler' pour annuler
l'action.<br>
Cliquer sur les liens 'r'/'x' pour renommer/supprimer le fichier ou le dossier.<br>
<br>
<b>Note:</b> un dossier ne peut &ecirc;tre supprim&eacute; que s'il ne contient plus aucun
fichier ou sous-dossier.<br>
<br>

Quand une image est s&eacute;lectionn&eacute;e dans le gestionnaire de fichiers ou dans le
formulaire de t&eacute;l&eacute;chargement (upload)
(dans le champ d&eacute;finissant le chemin complet local), elle est visible dans la
zone de pr&eacute;visualisation.<br>
<br>

Cliquer sur le bouton de t&eacute;l&eacute;chargement pour s&eacute;lectionner un fichier local &agrave;
t&eacute;l&eacute;charger<br>
<br>

Propri&eacute;t&eacute;s d'image:<br>
Alignement -- alignement horizontal de l'image; Largeur -- largeur de l'image;
Hauteur -- hauteur de l'image; Bordure -- &eacute;paisseur de la bordure d'image;
Espace
vertical
-- espace vertical entre l'image et un texte; Espace horizontal -- espace horizontal
entre l'image et un texte; Alternative -- texte alternatif de l'image (s'affiche
quand
le curseur souris survole l'image);
Source -- d&eacute;finit l'emplacement de l'image<br>
<br>

Pour &eacute;diter les propri&eacute;t&eacute;s d'une image, cliquer droit sur
celle-ci ou s&eacute;lectionner l'image et cliquer sur
le bouton correspondant
dans la barre de menus</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateFlash'); ?>" src="../images/flash.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour ajouter une animation Flash &agrave; l'emplacement
du curseur ou pour changer les propri&eacute;t&eacute;s d'une animation Flash
d&eacute;j&agrave; pr&eacute;sente<br>
<br>

Propri&eacute;t&eacute;s d'une animation Flash:<br>
Alignement -- alignement horizontal de l'animation; Largeur -- largeur de l'animation;
Hauteur -- hauteur de l'animation; Jouer -- si activ&eacute;, l'animation Flash
d&eacute;marre au chargement de la page; Boucle -- si activ&eacute;, l'animation
Flash joue
en boucle;
Menu -- si activ&eacute;, le menu complet Flash est affich&eacute; sur un simple
clic droit;
Couleur de fond -- couleur de fond de l'animation Flash; Qualit&eacute; -- niveau
de
qualit&eacute;
d'affichage de l'animation;
Source -- d&eacute;finit l'emplacement de l'animation<br>
<br>

La gestion des dossiers/sous-dossiers c&ocirc;t&eacute; serveur ou en local ou d'un fichier
Flash
est la m&ecirc;me que celle d'une image<br>
<br>

Pour &eacute;diter les propri&eacute;t&eacute;s d'une animation Flash, cliquer
droit
sur
celle-ci ou s&eacute;lectionner l'animation et cliquer sur le bouton correspondant
dans la barre de menus<br>
<br>

<b>Note: </b>vous ne pouvez faire bouton droit sur les animations Flash qu'avec
MSIE
6.0+</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateLink'); ?>" src="../images/link.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour ajouter un lien HTML l'emplacement
du curseur ou pour changer les propri&eacute;t&eacute;s d'un lien HTML d&eacute;j&agrave; d&eacute;fini<br>
<br>

Propri&eacute;t&eacute;s d'un lien HTML:<br>
Cible -- d&eacute;finit la fen&ecirc;tre cible dans laquelle s'ouvrira le lien;
Lien -- d&eacute;finit
l'URL du lien;
Nom -- nom du lien (sert &agrave; cr&eacute;er une ancre); Titre --
le texte qui s'affiche quand le curseur souris survole le lien<br>
<br>

<b>Note: </b>pour supprimer un lien, ouvrir la fen&ecirc;tre de dialogue du lien, et
supprimer le texte d&eacute;fini dans la propri&eacute;t&eacute; 'lien'<br>
<br>

Pour &eacute;diter les propri&eacute;t&eacute;s d'un lien, s&eacute;lectionner tout
le texte du lien et cliquer sur le bouton correspondant
dans la barre de menus. Pour cr&eacute;er une ancre, il vous suffit de sp&eacute;cifier
une valeur quelconque &agrave; la propri&eacute;t&eacute; 'nom'<br>
<br>

La gestion des dossiers/sous-dossiers c&ocirc;t&eacute; serveur ou en local ou
d'une page HTML est la m&ecirc;me que celle d'une image</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PasteWord'); ?>" src="../images/paste_word.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Colle du contenu de presse-papier venant de MS Word &agrave; l'emplacement du curseur.
  Enl&egrave;ve au pr&eacute;alable toutes les balises et les styles vides</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SwitchBorders'); ?>" src="../images/borders.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Affiche les bordures de tous les tableaux non visibles. Tous les tableaux et
  toutes les cellules ont des bordures grises<br>
Cette action ne modifie pas le contenu du document<br>
<br>

Si ce mode est actif, le bouton correspondant est affich&eacute; 'enfonc&eacute;'</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertChar'); ?>" src="../images/inschar.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour ins&eacute;rer un caract&egrave;re sp&eacute;cial &agrave; l'emplacement
du curseur<br>
<br>

Cliquer simplement sur le symbole souhait&eacute; pour ins&eacute;rer le caract&egrave;re correspondant</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertSnippet'); ?>" src="../images/snippet.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour ins&eacute;rer un ' snippet' (code HTML
sp&eacute;cifique).
Le 'snippet' est visible dans la zone de pr&eacute;visualisation</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('AbsolutePosition'); ?>" src="../images/abspos.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Bascule en mode 'positionnement absolu' pour l'&eacute;l&eacute;ment s&eacute;lectionn&eacute;</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PageProperties'); ?>" src="../images/page.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ouvre une fen&ecirc;tre de dialogue pour changer les propri&eacute;t&eacute;s de la page en
cours dans l'&eacute;diteur<br>
<br>

Propri&eacute;t&eacute;s de la page:<br>
Titre -- titre de la page (texte dans la balise 'title'); Description --
description de la page (contenu du meta-field 'description'); mots-cl&eacute;s
-- ensemble
de mots-cl&eacute;s de la page (contenu du meta-field 'keywords'); Couleur --
couleur de fond de la page; Image -- image de fond de la page; Texte --
couleur du texte; Lien -- couleur des liens HTML; Lien visit&eacute; -- couleur
des
liens HTML d&eacute;j&agrave; visit&eacute;s;
Lien actif -- couleur des liens HTML actifs (non encore visit&eacute;s)<br>
<br>

Pour d&eacute;finir les couleurs, cliquer sur la case &agrave; cocher correspondante, ou sur
le carr&eacute; color&eacute; &agrave; gauche de la case<br>
Pour d&eacute;finir l'image de fond, cliquer sur la case &agrave; cocher ou dans l'espace entre
la case et le texte 'Image:'<br>
Pour retirer une couleur ou une image, d&eacute;cocher la case correspondante<br>
<br>

<b>Note:</b> ces fonctionnalit&eacute;s ne sont disponibles qu'en mode 'page'</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Help'); ?>" src="../images/help.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Affiche cette fen&ecirc;tre d'aide (si elle n'est pas d&eacute;j&agrave; ouverte)
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('Source'); ?>
</TD>
<TD VALIGN="TOP">
Bascule l'&eacute;diteur du mode WYWIWYG au mode source et inversement</TD>
</TR>

</TABLE>
</P>

<P class="help_text">
<div>
<b>Note: </b>certaines des actions ont peut-&ecirc;tre &eacute;t&eacute; d&eacute;sactiv&eacute;es par votre Administrateur
!
</div>
</P>

<HR>

<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0" WIDTH="100%">
<TR>
<TD ALIGN="RIGHT">
<A HREF="#TOP">Haut de la page</A>
</TD>
</TR>
</TABLE>

</BODY>

</HTML>