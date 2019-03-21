<?php

namespace TopviewDigital\Extension\Controller;

use App\Http\Controllers\Controller;
use TopviewDigital\LangSwitcher\Model\LangSwitcher;

class LanguageSelector extends Controller
{

    public function index()
    {
        LangSwitcher::registerGuard(['class' => 'Admin', 'method' => 'user', 'middleware' => 'admin']);
        LangSwitcher::switchLocale(config('admin-ext.lang-selector.data.field'));
        return back();
    }
}
