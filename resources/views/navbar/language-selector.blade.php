<li class="dropdown">
    <a class="dropdown-toggle" href="#" data-toggle="dropdown">
        <span><i class="flag-icon flag-icon-{{$default['flag']}}"></i>&nbsp;&nbsp;{{$default['native']}}<b
                class="caret"></b>
        </span>
    </a>
    <ul class="dropdown-menu">
        @foreach($locales as $locale)
        @if($locale['locale']!=$default['locale'])
        <li class='dropdown-item'>
            <a class='{{config('admin-ext.lang-selector.form.id')}}' data-locale="{{$locale['locale']}}"
                href='javascript:void(0)'>
                <span style="margin:15px">
                    <i class="flag-icon flag-icon-{{$locale['flag']}}"
                        style="margin-right:10px"></i>{{$locale['native']}}
                </span>
            </a>
        </li>
        @endif
        @endforeach
    </ul>
    <form id='{{config('admin-ext.lang-selector.form.id')}}'
        action='{{admin_url(config('admin-ext.lang-selector.form.url'))}}'
        method='{{config('admin-ext.lang-selector.form.method')}}'>
        <input type='hidden' name='_token' value='{{csrf_token()}}' />
        <input type="hidden" name='{{config('admin-ext.lang-selector.data.field')}}' value='{{app()->getLocale()}}' />
    </form>
</li>