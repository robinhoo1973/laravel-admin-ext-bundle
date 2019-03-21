<?php

namespace TopviewDigital\Extension\Form\Field;

use Illuminate\Support\Arr;
use Encore\Admin\Form\Field\Tags;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Form\Field\Listbox;
use Encore\Admin\Form\Field\Checkbox;
use Encore\Admin\Form\Field\MultipleFile;
use Illuminate\Support\Facades\Validator;
use Encore\Admin\Form\Field\MultipleImage;
use Encore\Admin\Form\Field\HasMany as HasManyLegacy;

class HasMany extends HasManyLegacy
{
    /**
     * Get validator for this field.
     *
     * @param array $input
     *
     * @return bool|Validator
     */
    public function getValidator(array $input)
    {
        if (! array_key_exists($this->column, $input)) {
            return false;
        }
        $input = Arr::only($input, (array) $this->column);
        $form = $this->buildNestedForm($this->column, $this->builder);
        $rel = $this->relationName;
        $rules = $attributes = $messages = $newInputs = [];
        // remove all inputs & keys marked as removed
        $availInput = array_filter(array_map(function ($v) {
            return $v[NestedForm::REMOVE_FLAG_NAME] ? null : $v;
        }, $input[$rel]));
        $keys = array_keys($availInput);
        /* @var Field $field */
        foreach ($form->fields() as $field) {
            if ($field instanceof self) {
                throw new \Exception('nested hasMany field found.');
            }
            if (! ($field instanceof Embeds) && ! ($fieldRules = $field->getRules())) {
                continue;
            }
            $column = $field->column();
            $columns = is_array($column) ? $column : [$column];
            if (
                $field instanceof MultipleSelect
                || $field instanceof Listbox
                || $field instanceof Checkbox
                || $field instanceof Tags
                || $field instanceof MultipleImage
                || $field instanceof MultipleFile
            ) {
                foreach ($keys as $key) {
                    $availInput[$key][$column] = array_filter($availInput[$key][$column], 'strlen') ?: null;
                }
            }
            $newColumn = call_user_func_array('array_merge', array_map(function ($u) use ($columns, $rel) {
                return array_map(function ($k, $v) use ($u, $rel) {
                    //Fix ResetInput Function! A Headache Implementation!
                    return $k ? "{$rel}.{$u}.{$v}:{$k}" : "{$rel}.{$u}.{$v}";
                }, array_keys($columns), array_values($columns));
            }, $keys));
            if ($field instanceof Embeds) {
                $newRules = array_map(function ($v) use ($availInput, $field) {
                    list($r, $k, $c) = explode('.', $v);
                    $v = "{$r}.{$k}";
                    $embed = $field->getValidationRules([$field->column() => $availInput[$k][$c]]);

                    return $embed ? array_key_attach_str($embed, $v) : null;
                }, $newColumn);
                $rules = array_clean_merge($rules, array_filter($newRules));
                $newAttributes = array_map(function ($v) use ($availInput, $field) {
                    list($r, $k, $c) = explode('.', $v);
                    $v = "{$r}.{$k}";
                    $embed = $field->getValidationAttributes([$field->column() => $availInput[$k][$c]]);

                    return $embed ? array_key_attach_str($embed, $v) : null;
                }, $newColumn);
                $attributes = array_clean_merge($attributes, array_filter($newAttributes));
                $newInput = array_map(function ($v) use ($availInput, $field) {
                    list($r, $k, $c) = explode('.', $v);
                    $v = "{$r}.{$k}";
                    $embed = $field->getValidationInput([$field->column() => $availInput[$k][$c]]);

                    return $embed ? array_key_attach_str($embed, $v) : [null => 'null'];
                }, $newColumn);
                $newInputs = array_clean_merge($newInputs, array_filter($newInput, 'strlen', ARRAY_FILTER_USE_KEY));
                $newMessages = array_map(function ($v) use ($availInput, $field) {
                    list($r, $k, $c) = explode('.', $v);
                    $v = "{$r}.{$k}";
                    $embed = $field->getValidationMessages([$field->column() => $availInput[$k][$c]]);

                    return $embed ? array_key_attach_str($embed, $v) : null;
                }, $newColumn);
                $messages = array_clean_merge($messages, array_filter($newMessages));
            } else {
                $fieldRules = is_array($fieldRules) ? implode('|', $fieldRules) : $fieldRules;
                $newRules = array_map(function ($v) use ($fieldRules, $availInput) {
                    list($r, $k, $c) = explode('.', $v);
                    //Fix ResetInput Function! A Headache Implementation!
                    $col = explode(':', $c)[0];
                    if (array_key_exists($col, $availInput[$k]) && is_array($availInput[$k][$col])) {
                        return array_key_attach_str(preg_replace('/.+/', $fieldRules, $availInput[$k][$col]), $v, ':');
                    }

                    return [$v => $fieldRules];
                }, $newColumn);
                $rules = array_clean_merge($rules, $newRules);
                $newInput = array_map(function ($v) use ($availInput) {
                    list($r, $k, $c) = explode('.', $v);
                    //Fix ResetInput Function! A Headache Implementation!
                    $col = explode(':', $c)[0];
                    if (! array_key_exists($col, $availInput[$k])) {
                        return [$v => null];
                    }
                    if (is_array($availInput[$k][$col])) {
                        return array_key_attach_str($availInput[$k][$col], $v, ':');
                    }

                    return [$v => $availInput[$k][$col]];
                }, $newColumn);
                $newInputs = array_clean_merge($newInputs, $newInput);
                $newAttributes = array_map(function ($v) use ($field, $availInput) {
                    list($r, $k, $c) = explode('.', $v);
                    //Fix ResetInput Function! A Headache Implementation!
                    $col = explode(':', $c)[0];
                    if (array_key_exists($col, $availInput[$k]) && is_array($availInput[$k][$col])) {
                        return call_user_func_array('array_merge', array_map(function ($u) use ($v, $field) {
                            $w = $field->label();
                            //Fix ResetInput Function! A Headache Implementation!
                            $w .= is_array($field->column()) ? '['.explode(':', explode('.', $v)[2])[0].']' : '';

                            return ["{$v}:{$u}" => $w];
                        }, array_keys($availInput[$k][$col])));
                    }
                    $w = $field->label();
                    //Fix ResetInput Function! A Headache Implementation!
                    $w .= is_array($field->column()) ? '['.explode(':', explode('.', $v)[2])[0].']' : '';

                    return [$v => $w];
                }, $newColumn);
                $attributes = array_clean_merge($attributes, $newAttributes);
            }
            if ($field->validationMessages) {
                $newMessages = array_map(function ($v) use ($field, $availInput) {
                    list($r, $k, $c) = explode('.', $v);
                    //Fix ResetInput Function! A Headache Implementation!
                    $col = explode(':', $c)[0];
                    if (array_key_exists($col, $availInput[$k]) && is_array($availInput[$k][$col])) {
                        return call_user_func_array('array_merge', array_map(function ($u) use ($v, $field) {
                            return array_key_attach_str($field->validationMessages, "{$v}:{$u}");
                        }, array_keys($availInput[$k][$col])));
                    }

                    return array_key_attach_str($field->validationMessages, $v);
                }, $newColumn);
                $messages = array_clean_merge($messages, $newMessages);
            }
        }
        $rules = array_filter($rules, 'strlen');
        if (empty($rules)) {
            return false;
        }
        $input = array_key_clean_undot(array_filter($newInputs, 'strlen', ARRAY_FILTER_USE_KEY));
        $rules = array_key_clean($rules);
        $attributes = array_key_clean($attributes);
        $messages = array_key_clean($messages);
        if (empty($input)) {
            $input = [$rel => $availInput];
        }

        return Validator::make($input, $rules, $messages, $attributes);
    }
}
