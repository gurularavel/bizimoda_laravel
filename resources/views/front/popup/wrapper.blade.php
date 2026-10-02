<html>
<head>
  <style>.module-popup-22 .popup-container{width:500px}.module-popup-22 .popup-inner-body{height:420px}@media (max-width: 470px){.module-popup-22 .popup-inner-body{height:480px}}</style>
</head>
<body>
  <div class="popup-wrapper module module-popup module-popup-22 popup-iframe" data-options='{"showAfter":"","hideAfter":"","cookie":"oneclick","doNotShowAgain":false,"doNotShowAgainChecked":false}'>
    <div class="popup-container">
      <button class="btn popup-close"></button>
      <div class="popup-body">
        <div class="popup-inner-body">
          <div class="journal-loading"><i class="fa fa-spinner fa-spin"></i></div>
          <iframe src="{{ $src }}" width="100%" height="100%" frameborder="0" onload="this.height = this.contentWindow.document.querySelector('.site-wrapper').offsetHeight; $(this).prev('.journal-loading').hide();"></iframe>
        </div>
      </div>
    </div>
    <div class="popup-bg popup-bg-closable"></div>
  </div>
</body>
</html>
