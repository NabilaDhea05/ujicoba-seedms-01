<?php
//    SeedDMS. Document Management System
//    Copyright (C) 2013 Uwe Steinmann
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

require_once('inc.ClassControllerCommon.php');

class Controller {
	/**
	 * Create a controller from a class
	 *
	 * This method will check for a class file in the controller directory
	 * and returns an instance of it.
	 *
	 * @param string $class name of controller class
	 * @param array $params parameter passed to constructor of controller class
	 * @return object an object of a class implementing the view
	 */
	static function factory($class, $params=array()) { /* {{{ */
		global $settings, $session, $extMgr, $request, $logger, $notifier;
		if(!$class) {
			return null;
		}

		$classname = "SeedDMS_Controller_".$class;
		$filename = '';
		foreach($extMgr->getExtensionConfiguration() as $extname=>$extconf) {
			$filename = $settings->_rootDir.'ext/'.$extname.'/controllers/class.'.$class.".php";
			if(file_exists($filename)) {
				break;
			}
			$filename = '';
		}
		if(!$filename)
			$filename = $settings->_rootDir."controllers/class.".$class.".php";
		if(!file_exists($filename))
			$filename = '';
		if($filename) {
			require_once($filename);
			$controller = new $classname($params);
			/* Set some configuration parameters */
			$controller->setParam('class', $class);
			$controller->setParam('postVars', $_POST);
			$controller->setParam('getVars', $_GET);
			$controller->setParam('requestVars', $_REQUEST);
			$controller->setParam('session', $session);
			$controller->setParam('request', $request);
			$controller->setParam('settings', $settings);
			$controller->setParam('logger', $logger);
			$controller->setParam('notifier', $notifier);
			return $controller;
		}
		return null;
	} /* }}} */

}
