{**
 * Smart Categories - Formulario de regla
 *}
<div class="sc-module">

  <div class="sc-header">
    <div class="sc-header-inner">
      <div class="sc-header-title">
        <a href="{$base_url}" class="sc-back">←</a>
        <div>
          <h1>{if $rule && $rule->id_rule}Editar regla{else}Nueva regla{/if}</h1>
          <p>Define los criterios para la categorización automática</p>
        </div>
      </div>
    </div>
  </div>

  {if isset($errors) && $errors}
    {foreach $errors as $err}
      <div class="sc-alert sc-alert-error">{$err}</div>
    {/foreach}
  {/if}

  <form action="{$save_url}" method="post" id="scRuleForm">
    {if $rule && $rule->id_rule}
      <input type="hidden" name="id_rule" value="{$rule->id_rule}">
    {/if}

    <div class="sc-layout">
      <div class="sc-main">

        {* Basic info *}
        <div class="sc-card">
          <div class="sc-card-header">
            <h2>Información básica</h2>
          </div>
          <div class="sc-form-grid">
            <div class="sc-form-group sc-span-2">
              <label class="sc-label" for="ruleName">Nombre de la regla *</label>
              <input type="text" id="ruleName" name="name" class="sc-input"
                     value="{if $rule && $rule->id_rule}{$rule->name|escape:'html'}{/if}"
                     placeholder="Ej: Productos Día del Padre" required>
              <span class="sc-hint">Un nombre descriptivo para identificar esta regla</span>
            </div>

            <div class="sc-form-group">
              <label class="sc-label" for="ruleCategory">Categoría destino *</label>
              <div class="sc-select-with-action">
                <select id="ruleCategory" name="id_category" class="sc-select" required>
                  <option value="">— Seleccionar categoría —</option>
                  {foreach $category_list as $catId => $catName}
                    <option value="{$catId}" {if $rule && $rule->id_rule && $rule->id_category == $catId}selected{/if}>
                      {$catName}
                    </option>
                  {/foreach}
                </select>
                <button type="button" class="sc-btn-new-cat" onclick="scOpenCatModal()" title="Crear nueva categoría">+</button>
              </div>
              <span class="sc-hint">Los productos que cumplan las condiciones se añadirán a esta categoría</span>
            </div>

            <div class="sc-form-group">
              <label class="sc-label" for="ruleActive">Estado</label>
              <select id="ruleActive" name="active" class="sc-select">
                <option value="1" {if !$rule || !$rule->id_rule || $rule->active}selected{/if}>Activa</option>
                <option value="0" {if $rule && $rule->id_rule && !$rule->active}selected{/if}>Inactiva</option>
              </select>
              <span class="sc-hint">Las reglas inactivas no se ejecutan en el cron</span>
            </div>

            <div class="sc-form-group">
              <label class="sc-label" for="ruleStartDate">Inicio de campaña</label>
              <input type="datetime-local" id="ruleStartDate" name="start_date" class="sc-input"
                     value="{$start_date_input|escape:'html'}">
              <span class="sc-hint">Si la dejas vacía, la campaña puede empezar en cualquier momento.</span>
            </div>

            <div class="sc-form-group">
              <label class="sc-label" for="ruleEndDate">Fin de campaña</label>
              <input type="datetime-local" id="ruleEndDate" name="end_date" class="sc-input"
                     value="{$end_date_input|escape:'html'}">
              <span class="sc-hint">Cuando llegue esta fecha, el módulo vaciará siempre la categoría en la siguiente ejecución.</span>
            </div>

            <div class="sc-form-group sc-span-2">
              <label class="sc-label sc-label-check">
                <input type="hidden" name="noindex" value="0">
                <input type="checkbox" id="ruleNoindex" name="noindex" value="1"
                  {if $rule && $rule->id_rule && $rule->noindex}checked{/if}>
                No indexar la categoría destino (meta robots: noindex)
              </label>
              <span class="sc-hint">Al ejecutar la regla, la categoría quedará marcada como noindex en PrestaShop. Útil para categorías automáticas que no deben aparecer en buscadores.</span>
            </div>

            <div class="sc-form-group sc-span-2">
              <label class="sc-label" for="ruleFlagText">Etiqueta en productos <span class="sc-optional">(opcional)</span></label>
              <div class="sc-badge-editor">
                <div class="sc-form-group">
                  <input type="text" id="ruleFlagText" name="flag_text" class="sc-input"
                         maxlength="100"
                         value="{if $rule && $rule->id_rule}{$rule->flag_text|escape:'html'}{/if}"
                         placeholder="Ej: Promo Primavera…"
                         oninput="scPreviewBadge()">
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:8px 0">
                  <span style="font-size:12px;color:#888;white-space:nowrap">Color del badge:</span>
                  <div id="scSwatchRow" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                    {foreach [
                      ['bg'=>'#e84444','color'=>'#ffffff','name'=>'Rojo'],
                      ['bg'=>'#e87c2a','color'=>'#ffffff','name'=>'Naranja'],
                      ['bg'=>'#f0c000','color'=>'#222222','name'=>'Amarillo'],
                      ['bg'=>'#2eaa2e','color'=>'#ffffff','name'=>'Verde'],
                      ['bg'=>'#1a73e8','color'=>'#ffffff','name'=>'Azul'],
                      ['bg'=>'#8b44e8','color'=>'#ffffff','name'=>'Morado'],
                      ['bg'=>'#e84488','color'=>'#ffffff','name'=>'Rosa'],
                      ['bg'=>'#009aa8','color'=>'#ffffff','name'=>'Cian'],
                      ['bg'=>'#222222','color'=>'#ffffff','name'=>'Negro'],
                      ['bg'=>'#f0f0f0','color'=>'#333333','name'=>'Gris claro']
                    ] as $p}
                      <span
                        data-bg="{$p.bg}" data-color="{$p.color}"
                        title="{$p.name}"
                        onclick="scPickColor(this)"
                        style="display:inline-block;width:28px;height:28px;border-radius:50%;background:{$p.bg};cursor:pointer;border:3px solid {if $rule && $rule->id_rule && $rule->flag_bg == $p.bg}#333{else}transparent{/if};box-sizing:border-box;flex-shrink:0">
                      </span>
                    {/foreach}
                  </div>
                  <input type="hidden" id="ruleFlagBg"    name="flag_bg"    value="{if $rule && $rule->id_rule && $rule->flag_bg}{$rule->flag_bg|escape:'html'}{else}#e84444{/if}">
                  <input type="hidden" id="ruleFlagColor" name="flag_color" value="{if $rule && $rule->id_rule && $rule->flag_color}{$rule->flag_color|escape:'html'}{else}#ffffff{/if}">
                </div>
                <div class="sc-badge-preview-row">
                  <span class="sc-label">Preview:</span>
                  <li id="scBadgePreview" class="product-flag sc-custom-flag" style="display:none;list-style:none"></li>
                </div>
              </div>
              <span class="sc-hint">Déjalo vacío para no mostrar etiqueta. Se elimina automáticamente si el producto deja de cumplir las condiciones.</span>
            </div>

            {* ── Selector de posición en LISTADO ── *}
            <div class="sc-form-group sc-span-2">
              <label class="sc-label">Posición en listado de productos</label>
              <span class="sc-hint" style="margin-bottom:10px;display:block">Indica dónde quieres que aparezca el badge en el listado. Usa el inspector de tu navegador para encontrar el ID, clase o atributo del elemento destino.</span>

              <div class="sc-selector-block">
                <div class="sc-selector-row">
                  <span class="sc-selector-label">Tipo:</span>
                  {foreach [['id','ID'],['class','Clase'],['attr','Atributo'],['other','Otro']] as $t}
                    <label class="sc-radio-pill">
                      <input type="radio" name="listing_sel_type" value="{$t[0]}"
                        {if $rule && $rule->id_rule && $rule->listing_sel_type == $t[0]}checked{elseif !$rule || !$rule->id_rule && $t[0]=='class'}checked{/if}
                        onchange="scUpdateSelectorPreview('listing')">
                      <span>{$t[1]}</span>
                    </label>
                  {/foreach}
                  <input type="text" name="listing_sel_value" class="sc-input sc-input-sel"
                    value="{if $rule && $rule->id_rule}{$rule->listing_sel_value|escape:'html'}{else}product-price-and-shipping{/if}"
                    placeholder="nombre-del-elemento"
                    oninput="scUpdateSelectorPreview('listing')">
                </div>
                <div class="sc-selector-preview">
                  <code id="scListingSelectorPreview" class="sc-sel-preview"></code>
                </div>
                <div class="sc-selector-row" style="margin-top:10px">
                  <span class="sc-selector-label">Posición:</span>
                  {foreach [['before','Antes'],['prepend','Al inicio (dentro)'],['append','Al final (dentro)'],['after','Después'],['replace','Reemplazar']] as $p}
                    <label class="sc-radio-pill">
                      <input type="radio" name="listing_position" value="{$p[0]}"
                        {if $rule && $rule->id_rule && $rule->listing_position == $p[0]}checked{elseif !$rule || !$rule->id_rule && $p[0]=='prepend'}checked{/if}>
                      <span>{$p[1]}</span>
                    </label>
                  {/foreach}
                </div>
              </div>
            </div>

            {* ── Selector de posición en FICHA ── *}
            <div class="sc-form-group sc-span-2">
              <label class="sc-label">Posición en ficha de producto</label>
              <span class="sc-hint" style="margin-bottom:10px;display:block">Mismo sistema que arriba, pero para la página de producto individual.</span>

              <div class="sc-selector-block">
                <div class="sc-selector-row">
                  <span class="sc-selector-label">Tipo:</span>
                  {foreach [['id','ID'],['class','Clase'],['attr','Atributo'],['other','Otro']] as $t}
                    <label class="sc-radio-pill">
                      <input type="radio" name="product_sel_type" value="{$t[0]}"
                        {if $rule && $rule->id_rule && $rule->product_sel_type == $t[0]}checked{elseif !$rule || !$rule->id_rule && $t[0]=='class'}checked{/if}
                        onchange="scUpdateSelectorPreview('product')">
                      <span>{$t[1]}</span>
                    </label>
                  {/foreach}
                  <input type="text" name="product_sel_value" class="sc-input sc-input-sel"
                    value="{if $rule && $rule->id_rule}{$rule->product_sel_value|escape:'html'}{else}product-prices{/if}"
                    placeholder="nombre-del-elemento"
                    oninput="scUpdateSelectorPreview('product')">
                </div>
                <div class="sc-selector-preview">
                  <code id="scProductSelectorPreview" class="sc-sel-preview"></code>
                </div>
                <div class="sc-selector-row" style="margin-top:10px">
                  <span class="sc-selector-label">Posición:</span>
                  {foreach [['before','Antes'],['prepend','Al inicio (dentro)'],['append','Al final (dentro)'],['after','Después'],['replace','Reemplazar']] as $p}
                    <label class="sc-radio-pill">
                      <input type="radio" name="product_position" value="{$p[0]}"
                        {if $rule && $rule->id_rule && $rule->product_position == $p[0]}checked{elseif !$rule || !$rule->id_rule && $p[0]=='prepend'}checked{/if}>
                      <span>{$p[1]}</span>
                    </label>
                  {/foreach}
                </div>
              </div>
            </div>
          </div>
        </div>

        {* Conditions *}
        <div class="sc-card">
          <div class="sc-card-header">
            <h2>Condiciones</h2>
            <span class="sc-badge-info">Se deben cumplir TODAS (operador AND)</span>
          </div>

          <div id="scConditionsList">
            {if $conditions}
              {foreach $conditions as $i => $cond}
                <div class="sc-condition-row" data-index="{$i}">
                  <div class="sc-condition-inner">
                    <div class="sc-condition-number">{$i+1}</div>
                    <div class="sc-condition-fields">
                      <div class="sc-form-group">
                        <label class="sc-label">Tipo de condición</label>
                        <select name="condition_type[]" class="sc-select sc-condition-type" data-index="{$i}">
                          <option value="">— Seleccionar —</option>
                          <optgroup label="Precio">
                            <option value="price_between" {if $cond.condition_type == 'price_between'}selected{/if}>Precio entre X e Y</option>
                            <option value="price_greater" {if $cond.condition_type == 'price_greater'}selected{/if}>Precio mayor que X</option>
                            <option value="price_less"    {if $cond.condition_type == 'price_less'}selected{/if}>Precio menor que X</option>
                          </optgroup>
                          <optgroup label="Fecha de alta">
                            <option value="date_added_before" {if $cond.condition_type == 'date_added_before'}selected{/if}>Dado de alta hace más de X días</option>
                            <option value="date_added_after"  {if $cond.condition_type == 'date_added_after'}selected{/if}>Dado de alta hace menos de X días</option>
                          </optgroup>
                          <optgroup label="Stock">
                            <option value="stock_with"    {if $cond.condition_type == 'stock_with'}selected{/if}>Con stock (> 0 uds.)</option>
                            <option value="stock_without" {if $cond.condition_type == 'stock_without'}selected{/if}>Sin stock (= 0 uds.)</option>
                            <option value="stock_greater" {if $cond.condition_type == 'stock_greater'}selected{/if}>Stock mayor que X</option>
                            <option value="stock_less"    {if $cond.condition_type == 'stock_less'}selected{/if}>Stock menor que X</option>
                            <option value="stock_equal"   {if $cond.condition_type == 'stock_equal'}selected{/if}>Stock igual a X</option>
                          </optgroup>
                          <optgroup label="Catálogo">
                            <option value="in_categories"     {if $cond.condition_type == 'in_categories'}selected{/if}>Pertenece a categorías</option>
                            <option value="not_in_categories" {if $cond.condition_type == 'not_in_categories'}selected{/if}>Excluir categorías</option>
                            <option value="in_feature_values" {if $cond.condition_type == 'in_feature_values'}selected{/if}>Tiene característica con valor</option>
                          </optgroup>
                          <optgroup label="Marca">
                            <option value="in_manufacturers"     {if $cond.condition_type == 'in_manufacturers'}selected{/if}>Pertenece a marca(s)</option>
                            <option value="not_in_manufacturers" {if $cond.condition_type == 'not_in_manufacturers'}selected{/if}>Excluir marca(s)</option>
                          </optgroup>
                          <optgroup label="Ventas">
                            <option value="no_sales_since_days" {if $cond.condition_type == 'no_sales_since_days'}selected{/if}>Sin ventas en los últimos X días</option>
                            <option value="no_sales_ever"       {if $cond.condition_type == 'no_sales_ever'}selected{/if}>Nunca vendido</option>
                          </optgroup>
                        </select>
                      </div>
                      <div class="sc-condition-values" id="scValues{$i}">
                        {* Rendered by JS on load *}
                      </div>
                    </div>
                    <button type="button" class="sc-remove-condition" onclick="scRemoveCondition(this)" title="Eliminar">✕</button>
                  </div>
                </div>
              {/foreach}
            {/if}
          </div>

          <div class="sc-add-condition">
            <button type="button" class="sc-btn sc-btn-ghost" onclick="scAddCondition()">
              + Añadir condición
            </button>
          </div>

          <div class="sc-conditions-empty" id="scConditionsEmpty" {if $conditions}style="display:none"{/if}>
            <span>⚠</span> Añade al menos una condición para que la regla funcione
          </div>
        </div>

      </div>

      {* Sidebar *}
      <div class="sc-sidebar">
        <div class="sc-card sc-card-help">
          <div class="sc-card-header">
            <h3>Ayuda</h3>
          </div>
          <div class="sc-help-content">
            <h4>Cómo funciona</h4>
            <p>Los productos que cumplan <strong>todas</strong> las condiciones se añadirán automáticamente a la categoría seleccionada.</p>
            <h4>Tipos de condiciones</h4>
            <ul>
              <li><strong>Precio</strong>: Filtra por precio de venta</li>
              <li><strong>Fecha de alta</strong>: Días desde que se creó el producto</li>
              <li><strong>Stock</strong>: Unidades disponibles</li>
              <li><strong>Categorías</strong>: Producto pertenece a cualquiera de las categorías seleccionadas</li>
              <li><strong>Características</strong>: Producto tiene alguno de los valores de característica seleccionados</li>
              <li><strong>Marca</strong>: Producto pertenece (o no) a alguna de las marcas seleccionadas</li>
              <li><strong>Ventas</strong>: Sin ventas en X días, o nunca vendido</li>
            </ul>
            <h4>Opción noindex</h4>
            <p>Marca la categoría destino como <strong>noindex</strong> cada vez que se ejecuta la regla, para que los buscadores no la indexen.</p>
            <h4>Etiqueta en productos</h4>
            <p>Escribe un texto libre (ej: <em>Promo Primavera</em>). Al ejecutar la regla se mostrará como badge en el front usando el hook <strong>displayProductFlags</strong> del tema. Se elimina automáticamente si el producto deja de cumplir las condiciones.</p>
            <h4>Ejemplo</h4>
            <p>Vinos tintos Tempranillo con stock:</p>
            <ul>
              <li>Categorías: Vinos Tintos, Vinos Rosados</li>
              <li>Característica: Uva → Tempranillo</li>
              <li>Con stock disponible</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="sc-form-footer">
      <a href="{$base_url}" class="sc-btn sc-btn-ghost">Cancelar</a>
      <button type="submit" class="sc-btn sc-btn-primary">Guardar regla</button>
    </div>

  </form>
</div>

{* ── Modal: crear categoría ── *}
<div id="scCatModal" class="sc-modal-overlay" style="display:none" onclick="if(event.target===this)scCloseCatModal()">
  <div class="sc-modal">
    <div class="sc-modal-header">
      <h3>Nueva categoría</h3>
      <button type="button" class="sc-modal-close" onclick="scCloseCatModal()">✕</button>
    </div>
    <div class="sc-modal-body">
      <div class="sc-form-group">
        <label class="sc-label" for="scNewCatName">Nombre de la categoría *</label>
        <input type="text" id="scNewCatName" class="sc-input" placeholder="Ej: Día del Padre 2025" autofocus>
      </div>
      <div class="sc-form-group">
        <label class="sc-label" for="scNewCatParent">Categoría padre</label>
        <select id="scNewCatParent" class="sc-select">
          {foreach $category_list as $catId => $catName}
            <option value="{$catId}">{$catName}</option>
          {/foreach}
        </select>
      </div>
      <div id="scCatModalError" class="sc-alert sc-alert-error" style="display:none"></div>
    </div>
    <div class="sc-modal-footer">
      <button type="button" class="sc-btn sc-btn-ghost" onclick="scCloseCatModal()">Cancelar</button>
      <button type="button" class="sc-btn sc-btn-primary" id="scCatModalSave" onclick="scSaveNewCategory()">
        Crear categoría
      </button>
    </div>
  </div>
</div>

{* ── Inject server-side data for JS ── *}
<script>
var scCreateCatUrl      = '{$create_cat_url}';
var scFilterCategories  = {$filter_categories|json_encode};
var scFilterFeatures    = {$filter_features|json_encode};
var scFilterManufacturers = {$filter_manufacturers|json_encode};
var scExistingConditions = {$conditions|json_encode};
var scConditionIndex     = {$conditions|count};
</script>

{literal}
<script>
/* ── Modal: crear categoría ── */
function scOpenCatModal() {
  document.getElementById('scCatModal').style.display = 'flex';
  document.getElementById('scNewCatName').focus();
  document.getElementById('scCatModalError').style.display = 'none';
  document.getElementById('scNewCatName').value = '';
}
function scCloseCatModal() {
  document.getElementById('scCatModal').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') scCloseCatModal();
});
function scSaveNewCategory() {
  var name     = document.getElementById('scNewCatName').value.trim();
  var parentId = document.getElementById('scNewCatParent').value;
  var errEl    = document.getElementById('scCatModalError');
  var btn      = document.getElementById('scCatModalSave');
  if (!name) { errEl.textContent = 'El nombre es obligatorio.'; errEl.style.display = 'block'; return; }
  btn.textContent = 'Creando...'; btn.disabled = true; errEl.style.display = 'none';
  var params = new URLSearchParams();
  params.append('ajax', '1'); params.append('action', 'create_category');
  params.append('cat_name', name); params.append('cat_parent', parentId);
  fetch(scCreateCatUrl + '&ajax=1', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString() })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      btn.textContent = 'Crear categoría'; btn.disabled = false;
      if (data.success) {
        var sel = document.getElementById('ruleCategory');
        var opt = document.createElement('option');
        opt.value = data.id_category; opt.textContent = data.name; opt.selected = true;
        sel.appendChild(opt); sel.value = data.id_category;
        scCloseCatModal();
      } else { errEl.textContent = data.error || 'Error al crear la categoría.'; errEl.style.display = 'block'; }
    })
    .catch(function() { btn.textContent = 'Crear categoría'; btn.disabled = false; errEl.textContent = 'Error de conexión.'; errEl.style.display = 'block'; });
}

/* ── Build multiselect checkboxes ── */
function scBuildCategoriesCheckboxes(selectedIds, exclude) {
  selectedIds = selectedIds || [];
  var label = exclude ? 'Excluir categorías (puede seleccionar varias)' : 'Categorías (puede seleccionar varias)';
  var html = '<div class="sc-form-group">'
    + '<label class="sc-label">' + label + '</label>'
    + '<input type="hidden" name="condition_value[]" class="sc-multisel-hidden" value="' + selectedIds.join(',') + '">'
    + '<input type="hidden" name="condition_value2[]" value="">'
    + '<div class="sc-multicheck-search"><input type="text" class="sc-input sc-input-sm sc-multicheck-filter" placeholder="Buscar categoría..." oninput="scFilterCheckboxes(this)"></div>'
    + '<div class="sc-multicheck-list">';
  scFilterCategories.forEach(function(cat) {
    var checked = selectedIds.indexOf(String(cat.id)) !== -1 || selectedIds.indexOf(cat.id) !== -1 ? 'checked' : '';
    html += '<label class="sc-check-item" style="padding-left:' + Math.max(0, (cat.level - 1) * 14) + 'px">'
      + '<input type="checkbox" value="' + cat.id + '" ' + checked + ' onchange="scSyncMultiHidden(this)">'
      + '<span>' + cat.name + '</span></label>';
  });
  html += '</div></div>';
  return html;
}

function scBuildFeaturesCheckboxes(selectedIds) {
  selectedIds = selectedIds || [];
  var html = '<div class="sc-form-group">'
    + '<label class="sc-label">Valores de característica (puede seleccionar varios)</label>'
    + '<input type="hidden" name="condition_value[]" class="sc-multisel-hidden" value="' + selectedIds.join(',') + '">'
    + '<input type="hidden" name="condition_value2[]" value="">'
    + '<div class="sc-multicheck-search"><input type="text" class="sc-input sc-input-sm sc-multicheck-filter" placeholder="Buscar característica o valor..." oninput="scFilterCheckboxes(this)"></div>'
    + '<div class="sc-multicheck-list">';
  scFilterFeatures.forEach(function(feat) {
    html += '<div class="sc-check-group-title">' + feat.feature_name + '</div>';
    feat.values.forEach(function(val) {
      var checked = selectedIds.indexOf(String(val.id)) !== -1 || selectedIds.indexOf(val.id) !== -1 ? 'checked' : '';
      html += '<label class="sc-check-item sc-check-item-value">'
        + '<input type="checkbox" value="' + val.id + '" ' + checked + ' onchange="scSyncMultiHidden(this)">'
        + '<span>' + val.name + '</span></label>';
    });
  });
  html += '</div></div>';
  return html;
}

function scBuildManufacturersCheckboxes(selectedIds, exclude) {
  selectedIds = selectedIds || [];
  var label = exclude ? 'Excluir marca(s) (puede seleccionar varias)' : 'Marca(s) (puede seleccionar varias)';
  var html = '<div class="sc-form-group">'
    + '<label class="sc-label">' + label + '</label>'
    + '<input type="hidden" name="condition_value[]" class="sc-multisel-hidden" value="' + selectedIds.join(',') + '">'
    + '<input type="hidden" name="condition_value2[]" value="">'
    + '<div class="sc-multicheck-search"><input type="text" class="sc-input sc-input-sm sc-multicheck-filter" placeholder="Buscar marca..." oninput="scFilterCheckboxes(this)"></div>'
    + '<div class="sc-multicheck-list">';
  scFilterManufacturers.forEach(function(brand) {
    var checked = selectedIds.indexOf(String(brand.id)) !== -1 || selectedIds.indexOf(brand.id) !== -1 ? 'checked' : '';
    html += '<label class="sc-check-item">'
      + '<input type="checkbox" value="' + brand.id + '" ' + checked + ' onchange="scSyncMultiHidden(this)">'
      + '<span>' + brand.name + '</span></label>';
  });
  html += '</div></div>';
  return html;
}

function scSyncMultiHidden(checkbox) {
  var list   = checkbox.closest('.sc-multicheck-list');
  var hidden = checkbox.closest('.sc-condition-values').querySelector('.sc-multisel-hidden');
  if (!hidden) return;
  var checked = list.querySelectorAll('input[type=checkbox]:checked');
  var ids = Array.prototype.map.call(checked, function(c) { return c.value; });
  hidden.value = ids.join(',');
}

function scFilterCheckboxes(input) {
  var q    = input.value.toLowerCase();
  var list = input.closest('.sc-form-group').querySelector('.sc-multicheck-list');
  var items = list.querySelectorAll('.sc-check-item');
  var groupTitles = list.querySelectorAll('.sc-check-group-title');
  items.forEach(function(el) {
    el.style.display = el.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
  });
  // Hide group titles if all their values are hidden
  groupTitles.forEach(function(title) {
    var next = title.nextElementSibling;
    var hasVisible = false;
    while (next && next.classList.contains('sc-check-item-value')) {
      if (next.style.display !== 'none') hasVisible = true;
      next = next.nextElementSibling;
    }
    title.style.display = hasVisible ? '' : 'none';
  });
}

/* ── Build value fields ── */
function scBuildValueHtml(type, val1, val2) {
  val1 = val1 || ''; val2 = val2 || '';
  if (type === 'price_between') {
    return '<div class="sc-values-range">'
      + '<div class="sc-form-group"><label class="sc-label">Min (\u20ac)</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" step="0.01" min="0" placeholder="0.00" value="' + val1 + '"></div>'
      + '<span class="sc-range-sep">\u2014</span>'
      + '<div class="sc-form-group"><label class="sc-label">Max (\u20ac)</label>'
      + '<input type="number" name="condition_value2[]" class="sc-input sc-input-sm" step="0.01" min="0" placeholder="999.99" value="' + val2 + '"></div>'
      + '</div>';
  } else if (type === 'price_greater') {
    return '<div class="sc-form-group"><label class="sc-label">Mayor que (\u20ac)</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" step="0.01" min="0" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value=""></div>';
  } else if (type === 'price_less') {
    return '<div class="sc-form-group"><label class="sc-label">Menor que (\u20ac)</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" step="0.01" min="0" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value=""></div>';
  } else if (type === 'date_added_before') {
    return '<div class="sc-form-group"><label class="sc-label">Hace m\u00e1s de X d\u00edas</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" min="1" step="1" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value=""></div>';
  } else if (type === 'date_added_after') {
    return '<div class="sc-form-group"><label class="sc-label">Hace menos de X d\u00edas</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" min="1" step="1" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value=""></div>';
  } else if (type === 'stock_with' || type === 'stock_without') {
    return '<input type="hidden" name="condition_value[]" value="0">'
      + '<input type="hidden" name="condition_value2[]" value="">'
      + '<p class="sc-no-value-note">No requiere valor adicional</p>';
  } else if (type === 'stock_greater') {
    return '<div class="sc-form-group"><label class="sc-label">Stock mayor que</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" min="0" step="1" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value=""></div>';
  } else if (type === 'stock_less') {
    return '<div class="sc-form-group"><label class="sc-label">Stock menor que</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" min="0" step="1" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value=""></div>';
  } else if (type === 'stock_equal') {
    return '<div class="sc-form-group"><label class="sc-label">Stock igual a</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" min="0" step="1" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value=""></div>';
  } else if (type === 'in_categories') {
    var ids = val1 ? val1.split(',').map(function(s){return s.trim();}).filter(Boolean) : [];
    return scBuildCategoriesCheckboxes(ids, false);
  } else if (type === 'not_in_categories') {
    var ids = val1 ? val1.split(',').map(function(s){return s.trim();}).filter(Boolean) : [];
    return scBuildCategoriesCheckboxes(ids, true);
  } else if (type === 'in_feature_values') {
    var ids = val1 ? val1.split(',').map(function(s){return s.trim();}).filter(Boolean) : [];
    return scBuildFeaturesCheckboxes(ids);
  } else if (type === 'in_manufacturers') {
    var ids = val1 ? val1.split(',').map(function(s){return s.trim();}).filter(Boolean) : [];
    return scBuildManufacturersCheckboxes(ids, false);
  } else if (type === 'not_in_manufacturers') {
    var ids = val1 ? val1.split(',').map(function(s){return s.trim();}).filter(Boolean) : [];
    return scBuildManufacturersCheckboxes(ids, true);
  } else if (type === 'no_sales_since_days') {
    return '<div class="sc-form-group"><label class="sc-label">D\u00edas sin ventas</label>'
      + '<input type="number" name="condition_value[]" class="sc-input sc-input-sm" min="1" step="1" placeholder="Ej: 90, 180, 365, 730" value="' + val1 + '">'
      + '<input type="hidden" name="condition_value2[]" value="">'
      + '<p class="sc-no-value-note">Incluye productos nunca vendidos.</p></div>';
  } else if (type === 'no_sales_ever') {
    return '<input type="hidden" name="condition_value[]" value="1">'
      + '<input type="hidden" name="condition_value2[]" value="">'
      + '<p class="sc-no-value-note">No requiere valor adicional</p>';
  }
  return '<input type="hidden" name="condition_value[]" value=""><input type="hidden" name="condition_value2[]" value="">';
}

function scConditionTypeOptions(selected) {
  selected = selected || '';
  var opts = '<option value="">\u2014 Seleccionar \u2014</option>'
    + '<optgroup label="Precio">'
    + '<option value="price_between"' + (selected==='price_between'?' selected':'') + '>Precio entre X e Y</option>'
    + '<option value="price_greater"' + (selected==='price_greater'?' selected':'') + '>Precio mayor que X</option>'
    + '<option value="price_less"' + (selected==='price_less'?' selected':'') + '>Precio menor que X</option>'
    + '</optgroup>'
    + '<optgroup label="Fecha de alta">'
    + '<option value="date_added_before"' + (selected==='date_added_before'?' selected':'') + '>Dado de alta hace m\u00e1s de X d\u00edas</option>'
    + '<option value="date_added_after"' + (selected==='date_added_after'?' selected':'') + '>Dado de alta hace menos de X d\u00edas</option>'
    + '</optgroup>'
    + '<optgroup label="Stock">'
    + '<option value="stock_with"' + (selected==='stock_with'?' selected':'') + '>Con stock (&gt; 0 uds.)</option>'
    + '<option value="stock_without"' + (selected==='stock_without'?' selected':'') + '>Sin stock (= 0 uds.)</option>'
    + '<option value="stock_greater"' + (selected==='stock_greater'?' selected':'') + '>Stock mayor que X</option>'
    + '<option value="stock_less"' + (selected==='stock_less'?' selected':'') + '>Stock menor que X</option>'
    + '<option value="stock_equal"' + (selected==='stock_equal'?' selected':'') + '>Stock igual a X</option>'
    + '</optgroup>'
    + '<optgroup label="Cat\u00e1logo">'
    + '<option value="in_categories"' + (selected==='in_categories'?' selected':'') + '>Pertenece a categor\u00edas</option>'
    + '<option value="not_in_categories"' + (selected==='not_in_categories'?' selected':'') + '>Excluir categor\u00edas</option>'
    + '<option value="in_feature_values"' + (selected==='in_feature_values'?' selected':'') + '>Tiene caracter\u00edstica con valor</option>'
    + '</optgroup>'
    + '<optgroup label="Marca">'
    + '<option value="in_manufacturers"' + (selected==='in_manufacturers'?' selected':'') + '>Pertenece a marca(s)</option>'
    + '<option value="not_in_manufacturers"' + (selected==='not_in_manufacturers'?' selected':'') + '>Excluir marca(s)</option>'
    + '</optgroup>'
    + '<optgroup label="Ventas">'
    + '<option value="no_sales_since_days"' + (selected==='no_sales_since_days'?' selected':'') + '>Sin ventas en los \u00faltimos X d\u00edas</option>'
    + '<option value="no_sales_ever"' + (selected==='no_sales_ever'?' selected':'') + '>Nunca vendido</option>'
    + '</optgroup>';
  return opts;
}

function scUpdateConditionFields(select) {
  var row       = select.closest('.sc-condition-row');
  var idx       = row.dataset.index;
  var container = document.getElementById('scValues' + idx);
  if (!container) return;
  container.innerHTML = scBuildValueHtml(select.value, '', '');
}

function scAddCondition() {
  var idx = scConditionIndex++;
  var div = document.createElement('div');
  div.className = 'sc-condition-row';
  div.dataset.index = idx;
  div.innerHTML = '<div class="sc-condition-inner">'
    + '<div class="sc-condition-number">' + (document.querySelectorAll('.sc-condition-row').length + 1) + '</div>'
    + '<div class="sc-condition-fields">'
    + '<div class="sc-form-group"><label class="sc-label">Tipo de condici\u00f3n</label>'
    + '<select name="condition_type[]" class="sc-select sc-condition-type" data-index="' + idx + '" onchange="scUpdateConditionFields(this)">'
    + scConditionTypeOptions('')
    + '</select></div>'
    + '<div class="sc-condition-values" id="scValues' + idx + '">'
    + '<input type="hidden" name="condition_value[]" value="">'
    + '<input type="hidden" name="condition_value2[]" value="">'
    + '</div>'
    + '</div>'
    + '<button type="button" class="sc-remove-condition" onclick="scRemoveCondition(this)" title="Eliminar">\u2715</button>'
    + '</div>';
  document.getElementById('scConditionsList').appendChild(div);
  document.getElementById('scConditionsEmpty').style.display = 'none';
}

function scRemoveCondition(btn) {
  btn.closest('.sc-condition-row').remove();
  if (document.querySelectorAll('.sc-condition-row').length === 0) {
    document.getElementById('scConditionsEmpty').style.display = 'flex';
  }
}

// On page load: populate value fields for existing conditions
document.addEventListener('DOMContentLoaded', function() {
  if (!scExistingConditions || !scExistingConditions.length) return;
  var rows = document.querySelectorAll('.sc-condition-row');
  scExistingConditions.forEach(function(cond, i) {
    if (!rows[i]) return;
    var container = rows[i].querySelector('.sc-condition-values');
    if (container) {
      container.innerHTML = scBuildValueHtml(cond.condition_type, cond.value, cond.value2);
    }
    var sel = rows[i].querySelector('.sc-condition-type');
    if (sel) {
      sel.addEventListener('change', function() { scUpdateConditionFields(this); });
    }
  });
  document.querySelectorAll('.sc-condition-type').forEach(function(sel) {
    if (!sel.dataset.listenerAttached) {
      sel.addEventListener('change', function() { scUpdateConditionFields(this); });
      sel.dataset.listenerAttached = '1';
    }
  });
});
</script>
{/literal}

<script>
{literal}
function scPreviewBadge() {
  var textEl  = document.getElementById('ruleFlagText');
  var bgEl    = document.getElementById('ruleFlagBg');
  var colorEl = document.getElementById('ruleFlagColor');
  var prev    = document.getElementById('scBadgePreview');
  if (!textEl || !prev) return;
  var text  = textEl.value.trim();
  var bg    = bgEl    ? bgEl.value    : '#e84444';
  var color = colorEl ? colorEl.value : '#ffffff';
  if (text) {
    prev.textContent = text;
    prev.style.backgroundColor = bg;
    prev.style.color = color;
    prev.style.display = 'inline-block';
  } else {
    prev.style.display = 'none';
  }
}

function scPickColor(el) {
  var bg    = el.getAttribute('data-bg');
  var color = el.getAttribute('data-color');
  document.getElementById('ruleFlagBg').value    = bg;
  document.getElementById('ruleFlagColor').value = color;
  var row = document.getElementById('scSwatchRow');
  if (row) {
    row.querySelectorAll('[data-bg]').forEach(function(s) {
      s.style.border = '3px solid transparent';
    });
  }
  el.style.border = '3px solid #333';
  scPreviewBadge();
}

document.addEventListener('DOMContentLoaded', function() {
  scPreviewBadge();
  // Marcar swatch activo al cargar
  var bgEl = document.getElementById('ruleFlagBg');
  if (bgEl && bgEl.value) {
    var row = document.getElementById('scSwatchRow');
    if (row) {
      row.querySelectorAll('[data-bg]').forEach(function(s) {
        if (s.getAttribute('data-bg') === bgEl.value) {
          s.style.border = '3px solid #333';
        }
      });
    }
  }
});
{/literal}
</script>

<script>
{literal}
function scUpdateSelectorPreview(context) {
  var typeInputs = document.querySelectorAll('input[name="' + context + '_sel_type"]');
  var valInput   = document.querySelector('input[name="' + context + '_sel_value"]');
  var preview    = document.getElementById('sc' + context.charAt(0).toUpperCase() + context.slice(1) + 'SelectorPreview');
  if (!preview || !valInput) return;
  var type = '';
  typeInputs.forEach(function(r) { if (r.checked) type = r.value; });
  var val = valInput.value.trim();
  var prefix = type === 'id' ? '#' : type === 'class' ? '.' : type === 'attr' ? '[' : '';
  var suffix = type === 'attr' ? ']' : '';
  preview.textContent = val ? prefix + val + suffix : '—';
}
document.addEventListener('DOMContentLoaded', function() {
  scUpdateSelectorPreview('listing');
  scUpdateSelectorPreview('product');
});
{/literal}
</script>
