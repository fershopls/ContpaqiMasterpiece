<?php

namespace lib\Frontend;

class FormReceiver {

    protected $config;

    public function __construct($formConfig)
    {
        $this->setConfig($formConfig);
    }

    public function getConfig() {
        return $this->config;
    }

    public function setConfig ($formConfig) {
        $this->config = $formConfig;
        return $formConfig;
    }

    public function get ($fieldData)
    {
        // Receive!
        $config = $this->getConfig();
        return $this->receiveFieldArray($fieldData, $config['fields']);
    }

    public function receiveFieldArray ($fieldDataArray, $fields, $resolve = [])
    {
        $solvedFields = array();
        // This should be $_POST
        foreach ($fieldDataArray as $key => $value)
        {
            if (is_array($value))
            {
                // If its an array field[] goes deep keeping track of the tree in $resolve
                array_push($resolve, $key);
                $value = $this->receiveFieldArray($value, $fields, $resolve);
                $solvedFields[$key] = $value;
                // Dont get into unwanted bucles
                array_pop($resolve);
            } else {
                // In case a single item find to whom belongs in $fields
                $field = array_filter($fields, function ($f) use ($key, $resolve) {
                    $_key = $key;
                    // Remember $resolve?
                    if (count($resolve) > 0)
                    {
                        array_push($resolve, $key);
                        $route = '';
                        foreach ($resolve as $item)
                        {
                            if ($route == '')
                                $route = $item;
                            else
                                $route .= '['.$item.']';
                        }
                        $_key = $route;
                    }
                    // On $fields should be stored as key0[key1][key2][key3]...etc
                    return $f['name'] == $_key?true:false;
                });
                // If isn't declared on $fields forget everything about it
                if (count($field)==0)
                    continue;
                $field = array_values($field)[0];
                // Finally solve with its $field and save!
                $value = $this->solveField($value, $field);
                $solvedFields[$key] = $value;
            }
        }
        return $solvedFields;
    }

    public function solveField ($value, $field)
    {
        // Solve only non-empty fields
        if ($value == '')
            return $value;
        // Case Field Type
        if ($field['type']=='date')
        {
            // Case DATE:
            $value = str_replace('-', '', $value). ' 00:00';
        } else if ($field['type']=='checkbox') {
            // Case CHECKBOX:
            $value = $value == 'on'?true:false;
        }
        return $value;
    }
}