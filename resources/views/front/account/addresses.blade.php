@extends('front.account._layout', ['crumbs' => [['title' => __('Ünvanlarım'), 'url' => lroute('front.account.addresses')]]])

@section('title', __('Ünvanlarım'))
@section('heading', __('Ünvanlarım'))

@section('account')
  @if($addresses->isNotEmpty())
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        @foreach($addresses as $address)
          <tr>
            <td class="text-left">{{ $address->first_name }} {{ $address->last_name }}<br/>{{ $address->phone }}<br/>{{ $address->city }}, {{ $address->address }}
              @if($address->is_default)<br/><span class="label label-success">{{ __('Əsas ünvan') }}</span>@endif
            </td>
            <td class="text-right">
              <form action="{{ lroute('front.account.addresses.delete', ['address' => $address->id]) }}" method="post" onsubmit="return confirm('{{ __('Silinsin?') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @endforeach
      </table>
    </div>
  @else
    <p>{{ __('Hələ ünvan əlavə etməmisiniz.') }}</p>
  @endif

  <h2 class="title">{{ __('Yeni ünvan') }}</h2>
  <form action="{{ lroute('front.account.addresses') }}" method="post" class="form-horizontal">
    @csrf
    @foreach([['first_name', __('Ad'), true], ['last_name', __('Soyad'), false], ['phone', __('Telefon'), false], ['city', __('Şəhər'), false], ['address', __('Ünvan'), true]] as [$field, $label, $required])
      <div class="form-group {{ $required ? 'required' : '' }} {{ $errors->has($field) ? 'has-error' : '' }}">
        <label class="col-sm-2 control-label" for="input-{{ $field }}">{{ $label }}</label>
        <div class="col-sm-10">
          <input type="text" name="{{ $field }}" value="{{ old($field) }}" id="input-{{ $field }}" class="form-control"/>
          @error($field)<div class="text-danger">{{ $message }}</div>@enderror
        </div>
      </div>
    @endforeach
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10"><label class="checkbox-inline"><input type="checkbox" name="is_default" value="1"/> {{ __('Əsas ünvan et') }}</label></div>
    </div>
    <div class="buttons clearfix">
      <div class="pull-right"><button type="submit" class="btn btn-primary">{{ __('Əlavə et') }}</button></div>
    </div>
  </form>
@endsection
