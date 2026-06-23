
(function () {
  function initSmartCategorySelect() {
    var $ = window.jQuery || window.$;
    if (!$) {
      return;
    }

    var $select = $('select[name="id_category"].sc-select2-category');
    if (!$select.length) {
      return;
    }

    if ($.fn.select2) {
      $select.select2({
        width: '100%',
        placeholder: 'Buscar categoría por nombre, ruta o ID...',
        allowClear: false
      });
      return;
    }

    if ($select.data('scFallbackReady')) {
      return;
    }

    $select.data('scFallbackReady', true);

    var $input = $('<input type="text" class="form-control" placeholder="Buscar categoría por nombre, ruta o ID..." style="margin-bottom:8px;">');
    $select.before($input);

    var originalOptions = [];
    $select.find('option').each(function () {
      originalOptions.push({
        value: $(this).attr('value'),
        text: $(this).text(),
        selected: $(this).is(':selected')
      });
    });

    $input.on('keyup change', function () {
      var q = ($(this).val() || '').toLowerCase();
      var currentValue = $select.val();

      $select.empty();

      originalOptions.forEach(function (opt) {
        if (!q || opt.text.toLowerCase().indexOf(q) !== -1 || String(opt.value).indexOf(q) !== -1) {
          var option = $('<option></option>').attr('value', opt.value).text(opt.text);
          if (String(opt.value) === String(currentValue)) {
            option.prop('selected', true);
          }
          $select.append(option);
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSmartCategorySelect);
  } else {
    initSmartCategorySelect();
  }
})();
