<?php
/**
 * SmartCategories - Módulo de categorización automática para PrestaShop
 * @author Luis de Yrissarri
 * @version 1.2.2
 * Compatible con PrestaShop 1.7.5 - 9.x
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/SmartCategoryRule.php';
require_once dirname(__FILE__) . '/classes/SmartCategoryCondition.php';

class SmartCategories extends Module
{
    public function __construct()
    {
        $this->name = 'smartcategories';
        $this->tab = 'administration';
        $this->version = '1.2.3';
        $this->author = 'Luis de Yrissarri';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.5.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Smart Categories');
        $this->description = $this->l('Categorización automática de productos mediante reglas configurables y tareas cron.');
        $this->confirmUninstall = $this->l('¿Estás seguro de que deseas desinstalar SmartCategories? Se eliminarán todas las reglas configuradas.');
    }

    public function install()
    {
        return parent::install()
            && $this->installDb()
            && $this->installTab()
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTab();
    }

    private function installDb()
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'smartcategory_rules` (
            `id_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `id_category` INT(10) UNSIGNED NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `noindex` TINYINT(1) NOT NULL DEFAULT 0,
            `flag_text` VARCHAR(100) DEFAULT \'\',
            `flag_bg` VARCHAR(7) DEFAULT \'#e84444\',
            `flag_color` VARCHAR(7) DEFAULT \'#ffffff\',
            `listing_sel_type` VARCHAR(10) DEFAULT \'class\',
            `listing_sel_value` VARCHAR(255) DEFAULT \'product-price-and-shipping\',
            `listing_position` VARCHAR(10) DEFAULT \'prepend\',
            `product_sel_type` VARCHAR(10) DEFAULT \'class\',
            `product_sel_value` VARCHAR(255) DEFAULT \'product-prices\',
            `product_position` VARCHAR(10) DEFAULT \'prepend\',
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_rule`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'smartcategory_rule_products` (
            `id_rule` INT(10) UNSIGNED NOT NULL,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_category` INT(10) UNSIGNED NOT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_rule`, `id_product`),
            KEY `id_category` (`id_category`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'smartcategory_badges` (
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_rule` INT(10) UNSIGNED NOT NULL,
            `badge_text` VARCHAR(100) NOT NULL,
            `badge_bg` VARCHAR(7) NOT NULL DEFAULT \'#e84444\',
            `badge_color` VARCHAR(7) NOT NULL DEFAULT \'#ffffff\',
            PRIMARY KEY (`id_product`, `id_rule`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'smartcategory_conditions` (
            `id_condition` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_rule` INT(10) UNSIGNED NOT NULL,
            `condition_type` VARCHAR(50) NOT NULL,
            `operator` VARCHAR(20) NOT NULL,
            `value` TEXT NOT NULL,
            `value2` TEXT DEFAULT NULL,
            `sort_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_condition`),
            KEY `id_rule` (`id_rule`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'smartcategory_logs` (
            `id_log` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_rule` INT(10) UNSIGNED NOT NULL,
            `products_added` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `products_removed` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `execution_time` FLOAT NOT NULL DEFAULT 0,
            `status` ENUM("success","error") NOT NULL DEFAULT "success",
            `message` TEXT,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_log`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        // Migraciones para instalaciones previas (errores ignorados si ya están aplicadas)
        $migrations = [
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_conditions`
             MODIFY `value` TEXT NOT NULL,
             MODIFY `value2` TEXT DEFAULT NULL',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `start_date` DATETIME DEFAULT NULL',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `end_date` DATETIME DEFAULT NULL',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `noindex` TINYINT(1) NOT NULL DEFAULT 0',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `flag_text` VARCHAR(100) DEFAULT \'\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `flag_bg` VARCHAR(7) DEFAULT \'#e84444\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `flag_color` VARCHAR(7) DEFAULT \'#ffffff\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `listing_sel_type` VARCHAR(10) DEFAULT \'class\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `listing_sel_value` VARCHAR(255) DEFAULT \'product-price-and-shipping\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `listing_position` VARCHAR(10) DEFAULT \'prepend\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `product_sel_type` VARCHAR(10) DEFAULT \'class\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `product_sel_value` VARCHAR(255) DEFAULT \'product-prices\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `product_position` VARCHAR(10) DEFAULT \'prepend\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_badges`
             ADD COLUMN IF NOT EXISTS `badge_bg` VARCHAR(7) NOT NULL DEFAULT \'#e84444\'',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_badges`
             ADD COLUMN IF NOT EXISTS `badge_color` VARCHAR(7) NOT NULL DEFAULT \'#ffffff\'',
        ];

        foreach ($migrations as $mig) {
            Db::getInstance()->execute($mig);
        }

        // Fallback seguro para motores que no soportan ADD COLUMN IF NOT EXISTS
        $this->ensureColumnExists('smartcategory_rules', 'start_date', 'DATETIME DEFAULT NULL');
        $this->ensureColumnExists('smartcategory_rules', 'end_date', 'DATETIME DEFAULT NULL');
        $this->ensureColumnExists('smartcategory_rules', 'noindex', 'TINYINT(1) NOT NULL DEFAULT 0');
        $this->ensureColumnExists('smartcategory_rules', 'flag_text', "VARCHAR(100) DEFAULT ''");
        $this->ensureColumnExists('smartcategory_rules', 'flag_bg', "VARCHAR(7) DEFAULT '#e84444'");
        $this->ensureColumnExists('smartcategory_rules', 'flag_color', "VARCHAR(7) DEFAULT '#ffffff'");
        $this->ensureColumnExists('smartcategory_rules', 'listing_sel_type', "VARCHAR(10) DEFAULT 'class'");
        $this->ensureColumnExists('smartcategory_rules', 'listing_sel_value', "VARCHAR(255) DEFAULT 'product-price-and-shipping'");
        $this->ensureColumnExists('smartcategory_rules', 'listing_position', "VARCHAR(10) DEFAULT 'prepend'");
        $this->ensureColumnExists('smartcategory_rules', 'product_sel_type', "VARCHAR(10) DEFAULT 'class'");
        $this->ensureColumnExists('smartcategory_rules', 'product_sel_value', "VARCHAR(255) DEFAULT 'product-prices'");
        $this->ensureColumnExists('smartcategory_rules', 'product_position', "VARCHAR(10) DEFAULT 'prepend'");
        $this->ensureColumnExists('smartcategory_badges', 'badge_bg', "VARCHAR(7) NOT NULL DEFAULT '#e84444'");
        $this->ensureColumnExists('smartcategory_badges', 'badge_color', "VARCHAR(7) NOT NULL DEFAULT '#ffffff'");

        return true;
    }

    private function ensureColumnExists($table, $column, $definition)
    {
        $exists = (bool) Db::getInstance()->getValue(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = '" . _DB_PREFIX_ . pSQL($table) . "'
               AND COLUMN_NAME = '" . pSQL($column) . "'"
        );

        if (!$exists) {
            Db::getInstance()->execute(
                'ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '` ADD COLUMN `' . bqSQL($column) . '` ' . $definition
            );
        }
    }

    private function removeAllModuleData()
    {
        $sql = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'smartcategory_badges`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'smartcategory_rule_products`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'smartcategory_logs`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'smartcategory_conditions`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'smartcategory_rules`',
        ];
        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }
        return true;
    }

    private function installTab()
    {
        $existingId = (int) Tab::getIdFromClassName('AdminSmartCategories');
        if ($existingId > 0) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminSmartCategories';
        $tab->module = $this->name;
        $tab->name = [];
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = 'Smart Categories';
        }
        $parentTabId = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->id_parent = $parentTabId > 0 ? $parentTabId : 0;
        return $tab->add();
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminSmartCategories');
        if ($tabId) {
            $tab = new Tab($tabId);
            return $tab->delete();
        }
        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminSmartCategories')
        );
    }

    /**
     * Hook front — carga el CSS del badge
     */
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'sc-front',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 200]
        );
    }

    /**
     * Hook displayHeader — inyecta JSON con los badges y el JS que los pinta
     * Compatible con cualquier tema: busca ul.product-flags por data-id-product
     */
    public function hookDisplayHeader()
    {
        $db = Db::getInstance();

        // Query principal: badges + configuración de selectores desde las reglas
        $rows = $db->executeS(
            'SELECT b.id_product, b.badge_text, b.badge_bg, b.badge_color,
                    r.listing_sel_type, r.listing_sel_value, r.listing_position,
                    r.product_sel_type, r.product_sel_value, r.product_position
             FROM `' . _DB_PREFIX_ . 'smartcategory_badges` b
             INNER JOIN `' . _DB_PREFIX_ . 'smartcategory_rules` r ON r.id_rule = b.id_rule'
        );

        // Fallback 1: columnas de selector no existen — intentar solo con colores
        if ($rows === false || $rows === null) {
            $rows = $db->executeS(
                'SELECT id_product, badge_text, badge_bg, badge_color FROM `' . _DB_PREFIX_ . 'smartcategory_badges`'
            );
            if (!empty($rows)) {
                foreach ($rows as &$r) {
                    $r['listing_sel_type']  = 'class';
                    $r['listing_sel_value'] = 'product-price-and-shipping';
                    $r['listing_position']  = 'prepend';
                    $r['product_sel_type']  = 'class';
                    $r['product_sel_value'] = 'product-prices';
                    $r['product_position']  = 'prepend';
                }
                unset($r);
            }
        }

        // Fallback 2: columnas de color tampoco existen
        if ($rows === false || $rows === null) {
            $rows = $db->executeS(
                'SELECT id_product, badge_text FROM `' . _DB_PREFIX_ . 'smartcategory_badges`'
            );
            if (!empty($rows)) {
                foreach ($rows as &$r) {
                    $r['badge_bg']          = '#e84444';
                    $r['badge_color']       = '#ffffff';
                    $r['listing_sel_type']  = 'class';
                    $r['listing_sel_value'] = 'product-price-and-shipping';
                    $r['listing_position']  = 'prepend';
                    $r['product_sel_type']  = 'class';
                    $r['product_sel_value'] = 'product-prices';
                    $r['product_position']  = 'prepend';
                }
                unset($r);
            }
        }

        if (empty($rows)) {
            return '';
        }

        // Construir mapa id_product => [ {text,bg,color,listingSel,listingPos,productSel,productPos}, ... ]
        $validTypes = ['id', 'class', 'attr', 'other'];
        $validPos   = ['before', 'prepend', 'append', 'after', 'replace'];
        $map = [];
        foreach ($rows as $row) {
            $id = (int) $row['id_product'];
            if (!isset($map[$id])) { $map[$id] = []; }

            $lstType = in_array($row['listing_sel_type'], $validTypes) ? $row['listing_sel_type'] : 'class';
            $lstVal  = $row['listing_sel_value'] ?: 'product-price-and-shipping';
            $lstPos  = in_array($row['listing_position'], $validPos) ? $row['listing_position'] : 'prepend';
            $prdType = in_array($row['product_sel_type'], $validTypes) ? $row['product_sel_type'] : 'class';
            $prdVal  = $row['product_sel_value'] ?: 'product-prices';
            $prdPos  = in_array($row['product_position'], $validPos) ? $row['product_position'] : 'prepend';

            // Construir selector CSS según tipo
            $lstSel = $lstType === 'id'    ? '#' . $lstVal
                    : ($lstType === 'class'  ? '.' . $lstVal
                    : ($lstType === 'attr'   ? '[' . $lstVal . ']'
                    : $lstVal));
            $prdSel = $prdType === 'id'    ? '#' . $prdVal
                    : ($prdType === 'class'  ? '.' . $prdVal
                    : ($prdType === 'attr'   ? '[' . $prdVal . ']'
                    : $prdVal));

            $map[$id][] = [
                'text'       => htmlspecialchars($row['badge_text'],           ENT_QUOTES, 'UTF-8'),
                'bg'         => htmlspecialchars($row['badge_bg']    ?: '#e84444', ENT_QUOTES, 'UTF-8'),
                'color'      => htmlspecialchars($row['badge_color'] ?: '#ffffff', ENT_QUOTES, 'UTF-8'),
                'listingSel' => htmlspecialchars($lstSel, ENT_QUOTES, 'UTF-8'),
                'listingPos' => $lstPos,
                'productSel' => htmlspecialchars($prdSel, ENT_QUOTES, 'UTF-8'),
                'productPos' => $prdPos,
            ];
        }

        $json = json_encode($map);

        return <<<HTML
<script>
(function () {
  var SC_BADGES = {$json};

  function scBuildEl(badge) {
    var el = document.createElement('div');
    el.className = 'sc-custom-flag';
    el.setAttribute('data-sc-badge', badge.text);
    el.style.cssText = 'background-color:' + badge.bg + ';color:' + badge.color
      + ';display:block;width:100%;text-align:center;font-size:.75rem;font-weight:700;'
      + 'padding:.35rem .5rem;text-transform:uppercase;line-height:1;margin:.3rem 0;box-sizing:border-box';
    el.textContent = badge.text;
    return el;
  }

  function scInject(target, el, pos) {
    if (!target) return;
    if (pos === 'before')   { target.parentNode && target.parentNode.insertBefore(el, target); }
    else if (pos === 'prepend')  { target.insertBefore(el, target.firstChild); }
    else if (pos === 'append')   { target.appendChild(el); }
    else if (pos === 'after')    { target.parentNode && target.parentNode.insertBefore(el, target.nextSibling); }
    else if (pos === 'replace')  { target.innerHTML = ''; target.appendChild(el); }
  }

  function scApplyBadges() {
    // ── LISTADO ──
    var articles = document.querySelectorAll('article[data-id-product], .product-miniature[data-id-product]');
    articles.forEach(function (article) {
      var id = parseInt(article.getAttribute('data-id-product'), 10);
      if (!id || !SC_BADGES[id]) return;
      SC_BADGES[id].forEach(function (badge) {
        if (article.querySelector('[data-sc-badge="' + badge.text + '"]')) return;
        var target = article.querySelector(badge.listingSel);
        if (target) scInject(target, scBuildEl(badge), badge.listingPos);
      });
    });

    // ── FICHA DE PRODUCTO ──
    var isProduct = !!document.getElementById('product_page_product_id');
    if (!isProduct) return;
    var idInput = document.getElementById('product_page_product_id');
    var id = idInput ? parseInt(idInput.value, 10) : 0;
    if (!id || !SC_BADGES[id]) return;
    SC_BADGES[id].forEach(function (badge) {
      var sel = badge.productSel;
      var targets = document.querySelectorAll(sel);
      targets.forEach(function(target) {
        if (target.closest('article.product-miniature')) return;
        if (target.querySelector('[data-sc-badge="' + badge.text + '"]')) return;
        scInject(target, scBuildEl(badge), badge.productPos);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scApplyBadges);
  } else {
    scApplyBadges();
  }

  document.addEventListener('updateProductList', scApplyBadges);
  document.addEventListener('filterProductSearch', scApplyBadges);

  var scObserver = new MutationObserver(function(mutations) {
    var relevant = mutations.some(function(m) {
      return Array.prototype.some.call(m.addedNodes, function(n) {
        return n.nodeType === 1 && (
          (n.matches && n.matches('[data-id-product]'))
          || (n.querySelector && n.querySelector('[data-id-product]'))
        );
      });
    });
    if (relevant) scApplyBadges();
  });

  document.addEventListener('DOMContentLoaded', function() {
    var container = document.querySelector('#js-product-list, .js-product-list, .products');
    if (container) {
      scObserver.observe(container.parentNode || container, { childList: true, subtree: true });
    }
  });
})();
</script>
HTML;
    }

    /**
     * Hook displayProductPriceBlock — badge encima del precio SOLO en ficha de producto.
     * En Classic este hook se llama tanto en listados como en ficha.
     * Usamos php_self para detectar que estamos en la ficha ('product').
     * En la ficha, Classic llama este hook con type='unit_price' primero.
     */
    public function hookDisplayProductPriceBlock($params)
    {
        // El badge se gestiona completamente via JS en hookDisplayHeader
        return '';
    }

    /**
     * Hook displayProductFlags — desactivado para evitar duplicados en listados.
     * El badge en listados lo gestiona el JS de displayHeader.
     */
    public function hookDisplayProductFlags($params)
    {
        return '';
    }

    /**
     * Hook displayProductAdditionalInfo — desactivado, el JS gestiona los listados.
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        return '';
    }

    /**
     * Renderiza los badges HTML para un producto
     * Classic theme espera <li class="product-flag ..."> dentro de ul.product-flags
     */
    private function renderBadgesForProduct($idProduct, $context = 'list')
    {
        $db   = Db::getInstance();
        $rows = $db->executeS(
            'SELECT badge_text, badge_bg, badge_color FROM `' . _DB_PREFIX_ . 'smartcategory_badges`
             WHERE id_product = ' . (int) $idProduct
        );
        if ($rows === false || $rows === null) {
            $rows = $db->executeS(
                'SELECT badge_text FROM `' . _DB_PREFIX_ . 'smartcategory_badges`
                 WHERE id_product = ' . (int) $idProduct
            );
            if (!empty($rows)) {
                foreach ($rows as &$r) {
                    $r['badge_bg'] = '#e84444'; $r['badge_color'] = '#ffffff';
                }
                unset($r);
            }
        }
        if (empty($rows)) {
            return '';
        }
        $html = '';
        foreach ($rows as $row) {
            $text  = htmlspecialchars($row['badge_text'],           ENT_QUOTES, 'UTF-8');
            $bg    = htmlspecialchars($row['badge_bg']    ?: '#e84444', ENT_QUOTES, 'UTF-8');
            $color = htmlspecialchars($row['badge_color'] ?: '#ffffff', ENT_QUOTES, 'UTF-8');
            if ($context === 'price_block') {
                // En la ficha de producto, encima del precio: div de ancho completo
                $html .= '<div class="sc-custom-flag" style="background-color:' . $bg . ';color:' . $color
                       . ';display:block;width:100%;text-align:center;font-size:.8rem;font-weight:700;'
                       . 'padding:.4rem .6rem;text-transform:uppercase;margin-bottom:.75rem;list-style:none">'
                       . $text . '</div>';
            } else {
                // En listados: li dentro de ul.product-flags
                $html .= '<li class="product-flag sc-custom-flag" style="background-color:' . $bg . ';color:' . $color . '">' . $text . '</li>';
            }
        }
        return $html;
    }

    /**
     * Ejecutar todas las reglas activas (llamado desde cron)
     */
    public function runAllRules()
    {
        $rules = SmartCategoryRule::getActiveRules();
        $results = [];
        foreach ($rules as $rule) {
            $results[] = $rule->execute();
        }
        return $results;
    }
    /**
     * Hook admin — aviso de nueva versión disponible
     */
    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('configure') !== $this->name) {
            return;
        }

        $remoteVersion = $this->getRemoteVersion();
        if ($remoteVersion && version_compare($remoteVersion, $this->version, '>')) {
            $this->context->controller->warnings[] =
                $this->l('Nueva versión disponible') . ': <strong>v' . $remoteVersion . '</strong> — '
                . '<a href="https://github.com/yrissarridev/modulo-prestashop-smart-categories" target="_blank">Ver en GitHub</a>';
        }
    }

    /**
     * Obtener versión remota desde GitHub (cacheada 24h)
     */
    private function getRemoteVersion()
    {
        $cacheKey = 'SC_REMOTE_VERSION';
        $cached   = Configuration::get($cacheKey);
        $cachedAt = (int) Configuration::get($cacheKey . '_TS');

        if ($cached && (time() - $cachedAt) < 86400) {
            return $cached;
        }

        $url  = 'https://raw.githubusercontent.com/yrissarridev/modulo-prestashop-smart-categories/main/version.json';
        $json = @file_get_contents($url);
        if (!$json) {
            return false;
        }

        $data = json_decode($json, true);
        if (empty($data['version'])) {
            return false;
        }

        Configuration::updateValue($cacheKey, $data['version']);
        Configuration::updateValue($cacheKey . '_TS', time());

        return $data['version'];
    }

}
