@extends('front.account._layout', ['crumbs' => [['title' => __('Şəxsi məlumatlar'), 'url' => lroute('front.account.edit')]]])

@section('title', __('Şəxsi məlumatlar'))
@section('heading', __('Şəxsi məlumatlar'))

@section('account')
  <form action="{{ lroute('front.account.edit') }}" method="post" class="form-horizontal">
    @csrf
    <fieldset>
      @foreach([['name', __('Ad, soyad'), 'text'], ['email', __('E-mail'), 'email'], ['phone', __('Telefon'), 'tel']] as [$field, $label, $type])
        <div class="form-group {{ $field !== 'phone' ? 'required' : '' }} {{ $errors->has($field) ? 'has-error' : '' }}">
          <label class="col-sm-2 control-label" for="input-{{ $field }}">{{ $label }}</label>
          <div class="col-sm-10">
            <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $user->$field) }}" id="input-{{ $field }}" class="form-control"/>
            @error($field)<div class="text-danger">{{ $message }}</div>@enderror
          </div>
        </div>
      @endforeach
      <div class="form-group">
        <label class="col-sm-2 control-label">{{ __('Xəbər bülleteni') }}</label>
        <div class="col-sm-10"><label class="checkbox-inline"><input type="checkbox" name="newsletter" value="1" @checked($user->newsletter)/> {{ __('Abunə olmaq istəyirəm') }}</label></div>
      </div>
    </fieldset>
    <div class="buttons clearfix">
      <div class="pull-left"><a href="{{ lroute('front.account') }}" class="btn btn-default">{{ __('Geri') }}</a></div>
      <div class="pull-right"><button type="submit" class="btn btn-primary">{{ __('Yadda saxla') }}</button></div>
    </div>
  </form>
@endsection
