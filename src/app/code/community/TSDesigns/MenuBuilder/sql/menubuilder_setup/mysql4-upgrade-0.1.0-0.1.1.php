<?php

$installer = $this;
/* @var $installer TSDesigns_MenuBuilder_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

// Update Entities...
$installer->installEntities();

// Modify Groups and Attributes for Menu
$entityTypeId     = $installer->getEntityTypeId('menubuilder');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// update General Group
$installer->updateAttributeGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'attribute_group_name',
    'General Information'
);
$installer->updateAttributeGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'sort_order',
    '10'
);

// Add groups
$groups = array(
    'design'    => array(
        'name'  => 'Custom Design',
        'sort'  => 20,
        'id'    => null
    )
);

foreach ($groups as $k => $groupProp) {
    $installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupProp['name'], $groupProp['sort']);
    $groups[$k]['id'] = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupProp['name']);
}


// update attributes group and sort
$installer->getOrder(0);
$attributes = array(
	// General
    'link_to' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_category' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_cms_page' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_product' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_internal' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_external' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_target' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),    
    ),
    'name' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'description' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'is_active' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
	),
    'active_from' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
	),
    'active_to' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    
    // Design
    'template' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(10),
    ),
    'custom_template' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'display_mode' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'display_name' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'image' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
	),
    'custom_css_class' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'font_weight' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'font_color' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'background_color' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
);

$groups['general']['id'] = $attributeGroupId;
foreach ($attributes as $attributeCode => $attributeProp) {
    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $groups[$attributeProp['group']]['id'],
        $attributeCode,
        $attributeProp['sort']
    );
}

$installer->endSetup();
