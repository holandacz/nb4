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

    <META name="Copyright" content="Copyright (c) 2002-2005 Vyacheslav Smolin">
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

<H1><a href="http://www.richarea.com/" target="_blank" class="rich_link" title="Rich Editor">Editor</a> Ayuda </H1>


<HR>

<!--
<P class="help_text">
<div>
To insert &lt;br&gt; instead of a new paragraph press Shift+Enter instead of Enter.
</div>
</P>
-->

<H2>Acciones Disponibles </H2>

<P>
<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="0" WIDTH="100%" class="help_tips">
<TR BGCOLOR="#CCCCCC">
<TD class="help_action">
<B>Acci&oacute;n</B>
</TD>
<TD ALIGN="CENTER" VALIGN="CENTER" WIDTH="100%">
<B>Descripci&oacute;n</B>
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('FullScreen'); ?>" src="../images/fullscreen.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Cambia el editor a pantalla completa. (si no hay otro editor utilizando la funci&oacute;n de pantalla completa)<br>
<br>

Si este modo est&aacute; activo, el bot&oacute;n correspondiente figura como oprimido. <br>
<br>

<b>Nota: </b>este modo est&aacute; disponible solo en  MSIE 5.5+
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Preview'); ?>" src="../images/preview.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Muestra una presentaci&oacute;n preliminar del contenido que est&aacute; editando en una ventana separada.<br>
<br>

Util&iacute;celo para ver, como se ver&aacute; el contenido que est&aacute; editando en un Explorador.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Find'); ?>" src="../images/find.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para buscar/reemplazar texto dentro del editor.<br>
<br>

Puede buscar diferenciando may&uacute;sculas/min&uacute;sculas y/o solo palabra completa</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Cut'); ?>" src="../images/cut.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Corta la secci&oacute;n seleccionada del documento y la guarda en el portapapeles.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Copy'); ?>" src="../images/copy.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Copia la secci&oacute;n seleccionada del documento.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Paste'); ?>" src="../images/paste.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Pega el contenido del portapapeles en la posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Undo'); ?>" src="../images/undo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Deshace el &uacute;ltimo cambio hecho al documento.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Redo'); ?>" src="../images/redo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Rehace el &uacute;ltimo cambio que se le deshizo al documento con la funci&oacute;n anterior. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Bold'); ?>" src="../images/bold.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Activa/desactiva el estilo &quot;Negrita&quot; al texto seleccionado y se lo aplicar&aacute; al nuevo texto desde la posici&oacute;n actual del cursor.
(lo desactiva si el texto est&aacute; en &quot;Negrita&quot; y viceversa)
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Italic'); ?>" src="../images/italic.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Activa/desactiva el estilo &quot;Cursiva&quot; al texto seleccionado y se lo aplicar&aacute; al nuevo  texto desde la posici&oacute;n actual del cursor. (lo desactiva si el texto est&aacute; en &quot;Cursiva&quot; y viceversa)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Underline'); ?>" src="../images/underline.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Activa/desactiva el estilo &quot;Subrayado&quot; al texto seleccionado y al texto desde la posici&oacute;n actual del cursor. (lo desactiva si el texto est&aacute; en &quot;Subrayado&quot; y viceversa)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Strikethrough'); ?>" src="../images/strikethrough.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Activa/desactiva el estilo &quot;Tachado&quot; al texto seleccionado y al texto desde la posici&oacute;n actual del cursor. (lo desactiva si el texto est&aacute; en &quot;Tachado&quot; y viceversa)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SuperScript'); ?>" src="../images/superscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Activa/desactiva el estilo &quot;Super&iacute;ndice&quot; al texto seleccionado y al texto desde la posici&oacute;n actual del cursor.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SubScript'); ?>" src="../images/subscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Activa/desactiva el estilo &quot;Sub&iacute;ndice&quot; al texto seleccionado y al texto desde la posici&oacute;n actual del cursor.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyLeft'); ?>" src="../images/left.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Alinea a la izquierda la porci&oacute;n seleccionada del documento y/o el nuevo texto desde la actual posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyCenter'); ?>" src="../images/center.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Centra la porci&oacute;n seleccionada del documento y/o el nuevo texto desde la actual posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyRight'); ?>" src="../images/right.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Alinea a la derecha la porci&oacute;n seleccionada del documento y/o el nuevo texto desde la actual posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyFull'); ?>" src="../images/justify.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Justifica la porci&oacute;n seleccionada del documento y/o el nuevo texto desde la actual posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertOrderedList'); ?>" src="../images/numlist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Convierte en una lista numerada al texto seleccionado y/o al nuevo texto desde la actual posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertUnorderedList'); ?>" src="../images/bullist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Convierte en una lista con vi&ntilde;etas al texto seleccionado y/o al nuevo texto desde la actual posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Outdent'); ?>" src="../images/outdent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Reduce la sangr&iacute;a del texto en la posici&oacute;n actual del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Indent'); ?>" src="../images/indent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Aumenta la sangr&iacute;a del texto en la posici&oacute;n actual del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertHorizontalRule'); ?>" src="../images/h_line.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Inserta una l&iacute;nea horizontal en la posici&oacute;n del cursor.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('RemoveFormat'); ?>" src="../images/rem_format.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Quita el formato al texto seleccionado y/o al  texto en la actual posici&oacute;n del cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTable'); ?>" src="../images/table.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para crear una tabla en la posici&oacute;n actual del cursor o editar las propiedades de la tabla seleccionada.<br>
<br>

Propiedades de tabla que se pueden asignar/editar:<br>
Filas -- n&uacute;mero de filas en la tabla; columnas -- n&uacute;mero de columnas en la tabla; ancho --
ancho de la tabla; altura -- altura de la tabla; color de fondo -- color de fondo de la tabla; imagen de fondo-- imagen de fondo de la tabla; relleno -- relleno alrededor de las celdas de la tabla; espaciado -- distancia entre las celdas de la tabla; ancho del borde --
ancho del borde alrededor de las celdas; color oscuro del borde-- color de la parte derecha e inferior de las celdas de la tabla; color claro del borde -- color de la parte superior e izquierda de las celdas de la tabla.<br>
<br>

Propiedades de celda que se pueden modificar:<br>
ancho -- ancho de la celda; alto -- alto de la celda; alineaci&oacute;n vertical-- alineaci&oacute;n vertical de la celda; color -- color de fondo de la celda<br>
<br>

Para editar las propiedades de la tabla haga clic con el bot&oacute;n derecho del rat&oacute;n sobre el borde de la tabla o selecci&oacute;nela y haga clic en el correspondiente bot&oacute;n de la barra de herramientas.<br>
Para editar las propiedades de las celdas de la tabla-- haga clic con el bot&oacute;n derecho del rat&oacute;n dentro de la celda que desea editar <br>
<br>

Para asignar colores, haga clic en la casilla de verificaci&oacute;n correspondiente o en el cuadrado de color a la izquierda de la casilla.<br>
Para asignar una imagen de fondo, haga clic en la casilla de verificaci&oacute;n correspondiente o dentro del espacio entre la casilla y el texto ?Imagen:'<br>
Para deshabilitar el color o la imagen deseleccione la casilla de verificaci&oacute;n correspondiente.<br>

<b>Nota:</b> los campos 'filas y 'columnas' no pueden ser modificados -- utilice como alternativa el bot&oacute;n insertar/borrar filas/columnas </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertRow'); ?>" src="../images/insrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Inserta una fila antes de la fila donde se encuentra posicionado el cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteRow'); ?>" src="../images/delrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Borra la fila donde se encuentra posicionado el cursor
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertColumn'); ?>" src="../images/inscol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Inserta una columna antes de la columna donde se encuentra posicionado el cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteColumn'); ?>" src="../images/delcol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Borra la columna donde se encuentra posicionado el cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertCell'); ?>" src="../images/inscell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Inserta una celda antes de la celda donde se encuentra posicionado el cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteCell'); ?>" src="../images/delcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Borra la celda donde se encuentra posicionado el cursor</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('MergeCells'); ?>" src="../images/mergecells.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Combina la celda donde se encuentra posicionado el cursor con la siguiente (s&oacute;lo si existe la siguiente) </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SplitCell'); ?>" src="../images/splitcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Divide la celda donde se encuentra posicionado el cursor si previamente fue combinada con otra.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateForm'); ?>" src="../images/form.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
  <p>Llama a una ventana de di&aacute;logo para crear un formulario en la posici&oacute;n actual del cursor.<br>
      <br>

Propiedades del formulario que se pueden asignar/modificar:<br>
  Nombre -- nombre del formulario; m&eacute;todo -- m&eacute;todo para el env&iacute;o de la informaci&oacute;n (get/post);
  acci&oacute;n -- destino a donde se enviar&aacute; la informaci&oacute;n <br>
  <br>

Active la visibilidad de los bordes para visualizar los formularios del documento en modo dise&ntilde;o.<br>
<br>
      Para cambiar las propiedades de un formulario oprima el bot&oacute;n derecho del rat&oacute;n dentro de los l&iacute;mites de dicho formulario.<br>
      <br>
      Para editar las propiedades de los elementos del formulario oprima el bot&oacute;n derecho del rat&oacute;n sobre sus bordes o selecci&oacute;nelo y oprima el &iacute;cono correspondiente en la barra de herramientas del editor.</p>  </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateText'); ?>" src="../images/text.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para crear un campo de texto en la posici&oacute;n actual del cursor <br>

Propiedades del campo de texto que se pueden asignar/modificar:<br>
Nombre -- nombre del elemento; valor -- valor predeterminado del elemento;
tipo -- tipo de campo de texto (texto/contrase&ntilde;a); caracteres m&aacute;ximos --
n&uacute;mero m&aacute;ximo de caracteres disponibles para el elemento;
ancho en caracteres-- ancho del elemento expresado en caracteres.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTextArea'); ?>" src="../images/textarea.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para crear un campo de &aacute;rea de texto en la posici&oacute;n actual del cursor<br>
<br>

Propiedades del &aacute;rea de texto que se pueden asignar/modificar:<br>
Nombre -- nombre del elemento; valor -- valor predeterminado del elemento;
filas -- altura del elemento en renglones de caracteres; columnas -- ancho del elemento en caracteres.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateButton'); ?>" src="../images/button.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para crear un bot&oacute;n de formulario en la posici&oacute;n actual del cursor<br>
<br>

Propiedades del bot&oacute;n que se pueden asignar/modificar:<br>
Nombre -- nombre del elemento; valor -- valor predeterminado del elemento;
tipo -- tipo de bot&oacute;n (bot&oacute;n/restablecer/enviar)</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateSelect'); ?>" src="../images/select.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
  <p>Llama a una ventana de di&aacute;logo para crear una lista/men&uacute; de formulario en la posici&oacute;n actual del cursor<br>
      <br>

Propiedades de la lista/men&uacute; que se pueden asignar/modificar:<br>
  Nombre -- nombre del elemento; Tama&ntilde;o -- especifica el n&uacute;mero de renglones visibles al mismo tiempo en la lista; M&uacute;ltiple -- si est&aacute; activado permite selecci&oacute;n m&uacute;ltiple, si no esta activado s&oacute;lo permite seleccionar un &iacute;tem;
  Items -- grupo de opciones del elemento<br>
  <br>
  Propiedades de la lista/men&uacute; que se pueden asignar/modificar:<br>
      Texto -- contenido de la opci&oacute;n; Valor -- contenido inicial de la opci&oacute;n;
    Seleccionado -- si est&aacute; activada especifica que esa opci&oacute;n es la que esta preseleccionada.<br>
    <br>
    Botones:<br>
    Agregar -- agrega una opci&oacute;n con valores de Texto, Valor y  Seleccionado; Borrar -- borra la opci&oacute;n; Actualizar --
    actualiza los nuevos valores ingresados para la opci&oacute;n; /\ -- mueve la opci&oacute;n seleccionada hacia arriba; \/ -- mueve la opci&oacute;on seleccionada hacia abajo </p>  </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateHidden'); ?>" src="../images/hidden.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para crear un campo oculto de formulario en la posici&oacute;n actual del cursor<br>
<br>

Propiedades del campo oculto que se pueden asignar/modificar:<br>
Name -- nombre del elemento; value -- valor del elemento </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateCheckBox'); ?>" src="../images/checkbox.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Propiedades de la casilla de verificaci&oacute;n que se pueden asignar/modificar:<br>
<br>

Propiedades de la casilla de verificaci&oacute;n que se pueden asignar/modificar:<br>
Nombre -- nombre del elemento; valor -- valor del elemento; seleccionada -- si esta activada la casilla aparece marcada</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateRadio'); ?>" src="../images/radio.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Propiedades del bot&oacute;n de opci&oacute;n que se pueden asignar/modificar:<br>
<br>

Propiedades del bot&oacute;n de opci&oacute;n que se pueden asignar/modificar:<br>
Nombre -- nombre del elemento; valor -- valor del elemento; seleccionado -- si se activa la correspondiente  opci&oacute;n esta activa</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FormatBlock'); ?>
</TD>
<TD VALIGN="TOP">
Asigna un formato de p&aacute;rrafo a texto seleccionado o al texto nuevo desde la posici&oacute;n actual del cursor </TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontName'); ?>
</TD>
<TD VALIGN="TOP">
Cambia la tipograf&iacute;a al texto seleccionado o al texto nuevo desde la posici&oacute;n actual del cursor</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('ClassName'); ?>
</TD>
<TD VALIGN="TOP">
Le asigna un estilo al texto seleccionado
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontSize'); ?>
</TD>
<TD VALIGN="TOP">
Le asigna un tama&ntilde;o de tipograf&iacute;a al texto seleccionado o al texto nuevo desde la posici&oacute;n actual del cursor.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('ForeColor'); ?>" src="../images/fgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Cambia el color del texto seleccionado o al texto nuevo desde la posici&oacute;n actual del cursor.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('BackColor'); ?>" src="../images/bgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Cambia el color de fondo del texto seleccionado o al texto nuevo desde la posici&oacute;n actual del cursor.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateImage'); ?>" src="../images/image.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
  <p>Llama a una ventana de di&aacute;logo para insertar una imagen en la posici&oacute;n actual del cursor o cambiar las propiedades de la imagen seleccionada.<br>
      <br>

La ventana de di&aacute;logo de im&aacute;genes cuenta con 4 secciones:<br>
  Manejador de archivos (en la secci&oacute;n izquierda de la ventana), &aacute;rea de la vista preliminar(en la parte superior derecha), &aacute;rea de propiedades (debajo del &aacute;rea de vista preliminar) y el formulario para subir im&aacute;genes (en la parte inferior de toda la ventana)<br>
  <br>

El explorador de archivos muestra las carpetas en forma de &aacute;rbol desde la carpeta asignada para guardar las im&aacute;genes.<br>
  Para ver las subcarpetas y los archivos que contienen, haga clic en el &iacute;cono
  '+' correspondiente. para cerrar el contenido de una carpeta, haga clic en el &iacute;cono '-' correspondiente.
  <br>
  El nombre de la carpeta seleccionada para subir la imagen actual est&aacute; resaltado en rojo. Para seleccionar la carpeta a la que desea subir la imagen, simplemente haga clic sobre ella. <br>
  Para crear una nueva carpeta, haga clic en 'crear carpeta', ingrese un nombre y confirme oprimiendo el bot&oacute;n
  'aceptar'  o anule la acci&oacute;n con el bot&oacute;n 'cancelar'.<br>
  Haga clic en los v&iacute;nculos 'r' / 'x' para renombrar / borrar los archivos o carpetas.<br>
  <br>
  <b>Nota:</b> las carpetas solo se pueden borrar si no hay contenido en ellas (archivos y/o carpetas).<br>
  <br>
  Cuando una imagen esta seleccionada en el explorador de im&aacute;genes o en el formulario para subir im&aacute;genes se la puede ver en el &aacute;rea de vista preliminar.<br>

Oprima el bot&oacute;n &quot;Examinar&quot; en el formulario para subir im&aacute;genes, para seleccionar el archivo que desee subir de su m&aacute;quina.<br>
<br>
    Propiedades de im&aacute;genes que se pueden asignar/modificar:<br>
    Alineaci&oacute;n -- alineamiento horizontal de la imagen; ancho -- ancho de la imagen;
    alto -- altura de la imagen; borde -- ancho del borde de la imagen; espaciado V -- espaciado vertical entre la imagen y el texto; espaciado H -- espaciado horizontal entre la imagen y el texto;
    alt -- texto alternativo para la imagen (se muestra cuando se coloca el rat&oacute;n sobre la imagen);
    Origen -- define la ruta origen de la imagen <br>
    <br>
    Para editar las propiedades de una imagen oprima el bot&oacute;n derecho del rat&oacute;n sobre ella o selecci&oacute;nela y luego oprima el bot&oacute;n correspondiente de la barra de herramientas.</p>  </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateFlash'); ?>" src="../images/flash.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para insertar archivo flash en la posici&oacute;n actual del cursor o cambiar las propiedades del objeto flash seleccionado.<br>
<br>
Propiedades del Flash que se pueden asignar/modificar:<br>
Alineaci&oacute;n -- alineaci&oacute;n horizontal del flash; ancho -- ancho del flash;
height -- alto del flash; reproducir -- si el flash comienza luego de la carga;
bucle -- si el flash se repite continuamente; men&uacute; -- si muestra el men&uacute; de opciones del flash cuando se hace clic con el bot&oacute;n derecho del rat&oacute;n sobre &eacute;l.; color de fondo-- color de fondo del flash; calidad --
calidad de reproducci&oacute;n del flash; Origen -- ruta del flash original <br>
<br>

Las opciones de trabajo con los archivos flash son los mismos que con las im&aacute;genes <br>

<br>
Para editar las propiedades de un objeto flash oprima el bot&oacute;n derecho del rat&oacute;n sobre &eacute;l o selecci&oacute;nelo y luego oprima el bot&oacute;n correspondiente de la barra de herramientas.<br>
<br>

<b>Nota: </b>s&oacute;lo se puede hacer clic con el bot&oacute;n derecho del rat&oacute;n sobre un objeto flash en MSIE 6.0+</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateLink'); ?>" src="../images/link.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para crear un v&iacute;nculo en el texto seleccionado o cambia las propiedades de un v&iacute;nculo seleccionado. <br>
<br>

Propiedades del v&iacute;nculo que se pueden asignar/modificar:<br>
destino -- define la ventana destino del archivo que abrir&aacute;; v&iacute;nculo -- define la URL del v&iacute;nculo; nombre -- nombre del v&iacute;nculo (se usa para crear anclajes); t&iacute;tulo -- el texto que se muestra cuando se posiciona el rat&oacute;n sobre el v&iacute;nculo. <br>
<br>

<b>Nota: </b>para remover un v&iacute;nculo, abra la ventana de di&aacute;logo correspondiente y borre la ruta en la propiedad &quot;V&iacute;nculo&quot; <br>
<br>

Para editar las propiedades de un v&iacute;nculo, selecci&oacute;nelo o una parte de &eacute;l y haga clic en el bot&oacute;n correspondiente de la barra de herramientas. Para crear un anclaje solo debe darle una valor al &quot;nombre&quot; <br>
<br>

Las opciones de trabajo son los mismas que con las im&aacute;genes </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PasteWord'); ?>" src="../images/paste_word.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Pega el contenido formateado de MS Word previamente copiado, en la posici&oacute;n actual del cursor. Esta herramienta remueve el estilo y formateo propio del MS Word convirti&eacute;ndolo al formato html.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SwitchBorders'); ?>" src="../images/borders.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Muestra todos los bordes invisibles. Todas las tablas y celdas se muestran con bordes grises punteados <br>
Esta acci&oacute;n es solo para visualizar y no cambia el documento en s&iacute;.
<br>
<br>

Si la acci&oacute;n est&aacute; activa el bot&oacute;n correspondiente se lo ve oprimido. </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertChar'); ?>" src="../images/inschar.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para insertar un car&aacute;cter especial en la posici&oacute;n actual del cursor.
<br>
Haga clic en el caracter que desee insertar.
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertSnippet'); ?>" src="../images/snippet.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Llama a una ventana de di&aacute;logo para insertar un snippet (una secci&oacute;n preformateada en html). El snippet seleccionado se ve en la ventana de vista preliminar </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('AbsolutePosition'); ?>" src="../images/abspos.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Activa/desactiva la propiedad de posici&oacute;n absoluta del elemento seleccionado.</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PageProperties'); ?>" src="../images/page.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
<p>Llama a una ventana de di&aacute;logo para cambiar las propiedades
  de la p&aacute;gina que se est&aacute; editando.<br>
  <br>

Propiedades de p&aacute;gina que se pueden modificar:<br>
  T&iacute;tulo -- t&iacute;tulo de la p&aacute;gina (contenido de la etiqueta 'title', t&iacute;tulo de la p&aacute;gina que se ve en la barra superior de la ventana del explorador); descripci&oacute;n --
  s&iacute;ntesis del contenido de la p&aacute;gina (contenido de la etiqueta META 'description'); palabras clave -- grupo de palabras clave para la p&aacute;gina (contenido de la etiqueta META 'keywords'); color --
  color de fondo de la p&aacute;gina; imagen -- imagen de fondo de la p&aacute;gina; texto --
  color predeterminado del texto; v&iacute;nculos -- color de los v&iacute;nculos; v&iacute;nculos visitados-- color de los v&iacute;nculos ya visitados; v&iacute;nculos activos -- color del v&iacute;nculo activo (no visitado) <br>
  <br>
  Para asignar colores, haga clic en la casilla de verificaci&oacute;n correspondiente o en el cuadrado de color a la izquierda de la casilla.<br>
  Para asignar una imagen de fondo, haga clic en la casilla de verificaci&oacute;n correspondiente o dentro del espacio entre la casilla y el texto 'Imagen:'<br>
  Para deshabilitar el color o la imagen deselecione la casilla de verificaci&oacute;n correspondiente.<br>
      <br>

      <b>Nota:</b> esta funci&oacute;n solo esta disponible en modo p&aacute;gina completa</p>  </TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Help'); ?>" src="../images/help.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Muestra esta p&aacute;gina de ayuda (si no est&aacute; abierta en otra ventana)
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('Source'); ?>
</TD>
<TD VALIGN="TOP">
Cambia el editor entre los modos de vista &quot;Dise&ntilde;o&quot; y &quot;C&oacute;digo fuente&quot; </TD>
</TR>

</TABLE>
</P>

<P class="help_text">
<div>
<b>Nota: </b>no todas las funciones est&aacute;n siempre disponibles!
</div>
</P>

<HR>

<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0" WIDTH="100%">
<TR>
<TD ALIGN="RIGHT">
<A HREF="#TOP">Ir Arriba</A></TD>
</TR>
</TABLE>

</BODY>

</HTML>