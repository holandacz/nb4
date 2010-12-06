<?php

$class_path = '../../';
//language class
require_once('class.rich_lang.php');

    //extract variables submitted to this page
    @extract($_GET);

    $rich_lang = new rich_lang($lang, false); //text data in current language

?><HTML>

<HEAD>
    <TITLE>Rich Editor Help</TITLE>

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
</STYLE>

</HEAD>

<BODY
BGCOLOR="#FFFFFF"
TEXT="#000000"
LINK="#0000FF"
VLINK="#840084"
ALINK="#0000FF"
>

<H1><a href="http://www.richarea.com/" target="_blank" class="rich_link" title="Rich Editor">Editor</a> Help</H1>


<HR>

<!--
<P class="help_text">
<div>
Gebruik Enter voor een nieuwe alinea en Shift + Enter voor een regeleinde.
</div>
</P>
-->

<H2>Beschikbare mogelijkheden</H2>

<P>
<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="0" WIDTH="100%" class="help_tips">
<TR BGCOLOR="#CCCCCC">
<TD class="help_action">
<B>Keuze</B>
</TD>
<TD ALIGN="CENTER" VALIGN="CENTER" WIDTH="100%">
<B>Beschrijving</B>
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('FullScreen'); ?>" src="../images/fullscreen.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Zet de editor in de stand volledig scherm (alleen mogelijk wanneer geen andere editor al in deze stand gebruikt wordt).<br>
<br>
Met volledig scherm in gebruik is deze knop ingedrukt.<br>
<br>
<b>Opmerking: </b>Volledig scherm is alleen mogelijk in MSIE 5.5+.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Preview'); ?>" src="../images/preview.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont in een apart venster een voorafblik van de tekst die bewerkt wordt.<br>
<br>
Gebruik dit om te bekijken hoe de inhoud er in een browservenster uit zal zien.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Find'); ?>" src="../images/find.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Opent een venster om in de geopende pagina teksten te zoeken en/of te vervangen.<br><br>

Hoofdlettergevoelig en op hele woorden zoeken is mogelijk.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Cut'); ?>" src="../images/cut.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Verwijdert het geselecteerde deel uit het document en plaats het op het klembord.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Copy'); ?>" src="../images/copy.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Kopieert het geselecteerde deel van het document naar het klembord.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Paste'); ?>" src="../images/paste.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voegt de inhoud van het klembord op de positie van de cursor in.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Undo'); ?>" src="../images/undo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Maakt de laatste wijziging ongedaan.</TD>
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Redo'); ?>" src="../images/redo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voert de laatste ongedaan gemaakte wijziging weer uit.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Bold'); ?>" src="../images/bold.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wisselt de geselecteerde tekst tussen gewoon of vet of wisselt tussen gewone of vette tekst typen op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Italic'); ?>" src="../images/italic.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wisselt de geselecteerde tekst tussen gewoon of cursief of wisselt tussen gewone of cursieve tekst typen op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Underline'); ?>" src="../images/underline.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wisselt de geselecteerde tekst tussen gewoon of onderstreept of wisselt tussen gewone of onderstreepte tekst typen op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Strikethrough'); ?>" src="../images/strikethrough.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wisselt de geselecteerde tekst tussen gewoon of doorgehaald of wisselt tussen gewone of doorgehaalde tekst typen op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SuperScript'); ?>" src="../images/superscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wisselt de geselecteerde tekst tussen gewoon of superscript of wisselt tussen gewone of superscript tekst typen op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SubScript'); ?>" src="../images/subscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wisselt de geselecteerde tekst tussen gewoon of subscript of wisselt tussen gewone of subscript tekst typen op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyLeft'); ?>" src="../images/left.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Lijnt het geselecteerde deel van het document of de tekst op de huidige cursorplaats, links uit.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyCenter'); ?>" src="../images/center.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Centreert het geselecteerde deel van het document of de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyRight'); ?>" src="../images/right.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Lijnt het geselecteerde deel van het document of de tekst op de huidige cursorplaats, rechts uit.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyFull'); ?>" src="../images/justify.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Vult het geselecteerde deel van de tekst of de tekst op de huidige cursorplaats, uit.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertOrderedList'); ?>" src="../images/numlist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Maakt van het geselecteerde deel van de tekst of van de tekst op de huidige cursorplaats, een genummerde lijst met opsommingstekens.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertUnorderedList'); ?>" src="../images/bullist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Maakt van het geselecteerde deel van de tekst of van de tekst op de huidige cursorplaats, een lijst met opsommingstekens.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Outdent'); ?>" src="../images/outdent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Verkleint het inspringen van het geselecteerde deel van het document of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Indent'); ?>" src="../images/indent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Vergroot het inspringen van het geselecteerde deel van het document of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertHorizontalRule'); ?>" src="../images/h_line.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voegt op de huidige cursorplaats een horizontale lijn in.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('RemoveFormat'); ?>" src="../images/rem_format.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Verwijdert de opmaak van het geselecteerde deel van de tekst of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTable'); ?>" src="../images/table.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om de gekozen tabel te bewerken of om op de huidige cursorplaats een tabel aan te maken.<br>
<br>
De mogelijke tabel-eigenschappen zijn:<br>
Rijen -- het aantal rijen in de tabel; kolommen -- het aantal kolommen in de tabel;
breedte -- breedte van de gehele tabel; hoogte -- hoogte van de gehele tabel;
achtergrond kleur -- achtergrond kleur van de tabel; Afbeelding -- achtergrondplaat van de tabel;
celmarge -- de ruimte rondom de inhoud van de cellen; celafstand -- de afstand van de cellen onderling;
randdikte -- dikte van de cel- en tabelranden;
Lichte rand -- de lichte kleur van de onder- en rechtercelranden;
Donkere rand -- de donkere kleur van de linker- en bovenranden van de cellen.<br>
<br>
De mogelijke cel-eigenschappen zijn:<br>
Breedte -- breedte van de cel; hoogte -- hoogte van de cel;
vertikale uitlijning -- vertical uitlijning van de inhoud van de cel;
kleur -- achtergrondkleur van de cel.<br>
<br>
Klik op de rand van de tabel en dan op de overeenkomstige knop in de werkbalk om de eigenschappen van de tabel aan te passen.<br>
Klik met de rechtermuisknop in een cel om de eigenschappen van een cel aan te passen.<br>
<br>
Vink het kleurkeuzevakje af of klik op het kleurvakje links daarvan om kleuren te wijzigen.<br>
Vink het keuzevakje bij afbeelding af of klik de ruimte links ervan aan om een afbeelding te kiezen.<br>
Uitzetten van de achtergrondkleur of de afbeelding gaat door het vinkje weg te halen.<br>
<br>
<b>Opmerking:</b> de aantallen rijen en kolommen van een bestaande tabel kunnen alleen gewijzigd worden met de knoppen in de werkbalk en niet in het eigenschappenvenster.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertRow'); ?>" src="../images/insrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voegt een rij in onder de rij waarin de cursor is geplaatst.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteRow'); ?>" src="../images/delrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Verwijdert de rij waarin de cursor is geplaatst.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertColumn'); ?>" src="../images/inscol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voegt een kolom in rechts van de rij waarin de cursor is geplaatst.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteColumn'); ?>" src="../images/delcol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Verwijdert de kolom waarin de cursor is geplaatst.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertCell'); ?>" src="../images/inscell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voegt een cel in rechts van de cel waarin de cursor is geplaatst.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteCell'); ?>" src="../images/delcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Verwijdert de cel waarin de cursor is geplaatst.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('MergeCells'); ?>" src="../images/mergecells.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voegt de cel waarin de cursor is geplaatst samen met de cel rechts daarvan (cel rechts moet bestaan).
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SplitCell'); ?>" src="../images/splitcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Splitst eerder samengevoegde cellen op. De op te splisen cel moet meer dan 1 cel breed zijn).
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateForm'); ?>" src="../images/form.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om het gekozen formulier te bewerken of om op de huidige cursorplaats een formulier aan te maken.<br>
<br>
De mogelijke formulier-eigenschappen zijn:<br>
Naam -- naam van het formulier; methode -- de manier waarop de gegevens worden doorgegeven (get/post);
actie -- de bestemming van de gegevens van het formulier<br>
<br>
Gebruik de knop '<?php echo $rich_lang->item('SwitchBorders'); ?>' om alle formulieren op de pagina zichtbaar te maken.
<br>
Klik met de rechtermuistoets in het formulier om de eigenschappen van het formulier te wijzigen.<br>
<br>
Selecteer een bestanddeel van het formulier en klik de overeenkomstige knop in de menubalk om de eigenschappen van dat bestanddeel te bewerken.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateText'); ?>" src="../images/text.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een tekstveld op de huidige cursorplaats te plaatsen of te bewerken.<br>
<br>
De verschillende tekstveld-eigenschappen zijn:<br>
Naam -- naam van het veld; inhoud -- de beginwaarde van het tekstveld;
type -- type van het tekstveld (tekst/paswoord); Maximale invoer -- maximum aantal letters dat in het veld kan worden ingegeven;
grootte tekstveld -- breedte van het tekstveld (in aantal letters).
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTextArea'); ?>" src="../images/textarea.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een tekstvak op de huidige cursorplaats te plaatsen of te bewerken.<br>
<br>
De verschillende tekstvak-eigenschappen zijn:<br>
Naam -- naam van het veld; inhoud -- de beginwaarde van het tekstveld;
regels -- hoogte van het tekstvak in aantal regels; kolommen -- breedte van het tekstvak (in aantal letters).
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateButton'); ?>" src="../images/button.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een uitvoerknop op de huidige cursorplaats te plaatsen of te bewerken.<br>
<br>
De verschillende knop-eigenschappen zijn:<br>
Naam -- naam van de knop; inhoud -- waarde/naam die de knop krijgt;
type -- soort uitvoerknop (button/reset/submit).
</TD>
</TR>


<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateSelect'); ?>" src="../images/select.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een keuzemenu op de huidige cursorplaats te plaatsen of te bewerken.<br>
<br>

Eigenschappen van het keuzemenu:<br>
Naam -- de naam van het keuzemenu; Regels -- bepaalt het aantal zichbare regels van het menu;
Meerkeuze -- wanneer afgevinkt kunnen meerdere items gekozen worden uit de lijst, anders slechts 1 item;
Items -- de lijst met beschikbare keuzes van het menu<br>
<br>

Het vullen en wijzigen van de eigenschappen van het keuzemenu:<br>
Tekst -- Geef hier de weer te geven tekst van een item in; Waarde -- de bijbehorende waarde van dit item;
Geselecteerd -- als dit is afgevinkt zal bij het starten van het menu dit item alvast geselecteerd zijn<br>
<br>

De verschillende bedieningsknoppen:<br>
Voeg toe -- voegt een item toe met de gegevens uit de velden Tekst, Waarde en Geselecteerd;
Verwijder -- verwijdert een item uit de lijst;
Werk bij -- werkt een item bij met de gegevens uit de velden Tekst, Waarde en Geselecteerd;
/\ -- verplaatst een item omhoog in de rij; \/ -- verplaatst een item omlaag

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateHidden'); ?>" src="../images/hidden.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een niet zichtbaar veld op de huidige cursorplaats te plaatsen of te bewerken.<br>
<br>
De verschillende veld-eigenschappen zijn:<br>
Naam -- naam van het veld; inhoud -- de waarde van het niet zichtbare veld;
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateCheckBox'); ?>" src="../images/checkbox.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een keuzevakje op de huidige cursorplaats te plaatsen of te bewerken.<br>
<br>
De eigenschappen van het keuzevakje zijn:<br>
Naam -- naam van het keuzevakje; inhoud -- de waarde die het keuzevakje krijgt;
afgevinkt -- als dit is afgevinkt zal het keuzevakje ook afgevinkt op de pagina geplaatst worden, anders is het niet afgevinkt.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateRadio'); ?>" src="../images/radio.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een keuzerondje op de huidige cursorplaats te plaatsen of te bewerken.<br>
<br>
De eigenschappen van het keuzerondje zijn:<br>
Naam -- naam van het keuzerondje; inhoud -- de waarde die het keuzerondje krijgt;
afgevinkt -- als dit is afgevinkt zal een geselecteerd keuzerondje op de pagina geplaatst worden, anders is het niet geselecteerd.
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FormatBlock'); ?>
</TD>
<TD VALIGN="TOP">
Bepaalt de alineaopmaak van de geselecteerde alinea of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontName'); ?>
</TD>
<TD VALIGN="TOP">
Wijzigt het lettertype van het geselecteerde deel van de tekst of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('ClassName'); ?>
</TD>
<TD VALIGN="TOP">
Bepaalt de tekstopmaak van het geselecteerde deel van de tekst.
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontSize'); ?>
</TD>
<TD VALIGN="TOP">
Wijzigt de lettergrootte van het geselecteerde deel van de tekst of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('ForeColor'); ?>" src="../images/fgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wijzigt de kleur van het geselecteerde deel van de tekst of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('BackColor'); ?>" src="../images/bgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Wijzigt de kleur van de achtergrond van het geselecteerde deel van de tekst of van de tekst op de huidige cursorplaats.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateImage'); ?>" src="../images/image.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een afbeelding op de huidige cursorplaats te plaatsen of de eigenschappen te bewerken.<br>
<br>
Het eigenschappenvenster bestaat uit 4 delen:<br>
bestandsbeheer (linsboven), voorafblik (rechtsboven), eigenschappen (rechtsonder) en bestanden op internet plaatsen (linksonder)<br>
<br>
Bestandsbeheer toont de mappenstructuur van de op het internet geplaatste afbeeldingen beginnend in de moedermap.<br>
Klik op de '+' om submappen en daarin geplaatste afbeeldingen te zien. Klik op '-' om de submap weer te sluiten.<br>
De naam van de active map, waar afbeeldingen in terecht komen, is rood gekleurd. Om een andere map te kiezen kan deze gewoon aangeklikt worden.<br>
Klik op 'map maken' om een nieuwe map aan te maken.<br>
Klik op 'r'/'x' om de mappen of de bestanden resp. van naam te wijzigen of te verwijderen.<br>
<br>
<b>Opmerking:</b> een map kan alleen verwijderd worden wanneer deze geheel leeg is.<br>
<br>
Wanneer een afbeelding is geselecteerd in het bestandsbeheer of een nieuw bestand middels 'bladeren' is opgegeven zal dit rechtsboven in het eigenschappenvenster getoond worden.<br>
<br>
Wanneer de keus wordt bevestigd zal de afbeelding in de pagina geplaatst worden terwijl een nieuwe afbeelding tevens in de gekozen map op het internet geplaatst worden.<br>
<br>
De mogelijke eigenschappen van een afbeelding zijn:<br>
Uitlijnen -- de horizontale uitlijning; breedte -- de breedte van de afbeelding;
hoogte -- de hoogte van de afbeelding; kaderdikte -- de dikte van de rand rondom de afbeelding;
vertikale ruimte -- de ruimte vertikaal tussen tekst en afbeelding; horizontale ruimte -- de horizontake ruimte tussen tekst en afbeelding;
tekstlabel -- alternatieve tekst die getoond wordt waneer de muis op de afbeelding rust;
Lokatie -- de link naar de eventueel externe afbeelding<br>
<br>
Klik met de rechtermuistoets op de afbeelding of selecteer de afbeelding en klik de overeenkomstige knop in de werkbalk om de eigenschappen aan te passen.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateFlash'); ?>" src="../images/flash.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een flashbestand te plaatsen of om de eigenschappen te bewerken.<br>
<br>
De mogelijke eigenschappen van een flashbestand zijn:<br>
Uitlijnen -- de horizontale uitlijning van het getoonde flasbestand; breedte -- breedte van het flashbestand;
hoogte -- hoogte van het flashbestand;
afspelen -- wanneer dit is afgevinkt begint de flash direct af te spelen wanneer de pagina is geladen;
herhalen -- wanneer dit is afgevinkt zal de flash zich steeds herhalen;
menu -- wanneer dit is afgevinkt wordt het flashmenu getoond na het rechts aanklikken;
achtergrondkleur -- achtergrondkleur van het getoond flashbestand;
kwaliteit -- bepaalt de kwaliteit van het flashbestand; Verwijslink -- link naar extern flashbestand<br>
<br>
Het bestandsbeheer van de flash is gelijk aan dat van de afbeeldingen.<br>
<br>
Klik met de rechtermuistoets op de flash of selecteer de flash en klik de overeenkomstige knop in de werkbalk om de eigenschappen aan te passen.<br>
<br>
<b>Opmerking:</b> met de rechter muistoets op de flash klikken geeft alleen in MSIE 6.0+ de mogelijkheid het eigenschappenvenster te openen. Gebruik in oudere browsers de knop in de werkbalk.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateLink'); ?>" src="../images/link.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster om een hyperlink op de huidige cursorplaats te plaatsen of de eigenschappen te bewerken.<br>
<br>
De mogelijke eigenschappen van een link zijn:<br>
Doel -- bepaalt of de link in het huidige venster of in een nieuw venster wordt geopend;
link -- is het URL-adres van de link; naam -- is de naam van de link (gebruik dit ook om bladwijzers
op de pagina aan te brengen); hint -- deze tekst wordt getoond wanneer de muis op de link rust
<br><br>
<b>Opmerking:</b> een link verwijderen gebeurt door in het eigenschappenvenster
het URL-adres te verwijderen.<br>
<br>
Het bestandsbeheer van hyperlinks is gelijk aan dat van de afbeeldingen.<br>
<br>
Selecteer de gehele gelinkte text of een deel er van en klik daarop met de
rechtermuistoets of klik de overeenkomstige knop in de werkbalk, om de link aan
te passen. Om een bladwijzer aan te maken kan worden volstaan met alleen de naam.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PasteWord'); ?>" src="../images/paste_word.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Voegt op het klembord geplaatste MS Word tekst in. Hierbij wordt zgn. style-opmaak verwijderd, overige kenmerken en tekstindeling (opsommingstekens, tabellen e.d.) blijven behouden.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SwitchBorders'); ?>" src="../images/borders.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Maakt alle onzichtbare tabel- en formulierranden zichtbaar (d.m.v. stippellijnen). Dit verandert niets aan het document zelf.<br>
<br>
Wanneer de onzichtbare tabel- en formulierranden worden weergegeven is deze knop ingedrukt.<br>
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertChar'); ?>" src="../images/inschar.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een keuzevenster om een letter of symbool op de huidige cursorplaatst in te voegen.<br>
<br>
Op het gewenste teken klikken is voldoende.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertSnippet'); ?>" src="../images/snippet.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Geeft een keuzemenu om een HTML-fragment op de huidige cursorplaats in te voegen. In het keuzemenu worden de fragmenten met een voorafblik getoond.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('AbsolutePosition'); ?>" src="../images/abspos.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Zet absolute positionering voor het geselecteerde element aan of uit
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PageProperties'); ?>" src="../images/page.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont een eigenschappenvenster van de in de editor geopende pagina.<br>
<br>
De mogelijke pagina-eigenschappen zijn:<br>
Titel -- titel van de pagina (wordt geplaatst tussen de titel-tags);
beschrijving -- beschrijving van de pagina (wordt geplaatst in het meta-veld 'description');
trefwoorden -- serie woorden om de pagina te kenmerken voor zoekmachines (wordt geplaatst in het meta-veld 'keywords');
kleur -- achtergrondkleur van de pagina; afbeelding -- achtergrondafbeelding van de pagina; tekst -- kleur van de tekst;
link -- kleur van de hyperlinks; bezochte link -- kleur van bezochte hyperlinks;
actieve links -- kleur van de geactiveerde (nog niet bezochte) hyperlinks<br>
<br>
Vink het kleurkeuzevakje af of klik op het kleurvakje links daarvan om kleuren te wijzigen.<br>
Vink het keuzevakje bij afbeelding af of klik de ruimte links ervan aan om een afbeelding te kiezen.<br>
Uitzetten van de achtergrondkleur of de afbeelding gaat door het vinkje weg te halen.<br>
<br>
<b>Opmerking:</b> pagina-eigenschappen bewerken is alleen beschikbaar in 'modus pagina'.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Help'); ?>" src="../images/help.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toont dit helpscherm (als het niet al open is)
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('Source'); ?>
</TD>
<TD VALIGN="TOP">
Wisselt de editor tussen WatJeZietIsWatJeKrijgt en de brontekst.
</TD>
</TR>

</TABLE>
</P>

<P class="help_text">
<div>
<b>Opmerking: </b>sommige van de mogelijkheden kunnen door de websitebeheerder zijn uitgezet!
</div>
</P>

<HR>

<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0" WIDTH="100%">
<TR>
<TD ALIGN="RIGHT">
Naar de <A HREF="#TOP">bovenzijde</A> van dit helpbestand.
</TD>
</TR>
</TABLE>

</BODY>

</HTML>