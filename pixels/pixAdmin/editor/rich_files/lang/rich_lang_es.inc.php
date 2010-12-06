<?php

/* =============================================================================
 * español
 * Spanish version of the Rich Editor text data
 *
 * Translated by Jorge da Fonseca / jdf@multimagen.com
 * 04.05.2005
 * =============================================================================
*/

$rich_lang = array(
'Charset' 		=> 'iso-8859-1',
'Direction' 	=> '', //set to 'rtl' if language direction is right to left
'Multibyte' 	=> '', //set to not empty string if language is multibyte
'Save' 			=> 'Guardar',
'FullScreen' 	=> 'Pantalla completa',
'Preview' 		=> 'Vista previa',
'Find' 			=> 'Buscar&Reemplazar',

'Cut' 			=> 'Cortar',
'Copy' 			=> 'Copiar',
'Paste' 		=> 'Pegar',

'Undo' 			=> 'Deshacer',
'Redo' 			=> 'Rehacer',

'Bold' 			=> 'Negrita',
'Italic' 		=> 'Cursiva',
'Underline' 	=> 'Subrayada',
'Strikethrough' => 'Tachada',
'SuperScript' 	=> 'Superíndice',
'SubScript'		=>'Subíndice',

'JustifyLeft' 	=> 'Alineación izquierda',
'JustifyCenter' => 'Centrado',
'JustifyRight' 	=> 'Alineación derecha',
'JustifyFull' 	=> 'Justificado',

'InsertOrderedList' 	=> 'Insertar lista ordenada',
'InsertUnorderedList' 	=> 'Insertar lista con bulets',
'Outdent' 				=> 'Aumentar sangría',
'Indent' 				=> 'Disminuir sangría',

'InsertHorizontalRule' 	=> 'Insertar línea horizontal',
'RemoveFormat' 			=> 'Quitar formato',

'CreateTable' 	=> 'Crear Tabla',
'InsertRow' 	=> 'Insertar Fila', 'DeleteRow' => 'Borrar Fila',
'InsertColumn' 	=> 'Insertar Columna', 'DeleteColumn' => 'Borrar Columna',

'CreateForm' 	=> 'Crear Formulario',
'CreateText' 	=> 'Crear Campo de Texto',
'CreateTextArea'=> 'Crear Area de texto',
'CreateButton' 	=> 'Crear Botón',
'CreateSelect' 	=> 'Crear Lista',
'CreateHidden' 	=> 'Crear Campo Oculto',
'CreateCheckBox'=> 'Crear Casilla de Verificación',
'CreateRadio' 	=> 'Crear Botón de Opción',

'InsertCell' 	=> 'Insertar Celda',
'DeleteCell' 	=> 'Borrar Celda',
'MergeCells' 	=> 'Unir Celdas',
'SplitCell' 	=> 'Dividir Celdas',
'MergeCellsDown'=> 'Unir Celdas',
'SplitCellDown' => 'Dividir Celdas',

'FormatBlock' 	=> 'Párrafo',
'FontName' 		=> 'Fuente',
'ClassName' 	=> 'Clase',
'FontSize' 		=> 'Tamaño',

'ForeColor' 	=> 'Color del Texto',
'BackColor' 	=> 'Color del Fondo',

'CreateImage' 	=> 'Insertar Imagen',
'CreateFlash' 	=> 'Insertar Flash',
'CreateLink' 	=> 'Insertar Link',
'CreateAnchor'	=> 'Create Anchor',

'PasteWord' 	=> 'Pegar de Word',
'SwitchBorders' => 'Ver Bordes',
'InsertChar' 	=> 'Insertar Carácter',
'InsertSnippet' => 'Insertar Plantilla',
'AbsolutePosition' => 'Posición Absoluta',
'PageProperties' => 'Propiedades de Página',

'Help' 			=> 'Ayuda',

'Source' 		=> 'Fuente',

//Context menu items
'EditTable' 	=> 'Editar Tabla',
'EditImage' 	=> 'Editar Imagen',
'EditFlash' 	=> 'Editar Flash',
'EditText' 		=> 'Editar Campo de Texto',
'EditTextArea' 	=> 'Editar Area de Texto',
'EditButton' 	=> 'Editar Botón',
'EditSelect' 	=> 'Editar Lista',
'EditHidden' 	=> 'Editar Campo Oculto',
'EditCheckBox' 	=> 'Editar Casilla de Verificación',
'EditRadio' 	=> 'Editar Botón de Opción',
'EditLink' 		=> 'Editar Link',
'EditAnchor'	=> 'Edit Anchor',
'EditForm' 		=> 'Editar Formulario',
'EditCell' 		=> 'Editar Celda',
'EditRow'		=> 'Editar Fila',


//dialog texts
'Table' => array(
	'Title' 	=> 'Crea/Editar Tabla',
	'Sizes' 	=> 'Medidas',
	'Rows' 		=> 'Filas',
	'Columns' 	=> 'Columnas',
	'Width' 	=> 'Ancho',
	'Height' 	=> 'Altura',
	'Background'=> 'Fondo',
	'Color' 	=> 'Color',
	'Image' 	=> 'Imagen',
	'Padding&Spacing' => '<font style="font-size:10px">Relleno y Espaciado</font>',
	'Padding' 	=> 'Relleno',
	'Spacing' 	=> 'Espaciado',
	'Border' 	=> 'Borde',
	'Colorlight'=> 'Color Claro',
	'Colordark' => 'Color Oscuro',
	'Align' => 'Alineación',
	'TableAlign' => 'Tabla',
	'Text' => 'Texto',
	'BorderCollapse' => 'Collapse',

	'Table' => 'TABLA',
	'Row' => 'FILA',
	'Cell' => 'CELDA',
	'Apply' => 'Apply',
	'Preview' => 'Preview',
	'CurrentRow' => 'Fila',
	'CurrentCell' => 'Celda',
	'PreviewText' => 'This is some text placed near the table.'

),

'TableCell' 	=> array(
    'Title' 	=> 'Editar Celdas',
	'Sizes' 	=> 'Medidas',
	'Width' 	=> 'Ancho',
	'Height' 	=> 'Alto',
	'Align&Color' => 'Alineación y Color',
	'vAlign' 	=> 'Alineación Vertical',
	'Align' => 'Alineación',
	'NoWrap' => 'nowrap',
	'Color' 	=> 'Color'
),

'ColorPicker' 	=> array(
	'Title' 	=> 'Paleta de Colores'
),

'RemoteFiles' 	=> array(
	'root' 			=> 'root',
	'CreateFolder' 	=> 'crear carpeta',
	'CreateFolderIn'=> 'Crear Carpeta en',
	'NewNameFor' 	=> 'Renombrar',
	'Delete' 		=> 'Borrar',
	'CannotCreateFolder' => 'No se pudo crear la carpeta',
	'CannotRename' 	=> 'No se pudo renombrar',
	'CannotDelete' 	=> 'No se pudo Borrar',
	'InputAName' 	=> 'Ingrese un nombre!',
	'NoAccessToThisDir' => 'No tiene acceso a la carpeta',
	'WrongFilesPath' => 'No se pudo abrir la carpeta',
	'WrongExt' => 'Extensión no permitida',
	'FreeSpace' => 'Libre'
),

'LocalFiles' => array(
	'CannotUpload' => 'No tiene permiso para subir archivos!',
	'NoAccessToThisDir' => 'No tiene permisos para subir archivos a la carpeta',
	'TotalSizeExceeded' => 'El tamaño total de los archivos subidos no puede exceder de',
	'MaxSizeExceeded' => 'El tamaño total del archivo a subir no pude exceder de',
	'CannotOverride' => 'No tiene permiso para sobreescribir archivos!',
	'WrongExt' => 'Extension no permitida',
	'WrongImageType' => 'Formato de imagen no permitido para subir!',
	'WrongImageSize' => 'La imagen a subir no puede ser mayor de',
	'WrongImageWidth' => 'El ancho de la imagen a subir no puede ser mayor a ',
	'WrongImageHeight' => 'La altura de la imagen a subir no puede ser mayor a',
	'WrongFlashSize' => 'El archivo Flash no puede se mayor de',
	'UploadError' => 'La subida del archivo a fallado. \nPor favor revise los permisos para subir archivos \nen la carpeta y si la carpeta existe!'
),

'Align' => array(
	'left' 		=> 'izquierda',
	'center' 	=> 'centrado',
	'right' 	=> 'derecha',
	'top' 		=> 'arriba',
	'middle' 	=> 'medio',
	'bottom' 	=> 'abajo',
	'absBottom' => 'absAbajo',
	'absMiddle' => 'absMedio',
	'baseline' 	=> 'línea base'
),

'TextAlign' => array(
	'left' 		=> 'izquierda',
	'center' 	=> 'centrado',
	'right' 	=> 'derecha',
	'justify'	=> 'justificado'
),

'TableAlign' => array(
	'left' 		=> 'izquierda',
	'center' 	=> 'centrado',
	'right' 	=> 'derecha'
),

'vAlign' => array(
	'top' 		=> 'arriba',
	'middle' 	=> 'centro',
	'bottom' 	=> 'abajo'
),

'Uploading' => 'Uploading...',

'Image' => array(
	'Title' 	=> 'Crear/Editar Imagen',
	'Align' 	=> 'Alinear',
	'Width' 	=> 'Ancho',
	'Height' 	=> 'Alto',
	'Border' 	=> 'Borde',
	'Vspace' 	=> 'Esp. V',
	'Hspace' 	=> 'Esp. H',
	'Alt' 		=> 'Alt',
	'Src' 		=> 'Orígen'
),

'Flash' => array(
	'Title' 	=> 'Crear/Editar Flash',
	'Align' 	=> 'Alinear',
	'Width' 	=> 'Ancho',
	'Height' 	=> 'Alto',
	'Play' 		=> 'Reproducir',
	'Loop' 		=> 'Bucle',
	'Menu' 		=> 'Menú',
	'BGColor' 	=> 'Fondo',
	'Quality' 	=> 'Calidad',
	'Src' 		=> 'Origen'
),

'Link' => array(
	'Title' 	=> 'Crear/Editar Vínculo',
	'Target' 	=> 'Destino',
	'Name' 		=> 'Nombre',
	'Hint' 		=> 'Título',
	'Link' 		=> 'Vínculo',

	'Targets' 	=> array(
		'_self' => 'ventana actual',
		'_blank'=> 'nueva ventana'
	)
),

'form' => array(
	'Title' 	=> 'Crear/Editar Formulario',
	'Legend' 	=> 'Propiedades del Formulario',
	'Name' 		=> 'Nombre',
	'Method' 	=> 'Método',
	'Action' 	=> 'Acción'
),

'text' => array(
	'Title' 	=> 'Crear/Editar campo de texto',
	'Legend' 	=> '<font style="font-size:10px">Propiedades del Campo de Texto</font>',
	'Name' 		=> 'Nombre',
	'Value' 	=> 'Valor',
	'Type' 		=> 'Tipo',
	'MaxChars' 	=> 'Máximo de caracteres',
	'CharWidth' => 'Ancho en caracteres'
),

'textarea' => array(
	'Title' 	=> 'Crear/Editar área de texto',
	'Legend' 	=> '<font style="font-size:10px">Propiedades de Area de Texto</font>',
	'Name' 		=> 'Nombre',
	'Rows' 		=> 'Filas',
	'Columns' 	=> 'Columnas',
	'Value' 	=> 'Valor'
),

'button' => array(
	'Title' 	=> 'Crear/Editar Botón',
	'Legend' 	=> 'Propiedades del Botón',
	'Name' 		=> 'Nombre',
	'Value' 	=> 'Valor',
	'Type' 		=> 'Tipo'
),

'hidden' => array(
	'Title' 	=> 'Crear/Editar campos ocultos',
	'Legend' 	=> 'Propiedades de campos ocultos',
	'Name' 		=> 'Nombre',
	'Value' 	=> 'Valor'
),

'checkbox' 		=> array(
	'Title' 	=> 'Crear/Editar Casillas de verificación',
	'Legend' 	=> '<font style="font-size:9px">Propiedades de las Casillas de Verificación</font>',
	'Name' 		=> 'Nombre',
	'Value' 	=> 'Valor',
	'Checked' 	=> 'Seleccionado'
),

'radio' => array(
	'Title' 	=> 'Crear/Editar Botones de Opción',
	'Legend' 	=> '<font style="font-size:10px">Propiedades de Botones de Opción</font>',
	'Name' 		=> 'Nombre',
	'Value' 	=> 'Valor',
	'Checked' 	=> 'Seleccionado'
),

'select' => array(
	'Title' 	=> 'Crear/Editar Lista',
	'Legend' 	=> 'Parámetros de la Lista',
	'Name' 		=> 'Nombre',
	'Size' 		=> 'Tamaño',
	'Multiple' 	=> 'Múltiple',
	'Items' 	=> 'Items',
	'Text' 		=> 'Texto',
	'Value' 	=> 'Valor',
	'Selected' 	=> 'Seleccionado',
	'Up' 		=> '/\\',
	'Down' 		=> '\\/',
	'Add' 		=> 'Agregar',
	'Delete' 	=> 'Borrar',
	'Update' 	=> 'Actualizar'

),

'snippet' => array(
	'Title' 	=> 'Insertar Objetos Prediseñados',
	'Legend' 	=> 'Objetos',
	'Preview' 	=> 'Vista Preliminar',
	'Group' 	=> 'Grupo',
	'Snippet' 	=> 'Objeto'
),

'paste_word' 	=> array(
	'Title' 	=> 'Pegar de Word'
),

'Page' => array(
	'Title' 		=> 'Edita Propiedades de Página',
	'Info' 			=> 'Info',
	'Title_prop'	=> 'Título',
	'Description'	=> 'Descripción',
	'Keywords' 		=> 'Palabras Clave',
	'Background'	=> 'Fondo',
	'Color' 		=> 'Color',
	'Image' 		=> 'Imagen',
	'Colors' 		=> 'Colores',
	'Text' 			=> 'Texto',
	'Link' 			=> 'Vínculos',
	'VisitedLink' 	=> 'Vinculos Visitados',
	'ActiveLink' 	=> 'Vínculos Activos'
),

'CharacterMap' => array(
	'Title' 		=> 'Mapa de Caracteres'
),

'Find&Replace' 		=> array(
	'Title' 		=> 'Buscar&Reemplazar',
	'ToFind' 		=> 'Buscar',
	'ToReplace' 	=> 'Reemplazar con',
	'Find' 			=> 'Buscar',
	'Replace' 		=> 'Reemplazar',
	'ReplaceAll' 	=> 'Reemplazar todos',
	'WholeWord' 	=> 'solo palabra completa',
	'MatchCase' 	=> 'diferenciar Mayúscula/Minúscula',
	'EmptyString' 	=> 'Ingrese un texto para buscar!',
	'NumberOfReplacements' => 'Cantidad de reemplazos realizados: ',
	'NotFound' 		=> 'Texto no encontrado. Comenzar desde el principio?',
	'NonEditableText' => 'This text is inside a non-editable area! Continue?'

),

'AdvTable' => array(
	'InsertRowTitle' 			=> 'Insertar Fila',
	'InsertColumnTitle' 		=> 'Insertar Columna',
	'InsertBeforeCurrentRow' 	=> 'Insertar antes de la fila actual',
	'InsertAfterCurrentRow' 	=> 'Insertar después de la fila actual',
	'InsertBeforeCurrentColumn' => 'Insertar antes de la columna actual',
	'InsertAfterCurrentColumn' 	=> 'Insertar después de la columna actual'
),

'Anchor' => array(
	'Title' => 'Create/Edit Anchor',
	'Name' => 'Anchor name',
),

'Upload'		=> 'Upload',
'Ok' 			=> 'Aceptar',
'Cancel' 		=> 'Cancelar'

	);

?>