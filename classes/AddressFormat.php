<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class AddressFormatCore
 */
class AddressFormatCore extends ObjectModel
{
    /** @var int $id_address_format Address format */
    public $id_address_format;

    /** @var int $id_country Country ID */
    public $id_country;

    /** @var string $format Format */
    public $format;

    protected $_errorFormatList = array();

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'address_format',
        'primary' => 'id_country',
        'fields' => array(
            'format' =>    array('type' => self::TYPE_HTML, 'validate' => 'isGenericName', 'required' => true),
            'id_country' => array('type' => self::TYPE_INT),
        ),
    );

    /** @var array Default required form fields list */
    public static $requireFormFieldsList = array(
        'firstname',
        'lastname',
        'address1',
        'city',
        'Country:name',
    );

    /** @var array Default forbidden property list */
    public static $forbiddenPropertyList = array(
        'deleted',
        'date_add',
        'alias',
        'secure_key',
        'note',
        'newsletter',
        'ip_registration_newsletter',
        'newsletter_date_add',
        'optin',
        'passwd',
        'last_passwd_gen',
        'active',
        'is_guest',
        'date_upd',
        'country',
        'years',
        'days',
        'months',
        'description',
        'meta_description',
        'short_description',
        'link_rewrite',
        'meta_title',
        'meta_keywords',
        'display_tax_label',
        'need_zip_code',
        'contains_states',
        'call_prefixes',
        'show_public_prices',
        'max_payment',
        'max_payment_days',
        'geoloc_postcode',
        'logged',
        'account_number',
        'groupBox',
        'ape',
        'max_payment',
        'outstanding_allow_amount',
        'call_prefix',
        'definition',
        'debug_list',
    );

    /** @var array Default formbidden class list */
    public static $forbiddenClassList = array(
        'Manufacturer',
        'Supplier',
    );

    const _CLEANING_REGEX_ = '#([^\w:_]+)#i';

    /**
     * Check if the the association of the field name and a class name
     * is valid
     *
     * @param string $className The name class
     * @param string $fieldName The property name
     * @param bool   $isIdField Do we have to allow a property name to be started with 'id_'
     *
     * @return bool Association of the field and class name is valid
     */
    protected function _checkValidateClassField($className, $fieldName, $isIdField)
    {
        $isValid = false;

        if (!class_exists($className)) {
            $this->_errorFormatList[] = Tools::displayError('This class name does not exist.').
            ': '.$className;
        } else {
            $obj = new $className();
            $reflect = new ReflectionObject($obj);

            // Check if the property is accessible
            $publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($publicProperties as $property) {
                $propertyName = $property->getName();
                if (($propertyName == $fieldName) && ($isIdField ||
                        (!preg_match('/\bid\b|id_\w+|\bid[A-Z]\w+/', $propertyName)))) {
                    $isValid = true;
                }
            }

            if (!$isValid) {
                $this->_errorFormatList[] = Tools::displayError('This property does not exist in the class or is forbidden.').
                ': '.$className.': '.$fieldName;
            }

            unset($obj);
            unset($reflect);
        }

        return $isValid;
    }

    /**
     * Verify the existence of a field name and check the availability
     * of an association between a field name and a class (ClassName:fieldName)
     * if the separator is overview
     *
     * @param string $patternName    The composition of the class and field name
     * @param string $fieldsValidate The list of available field for the Address class
     * @todo: Why is $fieldsValidate unused?
     */
    protected function _checkLiableAssociation($patternName, $fieldsValidate)
    {
        $patternName = trim($patternName);

        if ($associationName = explode(':', $patternName)) {
            $totalNameUsed = count($associationName);
            if ($totalNameUsed > 2) {
                $this->_errorFormatList[] = Tools::displayError('This association has too many elements.');
            } elseif ($totalNameUsed == 1) {
                $associationName[0] = strtolower($associationName[0]);
                if (in_array($associationName[0], self::$forbiddenPropertyList) ||
                    !$this->_checkValidateClassField('Address', $associationName[0], false)) {
                    $this->_errorFormatList[] = Tools::displayError('This name is not allowed.').': '.
                    $associationName[0];
                }
            } elseif ($totalNameUsed == 2) {
                if (empty($associationName[0]) || empty($associationName[1])) {
                    $this->_errorFormatList[] = Tools::displayError('Syntax error with this pattern.').': '.$patternName;
                } else {
                    $associationName[0] = ucfirst($associationName[0]);
                    $associationName[1] = strtolower($associationName[1]);

                    if (in_array($associationName[0], self::$forbiddenClassList)) {
                        $this->_errorFormatList[] = Tools::displayError('This name is not allowed.').': '.
                        $associationName[0];
                    } else {
                        // Check if the id field name exist in the Address class
                        // Don't check this attribute on Address (no sense)
                        if ($associationName[0] != 'Address') {
                            $this->_checkValidateClassField('Address', 'id_'.strtolower($associationName[0]), true);
                        }

                        // Check if the field name exist in the class write by the user
                        $this->_checkValidateClassField($associationName[0], $associationName[1], false);
                    }
                }
            }
        }
    }

    /**
     * Check if the set fields are valid
     */
    public function checkFormatFields()
    {
        $this->_errorFormatList = array();
        $fieldsValidate = Address::getFieldsValidate();
        $usedKeyList = array();

        $multipleLineFields = explode("\n", $this->format);
        if ($multipleLineFields && is_array($multipleLineFields)) {
            foreach ($multipleLineFields as $lineField) {
                if (($patternsName = preg_split(self::_CLEANING_REGEX_, $lineField, -1, PREG_SPLIT_NO_EMPTY))) {
                    if (is_array($patternsName)) {
                        foreach ($patternsName as $patternName) {
                            if (!in_array($patternName, $usedKeyList)) {
                                $this->_checkLiableAssociation($patternName, $fieldsValidate);
                                $usedKeyList[] = $patternName;
                            } else {
                                $this->_errorFormatList[] = Tools::displayError('This key has already been used.').
                                    ': '.$patternName;
                            }
                        }
                    }
                }
            }
        }

        return (count($this->_errorFormatList)) ? false : true;
    }

    /**
     * Returns the error list
     */
    public function getErrorList()
    {
        return $this->_errorFormatList;
    }

    /**
     * Set the layout key with the liable value
     * example : (firstname) => 'Presta' will result (Presta)
     *         : (firstname-lastname) => 'Presta' and 'Shop' result '(Presta-Shop)'
     */
    protected static function _setOriginalDisplayFormat(&$formattedValueList, $currentLine, $currentKeyList)
    {
        if ($currentKeyList && is_array($currentKeyList)) {
            if ($originalFormattedPatternList = explode(' ', $currentLine)) {
                // Foreach the available pattern
                foreach ($originalFormattedPatternList as $patternNum => $pattern) {
                    // Var allows to modify the good formatted key value when multiple key exist into the same pattern
                    $mainFormattedKey = '';

                    // Multiple key can be found in the same pattern
                    foreach ($currentKeyList as $key) {
                        // Check if we need to use an older modified pattern if a key has already be matched before
                        $replacedValue = empty($mainFormattedKey) ? $pattern : $formattedValueList[$mainFormattedKey];

                        $chars = $start = $end = str_replace($key, '', $replacedValue);
                        if (preg_match(self::_CLEANING_REGEX_, $chars)) {
                            if (Tools::substr($replacedValue, 0, Tools::strlen($chars)) == $chars) {
                                $end = '';
                            } else {
                                $start = '';
                            }

                            if ($chars) {
                                $replacedValue = str_replace($chars, '', $replacedValue);
                            }
                        }

                        if ($formattedValue = preg_replace('/^'.$key.'$/', $formattedValueList[$key], $replacedValue, -1, $count)) {
                            if ($count) {
                                // Allow to check multiple key in the same pattern,
                                if (empty($mainFormattedKey)) {
                                    $mainFormattedKey = $key;
                                }
                                // Set the pattern value to an empty string if an older key has already been matched before
                                if ($mainFormattedKey != $key) {
                                    $formattedValueList[$key] = '';
                                }
                                // Store the new pattern value
                                $formattedValueList[$mainFormattedKey] = $start.$formattedValue.$end;
                                unset($originalFormattedPatternList[$patternNum]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Cleaned the layout set by the user
     */
    public static function cleanOrderedAddress(&$orderedAddressField)
    {
        foreach ($orderedAddressField as &$line) {
            $cleanedLine = '';
            if (($keyList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY))) {
                foreach ($keyList as $key) {
                    $cleanedLine .= $key.' ';
                }
                $cleanedLine = trim($cleanedLine);
                $line = $cleanedLine;
            }
        }
    }

    /**
     * Returns the formatted fields with associated values
     *
     * @param Address $address Address object
     * @param AddressFormat $addressFormat The format
     *
     * @return array
     */
    public static function getFormattedAddressFieldsValues($address, $addressFormat, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        $tab = array();
        $temporyObject = array();

        // Check if $address exist and it's an instanciate object of Address
        if ($address && ($address instanceof Address)) {
            foreach ($addressFormat as $line) {
                if (($keyList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY)) && is_array($keyList)) {
                    foreach ($keyList as $pattern) {
                        if ($associateName = explode(':', $pattern)) {
                            $totalName = count($associateName);
                            if ($totalName == 1 && isset($address->{$associateName[0]})) {
                                $tab[$associateName[0]] = $address->{$associateName[0]};
                            } else {
                                $tab[$pattern] = '';

                                // Check if the property exist in both classes
                                if (($totalName == 2) && class_exists($associateName[0]) &&
                                    property_exists($associateName[0], $associateName[1]) &&
                                    property_exists($address, 'id_'.strtolower($associateName[0]))) {
                                    $idFieldName = 'id_'.strtolower($associateName[0]);

                                    if (!isset($temporyObject[$associateName[0]])) {
                                        $temporyObject[$associateName[0]] = new $associateName[0]($address->{$idFieldName});
                                    }
                                    if ($temporyObject[$associateName[0]]) {
                                        $tab[$pattern] = (is_array($temporyObject[$associateName[0]]->{$associateName[1]})) ?
                                            ((isset($temporyObject[$associateName[0]]->{$associateName[1]}[$id_lang])) ?
                                            $temporyObject[$associateName[0]]->{$associateName[1]}[$id_lang] : '') :
                                            $temporyObject[$associateName[0]]->{$associateName[1]};
                                    }
                                }
                            }
                        }
                    }
                    AddressFormat::_setOriginalDisplayFormat($tab, $line, $keyList);
                }
            }
        }
        AddressFormat::cleanOrderedAddress($addressFormat);
        // Free the instanciate objects
        foreach ($temporyObject as &$object) {
            unset($object);
        }

        return $tab;
    }

    /**
     * Generates the full address text
     *
     * @param Address $address
     * @param array   $patternRules A defined rules array to avoid some pattern
     * @param string  $newLine      A string containing the newLine format
     * @param string  $separator    A string containing the separator format
     * @param array   $style
     *
     * @return string
     */
    public static function generateAddress(Address $address, $patternRules = array(), $newLine = "\r\n", $separator = ' ', $style = array())
    {
        $addressFields = AddressFormat::getOrderedAddressFields($address->id_country);
        $addressFormatedValues = AddressFormat::getFormattedAddressFieldsValues($address, $addressFields);

        $addressText = '';
        foreach ($addressFields as $line) {
            if (($patternsList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY))) {
                $tmpText = '';
                foreach ($patternsList as $pattern) {
                    if ((!array_key_exists('avoid', $patternRules)) ||
                                (is_array($patternRules) && array_key_exists('avoid', $patternRules) && !in_array($pattern, $patternRules['avoid']))) {
                        $tmpText .= (isset($addressFormatedValues[$pattern]) && !empty($addressFormatedValues[$pattern])) ?
                                (((isset($style[$pattern])) ?
                                    (sprintf($style[$pattern], $addressFormatedValues[$pattern])) :
                                    $addressFormatedValues[$pattern]).$separator) : '';
                    }
                }
                $tmpText = trim($tmpText);
                $addressText .= (!empty($tmpText)) ? $tmpText.$newLine: '';
            }
        }

        $addressText = preg_replace('/'.preg_quote($newLine, '/').'$/i', '', $addressText);
        $addressText = rtrim($addressText, $separator);

        return $addressText;
    }

    /**
     * Generate formatted Address string for display on Smarty templates
     *
     * @param array $params Address parameters
     * @param Smarty $smarty Smarty instance
     *
     * @return string Formatted Address string
     */
    public static function generateAddressSmarty($params, &$smarty)
    {
        return AddressFormat::generateAddress(
            $params['address'],
            (isset($params['patternRules']) ? $params['patternRules'] : array()),
            (isset($params['newLine']) ? $params['newLine'] : "\r\n"),
            (isset($params['separator']) ? $params['separator'] : ' '),
            (isset($params['style']) ? $params['style'] : array())
        );
    }

    /**
     * Returns selected fields required for an address in an array according to a selection hash
     *
     * @return array String values
     */
    public static function getValidateFields($className)
    {
        $propertyList = array();

        if (class_exists($className)) {
            $object = new $className();
            $reflect = new ReflectionObject($object);

            // Check if the property is accessible
            $publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($publicProperties as $property) {
                $propertyName = $property->getName();
                if ((!in_array($propertyName, AddressFormat::$forbiddenPropertyList)) &&
                        (!preg_match('#id|id_\w#', $propertyName))) {
                    $propertyList[] = $propertyName;
                }
            }
            unset($object);
            unset($reflect);
        }

        return $propertyList;
    }

    /**
     * Return a list of liable class of the className
     *
     * @param string $className
     *
     * @return array
     */
    public static function getLiableClass($className)
    {
        $objectList = array();

        if (class_exists($className)) {
            $object = new $className();
            $reflect = new ReflectionObject($object);

            // Get all the name object liable to the Address class
            $publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($publicProperties as $property) {
                $propertyName = $property->getName();
                if (preg_match('#id_\w#', $propertyName) && strlen($propertyName) > 3) {
                    $nameObject = ucfirst(substr($propertyName, 3));
                    if (!in_array($nameObject, self::$forbiddenClassList) &&
                            class_exists($nameObject)) {
                        $objectList[$nameObject] = new $nameObject();
                    }
                }
            }
            unset($object);
            unset($reflect);
        }

        return $objectList;
    }

    /**
     * Returns address format fields in array by country
     *
     * @param int  $idCountry If null using PS_COUNTRY_DEFAULT
     * @param bool $splitAll
     * @param bool $cleaned
     *
     * @return array String field address format
     */
    public static function getOrderedAddressFields($idCountry = 0, $splitAll = false, $cleaned = false)
    {
        $out = array();
        $fieldSet = explode("\n", AddressFormat::getAddressCountryFormat($idCountry));
        foreach ($fieldSet as $fieldItem) {
            if ($splitAll) {
                if ($cleaned) {
                    $keyList = ($cleaned) ? preg_split(self::_CLEANING_REGEX_, $fieldItem, -1, PREG_SPLIT_NO_EMPTY) :
                        explode(' ', $fieldItem);
                }
                foreach ($keyList as $wordItem) {
                    $out[] = trim($wordItem);
                }
            } else {
                $out[] = ($cleaned) ? implode(' ', preg_split(self::_CLEANING_REGEX_, trim($fieldItem), -1, PREG_SPLIT_NO_EMPTY))
                    : trim($fieldItem);
            }
        }

        return $out;
    }

    /**
     * Return a data array containing ordered, formatedValue and object fields
     */
    public static function getFormattedLayoutData($address)
    {
        $layoutData = array();

        if ($address && $address instanceof Address) {
            $layoutData['ordered'] = AddressFormat::getOrderedAddressFields((int) $address->id_country);
            $layoutData['formated'] = AddressFormat::getFormattedAddressFieldsValues($address, $layoutData['ordered']);
            $layoutData['object'] = array();

            $reflect = new ReflectionObject($address);
            $publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($publicProperties as $property) {
                if (isset($address->{$property->getName()})) {
                    $layoutData['object'][$property->getName()] = $address->{$property->getName()};
                }
            }
        }

        return $layoutData;
    }

    /**
     * Returns address format by country if not defined using default country
     *
     * @param int $idCountry Country ID
     *
     * @return String field address format
     */
    public static function getAddressCountryFormat($idCountry = 0)
    {
        $idCountry = (int) $idCountry;

        $tmpObj = new AddressFormat();
        $tmpObj->id_country = $idCountry;
        $out = $tmpObj->getFormat($tmpObj->id_country);
        unset($tmpObj);

        return $out;
    }

    /**
     * Returns address format by Country
     *
     * @param int $idCountry Country ID
     *
     * @return String field Address format
     */
    public function getFormat($idCountry)
    {
        $out = $this->getFormatDB($idCountry);
        if (empty($out)) {
            $out = $this->getFormatDB(Configuration::get('PS_COUNTRY_DEFAULT'));
        }

        return $out;
    }

    /**
     * @param int $idCountry
     *
     * @return false|null|string
     * @deprecated 1.7.0
     */
    protected function _getFormatDB($idCountry)
    {
        return self::getFormatDB($idCountry);
    }

    /**
     * Get Address format from DB
     *
     * @param int $idCountry Country ID
     *
     * @return false|null|string Address format
     * @since 1.7.0
     */
    protected function getFormatDB($idCountry)
    {
        if (!Cache::isStored('AddressFormat::getFormatDB'.$idCountry)) {
            $format = Db::getInstance()->getValue('
			SELECT format
			FROM `'._DB_PREFIX_.$this->def['table'].'`
			WHERE `id_country` = '.(int) $idCountry);
            $format = trim($format);
            Cache::store('AddressFormat::getFormatDB'.$idCountry, $format);

            return $format;
        }

        return Cache::retrieve('AddressFormat::getFormatDB'.$idCountry);
    }

    /**
     * @see ObjectModel::getFieldsRequired()
     */
    public static function getFieldsRequired()
    {
        $address = new Address();

        return array_unique(array_merge($address->getFieldsRequiredDB(), AddressFormat::$requireFormFieldsList));
    }
}
