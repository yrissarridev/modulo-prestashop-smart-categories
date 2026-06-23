
(function () {
  function initCategorySearch() {
    var $ = window.jQuery || window.$;
    if (!$) return;

    var $input = $('#sc-category-search');
    var $select = $('select[name="id_category"]');

    if (!$input.length || !$select.length) return;

    var allOptions = [];

    $select.find('option').each(function () {
      allOptions.push({
        value: $(this).attr('value'),
        text: $(this).text(),
        selected: $(this).is(':selected')
      });
    });

    $input.on('input keyup change', function () {
      var query = ($input.val() || '').toLowerCase().trim();
      var currentValue = $select.val();

      $select.empty();

      allOptions.forEach(function (opt) {
        var text = (opt.text || '').toLowerCase();
        var value = String(opt.value || '').toLowerCase();

        if (!query || text.indexOf(query) !== -1 || value.indexOf(query) !== -1) {
          var $option = $('<option></option>').attr('value', opt.value).text(opt.text);

          if (String(opt.value) === String(currentValue)) {
            $option.prop('selected', true);
          }

          $select.append($option);
        }
      });

      if (!$select.find('option[value="' + currentValue + '"]').length && $select.find('option').length) {
        $select.prop('selectedIndex', 0);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCategorySearch);
  } else {
    initCategorySearch();
  }
})();
