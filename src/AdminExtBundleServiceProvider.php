<?php

namespace TopviewDigital\Extension;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\ServiceProvider;
use TopviewDigital\Extension\Form\Field\File;
use TopviewDigital\Extension\Form\Field\Image;
use TopviewDigital\Extension\Form\Field\Embeds;
use TopviewDigital\Extension\Form\Field\Select;
use TopviewDigital\Extension\Form\Field\HasMany;
use TopviewDigital\Extension\Form\Field\TabbedFields;
use TopviewDigital\Extension\Form\Field\MultipleSelect;
use TopviewDigital\Extension\Grid\Displayers\ButtonTextActions;
use TopviewDigital\Extension\Navbar\LanguageSelector;

class AdminExtBundleServiceProvider extends ServiceProvider
{


    private function publishAssets()
    {
        $this->publishes(
            [dirname(__DIR__) . '/resources/assets' => public_path('vendor/laravel-admin-ext-bundle')],
            'laravel-admin-ext-bundle-assets'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(dirname(__DIR__) . '/resources/views', 'admin-ext');
        Admin::booting(function () {
            Form::forget(['embeds', 'hasMany', 'select', 'multipleSelect', 'file', 'image']);
            Form::extend('embeds', Embeds::class);
            Form::extend('hasMany', HasMany::class);
            Form::extend('select', Select::class);
            Form::extend('file', File::class);
            Form::extend('image', Image::class);
            Form::extend('multipleSelect', MultipleSelect::class);
            Form::extend('tabbedFields', TabbedFields::class);
            Form::init(function (Form $form) {
                $form->disableEditingCheck();
                $form->disableCreatingCheck();
                $form->disableViewCheck();
                $form->tools(function (Form\Tools $tools) {
                    $tools->disableDelete();
                    $tools->disableView();
                });
            });
            Grid::init(function (Grid $grid) {
                $grid->actions(ButtonTextActions::class);
            });
            if (config('admin-ext.lang-selector.enable')) {
                Admin::navbar(function ($navbar) {
                    $navbar->right(new LanguageSelector);
                });
            }
        });

        if ($this->app->runningInConsole()) {
            $this->publishAssets();
        }
    }
}
