@extends('front.account._layout', ['crumbs' => [['title' => __('Şifrəni dəyiş'), 'url' => lroute('front.account.password')]]])

@section('title', __('Şifrəni dəyiş'))
@section('heading', __('Şifrəni dəyiş'))

@section('account')
  <form action="{{ lroute('front.account.password') }}" method="post" class="form-horizontal">
    @csrf
    <fieldset>
      @if(auth('web')->user()->password)
        <div class="form-group required {{ $errors->has('current_password') ? 'has-error' : '' }}">
          <label class="col-sm-2 control-label" for="input-current">{{ __('Cari şifrə') }}</label>
          <div class="col-sm-10">
            <input type="password" name="current_password" id="input-current" class="form-control"/>
            @error('current_password')<div class="text-danger">{{ $message }}</div>@enderror
          </div>
        </div>
      @endif
      <div class="form-group required {{ $errors->has('password') ? 'has-error' : '' }}">
        <label class="col-sm-2 control-label" for="input-password">{{ __('Yeni şifrə') }}</label>
        <div class="col-sm-10">
          <input type="password" name="password" id="input-password" class="form-control"/>
          @error('password')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="form-group required">
        <label class="col-sm-2 control-label" for="input-confirm">{{ __('Şifrə (təkrar)') }}</label>
        <div class="col-sm-10"><input type="password" name="password_confirmation" id="input-confirm" class="form-control"/></div>
      </div>
    </fieldset>
    <div class="buttons clearfix">
      <div class="pull-left"><a href="{{ lroute('front.account') }}" class="btn btn-default">{{ __('Geri') }}</a></div>
      <div class="pull-right"><button type="submit" class="btn btn-primary">{{ __('Yadda saxla') }}</button></div>
    </div>
  </form>
@endsection
