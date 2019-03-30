    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-label">
                    {{$form->title()}}
                </h4>
            </div>
            <div class="modal-body">
                <!-- form start -->
                {!! $form->open(['class' => "form-horizontal"]) !!}
                @if(!$tabObj->isEmpty())
                @include('admin::form.tab', compact('tabObj'))
                @else
                <div class="fields-group">
                    @if($form->hasRows())
                    @foreach($form->getRows() as $row)
                    {!! $row->render() !!}
                    @endforeach
                    @else
                    @foreach($form->fields() as $field)
                    {!! $field->render() !!}
                    @endforeach
                    @endif
                </div>
                @endif
                <div class="modal-footer">
                    {!! $form->renderFooter() !!}
                    @foreach($form->getHiddenFields() as $field)
                    {!! $field->render() !!}
                    @endforeach
                    <!-- /.box-footer -->
                    <div class="text-center">
                        <button type="button" class="btn btn-primary modal-submit"
                            onclick="submitModalTrigger('{{$form->getAction()}}')">{{ trans('admin.submit') }}
                        </button>
                    </div>
                    {!! $form->close() !!}
                </div>
            </div>
        </div>
    </div>
    {!! \Encore\Admin\Facades\Admin::script() !!}