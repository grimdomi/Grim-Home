<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_compresspicuploads.php 5899 2013-09-29 13:14:50Z pwolfer $                          *
********************************************************************************************************/

/**
 * Resizes and compresses all uploaded pictures in /portal/pics/upload to save disk space
 *
 * @package module_system
 */
class class_systemtask_compresspicuploads extends class_systemtask_base implements interface_admin_systemtask {

    //class vars
    private $strPicsPath = "/portal/pics/upload";
    private $intMaxWidth = 1024;
    private $intMaxHeight = 1024;

    private $intFilesTotal = 0;
    private $intFilesProcessed = 0;

	/**
	 * contructor to call the base constructor
	 */
	public function __construct() {
		parent::__construct();

		//Increase max execution time
        @ini_set("max_execution_time", "3600");
    }


    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "";
    }


    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
    	return "compresspicuploads";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getLang("systemtask_compresspicuploads_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {
    	$strReturn = "";

    	$this->intMaxWidth = (int)$this->getParam("intMaxWidth");
    	$this->intMaxHeight = (int)$this->getParam("intMaxHeight");

    	$this->recursiveImageProcessing($this->strPicsPath);

    	//build the return string
    	$strReturn .= $this->getLang("systemtask_compresspicuploads_done")."<br />";
    	$strReturn .= $this->getLang("systemtask_compresspicuploads_found").": ".$this->intFilesTotal."<br />";
    	$strReturn .= $this->getLang("systemtask_compresspicuploads_processed").": ".$this->intFilesProcessed;
    	return $strReturn;
    }

    private function recursiveImageProcessing($strPath) {
        $objFilesystem = new class_filesystem();

        $arrFilesFolders = $objFilesystem->getCompleteList($strPath, array(".jpg", ".jpeg", ".png", ".gif"), array(), array(".", "..", ".svn"));
        $this->intFilesTotal += $arrFilesFolders["nrFiles"];

        foreach ($arrFilesFolders["folders"] as $strOneFolder) {
            $this->recursiveImageProcessing($strPath."/".$strOneFolder);
        }

        foreach ($arrFilesFolders["files"] as $arrOneFile) {
            $strImagePath = $strPath."/".$arrOneFile["filename"];

            $objImage = new class_image2();
            $objImage->setUseCache(false);
            $objImage->load($strImagePath);
            $objImage->addOperation(new class_image_scale($this->intMaxWidth, $this->intMaxHeight));
            if ($objImage->saveImage($strImagePath)) {
                $this->intFilesProcessed++;
            };
        }
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        $strReturn = "";

        //show input fields to choose maximal width and height
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("systemtask_compresspicuploads_hint"));
        $strReturn .= $this->objToolkit->divider();
        $strReturn .= $this->objToolkit->formInputText("intMaxWidth", $this->getLang("systemtask_compresspicuploads_width"), $this->intMaxWidth);
        $strReturn .= $this->objToolkit->formInputText("intMaxHeight", $this->getLang("systemtask_compresspicuploads_height"), $this->intMaxHeight);

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        return "&intMaxWidth=".$this->getParam('intMaxWidth')."&intMaxHeight=".$this->getParam('intMaxHeight');
    }

}
