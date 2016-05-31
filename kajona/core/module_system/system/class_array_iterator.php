<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_array_iterator.php 5409 2012-12-30 13:09:07Z sidler $                                     *
********************************************************************************************************/

/**
 * Class to iterator over an array.
 * This class is able to create a pageview-mechanism
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_array_iterator implements interface_iterator {

    protected $arrElements = array();
    protected $intArrCursor = 0;

    protected $intElementsPerPage = 15;

    /**
     * Constructor
     * @param $arrElements
     * @return \class_array_iterator
     *
     */
	public function __construct($arrElements) {

        $this->intElementsPerPage = _admin_nr_of_rows_;
        $this->setArrElements($arrElements);
    }

    /**
     * Sets the ArrElements
     *
     * @param $arrElements
     */
    public function setArrElements($arrElements)
    {
        $this->arrElements = array();

        //Loop over elements to create numeric indices
        if (count($arrElements) > 0) {
            foreach ($arrElements as $objOneElement) {
                $this->arrElements[] = $objOneElement;
            }
        }
    }

    /**
     * Returns the current element
     *
     * @return mixed
     */
    public function getCurrentElement() {
        return $this->arrElements[$this->intArrCursor];
    }

    /**
     * Returns the next element, null if no further element available
     *
     * @return mixed
     */
    public function getNextElement() {
        if(!$this->isNext())
            return null;

        return $this->arrElements[++$this->intArrCursor];
    }

    /**
     * Checks if theres one more element to return
     *
     * @return bool
     */
    public function isNext() {
        return ($this->intArrCursor < count($this->arrElements));
    }

    /**
     * Returns the first element of the colection,
     * rewinds the cursor
     *
     * @return mixed
     */
    public function getFirstElement() {
        $this->intArrCursor = 0;
        return $this->arrElements[$this->intArrCursor];
    }

    /**
     * Returns the number of elements
     *
     * @return int
     */
    public function getNumberOfElements() {
        return count($this->arrElements);
    }

    // --- PageViewStuff ------------------------------------------------------------------------------------

    /**
     * Set the number of elements per page
     *
     * @param int $intElements
     */
    public function setIntElementsPerPage($intElements) {
        if((int)$intElements > 0) {
            $this->intElementsPerPage = (int)$intElements;
        } else {
            $this->intElementsPerPage = 100;
        }
    }

    /**
     * Set the cursor to a defined position
     *
     * @param int $intElement
     * @return bool
     */
    public function setCursorPosition($intElement) {
        if($this->getNumberOfElements() > $intElement) {
            $this->intArrCursor = $intElement;
            return true;
        }
        else
            return false;

    }

    /**
     * Returns the number of pages available
     *
     * @return int
     */
    public function getNrOfPages() {
        if($this->intElementsPerPage == (int)0)
            return 0;

        return ceil($this->getNumberOfElements() / $this->intElementsPerPage);
    }

    /**
     * Returns the elements placed on the given page
     *
     * @param int $intPageNumber
     * @return array
     */
    public function getElementsOnPage($intPageNumber) {
        if((int)$intPageNumber <= 0)
            $intPageNumber = 1;

        $arrReturn = array();
        //calc elements to return
        $intStart = ($intPageNumber * $this->intElementsPerPage)-$this->intElementsPerPage;
        $intEnd = $this->intElementsPerPage + $intStart -1;

        if($intEnd > $this->getNumberOfElements())
            $intEnd = $this->getNumberOfElements()-1;

        for($intI = $intStart; $intI <= $intEnd; $intI++)  {
            if(!$this->setCursorPosition($intI))
                break;
            $arrReturn[] = $this->arrElements[$this->intArrCursor];
        }
        return $arrReturn;
    }
}
