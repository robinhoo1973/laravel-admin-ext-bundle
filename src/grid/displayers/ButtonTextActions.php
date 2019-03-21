<?php

namespace TopviewDigital\Extension\Grid\Displayers;

use Encore\Admin\Grid\Displayers\Actions;

class ButtonTextActions extends Actions
{
    protected function renderEdit()
    {
        $edit = trans('admin.edit');

        return <<<EOT
&nbsp;&nbsp;<a href="{$this->getResource()}/{$this->getRouteKey()}/edit" 
               class="btn btn-xs btn-primary">
            <i class="fa fa-edit"></i>&nbsp;{$edit}</a>&nbsp;&nbsp;
EOT;
    }

    protected function renderView()
    {
        $show = trans('admin.show');

        return <<<EOT
&nbsp;&nbsp;<a href="/{$this->getResource()}/{$this->getRouteKey()}/show" class="btn btn-xs btn-info">
    <i class="fa fa-eye"></i>{$show}</a>&nbsp;&nbsp;
EOT;
    }

    protected function renderDelete()
    {
        $delete = trans('admin.delete');
        parent::renderDelete();

        return <<<EOT
&nbsp;&nbsp;<a href="javascript:void(0);" 
               data-id="{$this->getKey()}" 
               class="grid-row-delete btn btn-xs btn-danger">
               <i class="fa fa-trash"></i>&nbsp;{$delete}</a>&nbsp;&nbsp;
EOT;
    }
}
