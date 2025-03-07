<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010 Matteo Lucarelli
//    Copyright (C) 2010-2016 Uwe Steinmann
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.LogInit.php");
include("../inc/inc.Language.php");
include("../inc/inc.Init.php");
include("../inc/inc.Extension.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");

/* Check if the form data comes from a trusted request */
if(!checkFormKey('setexpires')) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_request_token"))),getMLText("invalid_request_token"));
}

if (!isset($_POST["documentid"]) || !is_numeric($_POST["documentid"]) || intval($_POST["documentid"])<1) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}

$documentid = $_POST["documentid"];
$document = $dms->getDocument($documentid);

if (!is_object($document)) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}

if ($document->getAccessMode($user) < M_READWRITE) {
	UI::exitError(getMLText("document_title", array("documentname" => $document->getName())),getMLText("access_denied"));
}

if (!isset($_POST["presetexpdate"]) || $_POST["presetexpdate"] == "") {
	UI::exitError(getMLText("document_title", array("documentname" => $document->getName())),getMLText("invalid_expiration_date"));
}

if ($_POST["presetexpdate"] == 'date' && $_POST["expdate"] == "") {
	UI::exitError(getMLText("document_title", array("documentname" => $document->getName())),getMLText("invalid_expiration_date"));
}

switch($_POST["presetexpdate"]) {
case "date":
	$expires = makeTsFromDate($_POST["expdate"]);
	break;
case "never":
	$expires = null;
	break;
default:
	$expires = getTsByPeriod($_POST["presetexpdate"], 's');
	break;
}

if(isset($GLOBALS['SEEDDMS_HOOKS']['setExpires'])) {
	foreach($GLOBALS['SEEDDMS_HOOKS']['setExpires'] as $hookObj) {
		if (method_exists($hookObj, 'preSetExpires')) {
			$hookObj->preSetExpires(null, array('document'=>$document, 'expires'=>&$expires));
		}
	}
}

if (!$document->setExpires($expires)){
	UI::exitError(getMLText("document_title", array("documentname" => $document->getName())),getMLText("error_occured"));
} else {
	if($expires)
		$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_expiration_date_set', array('date'=>getReadableDate($expires)))));
	else
		$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_expiration_date_cleared')));
}

$document->verifyLastestContentExpriry();

if(isset($GLOBALS['SEEDDMS_HOOKS']['setExpires'])) {
	foreach($GLOBALS['SEEDDMS_HOOKS']['setExpires'] as $hookObj) {
		if (method_exists($hookObj, 'postSetExpires')) {
			$hookObj->postSetExpires(null, array('document'=>$document, 'expires'=>$expires));
		}
	}
}

add_log_line("?documentid=".$documentid);

header("Location:../out/out.ViewDocument.php?documentid=".$documentid);

?>
