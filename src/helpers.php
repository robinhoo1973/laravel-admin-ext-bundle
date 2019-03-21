<?php
if (!function_exists('array_key_attach_str')) {
    function array_key_attach_str(array $a, string $b, string $c = '.')
    {
        return call_user_func_array(
            'array_merge',
            array_map(function ($u, $v) use ($b, $c) {
                return ["{$b}{$c}{$u}" => $v];
            }, array_keys($a), array_values($a))
        );
    }
}

if (!function_exists('array_clean_merge')) {
    function array_clean_merge(array $a, $b)
    {
        return $b ? array_merge($a, call_user_func_array('array_merge', $b)) : $a;
    }
}

if (!function_exists('array_key_clean_undot')) {
    function array_key_clean_undot(array $a)
    {
        $keys = preg_grep('/[\.\:]/', array_keys($a));
        if (!empty($keys)) {
            foreach ($keys as $key) {
                Arr::set($a, str_replace(':', '', $key), $a[$key]);
                unset($a[$key]);
            }
        }

        return $a;
    }
}

if (!function_exists('array_key_clean')) {
    function array_key_clean(array $a)
    {
        $a = count($a) ? call_user_func_array('array_merge', array_map(function ($k, $v) {
            return [str_replace(':', '', $k) => $v];
        }, array_keys($a), array_values($a))) : $a;

        return $a;
    }
}

if (!function_exists('array_sort_value')) {
    function array_sort_value($array, $mode = SORT_LOCALE_STRING)
    {
        // SORT_REGULAR - compare items normally (don't change types)
        // SORT_NUMERIC - compare items numerically
        // SORT_STRING - compare items as strings
        // SORT_LOCALE_STRING - compare items as strings, based on the current locale.
        // It uses the locale, which can be changed using setlocale()
        // SORT_NATURAL - compare items as strings using "natural ordering" like natsort()
        // SORT_FLAG_CASE

        if (!is_array($array)) {
            $array = method_exists($array, 'toArray') ? $array->toArray() : (array)$array;
        }
        // \Locale::setDefault(str_replace('-', '_', \App::getLocale()));
        $keys = array_keys($array);
        $vals = array_values($array);
        array_multisort($vals, $mode, $keys);

        return array_combine($keys, $vals);
    }
}

if (!function_exists('image_wraper')) {
    function image_wraper($src, array $options = [])
    {
        $options = array_intersect_key($options, array_flip(['style', 'class', 'width', 'height']));
        $options['src'] = $src;
        $options['style'] = (array)($options['style'] ?? []);
        $options['width'] = (array)($options['width'] ?? []);
        $options['height'] = (array)($options['height'] ?? []);
        $options['style'] = implode(
            ';',
            array_merge(
                $options['style'],
                array_map(
                    function ($u, $v) {
                        return $u . ':' . $v;
                    },
                    array_merge(
                        $options['width'],
                        $options['height']
                    )
                )
            )
        );
        unset($options['width'], $options['height']);
        $options = array_filter($options, 'strlen');
        $options = array_map(
            function ($u, $v) {
                return "$u='$v'";
            },
            array_keys($options),
            $options
        );

        return '<img ' . implode(' ', $options) . ' />';
    }
}
