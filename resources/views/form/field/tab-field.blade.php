<hr style="margin-top: 0px;">

<div id="embed-{{$column}}" class="embed-{{$column}}">
    <div class="embed-{{$column}}-forms">
        <div class="embed-{{$column}}-form fields-group">
            <div class="row">
                <div class="{{$viewClass['label']}}"></div>
                <div class="{{$viewClass['field']}}">
                    <ul class="nav nav-tabs">
                        @foreach($form->fields() as $field)

                        <li @if($loop->iteration==1) class="active"> @else > @endif
                            <a data-toggle="tab"
                                href="#tab_{{str_replace("[","_",str_replace("]","",$field->variables()['name']))}}">
                                {{$tabs[$loop->iteration-1]}}
                                <i class="fa fa-exclamation-circle text-red hide"></i>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                @foreach($form->fields() as $field)
                <div id="tab_{{str_replace("[","_",str_replace("]","",$field->variables()['name']))}}"
                    class="tab-pane fade in @if($loop->iteration==1)active @endif">
                    {!! $field->render() !!}
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<hr style="margin-top: 0px;">