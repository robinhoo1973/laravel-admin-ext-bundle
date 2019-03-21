<?php

namespace TopviewDigital\Extension\Form\Field;

class TabbedFields extends Embeds
{
    protected $tab_rules = ['default' => '', 'overall' => ''];
    protected $tab_messages = ['default' => [], 'overall' => []];
    protected $def_tab_ord = 0;
    protected $view = 'admin-ext::form.field.tab-field';
    protected $tabs = [];
    protected $builder = null;
    protected $label = '';
    protected $field_type = 'text';

    private function addRules($rules, $new)
    {
        $rules = implode('|', array_merge(explode('|', $rules), [$new]));

        return $rules;
    }

    private function addTabRules($rules, $messages, $scope = 'default')
    {
        $this->tab_rules[$scope] = $this->addRules($this->tab_rules[$scope], $rules);
        $this->tab_messages[$scope] = array_merge($this->tab_messages[$scope], $messages);
    }

    private function delTabRules($rules, $messages, $scope = 'overall')
    {
        $this->tab_rules[$scope] = implode(
            '|',
            array_unique(
                array_diff(
                    explode('|', $this->tab_rules[$scope]),
                    explode('|', $rules)
                )
            )
        );
        $this->tab_messages[$scope] = array_diff_key($this->tab_messages[$scope], $messages);
    }

    public function setDefaultTabOrder($order)
    {
        $this->def_tab_ord = $order;

        return $this;
    }

    public function __construct($column, $arguments = [])
    {
        $this->label = $arguments && is_string($arguments[0]) ? array_shift($arguments) : '';

        parent::__construct($column, [$this->label, null]);
    }

    public function onlyDefaultRequired($message = null)
    {
        $this->addTabRules('required', $message ? ['required' => $message] : []);
        $this->delTabRules('required', $message ? ['required' => $message] : []);

        return $this;
    }

    public function onlyDefaultUnique($table, $id, $message = null)
    {
        $field = $this->column;
        $key = first(array_keys($this->tabs));
        $unique = "unique:{$table},{$field}->{$key},{$id}";
        $this->addTabRules($unique, $message ? ['unique' => $message] : []);
        $this->delTabRules($unique, $message ? ['unique' => $message] : []);

        return $this;
    }

    public function rules($rules = null, $messages = [])
    {
        $this->addTabRules($rules, $messages, 'overall');

        return $this;
    }

    private function generateRules()
    {
        $rules = array_fill_keys(array_keys($this->tabs), '');
        $overall = explode('|', $this->tab_rules['overall']);
        $unique = preg_grep('/^unique:/', $overall);
        $overall = array_diff($overall, $unique);
        foreach (array_keys($this->tabs) as $key) {
            foreach ($unique as $rule) {
                $u = array_map('trim', explode(',', preg_replace('/^unique:/', 'unique_json:', $rule)));
                $u[1] .= "->{$key}";
                $rules[$key] = $this->addRules($rules[$key], implode(',', $u));
            }
            foreach ($overall as $rule) {
                $rules[$key] = $this->addRules($rules[$key], $rule);
            }
        }

        return $rules;
    }

    public function setTabs($tabs)
    {
        $this->tabs = $tabs;

        return $this;
    }

    public function setFieldType(string $type)
    {
        $type = strtolower($type);
        $fields = [];
        $this->field_type = in_array($type, $fields) ? $type : $this->field_type;

        return $this;
    }

    public function buildTextField()
    {
        $this->field_type = 'text';

        return $this;
    }

    public function buildTextareaField()
    {
        $this->field_type = 'textarea';

        return $this;
    }

    private function changeUniqueRules($ary)
    {
        return count($ary) ? call_user_func_array('array_merge', array_map(function ($u, $v) {
            return [$u == 'unique' ? 'unique_json' : $u => $v];
        }, array_keys($ary), $ary)) : $ary;
    }

    private function refreshBuilder()
    {
        $builder = function ($form) {
            $rules = $this->generateRules();
            $this->tab_messages['default'] = $this->changeUNiqueRules($this->tab_messages['default']);
            $this->tab_messages['overall'] = $this->changeUNiqueRules($this->tab_messages['overall']);
            foreach (array_keys($this->tabs) as $key) {
                $messages = $this->tab_messages['overall'];
                //assume the first item is the default tab
                if ($key == array_keys($this->tabs)[$this->def_tab_ord]) {
                    $rules[$key] = $this->addRules($this->tab_rules['default'], $rules[$key]);
                    $messages = array_merge(
                        $this->tab_messages['default'],
                        $this->tab_messages['overall']
                    );
                }
                $rules[$key] = implode('|', array_filter(array_unique(explode('|', $rules[$key])), 'strlen'));
                $form->{$this->field_type}($key, "{$this->label}")->rules($rules[$key], $messages);
            }
        };

        return $builder;
    }

    protected function buildEmbeddedForm()
    {
        $this->builder = $this->refreshBuilder();

        return parent::buildEmbeddedForm();
    }

    public function render()
    {
        return parent::render()
            ->with(['form' => $this->buildEmbeddedForm()])
            ->with(['tabs' => array_values($this->tabs)]);
    }
}
