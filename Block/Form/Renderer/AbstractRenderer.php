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
namespace Ecomteck\CustomAttributeManagement\Block\Form\Renderer;

/**
 * EAV entity Attribute Form Renderer Abstract Block
 *
 * @author      Ecomteck <ecomteck@gmail.com>
 */
abstract class AbstractRenderer extends \Magento\Framework\View\Element\Template
{
    /**
     * Attribute instance
     *
     * @var \Magento\Eav\Model\Attribute
     */
    protected $_attribute;

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
     * Set attribute instance
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @return $this
     */
    public function setAttributeObject(\Magento\Eav\Model\Attribute $attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Return Attribute instance
     *
     * @return \Magento\Eav\Model\Attribute
     */
    public function getAttributeObject()
    {
        return $this->_attribute;
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
     * Return Entity object
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Return Data Form Filter or false
     *
     * @return \Magento\Framework\Data\Form\Filter\FilterInterface|false
     */
    protected function _getFormFilter()
    {
        $filterCode = $this->getAttributeObject()->getInputFilter();
        if ($filterCode) {
            $filterClass = 'Magento\\Framework\\Data\\Form\\Filter\\' . ucfirst($filterCode);
            if ($filterCode == 'date') {
                $format = $this->_localeDate->getDateFormat(
                    \IntlDateFormatter::SHORT
                );
                $filter = new $filterClass($format);
            } else {
                $filter = new $filterClass();
            }
            return $filter;
        }
        return false;
    }

    /**
     * Apply output filter to value
     *
     * @param string $value
     * @return string
     */
    protected function _applyOutputFilter($value)
    {
        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->outputFilter($value);
        }

        return $value;
    }

    /**
     * Return validate class by attribute input validation rule
     *
     * @return string|false
     */
    protected function _getInputValidateClass()
    {
        $class = false;
        $validateRules = $this->getAttributeObject()->getValidateRules();
        if (!empty($validateRules['input_validation'])) {
            switch ($validateRules['input_validation']) {
                case 'alphanumeric':
                    $class = 'validate-alphanum';
                    break;
                case 'numeric':
                    $class = 'validate-digits';
                    break;
                case 'alpha':
                    $class = 'validate-alpha';
                    break;
                case 'email':
                    $class = 'validate-email';
                    break;
                case 'url':
                    $class = 'validate-url';
                    break;
                case 'date':
                    $class = 'product-custom-option datetime-picker input-text validate-date';
                    break;
            }
        }
        return $class;
    }

    /**
     * Return array of validate classes
     *
     * @param bool $withRequired
     * @return array
     */
    protected function _getValidateClasses($withRequired = true)
    {
        $classes = [];
        if ($withRequired && $this->isRequired()) {
            $classes[] = 'required-entry';
        }
        $inputRuleClass = $this->_getInputValidateClass();
        if ($inputRuleClass) {
            $classes[] = $inputRuleClass;
        }
        return $classes;
    }

    /**
     * Return original entity value
     * Value didn't escape and filter
     *
     * @return string
     */
    public function getValue()
    {
        $value = $this->getEntity()->getData($this->getAttributeObject()->getAttributeCode());
        return $value;
    }

    /**
     * Return HTML id for element
     *
     * @param string|null $index
     * @return string
     */
    public function getHtmlId($index = null)
    {
        $format = $this->_fieldIdFormat;
        if ($index !== null) {
            $format .= '_%2$s';
        }
        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    /**
     * Return HTML id for element
     *
     * @param string|null $index
     * @return string
     */
    public function getFieldName($index = null)
    {
        $format = $this->_fieldNameFormat;
        if ($index !== null) {
            $format .= '[%2$s]';
        }
        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    /**
     * Return HTML class attribute value
     * Validate and rules
     *
     * @return string
     */
    public function getHtmlClass()
    {
        $classes = $this->_getValidateClasses(true);
        return empty($classes) ? '' : ' ' . implode(' ', $classes);
    }

    /**
     * Check is attribute value required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->getAttributeObject()->getIsRequired();
    }

    /**
     * Return attribute label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getAttributeObject()->getStoreLabel();
    }

    /**
     * Set format for HTML element(s) id attribute
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
     * Set format for HTML element(s) name attribute
     *
     * @param string $format
     * @return $this
     */
    public function setFieldNameFormat($format)
    {
        $this->_fieldNameFormat = $format;
        return $this;
    }
}
