<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: image.php 5903 2013-09-30 13:40:29Z sidler $                                                    *
********************************************************************************************************/


/**
 * This class can be used to create fast "on-the-fly" resizes of images
 * or to create a captcha image
 * To resize an image, you can call this class like
 * _webpath_/image.php?image=path/to/image.jpg&maxWidth=200&maxHeight=200
 * To resize and crop an image to a fixed size, you can call this class like
 * _webpath_/image.php?image=path/to/image.jpg&fixedWidth=200&fixedHeight=200
 * To create a captcha image, you can call this class like
 * _webpath_/image.php?image=kajonaCaptcha
 * If used with jpeg-pictures, the param quality=[1-95] can be used, default value is 90.
 * The images are directly sent to the browser, so include the calling in img-tags.
 * ATTENTION:
 * Make sure to use urlencoded image-paths!
 * The params maxWidth, maxHeight and quality are optional. If fixedWidth and fixedHeight
 * are set, maxWidth and maxHeight won't be used.
 *
 * @package module_system
 */
class class_flyimage {

    private $strFilename;
    private $intMaxWidth = 0;
    private $intMaxHeight = 0;
    private $intFixedWidth = 0;
    private $intFixedHeight = 0;

    private $intQuality;

    private $strElementId;
    private $strSystemid;

    /**
     * constructor, init the parent-class
     */
    public function __construct() {
        //Loading all configs...
        class_carrier::getInstance();
        //find the params to use
        $this->strFilename = urldecode(getGet("image"));
        //avoid directory traversing
        $this->strFilename = str_replace("../", "", $this->strFilename);

        $this->intMaxHeight = (int)getGet("maxHeight");
        if($this->intMaxHeight < 0)
            $this->intMaxHeight = 0;

        $this->intMaxWidth = (int)getGet("maxWidth");
        if($this->intMaxWidth < 0)
            $this->intMaxWidth = 0;

        $this->intFixedHeight = (int)getGet("fixedHeight");
        if($this->intFixedHeight < 0 || $this->intFixedHeight > 2000)
            $this->intFixedHeight = 0;

        $this->intFixedWidth = (int)getGet("fixedWidth");
        if($this->intFixedWidth < 0 || $this->intFixedWidth > 2000)
            $this->intFixedWidth = 0;

        $this->intQuality = (int)getGet("quality");
        if($this->intQuality <= 0 || $this->intQuality > 100)
            $this->intQuality = 90;


        $this->strSystemid = getGet("systemid");
        $this->strElementId = getGet("elementid");

    }

    /**
     * Here happens the magic: creating the image and sending it to the browser
     */
    public function generateImage() {

        //switch the different modes - may be want to generate a detailed image-view
        if(validateSystemid($this->strSystemid) && validateSystemid($this->strElementId)) {
            class_carrier::getInstance()->getObjConfig()->loadConfigsDatabase(class_carrier::getInstance()->getObjDB());
            $this->generateMediamanagerImage();
        }
        else {
            class_carrier::getInstance()->getObjSession()->sessionClose();
            $this->resizeImage();
        }
    }

    /**
     * Wrapper to load a single element and generate the image
     */
    private function generateMediamanagerImage() {
        if(class_module_system_module::getModuleByName("mediamanager") !== null) {
            $objElement = new class_module_pages_pageelement($this->strElementId);
            $objPortalElement = $objElement->getConcretePortalInstance();

            $objFile = new class_module_mediamanager_file($this->strSystemid);


            if($objFile->rightView()) {

                $arrElementData = $objPortalElement->getElementContent($objElement->getSystemid());

                class_session::getInstance()->sessionClose();
                if (is_file(_realpath_.$objFile->getStrFilename())) {

                    $objImage = new class_image2();
                    $objImage->load($objFile->getStrFilename());
                    $objImage->addOperation(new class_image_scale($arrElementData["gallery_maxw_d"], $arrElementData["gallery_maxh_d"]));
                    $objImage->addOperation(new class_image_text($arrElementData["gallery_text"], $arrElementData["gallery_text_x"], $arrElementData["gallery_text_y"], 10, "#ffffff"));

                    if(is_file(_realpath_.$arrElementData["gallery_overlay"])) {
                        $objImageOverlay = new class_image2();
                        $objImageOverlay->load($arrElementData["gallery_overlay"]);
                        $objImage->addOperation(new class_image_overlay($arrElementData["gallery_overlay"], $arrElementData["gallery_text_x"], $arrElementData["gallery_text_y"]));
                    }
                    $objImage->setJpegQuality((int)$this->intQuality);
                    $objImage->sendToBrowser();
                }

            }
            else {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_FORBIDDEN);
                class_response_object::getInstance()->sendHeaders();
                return;
            }
        }

        class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_NOT_FOUND);
        class_response_object::getInstance()->sendHeaders();
    }

    /**
     * Wrapper to the real, fast resizing
     */
    private function resizeImage() {
        //Load the image-dimensions
        if(is_file(_realpath_ . $this->strFilename) && uniStrpos($this->strFilename, "/files") !== false) {

            //check headers, maybe execution could be terminated right here
            if(checkConditionalGetHeaders(md5(md5_file(_realpath_ . $this->strFilename) . $this->intMaxWidth . $this->intMaxHeight . $this->intFixedWidth . $this->intFixedHeight))) {
                class_response_object::getInstance()->sendHeaders();
                return;
            }

            $objImage = new class_image2();
            $objImage->load($this->strFilename);
            $objImage->addOperation(new class_image_scale_and_crop($this->intFixedWidth, $this->intFixedHeight));
            $objImage->addOperation(new class_image_scale($this->intMaxWidth, $this->intMaxHeight));

            //send the headers for conditional gets
            setConditionalGetHeaders(md5(md5_file(_realpath_ . $this->strFilename) . $this->intMaxWidth . $this->intMaxHeight . $this->intFixedWidth . $this->intFixedHeight));

            //TODO: add expires header for browser caching (optional)
            /*
            $intCacheSeconds = 604800; //default: 1 week (60*60*24*7)
            header("Expires: ".gmdate("D, d M Y H:i:s", time() + $intCacheSeconds)." GMT", true);
            header("Cache-Control: public, max-age=".$intCacheSeconds, true);
            header("Pragma: ", true);
            */

            //and send it to the browser
            $objImage->setJpegQuality((int)$this->intQuality);
            $objImage->sendToBrowser();
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_NOT_FOUND);
            class_response_object::getInstance()->sendHeaders();
        }
    }


    /**
     * Generates a captcha image to defend bots.
     * To generate a captcha image, use "kajonaCaptcha" as image-param
     * when calling image.php
     * Up to now, the size-params are ignored during the creation of a
     * captcha image

     */
    public function generateCaptchaImage() {
        if($this->intMaxWidth == 0 || $this->intMaxWidth > 500)
            $intWidth = 200;
        else
            $intWidth = $this->intMaxWidth;

        if($this->intMaxHeight == 0 || $this->intMaxHeight > 500)
            $intHeight = 50;
        else
            $intHeight = $this->intMaxHeight;


        $intMinfontSize = 15;
        $intMaxFontSize = 22;
        $intWidthPerChar = 30;
        $strCharsPossible = "abcdefghijklmnpqrstuvwxyz123456789";
        $intHorizontalOffset = 10;
        $intVerticalOffset = 10;
        $intForegroundOffset = 2;

        $strCharactersPlaced = "";

        //init the random-function
        srand((double)microtime() * 1000000);


        //create a blank image
        $objImage = new class_image();
        $objImage->setIntHeight($intHeight);
        $objImage->setIntWidth($intWidth);
        $objImage->createBlankImage();
        //create a white background
        $intWhite = $objImage->registerColor(255, 255, 255);
        $intBlack = $objImage->registerColor(0, 0, 0);
        $objImage->drawFilledRectangle(0, 0, $intWidth, $intHeight, $intWhite);

        //draw vertical lines
        $intStart = 5;
        while($intStart < $intWidth - 5) {
            $objImage->drawLine($intStart, 0, $intStart, $intWidth, $this->generateGreyLikeColor($objImage));
            $intStart += rand(10, 17);
        }
        //draw horizontal lines
        $intStart = 5;
        while($intStart < $intHeight - 5) {
            $objImage->drawLine(0, $intStart, $intWhite, $intStart, $this->generateGreyLikeColor($objImage));
            $intStart += rand(10, 17);
        }

        //draw floating horizontal lines
        for($intI = 0; $intI <= 3; $intI++) {
            $intXPrev = 0;
            $intYPrev = rand(0, $intHeight);
            while($intXPrev <= $intWidth) {
                $intNewX = rand($intXPrev, $intXPrev + 50);
                $intNewY = rand(0, $intHeight);
                $objImage->drawLine($intXPrev, $intYPrev, $intNewX, $intNewY, $this->generateGreyLikeColor($objImage));
                $intXPrev = $intNewX;
                $intYPrev = $intNewY;
            }
        }

        //calculate number of characters on the image
        $intNumberOfChars = floor($intWidth / $intWidthPerChar);

        //place characters in the image
        for($intI = 0; $intI < $intNumberOfChars; $intI++) {
            //character to place
            $strCurrentChar = $strCharsPossible[rand(0, (uniStrlen($strCharsPossible) - 1))];
            $strCharactersPlaced .= $strCurrentChar;
            //color to use
            $intCol1 = rand(0, 200);
            $intCol2 = rand(0, 200);
            $intCol3 = rand(0, 200);
            $strColor = "" . $intCol1 . "," . $intCol2 . "," . $intCol3;
            $strColorForeground = "" . ($intCol1 + 50) . "," . ($intCol2 + 50) . "," . ($intCol3 + 50);
            //fontsize
            $intSize = rand($intMinfontSize, $intMaxFontSize);
            //calculate x and y pos
            $intX = $intHorizontalOffset + ($intI * $intWidthPerChar);
            $intY = $intHeight - rand($intVerticalOffset, $intHeight - $intMaxFontSize);
            //the angle
            $intAngle = rand(-30, 30);
            //place the background character
            $objImage->imageText($strCurrentChar, $intX, $intY, $intSize, $strColor, "dejavusans.ttf", false, $intAngle);
            //place the foreground charater
            $objImage->imageText($strCurrentChar, $intX + $intForegroundOffset, $intY + $intForegroundOffset, $intSize, $strColorForeground, "dejavusans.ttf", false, $intAngle);
        }

        //register placed string to session
        class_carrier::getInstance()->getObjSession()->setCaptchaCode($strCharactersPlaced);

        //and send it to the browser
        //$objImage->saveImage("/test.jpg");
        //echo "<img src=\""._webpath_."/test.jpg\" />";
        $objImage->setBitNeedToSave(false);

        //force no-cache headers
        class_response_object::getInstance()->addHeader("Expires: Thu, 19 Nov 1981 08:52:00 GMT", true);
        class_response_object::getInstance()->addHeader("Cache-Control: no-store, no-cache, must-revalidate, private", true);
        class_response_object::getInstance()->addHeader("Pragma: no-cache", true);

        $objImage->sendImageToBrowser(70);
        $objImage->releaseResources();
    }

    /**
     * Generates a greyish color and registers the color to the image
     *
     * @return int color-id in image
     */
    private function generateGreyLikeColor($objImage) {
        return $objImage->registerColor(rand(150, 230), rand(150, 230), rand(150, 230));
    }

    /**
     * Returns the filename of the current image
     *
     * @return string
     */
    public function getImageFilename() {
        return $this->strFilename;
    }
}

define("_block_config_db_loading_", true);
define("_autotesting_", false);

$objImage = new class_flyimage();
if($objImage->getImageFilename() == "kajonaCaptcha") {
    $objImage->generateCaptchaImage();
}
else {
    $objImage->generateImage();
}

