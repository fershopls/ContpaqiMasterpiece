<?php

namespace lib\Frontend;

class FormBuilder {

    protected $config;
    protected $fields = array();
    protected $sourceParameters = array();

    public function __construct($formConfig)
    {
        $this->setConfig($formConfig);
        $this->makeFields();
    }

    public function getConfig() {
        return $this->config;
    }

    public function setConfig ($formConfig) {
        $this->config = $formConfig;
        return $formConfig;
    }

    public function getSourceParameters()
    {
        return $this->sourceParameters;
    }

    public function setSourceParameters($sourceParameters = [])
    {
        $this->sourceParameters = $sourceParameters;
        return $sourceParameters;
    }

    public function makeFields()
    {
        $config = $this->getConfig();
        $fields = array();
        foreach ($config['fields'] as $id => $f)
        {
            // Fallback attributes
            $f['id'] = isset($f['id'])?$f['id']:$f['name'];
            $f['type'] = isset($f['type'])?$f['type']:'text';
            $f['value'] = isset($f['value'])?$f['value']:'';
            $f['source'] = isset($f['source']) && isset($config['sources'][$f['source']])?$config['sources'][$f['source']]:null;
            $f['attr'] = isset($f['attr'])?$f['attr']:[];
            // Set
            $fields[$id] = $f;
        }
        $config['fields'] = $fields;
        // Save
        $this->setConfig($config);
        return $fields;
    }

    public function solveAttributes ($attributeStack = array())
    {
        $attrString = '';
        foreach ($attributeStack as $key => $val)
            $attrString .= $key.'="'.str_replace('"', '\'', $val).'" ';
        return $attrString;
    }

    public function solveValueSelect ($itemStack = array())
    {
        $string = '';
        foreach ($itemStack as $item)
        {
            if (!isset($item['key']) || !isset($item['value']))
                continue;

            // Solve extra attributes
            $item['attr'] = isset($item['attr'])?$item['attr']:[];
            $attributes = $this->solveAttributes($item['attr']);
            // Dump
            $string .= '<option value="'.$item['key'].'" '.$attributes.'>'.$item['value'].'</option>';
        }
        return $string;
    }

    public function dumpFields ()
    {
        $config = $this->getConfig();
        $fields = array();
        foreach ($config['fields'] as $id => $f)
        {
            $attributes = $this->solveAttributes($f['attr']);

            // Replace original value if has a source
            if ($f['source']!=null && is_callable($f['source']))
            {
                $sourceReturn = call_user_func_array($f['source'], $this->getSourceParameters());
                $f['value'] = $sourceReturn;
            }

            // Case Field Type
            if ($f['type'] == 'select') {
                // Case SELECT
                $f['value'] = $f['value'] == '' ? [] : $f['value'];
                $values = $this->solveValueSelect($f['value']);
                $fieldHtml = '<select id="' . $f['id'] . '" name="' . $f['name'] . '" ' . $attributes . '>' . $values . '</select>';
            } else if ($f['type'] == 'textarea') {
                $fieldHtml = '<textarea id="' . $f['id'] . '" name="' . $f['name'] . '" ' . $attributes . '>' . $f['value'] . '</textarea>';
            } else {
                // Case CHECKBOX:
                if ($f['type'] == 'checkbox' && $f['value']=='')
                    $f['value'] = 'on';
                // Case OTHER:
                $fieldHtml = '<input id="' . $f['id'] . '" name="' . $f['name'] . '" value="' . $f['value'] . '" type="' . $f['type'] . '" ' . $attributes . '/>';
            }
            // Save
            $fields[$f['id']] = array(
                'label' => isset($config['texts'][$f['id']])?$config['texts'][$f['id']]:ucwords($f['name']),
                'html' => $fieldHtml,
            );
        }
        return $fields;
    }
    
    public function receive($dataPost)
    {
        $FormReceiver = new FormReceiver($this->getConfig());
        return $FormReceiver->get($dataPost);
    }

}