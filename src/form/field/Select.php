<?php

namespace TopviewDigital\Extension\Form\Field;

use Illuminate\Support\Str;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form\Field\Select as SelectLegacy;

class Select extends SelectLegacy
{
    protected static $js = [];
    protected static $css = [];
    private function template($key, $val)
    {
        $this->config['escapeMarkup'] = 'function (markup) {return markup;}';
        $key = strtolower($key);
        $func_key = 'template' . ucfirst($key);
        $func_name = str_replace('.', '', "{$this->getElementClassSelector()}_{$key}");
        $this->config[$func_key] = $func_name;
        $script = implode("\n", [
            "{$func_name} = function(data) {",
            "\tif ( !data.id || data.loading) return data.text;",
            $val,
            '}',
        ]);
        Admin::script($script);
    }

    public function templateResult(string $view)
    {
        $this->template('result', $view);

        return $this;
    }

    public function templateSelection(string $view)
    {
        $this->template('selection', $view);

        return $this;
    }

    public function readonly()
    {
        $script = <<<'EOT'
        $("form select").on("select2:opening", function (e) {
            if($(this).attr('readonly') || $(this).is(':hidden')){
            e.preventDefault();
            }
        });
        $(document).ready(function(){
            $('select').each(function(){
                if($(this).is('[readonly]')){
                    $(this).closest('.form-group').find('span.select2-selection__choice__remove').first().remove();
                    $(this).closest('.form-group').find('li.select2-search').first().remove();
                    $(this).closest('.form-group').find('span.select2-selection__clear').first().remove();
                }
            });
        });
EOT;
        Admin::script($script);
        // $this->config('allowClear', false);
        $this->attribute('readonly');

        return $this;
    }

    private function buildJsJson(array $options, array $functions = [])
    {
        $functions = array_merge([
            'ajax',
            'escapeMarkup',
            'templateResult',
            'templateSelection',
            'initSelection',
            'sorter',
            'tokenizer',
        ], $functions);

        return implode(
            ",\n",
            array_map(function ($u, $v) use ($functions) {
                if (is_string($v)) {
                    return  in_array($u, $functions) ? "{$u}: {$v}" : "{$u}: \"{$v}\"";
                }

                return "{$u}: " . json_encode($v);
            }, array_keys($options), $options)
        );
    }

    private function configs($default = [], $quoted = false)
    {
        $configs = array_merge(
            [
                'allowClear'  => true,
                'language'    => app()->getLocale(),
                'placeholder' => [
                    'id'   => '',
                    'text' => $this->label,
                ],
                'escapeMarkup' => 'function (markup) {return markup;}',
            ],
            $default,
            $this->config
        );
        $configs = $this->buildJsJson($configs);

        return $quoted ? '{' . $configs . '}' : $configs;
    }

    /**
     * Load options for other select on change.
     *
     * @param string $field
     * @param string $sourceUrl
     * @param string $idField
     * @param string $textField
     *
     * @return $this
     */
    public function load($field, $sourceUrl, $idField = 'id', $textField = 'text')
    {
        if (Str::contains($field, '.')) {
            $field = $this->formatName($field);
            $class = str_replace(['[', ']'], '_', $field);
        } else {
            $class = $field;
        }
        $script = <<<EOT
$(document).off('change', "{$this->getElementClassSelector()}");
$(document).on('change', "{$this->getElementClassSelector()}", function () {
    var target = $(this).closest('.fields-group').find(".$class");
    if(this.value)
    $.get("$sourceUrl?q="+this.value, function (data) {
        target.find("option").remove();
        config=window._config[".{$class}"];
        config.data=$.map(data, function (d) {
            d.id = d.$idField;
            d.text = d.$textField;
            return d;
        });
        $(target).select2(config).trigger('change');
    });
});
EOT;
        Admin::script($script);

        return $this;
    }

    /**
     * Load options for other selects on change.
     *
     * @param string $fields
     * @param string $sourceUrls
     * @param string $idField
     * @param string $textField
     *
     * @return $this
     */
    public function loads($fields = [], $sourceUrls = [], $idField = 'id', $textField = 'text')
    {
        $fieldsStr = implode('.', $fields);
        $urlsStr = implode('^', $sourceUrls);
        $script = <<<EOT
var fields = '$fieldsStr'.split('.');
var urls = '$urlsStr'.split('^');
var refreshOptions = function(url, target, name) {
    $.get(url).then(function(data) {
        target.find("option").remove();
        config=window._config[name];
        config.data=$.map(data, function (d) {
            d.id = d.$idField;
            d.text = d.$textField;
            return d;
        });
        $(target).select2(config).trigger('change');
    });
};
$(document).off('change', "{$this->getElementClassSelector()}");
$(document).on('change', "{$this->getElementClassSelector()}", function () {
    var _this = this;
    var promises = [];
    fields.forEach(function(field, index){
        var target = $(_this).closest('.fields-group').find('.' + fields[index]);
        promises.push(refreshOptions(urls[index] + "?q="+ _this.value, target, name));
    });
    $.when(promises).then(function() {
        console.log('开始更新其它select的选择options');
    });
});
EOT;
        Admin::script($script);

        return $this;
    }

    /**
     * Load options from remote.
     *
     * @param string $url
     * @param array  $parameters
     * @param array  $options
     *
     * @return $this
     */
    protected function loadRemoteOptions($url, $parameters = [], $options = [])
    {
        $ajaxOptions = [
            'url' => $url . '?' . http_build_query($parameters),
        ];
        $configs = $this->configs([
            'allowClear'         => true,
            'placeholder'        => [
                'id'        => '',
                'text'      => trans('admin.choose'),
            ],
        ]);
        $ajaxOptions = json_encode(array_merge($ajaxOptions, $options));
        $this->script = <<<EOT
$.ajax($ajaxOptions).done(function(data) {
  var select = $("{$this->getElementClassSelector()}");
  select.select2({
    data: data,
    $configs
  });
  
  var value = select.data('value') + '';
  
  if (value) {
    value = value.split(',');
    select.select2('val', value);
  }
});
EOT;

        return $this;
    }

    /**
     * Load options from ajax results.
     *
     * @param string $url
     * @param $idField
     * @param $textField
     *
     * @return $this
     */
    public function ajax($url, $idField = 'id', $textField = 'text')
    {
        $configs = $this->configs([
            'allowClear'         => true,
            'placeholder'        => $this->label,
            'minimumInputLength' => 1,
        ]);
        $this->script = <<<EOT
$("{$this->getElementClassSelector()}").select2({
  ajax: {
    url: "$url",
    dataType: 'json',
    delay: 250,
    data: function (params) {
      return {
        q: params.term,
        page: params.page
      };
    },
    processResults: function (data, params) {
      params.page = params.page || 1;
      return {
        results: $.map(data.data, function (d) {
                   d.id = d.$idField;
                   d.text = d.$textField;
                   return d;
                }),
        pagination: {
          more: data.next_page_url
        }
      };
    },
    cache: true
  },
  $configs,
  escapeMarkup: function (markup) {
      return markup;
  }
});
EOT;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        Admin::headJs([
            // '/vendor/laravel-admin-ext-bundle/select2/js/select2.min.js',
            '/vendor/laravel-admin-ext-bundle/select2/js/i18n/' . app()->getLocale() . '.js'
        ]);
        // Admin::baseCss(['/vendor/laravel-admin-ext-bundle/select2/css/select2.min.css']);
        $configs = str_replace("\n", '', $this->configs(
            [
                'allowClear'  => true,
                'placeholder' => [
                    'id'   => '',
                    'text' => $this->label,
                ],
            ],
            true
        ));
        Admin::script("if(!window.hasOwnProperty('_config')) window._config=new Object();");
        Admin::script("window._config['{$this->getElementClassSelector()}']=eval('({$configs})');\n");
        if (empty($this->script)) {
            $this->script = "$(\"{$this->getElementClassSelector()}\").select2({$configs});";
        }
        if ($this->options instanceof \Closure) {
            if ($this->form) {
                $this->options = $this->options->bindTo($this->form->model());
            }
            $this->options(call_user_func($this->options, $this->value, $this));
        }
        $this->options = array_filter($this->options, 'strlen');
        $this->addVariables([
            'options' => $this->options,
            'groups'  => $this->groups,
        ]);
        $this->attribute('data-value', implode(',', (array)$this->value()));

        return parent::render();
    }
}
