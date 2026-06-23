{**
 * Smart Categories - Vista de listado de reglas
 *}
<div class="sc-module">

  {* Header *}
  <div class="sc-header">
    <div class="sc-header-inner">
      <div class="sc-header-title">
        <span class="sc-logo">⚙</span>
        <div>
          <h1>Smart Categories</h1>
          <p>Categorización automática de productos mediante reglas inteligentes</p>
        </div>
      </div>
      <div class="sc-header-actions">
        <a href="{$base_url}&action=new" class="sc-btn sc-btn-primary">
          <span>+</span> Nueva regla
        </a>
        <a href="{$base_url}&action=run_all" class="sc-btn sc-btn-secondary sc-run-all">
          <span class="sc-icon-run">▶</span> Ejecutar todas
        </a>
        <a href="{$base_url}&action=toggle_debug"
           class="sc-btn sc-btn-debug" title="Log debug">
          {if $debug_enabled}🐛 Debug ON{else}🐛 Debug OFF{/if}
        </a>
        <a href="{$base_url}&action=run_migrations"
           class="sc-btn sc-btn-ghost" title="Aplicar migraciones de BD"
           onclick="return confirm('¿Aplicar migraciones de base de datos? Es seguro ejecutarlo varias veces.')">
          🔧 Migraciones BD
        </a>
        <a href="{$base_url}&action=diag_badges"
           class="sc-btn sc-btn-ghost" title="Ver badges guardados en BD">
          🔍 Diagnóstico badges
        </a>
        <a href="{$base_url}&action=purge_badges"
           class="sc-btn sc-btn-ghost" title="Eliminar badges de reglas borradas"
           onclick="return confirm('¿Eliminar badges huerfanos de reglas ya borradas?')">
          🧹 Limpiar badges
        </a>
      </div>
    </div>
  </div>

  {* Alerts *}
  {if isset($confirmations) && $confirmations}
    {foreach $confirmations as $conf}
      <div class="sc-alert sc-alert-success">{$conf}</div>
    {/foreach}
  {/if}
  {if isset($errors) && $errors}
    {foreach $errors as $err}
      <div class="sc-alert sc-alert-error">{$err}</div>
    {/foreach}
  {/if}

  <div class="sc-stats-grid">
    <div class="sc-stat-card sc-stat-active">🟢 {$rule_stats.active} activas</div>
    <div class="sc-stat-card sc-stat-scheduled">🟡 {$rule_stats.scheduled} programadas</div>
    <div class="sc-stat-card sc-stat-finished">⚫ {$rule_stats.finished} finalizadas</div>
    <div class="sc-stat-card sc-stat-inactive">🔵 {$rule_stats.inactive} inactivas</div>
  </div>

  <div class="sc-layout">

    {* Rules List *}
    <div class="sc-main">
      <div class="sc-card">
        <div class="sc-card-header">
          <h2>Reglas configuradas</h2>
          <span class="sc-badge">{$rules|count} {if $rules|count == 1}regla{else}reglas{/if}</span>
        </div>

        {if $rules}
          <div class="sc-rules-list">
            {foreach $rules as $rule}
              <div class="sc-rule-item {if !$rule.active}sc-inactive{/if}" data-id="{$rule.id_rule}">
                <div class="sc-rule-status">
                  <label class="sc-toggle" title="{if $rule.active}Desactivar{else}Activar{/if}">
                    <input type="checkbox" {if $rule.active}checked{/if}
                           onchange="scToggleRule({$rule.id_rule}, this)">
                    <span class="sc-toggle-slider"></span>
                  </label>
                </div>
                <div class="sc-rule-info">
                  <div class="sc-rule-name">{$rule.name}</div>
                  <div class="sc-rule-meta">
                    <span class="sc-meta-item">
                      <span class="sc-icon">📂</span>
                      {if $rule.category_name}{$rule.category_name}{else}<em>Categoría no encontrada</em>{/if}
                    </span>
                    <span class="sc-meta-item">
                      <span class="sc-icon">📋</span>
                      {$rule.conditions_count} condición{if $rule.conditions_count != 1}es{/if}
                    </span>
                  </div>
                  {if $rule.campaign_window}
                    <div class="sc-rule-schedule-line">
                      <span class="sc-icon">📅</span>
                      <span class="sc-schedule-text">{$rule.campaign_window}</span>
                    </div>
                    <div class="sc-progress-track">
                      <div class="sc-progress-bar sc-progress-{$rule.schedule_status}" style="width:{$rule.progress|intval}%"></div>
                    </div>
                  {/if}
                  <div class="sc-rule-status-line">
                    <span class="sc-status-pill sc-status-{$rule.schedule_status}">{$rule.status_label}</span>
                    {if $rule.status_hint}
                      <span class="sc-status-hint">{$rule.status_hint}</span>
                    {/if}
                  </div>
                </div>
                <div class="sc-rule-actions">
                  <a href="{$base_url}&action=run&id_rule={$rule.id_rule}"
                     class="sc-btn-icon sc-run-rule" title="Ejecutar ahora"
                     data-id="{$rule.id_rule}">
                    ▶
                  </a>
                  <a href="{$base_url}&action=edit&id_rule={$rule.id_rule}"
                     class="sc-btn-icon" title="Editar">
                    ✏
                  </a>
                  <a href="{$base_url}&action=logs&id_rule={$rule.id_rule}"
                     class="sc-btn-icon" title="Ver logs">
                    📊
                  </a>
                  <button type="button"
                     class="sc-btn-icon sc-btn-danger"
                     title="Eliminar"
                     onclick="scDeleteRule({$rule.id_rule}, '{$rule.name|escape:'javascript'}')">
                    🗑
                  </button>
                  <form id="scDeleteForm{$rule.id_rule}" method="post" action="{$base_url}&action=delete" style="display:none">
                    <input type="hidden" name="id_rule" value="{$rule.id_rule}">
                  </form>
                </div>
              </div>
            {/foreach}
          </div>
        {else}
          <div class="sc-empty">
            <div class="sc-empty-icon">⚙</div>
            <h3>No hay reglas configuradas</h3>
            <p>Crea tu primera regla para automatizar la categorización de productos.</p>
            <a href="{$base_url}&action=new" class="sc-btn sc-btn-primary">+ Crear primera regla</a>
          </div>
        {/if}
      </div>
    </div>

    {* Sidebar *}
    <div class="sc-sidebar">

      {* Cron info *}
      <div class="sc-card sc-card-cron">
        <div class="sc-card-header">
          <h3>🕐 Tarea Cron</h3>
        </div>
        <p class="sc-cron-desc">Configura esta URL o copia directamente el comando recomendado para pegarlo en el cron de tu servidor.</p>

        <p class="sc-cron-hint">URL del cron:</p>
        <div class="sc-cron-url-wrap">
          <code class="sc-cron-url" id="cronUrl">{$cron_url}</code>
          <button class="sc-copy-btn" onclick="scCopyValue('cronUrl', this)" title="Copiar URL">⧉</button>
        </div>

        <p class="sc-cron-hint">Comando recomendado (cada 5 minutos):</p>
        <div class="sc-cron-url-wrap">
          <code class="sc-cron-example" id="cronCommand5">{$cron_command_5min}</code>
          <button class="sc-copy-btn" onclick="scCopyValue('cronCommand5', this)" title="Copiar comando recomendado">⧉</button>
        </div>

        <p class="sc-cron-hint">Alta precisión (cada 1 minuto):</p>
        <div class="sc-cron-url-wrap">
          <code class="sc-cron-example" id="cronCommand1">{$cron_command_1min}</code>
          <button class="sc-copy-btn" onclick="scCopyValue('cronCommand1', this)" title="Copiar comando de alta precisión">⧉</button>
        </div>

        <p class="sc-cron-note">⚠️ Para campañas con fecha y hora, se recomienda ejecutar el cron cada 5 minutos.</p>
      </div>

      {* Recent logs *}
      {if $recent_logs}
        <div class="sc-card">
          <div class="sc-card-header">
            <h3>📊 Últimas ejecuciones</h3>
            <a href="{$base_url}&action=logs" class="sc-link-small">Ver todo</a>
          </div>
          <div class="sc-logs-mini">
            {foreach $recent_logs as $log}
              <div class="sc-log-mini-item sc-log-{$log.status}">
                <div class="sc-log-mini-info">
                  <span class="sc-log-mini-name">{$log.rule_name|default:'—'}</span>
                  <span class="sc-log-mini-date">{$log.date_add|date_format:"%d/%m %H:%M"}</span>
                </div>
                <div class="sc-log-mini-stats">
                  {if $log.status == 'success'}
                    <span class="sc-stat sc-stat-add">+{$log.products_added}</span>
                    <span class="sc-stat sc-stat-rem">−{$log.products_removed}</span>
                  {else}
                    <span class="sc-stat sc-stat-err">Error</span>
                  {/if}
                </div>
              </div>
            {/foreach}
          </div>
        </div>
      {/if}

    </div>
  </div>

</div>

<script>
var scBaseUrl = '{$base_url|escape:'javascript'}';
</script>
<script>
{literal}
function scToggleRule(idRule, checkbox) {
  const item = checkbox.closest('.sc-rule-item');
  fetch(scBaseUrl + '&action=toggle&id_rule=' + idRule + '&ajax=1')
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        if (data.active) {
          item.classList.remove('sc-inactive');
          checkbox.checked = true;
          item.querySelector('.sc-toggle').title = 'Desactivar';
        } else {
          item.classList.add('sc-inactive');
          checkbox.checked = false;
          item.querySelector('.sc-toggle').title = 'Activar';
        }
      }
    });
}

function scDeleteRule(id, name) {
  if (confirm('¿Eliminar la regla "' + name + '"? Esta acción no se puede deshacer.')) {
    document.getElementById('scDeleteForm' + id).submit();
  }
}

function scCopyValue(elementId, button) {
  const value = document.getElementById(elementId).textContent;
  navigator.clipboard.writeText(value).then(() => {
    const original = button.textContent;
    button.textContent = '✓';
    setTimeout(() => button.textContent = original, 2000);
  });
}

document.querySelectorAll('.sc-run-rule').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const id = this.dataset.id;
    this.textContent = '⏳';
    this.style.pointerEvents = 'none';
    const url = this.href + '&ajax=1';
    fetch(url)
      .then(r => r.json())
      .then(data => {
        this.textContent = '✓';
        setTimeout(() => { this.textContent = '▶'; this.style.pointerEvents = ''; }, 2000);
        const msg = `Ejecutado: +${data.added} añadidos, −${data.removed} eliminados`;
        scShowNotification(msg, data.status === 'success' ? 'success' : 'error');
      });
  });
});

document.querySelector('.sc-run-all')?.addEventListener('click', function(e) {
  e.preventDefault();
  this.textContent = '⏳ Ejecutando...';
  this.style.pointerEvents = 'none';
  const url = this.href + '&ajax=1';
  fetch(url)
    .then(r => r.json())
    .then(data => {
      this.innerHTML = '<span class="sc-icon-run">▶</span> Ejecutar todas';
      this.style.pointerEvents = '';
      scShowNotification(`Todas ejecutadas: +${data.total_added} añadidos, −${data.total_removed} eliminados`, 'success');
    });
});

function scShowNotification(msg, type) {
  const n = document.createElement('div');
  n.className = 'sc-alert sc-alert-' + type + ' sc-alert-float';
  n.textContent = msg;
  document.querySelector('.sc-module').prepend(n);
  setTimeout(() => n.remove(), 4000);
}
{/literal}
</script>
