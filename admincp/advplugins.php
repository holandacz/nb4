<?php
/*======================================================================*\
|| #################################################################### ||
|| # Advanced Plugin Manager for vBulletin 3.5 / 3.6 by Andreas & Psionic Vision
|| # Back-End Administrator Interface
|| #################################################################### ||
\*======================================================================*/

// #############################################################################
error_reporting(E_ALL & ~E_NOTICE);

// ###############################################################################################################
$phrasegroups = array('plugins');
$specialtemplates = array();

// ###############################################################################################################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

// ###############################################################################################################
if (is_demo_mode() OR !can_administer('canadminplugins'))
{
	print_cp_no_permission();
}

// ###############################################################################################################
log_admin_action();

################################################################################################################################
################################################################################################################################
################################################################################################################################

print_cp_header($vbphrase['plugin_system']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'modify';
}

################################################################################################################################
################################################################################################################################
################################################################################################################################

$vbulletin->input->clean_gpc('r', 'vbulletin_collapse', TYPE_NOCLEAN);
$vbcollapse = array();
if (!empty($vbulletin->GPC['vbulletin_collapse']))
{
	$val = preg_split('#\n#', $vbulletin->GPC['vbulletin_collapse'], -1, PREG_SPLIT_NO_EMPTY);
	foreach ($val AS $key)
	{
		$vbcollapse["collapseobj_$key"] = 'display:none;';
		$vbcollapse["collapseimg_$key"] = '_collapsed';
		$vbcollapse["collapsecel_$key"] = '_collapsed';
	}
	unset($val);
}

################################################################################################################################
################################################################################################################################
################################################################################################################################

if ($_REQUEST['do'] == 'modify')
{
	?>
	<script type="text/javascript">
	function js_page_jump(sid)
	{
		var action = eval("document.cpform.s" + sid + ".options[document.cpform.s" + sid + ".selectedIndex].value");
		if (action != '')
		{
			switch (action)
			{
				case 'productdisable': page = "plugin.php?do=productdisable&productid="; break;
				case 'productenable': page = "plugin.php?do=productenable&productid="; break;
				case 'productedit': page = "plugin.php?do=productedit&productid="; break;
				case 'productversioncheck': page = "plugin.php?do=productversioncheck&productid="; break;
				case 'productexport': page = "plugin.php?do=productexport&productid="; break;
				case 'productdelete': page = "plugin.php?do=productdelete&productid="; break;
				default: return;
			}
			document.cpform.reset();
			jumptopage = page + sid + "&s=<?php echo $vbulletin->session->vars['sessionhash']; ?>";
			window.location = jumptopage;
		}
		else
		{
			alert("<?php echo $vbphrase['invalid_action_specified']; ?>");
		}
	}
	</script>
	<?php
	if (!$vbulletin->options['enablehooks'] OR defined('DISABLE_HOOKS'))
	{
		print_form_header('', '');
		if (!$vbulletin->options['enablehooks'])
		{
			print_description_row($vbphrase['plugins_disabled_options']);
		}
		else
		{
			print_description_row($vbphrase['plugins_disable_config']);
		}
		print_table_footer();
	}

	############################################################################################################################
	############################################################################################################################
	############################################################################################################################

	$products = fetch_product_list(true);

	print_form_header('plugin', 'updateactive');
	print_table_header($vbphrase['plugin_system'], 4);
	print_cells_row(array($vbphrase['product'] . '/' . $vbphrase['title'], 'Hook Name', $vbphrase['active'], $vbphrase['controls']), 1);

	$getplugins = $db->query_read("
		SELECT * FROM " . TABLE_PREFIX . "plugin
		ORDER BY product, title, hookname
	");

	while ($plugin = $db->fetch_array($getplugins))
	{
		if (!$plugin['product'])
		{
			$plugin['product'] = 'vbulletin';
		}

		$plugins[$plugin['product']][] = $plugin;
	}

	$db->free_result($getplugins);

	############################################################################################################################
	############################################################################################################################
	############################################################################################################################

	foreach ($products as $product)
	{
		$product['title'] = htmlspecialchars_uni($product['title']);
		if ($product['active'] OR $product['productid'] == 'vbulletin')
		{
			$producttitle = '<strong>' . $product['title'] . '</strong>';
		}
		else
		{
			$producttitle = '<strike>' . $product['title'] . '</strike>';
		}

		if (isset($product['description']) AND !empty($product['description']))
		{
			$productdescription = ' - ' . htmlspecialchars_uni($product['description']);
		}
		else
		{
			$productdescription = '';
		}

		if (isset($product['url']) AND !empty($product['url']))
		{
			$producttitle = '<a href="' . htmlspecialchars_uni($product['url']) . '" style="color: black;" target="_blank">' . $producttitle . '</a>';
		}

		if (isset($product['version']) AND !empty($product['version']))
		{
			$productversion = ' - ' . htmlspecialchars_uni($product['version']);
		}

		$options = array('productedit' => $vbphrase['edit']);
		if (isset($product['versioncheckurl']) AND !empty($product['versioncheckurl']))
		{
			$options['productversioncheck'] = $vbphrase['check_version'];
		}
		if ($product['active'])
		{
			$options['productdisable'] = $vbphrase['disable'];
		}
		else
		{
			$options['productenable'] = $vbphrase['enable'];
		}
		$options['productexport'] = $vbphrase['export'];
		$options['productdelete'] = $vbphrase['uninstall'];

		print_description_row(
			(
				$product['productid'] != 'vbulletin' ?
				'<span style="float: right">'.
					'<select name="s' . $product['productid'] . '" onchange="js_page_jump(\'' . $product['productid'] . '\');" class="bginput">' . construct_select_options($options) . '</select> ' .
					'<input type="button" class="button" value="' . $vbphrase['go'] . '" onclick="js_page_jump(\'' . $product['productid'] . '\');" />' .
				'</span>' : ''
			) .
			'<a href="#" onclick="return toggle_collapse(\'' . $product['productid'] . '\')"><img border="0" style="vertical-align: middle" id="collapseimg_' . $product['productid'] . '" src="../images/buttons/collapse_alt' . $vbcollapse["collapseimg_" . $product['productid']] . '.gif" /></a> ' . $producttitle . $productversion . $productdescription, 0, 4, 'tfoot'
		);

		echo '<tbody id="collapseobj_' . $product['productid'] . '" style="' . $vbcollapse['collapseobj_' . $product['productid']] . '">';

		$pluginidx = ($product['productid'] ? $product['productid'] : 'vbulletin');

		if (isset($plugins["$pluginidx"]))
		{
			foreach ($plugins["$pluginidx"] as $plugin)
			{
				$plugintitle = htmlspecialchars_uni($plugin['title']);
				$plugintitle = ($product['active'] AND $plugin['active']) ? $plugintitle : '<strike>' . $plugintitle . '</strike>';


		// lwe
		$preview = strip_quotes($plugin['phpcode']);
		$preview = htmlspecialchars_uni(fetch_trimmed_title($preview,400));
		// / lwe




				print_cells_row(array(
				/*
					'<a href="plugin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=edit&amp;pluginid=' . $plugin['pluginid'] . '">' . $plugintitle . '</a>',
					*/
"<a href=\"plugin.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;pluginid=$plugin[pluginid]\" title=\"$preview\">$plugintitle</a>",



					$plugin['hookname'],
					'<input type="checkbox" name="active[' . $plugin['pluginid'] . ']" value="1"' . ($plugin['active'] ? ' checked="checked"' : '') . ' />',
					(
						construct_link_code($vbphrase['edit'], 'plugin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=edit&amp;pluginid=' . $plugin['pluginid']) .
						construct_link_code($vbphrase['delete'], 'plugin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=delete&amp;pluginid=' . $plugin['pluginid'])
					)
				));
			}
		}

		echo '</tbody>';
	}

	############################################################################################################################
	############################################################################################################################
	############################################################################################################################

	print_submit_row($vbphrase['save_active_status'], false, 4);

	echo '<p align="center">' . construct_link_code($vbphrase['add_new_plugin'], "plugin.php?" . $vbulletin->session->vars['sessionurl'] . "do=add") . ' ' . construct_link_code($vbphrase['add_import_product'], "plugin.php?" . $vbulletin->session->vars['sessionurl'] . "do=productadd") . '</p>';
}

################################################################################################################################
################################################################################################################################
################################################################################################################################

print_cp_footer();

/*======================================================================*\
|| #################################################################### ||
|| # Advanced Plugin Manager for vBulletin 3.5 by Andreas & Psionic Vision
|| # Back-End Administrator Interface
|| #################################################################### ||
\*======================================================================*/
?>