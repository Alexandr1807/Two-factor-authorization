(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $yadCity = $('.cm-yad-city', context);
    var $yadAddress = $('.cm-yad-address', context);

    if (!$yadCity.length || !$yadAddress) {
      return;
    }

    $yadCity.autocomplete({
      delay: 600,
      source: function source(request, response) {
        var url = 'yandex_delivery.autocomplete?type=locality&q=' + encodeURIComponent(request.term);
        $.ceAjax('request', fn_url(url), {
          callback: function callback(data) {
            response(data.autocomplete);
          }
        });
      }
    });
    $yadAddress.autocomplete({
      delay: 600,
      source: function source(request, response) {
        var city = $('.cm-yad-city').val();

        if (city) {
          var url = 'yandex_delivery.autocomplete?type=street&city=' + city + '&q=' + encodeURIComponent(request.term);
          $.ceAjax('request', fn_url(url), {
            callback: function callback(data) {
              response(data.autocomplete);
            }
          });
        }
      }
    });
  });
})(Tygh, Tygh.$);