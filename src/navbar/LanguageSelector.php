<?php
namespace TopviewDigital\Extension\Navbar;

use Encore\Admin\Facades\Admin;

class LanguageSelector
{
    public function __toString()
    {
        $class = config('admin-ext.lang-selector.data.class');
        $method = config('admin-ext.lang-selector.data.method');
        $locales = $class::$method();
        $locales = is_array($locales) ? $locales : (array)$locales;
        $locales = array_map(function ($u) {
            return [
                'locale' => $u[config('admin-ext.lang-selector.data.fields.locale')],
                'native' => $u[config('admin-ext.lang-selector.data.fields.native')],
                'flag' => strtolower($u[config('admin-ext.lang-selector.data.fields.flag')]),
            ];
        }, $locales);
        $index = array_search(
            app()->getLocale(),
            array_column($locales, 'locale')
        );
        $default = $locales[$index];
        $selector = config('admin-ext.lang-selector.form.id');
        $script = <<<JS

$('.{$selector}').on('click',function (e) {
    target=$(this).attr('data-locale');
    $('#{$selector}  input[name=locale]').val(target);
    $('#{$selector}').submit();
});
JS;
        Admin::script($script);
        return (string)view('admin-ext::navbar.language-selector')
            ->with('default', $default)
            ->with('locales', $locales);
    }
}
