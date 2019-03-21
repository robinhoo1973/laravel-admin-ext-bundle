<?php

namespace TopviewDigital\Extension\Form\Field;

use Illuminate\Support\Facades\Storage;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form\Field\File as LegacyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class File extends LegacyFile
{
    protected static $js = [];
    protected static $css = [];
    /**
     * Get store name of upload file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function getStoreName(UploadedFile $file)
    {
        if ($this->useUniqueName) {
            return $this->generateUniqueName($file);
        }
        if ($this->useSequenceName) {
            return $this->generateSequenceName($file);
        }
        if ($this->name instanceof \Closure) {
            $this->correctFileName($file);
            return $this->generateUniqueName($file);
        }
        if (is_string($this->name)) {
            return $this->name;
        }
        return $file->getClientOriginalName();
    }

    /**
     * Prepare validation rules based on input data.
     *
     * @param $field_nane
     *
     * @return $storage
     */
    protected function correctFileName($file, $storage = 'admin')
    {
        $file_field = $this;
        $callback = $this->name;
        $this->form->saved(function ($form) use ($file_field, $callback, $file, $storage) {
            $form->model()->refresh();
            $original_file = str_replace(
                Storage::disk($storage)->url(''),
                '',
                $form->model()->{$file_field->column()}
            );
            $dir_name = implode('/', array_filter(explode('/', dirname($original_file)), 'strlen'));
            $base_name = $callback->call($file_field, $file);

            $rename_file = $dir_name . '/' . $base_name;
            if (Storage::disk($storage)->exists($rename_file)) {
                Storage::disk($storage)->delete($rename_file);
            }
            Storage::disk($storage)->move($original_file, $rename_file);
            $form->model()->{$file_field->column()} = $rename_file;
            $form->model()->save();
        });
    }
    /**
     * Set default options form image field.
     *
     * @return void
     */
    protected function setupDefaultOptions()
    {
        $defaultOptions = [
            'lang'                 => app()->getLocale(),
            'overwriteInitial'     => false,
            'initialPreviewAsData' => true,
            'showRemove'           => true,
            'showUpload'           => false,
            'dropZoneEnabled'      => true,
            'showClose'            => false,
            'showCaption'          => false,
            'showDrag'             => false,
            'browseIcon'           => '<i class="glyphicon glyphicon-folder-open"></i>',
            'removeIcon'           => '<i class="glyphicon glyphicon-remove"></i>',
            'browseLabel'          => '',
            'removeLabel'          => '',
            'layoutTemplates'      => ['main2' => '{preview} {upload} {browse}'],

            //            'initialCaption'       => $this->initialCaption($this->value),
            'deleteExtraData'      => [
                $this->formatName($this->column) => static::FILE_DELETE_FLAG,
                static::FILE_DELETE_FLAG         => '',
                '_token'                         => csrf_token(),
                '_method'                        => 'PUT',
            ],
        ];
        if ($this->form instanceof Form) {
            $defaultOptions['deleteUrl'] = $this->form->resource() . '/' . $this->form->model()->getKey();
        }
        $this->options($defaultOptions);
        // Admin::baseJs([
        //     // '/vendor/laravel-admin-ext-bundle/bootstrap-fileinput/js/fileinput.min.js',
        //     '/vendor/laravel-admin-ext-bundle/bootstrap-fileinput/js/locales/' . app()->getLocale() . '.js'
        // ]);
        // // Admin::baseCss(['/vendor/laravel-admin-ext-bundle/bootstrap-fileinput/css/fileinput.min.css']);
    }

    public function allowedFileTypes(array $types)
    {
        $this->options(['allowedFileTypes' => $types]);
        return $this;
    }
}
