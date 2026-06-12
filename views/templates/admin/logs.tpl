{**
 * Smart Categories - Vista de logs
 *}
<div class="sc-module">

  <div class="sc-header">
    <div class="sc-header-inner">
      <div class="sc-header-title">
        <a href="{$base_url}" class="sc-back">←</a>
        <div>
          <h1>Historial de ejecuciones</h1>
          <p>{if $rule}Regla: {$rule->name}{else}Todas las reglas{/if}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="sc-card">
    {if $logs}
      <table class="sc-table">
        <thead>
          <tr>
            <th>Fecha</th>
            {if !$rule}<th>Regla</th>{/if}
            <th>Añadidos</th>
            <th>Eliminados</th>
            <th>Tiempo</th>
            <th>Estado</th>
            <th>Mensaje</th>
          </tr>
        </thead>
        <tbody>
          {foreach $logs as $log}
            <tr class="sc-log-row-{$log.status}">
              <td class="sc-td-date">{$log.date_add|date_format:"%d/%m/%Y %H:%M:%S"}</td>
              {if !$rule}<td>{$log.rule_name|default:'—'}</td>{/if}
              <td class="sc-td-center">
                <span class="sc-stat sc-stat-add">+{$log.products_added}</span>
              </td>
              <td class="sc-td-center">
                <span class="sc-stat sc-stat-rem">−{$log.products_removed}</span>
              </td>
              <td class="sc-td-center">{$log.execution_time|string_format:"%.2f"}s</td>
              <td class="sc-td-center">
                <span class="sc-status-badge sc-status-{$log.status}">
                  {if $log.status == 'success'}✓ OK{else}✗ Error{/if}
                </span>
              </td>
              <td class="sc-td-message">{$log.message|default:'—'}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    {else}
      <div class="sc-empty">
        <div class="sc-empty-icon">📊</div>
        <h3>Sin registros</h3>
        <p>Aún no se ha ejecutado ninguna regla.</p>
      </div>
    {/if}
  </div>

</div>
