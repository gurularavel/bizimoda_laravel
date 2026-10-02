@php $fid = 'cf-'.\Illuminate\Support\Str::random(6); @endphp
<div class="module module-form module-form-20">
  @if(! empty($title))<h3 class="title module-title">{{ $title }}</h3>@endif
  <div class="module-body">
    {{-- journal.js .module-form .btn-primary klikini AJAX ilə göndərir və {status, response} JSON gözləyir --}}
    <form action="{{ lroute('front.form.send', ['type' => 'contact']) }}" method="post" enctype="multipart/form-data" class="form-horizontal" data-language="{{ app()->getLocale() }}">
      @csrf
      <fieldset>
        <div class="form-group custom-field required">
          <label class="col-sm-2 control-label" for="{{ $fid }}-1">{{ __('Adınız') }}</label>
          <div class="col-sm-10"><input type="text" name="name" value="{{ auth('web')->user()?->name }}" placeholder="{{ __('Adınız') }}" id="{{ $fid }}-1" class="form-control"/></div>
        </div>
        <div class="form-group custom-field required">
          <label class="col-sm-2 control-label" for="{{ $fid }}-2">{{ __('E-mail') }}</label>
          <div class="col-sm-10"><input type="email" name="email" value="{{ auth('web')->user()?->email }}" placeholder="{{ __('E-mail') }}" id="{{ $fid }}-2" class="form-control"/></div>
        </div>
        <div class="form-group custom-field">
          <label class="col-sm-2 control-label" for="{{ $fid }}-5">{{ __('Telefon') }}</label>
          <div class="col-sm-10"><input type="tel" name="phone" value="{{ auth('web')->user()?->phone }}" placeholder="{{ __('Telefon') }}" id="{{ $fid }}-5" class="form-control"/></div>
        </div>
        <div class="form-group custom-field">
          <label class="col-sm-2 control-label" for="{{ $fid }}-3">{{ __('Mövzu') }}</label>
          <div class="col-sm-10">
            <select name="subject" id="{{ $fid }}-3" class="form-control">
              <option value=""> --- {{ __('Seçin') }} --- </option>
              @foreach(array_filter(array_map('trim', explode("\n", (string) setting_t('contact.subjects', '')))) as $subject)
                <option value="{{ $subject }}">{{ $subject }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group custom-field required">
          <label class="col-sm-2 control-label" for="{{ $fid }}-4">{{ __('Mətn') }}</label>
          <div class="col-sm-10"><textarea name="message" rows="5" placeholder="{{ __('Mətn') }}" id="{{ $fid }}-4" class="form-control"></textarea></div>
        </div>
      </fieldset>
      <div class="buttons">
        <div class="pull-right">
          <button type="submit" class="btn btn-primary" data-loading-text="<span>{{ __('Təsdiqlə') }}</span>"><span>{{ __('Təsdiqlə') }}</span></button>
        </div>
      </div>
    </form>
  </div>
</div>
