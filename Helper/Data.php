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

namespace Ecomteck\CustomAttributeManagement\Helper;

/**
 * Enterprise EAV Data Helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Array of User Defined attribute codes per entity type code
     *
     * @var array
     */
    protected $_userDefinedAttributeCodes = [];

    /**
     * Eav config instance
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Locale date instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * FilterManager instance
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_localeDate = $localeDate;
        $this->filterManager = $filterManager;
        parent::__construct($context);
    }

    /**
     * Default attribute entity type code
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getEntityTypeCode()
    {
        throw new \Magento\Framework\Exception\LocalizedException(__('Use helper with defined EAV entity.'));
    }

    /**
     * Return available EAV entity attribute form as select options
     * @codeCoverageIgnore
     * @return array
     */
    public function getAttributeFormOptions()
    {
        return [['label' => __('Default EAV Form'), 'value' => 'default']];
    }

    /**
     * Check validation rules for specified input type and return possible warnings.
     *
     * @param string $frontendInput
     * @param array $validateRules
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkValidateRules($frontendInput, $validateRules)
    {
        $errors = [];
        switch ($frontendInput) {
            case 'text':
            case 'textarea':
            case 'multiline':
                if (isset($validateRules['min_text_length']) && isset($validateRules['max_text_length'])) {
                    $minTextLength = (int)$validateRules['min_text_length'];
                    $maxTextLength = (int)$validateRules['max_text_length'];
                    if ($minTextLength > $maxTextLength) {
                        $errors[] = __(
                            'Please correct the values for minimum and maximum text length validation rules.'
                        );
                    }
                }
                break;
            case 'date':
                if (isset($validateRules['date_range_min']) && isset($validateRules['date_range_max'])) {
                    $minValue = (int)$validateRules['date_range_min'];
                    $maxValue = (int)$validateRules['date_range_max'];
                    if ($minValue > $maxValue) {
                        $errors[] = __('Please correct the values for minimum and maximum date validation rules.');
                    }
                }
                break;
            default:
                break;
        }

        return $errors;
    }

    /**
     * Return data array of available attribute Input Types
     *
     * @param string|null $inputType
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAttributeInputTypes($inputType = null)
    {
        $inputTypes = [
            'text' => [
                'label' => __('Text Field'),
                'manage_options' => false,
                'validate_types' => ['min_text_length', 'max_text_length'],
                'validate_filters' => ['alphanumeric', 'numeric', 'alpha', 'url', 'email'],
                'filter_types' => ['striptags', 'escapehtml'],
                'backend_type' => 'varchar',
                'default_value' => 'text',
            ],
            'textarea' => [
                'label' => __('Text Area'),
                'manage_options' => false,
                'validate_types' => ['min_text_length', 'max_text_length'],
                'validate_filters' => [],
                'filter_types' => ['striptags', 'escapehtml'],
                'backend_type' => 'text',
                'default_value' => 'textarea',
            ],
            'multiline' => [
                'label' => __('Multiple Line'),
                'manage_options' => false,
                'validate_types' => ['min_text_length', 'max_text_length'],
                'validate_filters' => ['alphanumeric', 'numeric', 'alpha', 'url', 'email'],
                'filter_types' => ['striptags', 'escapehtml'],
                'backend_type' => 'text',
                'default_value' => 'text',
            ],
            'date' => [
                'label' => __('Date'),
                'manage_options' => false,
                'validate_types' => ['date_range_min', 'date_range_max'],
                'validate_filters' => ['date'],
                'filter_types' => ['date'],
                'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
                'backend_type' => 'datetime',
                'default_value' => 'date',
            ],
            'select' => [
                'label' => __('Dropdown'),
                'manage_options' => true,
                'option_default' => 'radio',
                'validate_types' => [],
                'validate_filters' => [],
                'filter_types' => [],
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_type' => 'int',
                'default_value' => false,
            ],
            'multiselect' => [
                'label' => __('Multiple Select'),
                'manage_options' => true,
                'option_default' => 'checkbox',
                'validate_types' => [],
                'filter_types' => [],
                'validate_filters' => [],
                'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_type' => 'varchar',
                'default_value' => false,
            ],
            'boolean' => [
                'label' => __('Yes/No'),
                'manage_options' => false,
                'validate_types' => [],
                'validate_filters' => [],
                'filter_types' => [],
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'backend_type' => 'int',
                'default_value' => 'yesno',
            ],
            'file' => [
                'label' => __('File (attachment)'),
                'manage_options' => false,
                'validate_types' => ['max_file_size', 'file_extensions'],
                'validate_filters' => [],
                'filter_types' => [],
                'backend_type' => 'varchar',
                'default_value' => false,
            ],
            'image' => [
                'label' => __('Image File'),
                'manage_options' => false,
                'validate_types' => ['max_file_size', 'max_image_width', 'max_image_heght'],
                'validate_filters' => [],
                'filter_types' => [],
                'backend_type' => 'varchar',
                'default_value' => false,
            ],
        ];

        if (null === $inputType) {
            return $inputTypes;
        } elseif (isset($inputTypes[$inputType])) {
            return $inputTypes[$inputType];
        }
        return [];
    }

    /**
     * Return options array of EAV entity attribute Front-end Input types
     *
     * @return array
     */
    public function getFrontendInputOptions()
    {
        $inputTypes = $this->getAttributeInputTypes();
        $options = [];
        foreach ($inputTypes as $k => $v) {
            $options[] = ['value' => $k, 'label' => $v['label']];
        }

        return $options;
    }

    /**
     * Return available attribute validation filters
     *
     * @return array
     */
    public function getAttributeValidateFilters()
    {
        return [
            'alphanumeric' => __('Alphanumeric'),
            'numeric' => __('Numeric Only'),
            'alpha' => __('Alpha Only'),
            'url' => __('URL'),
            'email' => __('Email'),
            'date' => __('Date')
        ];
    }

    /**
     * Return available attribute filter types
     *
     * @return array
     */
    public function getAttributeFilterTypes()
    {
        return [
            'striptags' => __('Strip HTML Tags'),
            'escapehtml' => __('Escape HTML Entities'),
            'date' => __('Normalize Date')
        ];
    }

    /**
     * Get EAV attribute's elements scope
     *
     * @return array
     */
    public function getAttributeElementScopes()
    {
        return [
            'is_required' => 'website',
            'is_visible' => 'website',
            'multiline_count' => 'website',
            'default_value_text' => 'website',
            'default_value_yesno' => 'website',
            'default_value_date' => 'website',
            'default_value_textarea' => 'website',
            'date_range_min' => 'website',
            'date_range_max' => 'website'
        ];
    }

    /**
     * Return default value field name by attribute input type
     *
     * @param string $inputType
     * @return string|false
     */
    public function getAttributeDefaultValueByInput($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (isset($inputTypes[$inputType])) {
            $value = $inputTypes[$inputType]['default_value'];
            if ($value) {
                return 'default_value_' . $value;
            }
        }
        return false;
    }

    /**
     * Return array of attribute validate rules
     *
     * @param string $inputType
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttributeValidateRules($inputType, array $data)
    {
        $inputTypes = $this->getAttributeInputTypes();
        $rules = [];
        if (isset($inputTypes[$inputType])) {
            foreach ($inputTypes[$inputType]['validate_types'] as $validateType) {
                if (!empty($data[$validateType])) {
                    $rules[$validateType] = $data[$validateType];
                } elseif (!empty($data['scope_' . $validateType])) {
                    $rules[$validateType] = $data['scope_' . $validateType];
                }
            }
            //transform date validate rules to timestamp
            if ($inputType === 'date') {
                foreach (['date_range_min', 'date_range_max'] as $dateRangeBorder) {
                    if (isset($rules[$dateRangeBorder])) {
                        $rules[$dateRangeBorder] = (new \DateTime($rules[$dateRangeBorder]))->getTimestamp();
                    }
                }
            }

            if (!empty($inputTypes[$inputType]['validate_filters']) && !empty($data['input_validation'])) {
                if (in_array($data['input_validation'], $inputTypes[$inputType]['validate_filters'])) {
                    $rules['input_validation'] = $data['input_validation'];
                }
            }
        }
        return $rules;
    }

    /**
     * Return default attribute back-end model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeBackendModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['backend_model'])) {
            return $inputTypes[$inputType]['backend_model'];
        }
        return null;
    }

    /**
     * Return default attribute source model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeSourceModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['source_model'])) {
            return $inputTypes[$inputType]['source_model'];
        }
        return null;
    }

    /**
     * Return default attribute backend storage type by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeBackendTypeByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['backend_type'])) {
            return $inputTypes[$inputType]['backend_type'];
        }
        return null;
    }

    /**
     * Returns array of user defined attribute codes
     *
     * @param string $entityTypeCode
     * @return array
     */
    protected function _getUserDefinedAttributeCodes($entityTypeCode)
    {
        if (empty($this->_userDefinedAttributeCodes[$entityTypeCode])) {
            $this->_userDefinedAttributeCodes[$entityTypeCode] = [];
            /* @var $config \Magento\Eav\Model\Config */
            $config = $this->_eavConfig;
            foreach ($config->getEntityAttributeCodes($entityTypeCode) as $attributeCode) {
                $attribute = $config->getAttribute($entityTypeCode, $attributeCode);
                if ($attribute && $attribute->getIsUserDefined()) {
                    $this->_userDefinedAttributeCodes[$entityTypeCode][] = $attributeCode;
                }
            }
        }
        return $this->_userDefinedAttributeCodes[$entityTypeCode];
    }

    /**
     * Returns array of user defined attribute codes for EAV entity type
     *
     * @return array
     */
    public function getUserDefinedAttributeCodes()
    {
        return $this->_getUserDefinedAttributeCodes($this->_getEntityTypeCode());
    }

    /**
     * return date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
    }

    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function filterPostData($data)
    {
        if ($data) {
            //labels
            foreach ($data['frontend_label'] as &$value) {
                if ($value) {
                    $value = $this->filterManager->stripTags($value);
                }
            }

            //validate attribute_code
            if (isset($data['attribute_code'])) {
                $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z_0-9]{1,255}$/']);
                if (!$validatorAttrCode->isValid($data['attribute_code'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            'The attribute code is invalid. Please use only letters (a-z), '
                            . 'numbers (0-9) or underscores (_) in this field. The first character should be a letter.'
                        )
                    );
                }
            }
        }
        return $data;
    }
}
