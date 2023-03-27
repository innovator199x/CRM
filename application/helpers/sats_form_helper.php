<?php

if (! function_exists('sats_form_input_date')) {
    function sats_form_input_date($params) {

        $variable = $params['variable'];
        $postVarName = $params['post_var_name'];
        $postFieldKey = $params['post_field_key'];
        $validation = $params['validation'];

        if (empty($postFieldKey)) {
            $inputNameAttr = $postVarName;
            $value = $variable;
            $inputIdAttr = $postVarName;
        }
        else {
            $inputNameAttr = "{$postVarName}[{$postFieldKey}]";
            $value = $variable[$postFieldKey];
            $inputIdAttr = "{$postVarName}-{$postFieldKey}";
        }

        $validationProp = "";
        if ($validation) {
            $validationProp = "data-validation=\"[{$validation}]\"";
        }

        $otherClasses = $params["other_classes"] ?? "";
        $otherProps = $params["other_props"] ?? "";

        $CI =& get_instance();

        $date = $CI->customlib->formatYmdToDmy($value, true);
        $date = set_value($inputNameAttr, $date);

        return <<<EOD
        <input type="text"
            type="date"
            name="{$inputNameAttr}"
            id="{$inputIdAttr}"
            class="form-control flatpickr flatpickr-input {$otherClasses}"
            value="{$date}"
            data-allow-input="true"
            style="width: 125px;"
            autocomplete="off"
            {$validationProp}
            {$otherProps}
        />
EOD;
    }
}

?>