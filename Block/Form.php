<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomAttributeManagement
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomAttributeManagement\Block;

/**
 * EAV Dynamic attributes Form Block
 *
 * @author      Ecomteck <ecomteck@gmail.com>
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * Name of the block in layout update xml file
     *
     * @var string
     */
    protected $_xmlBlockName = '';

    /**
     * Class path of Form Model
     *
     * @var string
     */
    protected $_formModelPath = '';

    /**
     * EAV Form Type code
     *
     * @var string
     */
    protected $_formCode;

    /**
     * Entity model class type for new entity object
     *
     * @var string
     */
    protected $_entityModelClass;

    /**
     * Entity type instance
     *
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_entityType;

    /**
     * EAV form instance
     *
     * @var \Magento\Eav\Model\Form
     */
    protected $_form;

    /**
     * EAV Entity Model
     *
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_entity;

    /**
     * Format for HTML elements id attribute
     *
     * @var string
     */
    protected $_fieldIdFormat = '%1$s';

    /**
     * Format for HTML elements name attribute
     *
     * @var string
     */
    protected $_fieldNameFormat = '%1$s';

    /**
     * @var \Magento\Framework\Data\Collection\ModelFactory
     */
    protected $_modelFactory;

    /**
     * @var \Magento\Eav\Model\Form\Factory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Collection\ModelFactory $modelFactory
     * @param \Magento\Eav\Model\Form\Factory $formFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Collection\ModelFactory $modelFactory,
        \Magento\Eav\Model\Form\Factory $formFactory,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->_modelFactory = $modelFactory;
        $this->_formFactory = $formFactory;
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }

    /**
     * Try to get EAV Form Template Block
     * Get Attribute renderers from it, and add to self
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if (empty($this->_xmlBlockName)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The current module XML block name is undefined.')
            );
        }
        if (empty($this->_formModelPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The current module form model pathname is undefined.')
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Return attribute renderer by frontend input type
     *
     * @param string $type
     * @return \Ecomteck\CustomAttributeManagement\Block\Form\Renderer\AbstractRenderer
     */
    public function getRenderer($type)
    {
        return $this->getLayout()->getBlock($this->_xmlBlockName)->getChildBlock($type);
    }

    /**
     * Set Entity object
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return $this
     */
    public function setEntity(\Magento\Framework\Model\AbstractModel $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Set entity model class for new object
     *
     * @param string $model
     * @return $this
     */
    public function setEntityModelClass($model)
    {
        $this->_entityModelClass = $model;
        return $this;
    }

    /**
     * Set Entity type if entity model entity type is not defined or is different
     *
     * @param int|string|\Magento\Eav\Model\Entity\Type $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $this->_eavConfig->getEntityType($entityType);
        return $this;
    }

    /**
     * Return Entity object
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getEntity()
    {
        if ($this->_entity === null) {
            if ($this->_entityModelClass) {
                $this->_entity = $this->_modelFactory->create($this->_entityModelClass);
            }
        }
        return $this->_entity;
    }

    /**
     * Set EAV entity form instance
     *
     * @param \Magento\Eav\Model\Form $form
     * @return $this
     */
    public function setForm(\Magento\Eav\Model\Form $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Set EAV entity Form code
     *
     * @param string $code
     * @return $this
     */
    public function setFormCode($code)
    {
        $this->_formCode = $code;
        return $this;
    }

    /**
     * Return EAV entity Form instance
     *
     * @return \Magento\Eav\Model\Form
     */
    public function getForm()
    {
        if ($this->_form === null) {
            $this->_form = $this->_formFactory->create(
                $this->_formModelPath
            )->setFormCode(
                $this->_formCode
            )->setEntity(
                $this->getEntity()
            );
            if ($this->_entityType) {
                $this->_form->setEntityType($this->_entityType);
            }
            $this->_form->initDefaultValues();
        }
        return $this->_form;
    }

    /**
     * Check EAV entity form has User defined attributes
     *
     * @return boolean
     */
    public function hasUserDefinedAttributes()
    {
        return count($this->getUserDefinedAttributes()) > 0;
    }

    /**
     * Return array of user defined attributes
     *
     * @return array
     */
    public function getUserDefinedAttributes()
    {
        $attributes = [];
        foreach ($this->getForm()->getUserAttributes() as $attribute) {
            if ($attribute->getIsVisible()) {
                $attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $attributes;
    }

    /**
     * Render attribute row and return HTML
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @return string|false
     */
    public function getAttributeHtml(\Magento\Eav\Model\Attribute $attribute)
    {
        $type = $attribute->getFrontendInput();
        $block = $this->getRenderer($type);
        if ($block) {
            $block->setAttributeObject(
                $attribute
            )->setEntity(
                $this->getEntity()
            )->setFieldIdFormat(
                $this->_fieldIdFormat
            )->setFieldNameFormat(
                $this->_fieldNameFormat
            );
            return $block->toHtml();
        }
        return false;
    }

    /**
     * Set format for HTML elements id attribute
     *
     * @param string $format
     * @return $this
     */
    public function setFieldIdFormat($format)
    {
        $this->_fieldIdFormat = $format;
        return $this;
    }

    /**
     * Set format for HTML elements name attribute
     *
     * @param string $format
     * @return $this
     */
    public function setFieldNameFormat($format)
    {
        $this->_fieldNameFormat = $format;
        return $this;
    }

    /**
     * Check is show HTML container
     *
     * @return boolean
     */
    public function isShowContainer()
    {
        if ($this->hasData('show_container')) {
            return $this->getData('show_container');
        }
        return true;
    }
}
