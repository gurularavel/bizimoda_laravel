<div style="font-family:Arial,sans-serif;font-size:14px;color:#333">
  <h3>Saytdan yeni müraciət</h3>
  <p>
    <b>Ad:</b> {{ $submission->name }}<br/>
    @if($submission->email)<b>E-mail:</b> {{ $submission->email }}<br/>@endif
    @if($submission->phone)<b>Telefon:</b> {{ $submission->phone }}<br/>@endif
    @if($submission->subject)<b>Mövzu:</b> {{ $submission->subject }}<br/>@endif
    @if($submission->url)<b>Səhifə:</b> {{ $submission->url }}<br/>@endif
  </p>
  <p style="white-space:pre-line">{{ $submission->message }}</p>
</div>
