<?php

namespace TopviewDigital\Extension\Traits;

use Encore\Admin\Facades\Admin;

trait ModalForm
{
    protected function initialModalForm()
    {
        $update=localize('无法获取表单信息');
        $title=localize('错误');
        $script=<<<JS

    showModalTrigger=function(url){
        var modal=$('#modal-form');
        window._modal_url=url;
        $.ajax({
            url:url,
            type:"get",
            success: function(response){
                modal.html(response);
                $(response).find('script').each(function(idx,ele){
                    if(ele.html()){
                        eval(ele.html());
                    }
                });
                modal.modal('show');
            },
            error: function(){
                Swal.fire({
                    type: 'error',
                    title: '{$title}',
                    text: '{$update}',
                })
            }
        });
        
    };

    submitModalTrigger=function(url){
        var modal=$('#modal-form');
        var form=modal.find('form').first();
        $.ajax({
            url:url,
            type:"post",
            data:new FormData(form[0]),
            processData: false,
            contentType: false,
            success: function(response){
                modal.modal('hide');
                $.pjax.reload('#pjax-container');
                toastr.success(response.data)
            },
            error: function(response){
                if(response.responseJSON.data != 'validation'){
                    Swal.fire({
                        type: 'error',
                        title: '{$title}',
                        text: response.responseJSON.data,
                    });
                }
                showModalTrigger(window._modal_url);                
            }
        });
    };

JS;
        Admin::script($script);
    }

    protected function ModalForm()
    {
        return view('admin-ext::form.modal-form-frame');
    }
}
