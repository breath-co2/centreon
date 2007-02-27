<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	if (isset($search))
		$DBRESULT = & $pearDB->query("SELECT COUNT(*) FROM contact WHERE (contact_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR contact_alias LIKE '%".htmlentities($search, ENT_QUOTES)."%')");
	else
		$DBRESULT = & $pearDB->query("SELECT COUNT(*) FROM contact");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";

	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	$DBRESULT =& $pearDB->query("SELECT ldap_auth_enable FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";

	$DBRESULT->fetchInto($ldap_auth);
	$DBRESULT->free();

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['name']);
	$tpl->assign("headerMenu_desc", $lang['description']);
	$tpl->assign("headerMenu_hostNotif", $lang['cct_hostNotifTp']);
	$tpl->assign("headerMenu_svNotif", $lang['cct_svNotifTp']);
	$tpl->assign("headerMenu_status", $lang['status']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu
	#Contact list
	if ($search)
		$rq = "SELECT contact_id, timeperiod_tp_id, timeperiod_tp_id2, contact_name, contact_alias, contact_host_notification_options, contact_service_notification_options, contact_activate  FROM contact WHERE (contact_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR contact_alias LIKE '%".htmlentities($search, ENT_QUOTES)."%') ORDER BY contact_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT contact_id, timeperiod_tp_id, timeperiod_tp_id2, contact_name, contact_alias, contact_host_notification_options, contact_service_notification_options, contact_activate FROM contact ORDER BY contact_name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $DBRESULT->fetchInto($contact); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$contact['contact_id']."]");
		$moptions = "<a href='oreon.php?p=".$p."&contact_id=".$contact['contact_id']."&o=w&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&contact_id=".$contact['contact_id']."&o=c&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&contact_id=".$contact['contact_id']."&o=d&select[".$contact['contact_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		if ($contact["contact_activate"])
			$moptions .= "<a href='oreon.php?p=".$p."&contact_id=".$contact['contact_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='".$lang['disable']."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='oreon.php?p=".$p."&contact_id=".$contact['contact_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='".$lang['enable']."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$contact['contact_id']."]'></input>";
		/* Get Host Notif Period */
		$hostTp = NULL;
		$DBRESULT2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$contact["timeperiod_tp_id"]."'");
		if (PEAR::isError($DBRESULT2)) 
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
		$hostTp = $DBRESULT2->fetchRow();
		/* Get Service Notif Period */
		$svTp = NULL;
		$DBRESULT2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$contact["timeperiod_tp_id2"]."'");
		if (PEAR::isError($DBRESULT2)) 
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
		$svTp = $DBRESULT2->fetchRow();		
		$elemArr[$i] = array("MenuClass"=>"list_".$style,
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$contact["contact_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&contact_id=".$contact['contact_id'],
						"RowMenu_desc"=>$contact["contact_alias"],
						"RowMenu_hostNotif"=>html_entity_decode($hostTp["tp_name"], ENT_QUOTES)." (".$contact["contact_host_notification_options"].")",
						"RowMenu_svNotif"=>html_entity_decode($svTp["tp_name"], ENT_QUOTES)." (".$contact["contact_service_notification_options"].")",
						"RowMenu_status"=>$contact["contact_activate"] ? $lang['enable'] : $lang['disable'],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'],"ldap_importL"=>"?p=".$p."&o=li", "ldap_importT"=>$lang['cct_ldap_import']));
	if ($oreon->optGen['ldap_auth_enable'])
		$tpl->assign('ldap', $oreon->optGen['ldap_auth_enable'] );

	#
	##Toolbar select $lang["lgd_more_actions"]
	#
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
	$form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete'], "mc"=>$lang['mchange'], "ms"=>$lang['enable'], "mu"=>$lang['disable']), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete'], "mc"=>$lang['mchange'], "ms"=>$lang['enable'], "mu"=>$lang['disable']), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listContact.ihtml");
?>