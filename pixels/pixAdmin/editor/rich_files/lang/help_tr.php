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

<H1><a href="http://www.richarea.com/" target="_blank" class="rich_link" title="Rich Editor">Editor</a> Yardým </H1>

<HR>

<!--
<P class="help_text">
<div>
&lt;br&gt; yerine paragraf eklemek istiyorsanýz Shift+Enter tuþ kombinasyonunu kullanýnýz.
</div>
</P>
-->

<H2>Editörün Özellikleri </H2>

<P>
<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="0" WIDTH="100%" class="help_tips">
<TR BGCOLOR="#CCCCCC">
<TD class="help_action">
<B>Eylem</B>
</TD>
<TD ALIGN="CENTER" VALIGN="CENTER" WIDTH="100%">
<B>Taným</B></TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('FullScreen'); ?>" src="../images/fullscreen.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Editörü tam ekrana çevirir. (Piyasadaki diðer editörlerde henüz bu özellik yok)<br>
<br>

Bu modda iken kullanabileceðiniz butonlar seçilebilir durumdadýr.<br>
<br>

<b>Not: </b>Bu mod MSIE 5.5 ve üzeri browserlar tarafýndan desteklenmektedir </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Preview'); ?>" src="../images/preview.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Bulunduðunuz sayfanýn haricinde bir sayfa açarak oluþturduðunuz içeriðe ön izleme yapmanýzý saðlar. <br>
<br>

 Oluþturduðunuz sayfanýn internet tarayýcýnýzda nasýl göründüðünü bu buton sayesinde görebilirsiniz.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Find'); ?>" src="../images/find.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Editordeki yazýlar içinde aradýðýnýz metni kolay bir þekilde bulup deðiþtirebilirsiniz. <br>
<br>

Büyük küçük harf ve kelime biçimi duyarlýlýðý olduðu için uzun metinlerde editör içi aramalarýnýz da doðru sonuca kolaylýkla ulaþabilirsiniz.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Cut'); ?>" src="../images/cut.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Seçili içeriði kesip hafýzaya alýr</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Copy'); ?>" src="../images/copy.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Seçili içeriði kopyalar ve hafýzaya alýr
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Paste'); ?>" src="../images/paste.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Hafýzadaki içeriði imlecinizle gösterdiðiniz yere yapýþtýrýr.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Undo'); ?>" src="../images/undo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Dökümanda yaptýðýnýz son deðiþikligi geri alýr</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Redo'); ?>" src="../images/redo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Dökümanda yaptýðýnýz son deðiþikligi yineler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Bold'); ?>" src="../images/bold.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili yazýyý kalýnlaþtýrýr. Eðer kalýn ise normal hale getirir.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Italic'); ?>" src="../images/italic.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili yazýyý italik yapar. Eðer italik ise normal hale getirir.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Underline'); ?>" src="../images/underline.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili yazýnýn altýný çizer. Eðer altý çizili ise normal hale getirir.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Strikethrough'); ?>" src="../images/strikethrough.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili yazýnýn üzerini çizer. Eðer üzeri çizili ise normal hale getirir.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SuperScript'); ?>" src="../images/superscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu yerden itibaren üst simge yazabilirsiniz. Birkere daha týklarsanýz normale döner . </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SubScript'); ?>" src="../images/subscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yerden itibaren alt simge yazabilirsiniz. Birkere daha týklarsanýz normale döner . </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyLeft'); ?>" src="../images/left.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Seçili dökümaný yada imlecin bulunduðu pozisyondaki satýrý saða doðru hizalar.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyCenter'); ?>" src="../images/center.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili dökümaný yada imlecin bulunduðu pozisyondaki satýrý ortalar</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyRight'); ?>" src="../images/right.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili dökümaný yada imlecin bulunduðu pozisyondaki satýrý sola doðru hizalar.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyFull'); ?>" src="../images/justify.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili dökümaný iki yana yaslar. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertOrderedList'); ?>" src="../images/numlist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Seçili dökümaný numara sýralý listeye çevirir. Yada imlecin bulunduðu yerde numara sýralý liste oluþturur. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertUnorderedList'); ?>" src="../images/bullist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili dökümaný madde iþaretli listeye çevirir. Yada imlecin bulunduðu yerde madde iþaretli liste oluþturur. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Outdent'); ?>" src="../images/outdent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu pozisyondaki yazýyý dýþarý kaydýrýr. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Indent'); ?>" src="../images/indent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">Ýmlecin bulunduðu pozisyondaki yazýyý içeri kaydýrýr. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertHorizontalRule'); ?>" src="../images/h_line.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">Ýmlecin bulunduðu yere yatay çizgi ekler.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('RemoveFormat'); ?>" src="../images/rem_format.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Seçili yada imlecin bulunduðu poziyondaki  yazýnýn formatýný siler.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTable'); ?>" src="../images/table.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yerde tablo oluþturmak için tablo ekleme penceresini açar yada seçili tabloyu buradan deðiþtirebilirsiniz.<br>
<br>

Tablo Oluþtur/Düzenle:<br>
Satýr sayýsý-- Tablodaki satýr sayýsý; Sütun sayýsý-- Tablodaki sütun sayýsý; Geniþlik --
Tablonun geniþliði; Yükseklik -- Tablonun yüksekliði; Dolgu rengi -- Tablonun arka fon rengi; Dolgu resim -- Tablonun arka fon resmi; Hücre içi boþluk-- Hücre içindeki içeriðin hücre duvarlarýna olan mesafesi; Hücre dýþý boþluk-- Tablo hücreleri arasýndaki boþluk; Çerçeve --
Hücreler etrafýndaki çerçeve kalýnlýðý; Çerçeve ýþýk rengi-- Hücrelerin sað ve alt kenar renkleri; Çerçeve gölge rengi-- Hücrelerin sol ve üst kenar renkleri <br>
<br>

Hücre Özelliklerinin Düzenlenmesi:<br>
Geniþlik -- hücrenin geniþliði; Yükseklik -- hücrenin yüksekliði; Hizalama -- hücrenin dikey olarak hizasý; Renk --hücrenin arka plan rengi <br>
<br>

Tablo özelliklerinde deðiþiklik yapabilmeniz için önce tablo çerçevesini týklayarak tabloyu seçiniz sonra ilgili butona týklayarak iþlemleri yapýnýz. <br>
Hücreleri düzenlemek-- hücreye sað tuþla týklayýnýz.<br>
<br>

Renk ayarý için, ilgili kutucuðu seçip karþýnýza gelen pencerede beðendiðiniz rengi seçin <br>
Arka plan resmi için, ilgili seçimi yapýp karþýnýza gelen resim editöründen beðendiðiniz resmi seçin <br>
Seçimleri iptal etmek için seçim kutularýný boþ býrakýnýz.<br>
<br>

<b>Not:</b> Satýr ve sütun sayýlarýný tekrar düzenleyemessiniz. Fakar yeni satýr ve sütunlar ekleyebilir ve çýkartabilirsiniz </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertRow'); ?>" src="../images/insrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu satýrýn altýna satýr ekler </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteRow'); ?>" src="../images/delrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu satýrý siler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertColumn'); ?>" src="../images/inscol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu sütunun saðýna sütun ekler </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteColumn'); ?>" src="../images/delcol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu sütunu siler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertCell'); ?>" src="../images/inscell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu pozisyondan sonra hücre ekler.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteCell'); ?>" src="../images/delcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu pozisyondaki hücreyi siler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('MergeCells'); ?>" src="../images/mergecells.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu hücrenin yanýnda hücre varsa ikisini birleþtirir.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SplitCell'); ?>" src="../images/splitcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Önceden birleþtirilmiþ hücreler varsa bunlarý ayýrmaya yarar. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateForm'); ?>" src="../images/form.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecin bulunduðu yere Form Ekleme Penceresini çaðýrýr. <br>
<br>

Form özelliklerini ayarlama ve deðiþtirme:<br>
Adý -- Formun adý; Yönetimi -- Formun veri gönderme metodu (get/post);
Eylem -- Týklandýktan sonra verilerin gideceði yer <br>
<br>

Dökümandaki tüm formlarý görmek için çerçeveleri göster butonu aktif olmalýdýr. <br>
<br>

Özelliklerini deðiþtirmek için içine sað tuþla týklayýp deðiþtir diyeceksiniz.<br>
<br>

Diðer form öðelerini de deðiþtirmek için üzerlerine sað tuþla týklayýn ve ilgili butonu seçin. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateText'); ?>" src="../images/text.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yere yazý kutusu ekleme penceresini getirir. <br>
<br>

Yazý kutusu özelliklerini ayarlama ve deðiþtirme:<br>
Adý -- yazý kutusunun adý; deðeri -- yazý kutusunun varsayýlan deðeri;
tipi -- yazý kutusunun tipi(text/password); max. karakter --
yazý kutusununa yazýlabilecek maksimum karaker sayýsý;
karakter geniþliði-- yazý kutusunun geniþliði </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTextArea'); ?>" src="../images/textarea.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yere metin kutusu ekleme penceresini getirir. <br>
<br>
Metin kutusu özelliklerini ayarlama ve deðiþtirme:<br>
Adý -- metin kutusunun adý; Deðeri -- metin kutusunun varsayýlan deðeri;
Satýrlar -- Metin kutusuna kaç satýr karakter yazýlabileceði; Sütunlar -- Metin kutusunun geniþliði
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateButton'); ?>" src="../images/button.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yere Buton ekleme penceresini getirir. <br>
<br>
Buton özelliklerini ayarlama ve deðiþtirme:<br>
Adý -- butonun adý; Deðeri -- varsayýlan deðeri;
tipi -- Butonun tipi (button/reset/submit)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateSelect'); ?>" src="../images/select.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yere listeleme kutusu ekleme penceresini getirir. <br>
<br>
Listeleme kutusu özelliklerini ayarlama ve deðiþtirme:<br>
Adý -- listeleme kutusunun adý; Ebat -- kaç elemandan oluþtuðu; Çoklu -- iþaretli ise çoklu seçim yapýlabilir deðilse tek bir seçim yapýlýr;
Elemanlar -- liste elemanlarý <br>
<br>

Liste kutusunu ayarlama/düzeneme:<br>
Ýçerik -- Elemanýn içeri; Deðeri -- Seçimin varsayýlan deðeri;
Seçili -- iþaretli eleman önseçilmiþ olarak karþýmýza gelir <br>
<br>

Butonlar:<br>
Ekle -- Özelliklerini belirlediðiniz elemanlarý listeye ekler; Sil -- Listedeki seçilen elemaný siler; Güncelle --
Seçili elemanýn özelliklerini deðiþtirebilirsiniz; /\ -- seçili elemaný listede bulunduðu konumun bir üstüne taþýr; \/ -- seçili elemaný listede bulunduðu konumun bir altýna kaydýrýr </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateHidden'); ?>" src="../images/hidden.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yere gizli kutu ekleme penceresini getirir. <br>
<br>
gizli kutu özelliklerini ayarlama ve deðiþtirme:<br>
Adý -- gizli kutunun adý; Deðeri -- gizli kutunun deðeri</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateCheckBox'); ?>" src="../images/checkbox.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yere seçme kutusu ekleme penceresini getirir. <br>
<br>
seçme kutusu özelliklerini ayarlama ve deðiþtirme:<br>
Adý -- seçme kutusunun adý; Deðeri -- seçme kutusunun deðeri; Seçili -- içi dolu ise seçme kutusu seçili olarak gelir </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateRadio'); ?>" src="../images/radio.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu yere seçenek düðmesi ekleme penceresini getirir. <br>
<br>
Seçenek düðmesi özelliklerini ayarlama ve deðiþtirme::<br>
Adý -- seçenek düðmesinin adý; Deðeri -- seçenek düðmesinin deðeri; Seçili -- içi dolu ise seçenek düðmesi seçili olarak gelir </TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FormatBlock'); ?>
</TD>
<TD VALIGN="TOP">
Seçili yazýlar için paragraf özelliklerini ayarlar yada imlecin bulunduðu konum için paragraf özelliði oluþturur.</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontName'); ?>
</TD>
<TD VALIGN="TOP">
Seçili yazýlar için yazý tipini  ayarlar yada imlecin bulunduðu konum için yazý tipini belirler.</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('ClassName'); ?>
</TD>
<TD VALIGN="TOP"> Dökümanýn seçili kýsmýnýn stil biçimlerini ayarlar.</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontSize'); ?>
</TD>
<TD VALIGN="TOP">
Seçili yazýlar için yazý büyüklüðünü  ayarlar yada imlecin bulunduðu konumun için yazý tipini belirler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('ForeColor'); ?>" src="../images/fgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili yazýlarýn rengini  ayarlar yada imlecin bulunduðu konumun için yazý rengini belirler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('BackColor'); ?>" src="../images/bgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Seçili yazýlar için yazý dolgu rengini  ayarlar yada imlecin bulunduðu konumun için yazý dolgu rengini belirler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateImage'); ?>" src="../images/image.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu konuma resim ekleyebilirsiniz yada seçtiðiniz resmi düzenleyebilirsiniz<br>
<br>

Resim penceresi 4 bölümden oluþur. <br>
Dosya Yönetim Alaný (pencerinin solunda), Önizleme Alaný(sað üst köþede), Özellikler Alaný (önizleme alanýnýn altýnda) ve Dosya Yükleme Alaný(Pencerinin altýnda)<br>
<br>

Dosya yönetim alanýnda klasörler en üstte görülür. <br>
Klasörlerin altýndaki klasörlere ve içindeki dosyalara ulaþmak için ilgili klasörün yanýndaki '+' týklayýnýz. Klasör içeriðini kapatmak için gene ilgili klasörde oluþmuþ olan '-' ye týklayýnýz..
<br>
Dosya yükleyeceðiniz klasörü seçtiðiniz zaman renki kýrmýzý olur. Klasörü deðiþtirmek için farklý bir klasörü seçiniz.<br>
Yeni bir klasör oluþturmak için 'Klasör Oluþtur' linkine týklayýn, Klasör adýný girin ve
'Tamam' butonuna týklayýn yada iptal için 'Ýptal' butonunu kullanabilirsiniz. <br>
Dosya ve klasörleri yeniden adlandýrmak için herbirinin yanýnda görülen 'r' linkini, silmek için ise 'x' linklerini kullanýn.<br>
<br>
<b>Not:</b> Bir klasör içinde baþka bir dosya yada klasör varsa o klasör silinemez.<br>
<br>

Dosya yönetim alanýndan bir resim seçildiðinde yada Dosya yükleme alaný ile bilgisayarýmýzdan bir dosya seçildiðinde Ön izleme alanýnda seçim görülür. <br>
<br>

Bilgisayarýnýzdan dosyayý seçip tamam dediðinizde dosya upload edilir.<br>
<br>

Resim özelliklerini ayarlama/düzenleme: <br>
Hizala -- resmin yatay hizalanmasý; Geniþlik -- resim geniþliði;
Yükseklik -- resim yüksekliði; Çerçeve -- Resim çerçevesinin kalýnlýðý; hizalama -- yazý ve resim arasýndaki dikey boþluk; Y.boþluk -- Resim ve yazýlar arasýndaki yatay boþlukt;
Alt yazý-- farenin imleci resim üzerindeyken görünen yazý;
Adresi -- resmin adresi <br>
<br>

Resim özelliklerini düzenlemek için resmi seçin ve sað tuþla týklayarak ilgili butonu týklayýn. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateFlash'); ?>" src="../images/flash.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Ýmlecin bulunduðu konuma Flash objeleri ekleyebilirsiniz yada seçtiðiniz Flash objelerini düzenleyebilirsiniz<br>
<br>

Flash özelliklerini ayarlama/düzenleme:<br>
Hizala -- flash objesinin yatay hizalanmasý; Geniþlik --  flashýn geniþliði;
Yükseklik -- flashýn yüksekliði; Oynat -- Yükleme ile flash filmi oynamaya baþlar;
Döngü --Flash filmi bittiði yerde tekrar baþlar; Menü -- Flash filmi oynarken sað tuþla týkladýðýnýzda menünün görülüp görülmemesini ayarlarsýnýz; Arka rengi-- Flash arka fon rengini deðiþtirir.; Kalite --
flash filminin kalitesi; Adresi --  flash objesinin internet adresi <br>
<br>

Resim düzenleme penceresinde de flash objeleriyle çalýþabilirsiniz. <br>
<br>

Flash özelliklerini deðiþtirmek için editörde flashý seçin ve düzenle deyiniz.<br>
<br>

<b>Not: </b>Flash objesini sað tuþla düzenleyebilmeniz için sisteminiz MSIE 6.0 ve üzeri olmalýdýr.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateLink'); ?>" src="../images/link.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Seçtiðiniz yazý yada dökümana link eklemeye yada seçtiðiniz linki düzenlemeye yarayan pencereyi açar. <br>
<br>

Link özelliklerini ayarlama/düzenleme: <br>
hedef -- linki týkladýðýnýz zaman açýlmasýný istediðiniz yeri belirler; link -- linkin adresi bir URL; Ad --  linkin adý (anchors oluþtururken kullanýlýr); Baþlýk -- farenin imleci linkin üzerinde iken okunur.<br>
<br>

<b>Not: </b>Linki silmek için linki seçip ilgili pencereyi açýn ve 'link' bölümüne yazdýðýnýz URL yi silin<br>
<br>

Anchor oluþturmak için 'ad' kýsmýna yazdýðýnýz deðerler benzersiz olmalýdýr.<br>
<br>

Dosya yükleme, deðiþtirme ve silme iþlemleri ayný resim ekleme /deðiþtirme bölümündekiler gibidir. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PasteWord'); ?>" src="../images/paste_word.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
 MS Word formatýndaki içerikleri imlecinizle belirttiðiniz yere yapýþtýrýr. Yapýþtýrma iþleminden önce eski biçim ve stiller silinir. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SwitchBorders'); ?>" src="../images/borders.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Bazý çerçeve kalýnlýklarý sýfýr olduðu için görülmezler. Tüm tablo ve hücrelerin çerçevelerini gösterir.<br>
Bu eylem dökümanýn görünüþünü deðiþtirmemektedir.<br>
<br>

Bu özellik aktif olduðunda buton basýlý görülür.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertChar'); ?>" src="../images/inschar.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Ýmlecinizin bulunduðu konuma özel karakterler eklemenizi saðlar. <br>
<br>

Eklemek istediðiniz sembolün üzerine týklayýnýz </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertSnippet'); ?>" src="../images/snippet.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Önceden tanýmlanmýþ hazýr ufak kod parçacýklarýný html kodlarýný ekleyebilirsiniz. Seçili örneklerin ön izlemesi görülür. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('AbsolutePosition'); ?>" src="../images/abspos.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP"> Seçili öðe için mutlak bir pozisyon belirler. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PageProperties'); ?>" src="../images/page.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Çalýþtýðýnýz sayfanýn özelliklerini düzenlemeye yarayan pencereyi açar. <br>
<br>
Sayfa özelliklerini düzenleme:<br>
Baþlýk -- sayfanýn baþlýðý sayfanýn en üstünde görülür('title' bölümü); Tanýmlama --
Sayfanýzýn tanýmý(meta taglarýndan 'description' ); Anahtar kelimeler-- Sayfa için anahtar kelimeleri ayarlar( meta taglarýndan 'keywords' ); Renk --
Sayfa arkasý rengi; Resim -- Sayfa arkasý resmi; Yazý --
yazý rengi; link -- link rengi; Geçmiþ link-- Önceden ziyaret edilmiþ linklerin gösterileceði renk; Aktif link -- daha önce ziyaret edilmemiþ þu anda içinde bulunulan linklerin gösterileceði renk. <br>
<br>

Renkleri ayarlamak için ilgili seçme kutusunu iþaretleyin ve karþýnýza gelen pencereden beðendiðiniz rengi seçin. <br>
Arka fon resmini ayarlamak için ilgili seçme kutusunu seçin ve karþýnýza gelen resim düzenleme penceresinden beðendiðiniz resmi seçin.<br>
Renk ve resimleri iptal etmek için seçme kutularýnýn içini boþaltýn. <br>
<br>

<b>Not:</b> Bu özellik sadece Sayfa modunda geçerlidir. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Help'); ?>" src="../images/help.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">Yardým Penceresini açar. (Henüz açýlmadýysa) </TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('Source'); ?>
</TD>
<TD VALIGN="TOP">Kaynak kodu ve design modu arasýnda geçiþ yapmanýzý saðlar </TD>
</TR>

</TABLE>
</P>

<P class="help_text">
<div>
<b>Not: </b>Bazý özellikler sistem yöneticisi tarafýndan kapatýlmýþ olabilir !
</div>
</P>

<HR>

<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0" WIDTH="100%">
<TR>
<TD ALIGN="RIGHT">
<A HREF="#TOP">Dökümanýn baþýna git</A>
</TD>
</TR>
</TABLE>

</BODY>

</HTML>