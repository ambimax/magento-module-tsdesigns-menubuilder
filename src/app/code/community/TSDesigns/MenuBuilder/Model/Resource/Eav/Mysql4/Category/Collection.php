<?php

class TSDesigns_MenuBuilder_Model_Resource_Eav_Mysql4_Category_Collection
    extends Mage_Catalog_Model_Resource_Category_Collection
{
    /**
     * Never use flat table for category collection
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        return false;
    }
}