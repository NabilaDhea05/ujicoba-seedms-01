<?php
declare(strict_types=1);

/**
 * Some definitions for access control
 *
 * @category   DMS
 * @package    SeedDMS_Core
 * @license    GPL 2
 * @version    @version@
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Used to indicate that a search should return all
 * results in the ACL table. See {@link SeedDMS_Core_Folder::getAccessList()}
 */
define("M_ANY", -1);

/**
 * No access at all
 */
define("M_NONE", 1);

/**
 * Read access only
 */
define("M_READ", 2);

/**
 * Read and write access only
 */
define("M_READWRITE", 3);

/**
 * Unrestricted access
 */
define("M_ALL", 4);

/**
 * Lowest access right
 */
define("M_LOWEST_RIGHT", 1);

/**
 * Highest access right
 */
define("M_HIGHEST_RIGHT", 4);

/**
 * Operation greater than or equal
 */
define("O_GTEQ", ">=");

/**
 * Operation lower than or equal
 */
define("O_LTEQ", "<=");

/**
 * Operation equal
 */
define("O_EQ", "=");

/**
 * Folder notification
 */
define("T_FOLDER", 1);		//TargetType = Folder

/**
 * Document notification
 */
define("T_DOCUMENT", 2);	//    "      = Document

/**
 * Notify on all actions on the folder/document
 */
define("N_ALL", 0);

/**
 * Notify when object has been deleted
 */
define("N_DELETE", 1);

/**
 * Notify when object has been moved
 */
define("N_MOVE", 2);

/**
 * Notify when object has been updated (no new version)
 */
define("N_UPDATE", 3);

/**
 * Notify when document has new version
 */
define("N_NEW_VERSION", 4);

/**
 * Notify when version of document was deleted
 */
define("N_DELETE_VERSION", 5);

/**
 * Notify when version of document was deleted
 */
define("N_ADD_DOCUMENT", 6);
