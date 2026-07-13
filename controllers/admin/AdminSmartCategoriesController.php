<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/SmartCategoryRule.php';
require_once dirname(__FILE__) . '/../../classes/SmartCategoryCondition.php';

class AdminSmartCategoriesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->meta_title = $this->l('Smart Categories');
    }

    private function scLog($msg)
    {
        if (!Configuration::get('SC_DEBUG')) {
            return;
        }
        file_put_contents(
            dirname(__FILE__) . '/../../sc_debug.log',
            date('Y-m-d H:i:s') . ' ' . $msg . "\n",
            FILE_APPEND
        );
    }

    public function postProcess()
    {
        $this->scLog('postProcess GET=' . json_encode($_GET) . ' POST=' . json_encode($_POST));
        return true;
    }

    public function initContent()
    {
        $this->addCSS($this->module->getPathUri() . 'views/css/admin.css');
        $this->addJS($this->module->getPathUri() . 'views/js/admin-category-search.js');
        $this->addJS($this->module->getPathUri() . 'views/js/admin-select2.js');

        // Registrar hooks necesarios si no están aún (para módulos instalados antes de esta versión)
        foreach (['displayHeader', 'actionFrontControllerSetMedia'] as $hookName) {
            if (!$this->module->isRegisteredInHook($hookName)) {
                $this->module->registerHook($hookName);
            }
        }

        $action = Tools::getValue('action', 'list');
        $this->scLog('initContent action=[' . $action . ']');

        switch ($action) {
            case 'new':
            case 'edit':
                $this->renderFormView();
                break;
            case 'save':
                $this->scSave();
                break;
            case 'delete':
                $this->scDelete();
                break;
            case 'duplicate':
                $this->scDuplicate();
                break;
            case 'toggle':
                $this->scToggle();
                break;
            case 'run':
                $this->scRunRule();
                break;
            case 'run_all':
                $this->scRunAll();
                break;
            case 'create_category':
                $this->scCreateCategory();
                break;
            case 'toggle_debug':
                $this->scToggleDebug();
                break;
            case 'run_migrations':
                $this->scRunMigrations();
                break;
            case 'diag_badges':
                $this->scDiagBadges();
                break;
            case 'purge_badges':
                $this->scPurgeBadges();
                break;
            case 'logs':
                $this->renderLogsView();
                break;
            default:
                $this->renderListView();
                break;
        }
    }

    // ── Render methods — each ends with parent::initContent() ──────

    public function renderListView()
    {
        $scMsg = Tools::getValue('sc_msg', '');
        $scErr = Tools::getValue('sc_err', '');
        $this->scLog('renderListView sc_msg=[' . $scMsg . '] confirmations=' . count($this->confirmations));

        if ($scMsg) {
            $this->confirmations[] = $scMsg;
        }
        if ($scErr) {
            $this->errors[] = $scErr;
        }

        $rules      = SmartCategoryRule::getAllRules();
        list($rules, $ruleStats) = $this->scDecorateRulesForList($rules);
        $recentLogs = SmartCategoryRule::getAllRecentLogs(20);

        if (!Configuration::get('SC_SECURE_KEY')) {
            Configuration::updateValue('SC_SECURE_KEY', md5(_COOKIE_KEY_ . 'smartcategories'));
        }

        $cronUrl = $this->context->link->getModuleLink(
            'smartcategories',
            'cron',
            ['secure_key' => Configuration::get('SC_SECURE_KEY')]
        );

        $this->context->smarty->assign([
            'rules'               => $rules,
            'recent_logs'         => $recentLogs,
            'cron_url'            => $cronUrl,
            'cron_command_5min'   => '*/5 * * * * wget -q -O /dev/null "' . $cronUrl . '"',
            'cron_command_1min'   => '* * * * * wget -q -O /dev/null "' . $cronUrl . '"',
            'base_url'            => $this->context->link->getAdminLink('AdminSmartCategories'),
            'debug_enabled'       => (bool) Configuration::get('SC_DEBUG'),
            'rule_stats'          => $ruleStats,
            'confirmations'       => $scMsg ? [$scMsg] : $this->confirmations,
            'errors'              => $scErr ? [$scErr] : $this->errors,
        ]);

        $this->content = $this->context->smarty->fetch(
            dirname(__FILE__) . '/../../views/templates/admin/list.tpl'
        );

        $this->scLog('renderListView content length=' . strlen($this->content));
        parent::initContent();
    }

    public function renderFormView()
    {
        $idRule     = (int) Tools::getValue('id_rule', 0);
        $rule       = null;
        $conditions = [];

        // Garantizar columnas antes de cargar el formulario
        $this->scEnsureColumns();

        if ($idRule) {
            // Carga directa por SQL para evitar problemas de caché del ORM con columnas nuevas
            $row = Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'smartcategory_rules` WHERE id_rule = ' . $idRule
            );
            if ($row) {
                $rule = new SmartCategoryRule($idRule);
                // Forzar hidratación de campos que el ORM podría no cargar tras migraciones
                $rule->flag_text  = isset($row['flag_text'])  ? $row['flag_text']  : '';
                $rule->flag_bg           = isset($row['flag_bg'])           ? $row['flag_bg']           : '#e84444';
                $rule->flag_color        = isset($row['flag_color'])        ? $row['flag_color']        : '#ffffff';
                $rule->listing_sel_type  = isset($row['listing_sel_type'])  ? $row['listing_sel_type']  : 'class';
                $rule->listing_sel_value = isset($row['listing_sel_value']) ? $row['listing_sel_value'] : 'product-price-and-shipping';
                $rule->listing_position  = isset($row['listing_position'])  ? $row['listing_position']  : 'prepend';
                $rule->product_sel_type  = isset($row['product_sel_type'])  ? $row['product_sel_type']  : 'class';
                $rule->product_sel_value = isset($row['product_sel_value']) ? $row['product_sel_value'] : 'product-prices';
                $rule->product_position  = isset($row['product_position'])  ? $row['product_position']  : 'prepend';
                $rule->start_date = !empty($row['start_date']) ? $row['start_date'] : null;
                $rule->end_date   = !empty($row['end_date']) ? $row['end_date'] : null;
                $rule->noindex    = isset($row['noindex']) ? (int) $row['noindex'] : 0;
            }
            $conditions = SmartCategoryCondition::getConditionsByRule($idRule);
        }
        $categoryList = $this->scGetTargetCategoryOptions();

        $adminLink = $this->context->link->getAdminLink('AdminSmartCategories');

        $this->context->smarty->assign([
            'rule'                 => $rule,
            'conditions'           => $conditions,
            'category_list'        => $categoryList,
            'base_url'             => $adminLink,
            'save_url'             => $adminLink . '&action=save',
            'create_cat_url'       => $adminLink . '&action=create_category',
            'confirmations'        => $this->confirmations,
            'errors'               => $this->errors,
            'filter_categories'    => SmartCategoryRule::getCategoriesForCondition(),
            'filter_features'      => SmartCategoryRule::getFeaturesForCondition(),
            'filter_manufacturers' => SmartCategoryRule::getManufacturersForCondition(),
            'filter_attributes'    => SmartCategoryRule::getAttributesForCondition(),
            'start_date_input'     => ($rule && !empty($rule->start_date)) ? SmartCategoryRule::formatDatetimeForInput($rule->start_date) : '',
            'end_date_input'       => ($rule && !empty($rule->end_date)) ? SmartCategoryRule::formatDatetimeForInput($rule->end_date) : '',
        ]);

        $this->content = $this->context->smarty->fetch(
            dirname(__FILE__) . '/../../views/templates/admin/form.tpl'
        );

        parent::initContent();
    }

    public function renderLogsView()
    {
        $idRule = (int) Tools::getValue('id_rule', 0);

        if ($idRule) {
            $rule = new SmartCategoryRule($idRule);
            $logs = $rule->getLogs(50);
        } else {
            $rule = null;
            $logs = SmartCategoryRule::getAllRecentLogs(100);
        }

        $this->context->smarty->assign([
            'logs'     => $logs,
            'rule'     => $rule,
            'base_url' => $this->context->link->getAdminLink('AdminSmartCategories'),
        ]);

        $this->content = $this->context->smarty->fetch(
            dirname(__FILE__) . '/../../views/templates/admin/logs.tpl'
        );

        parent::initContent();
    }

    // ── Action methods ─────────────────────────────────────────────

    public function scSave()
    {
        $idRule     = (int) Tools::getValue('id_rule', 0);
        $name       = trim(Tools::getValue('name', ''));
        $idCategory = (int) Tools::getValue('id_category', 0);
        $active     = Tools::getIsset('active') ? (int) Tools::getValue('active') : null;
        $noindex    = (int) Tools::getValue('noindex', 0);
        $startDateRaw = trim(Tools::getValue('start_date', ''));
        $endDateRaw   = trim(Tools::getValue('end_date', ''));
        $startDate    = $this->scNormalizeDatetimeInput($startDateRaw);
        $endDate      = $this->scNormalizeDatetimeInput($endDateRaw);
        $flagText  = trim(Tools::getValue('flag_text', ''));
        $flagBg    = trim(Tools::getValue('flag_bg', '#e84444'));
        $flagColor = trim(Tools::getValue('flag_color', '#ffffff'));
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $flagBg))   $flagBg    = '#e84444';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $flagColor)) $flagColor = '#ffffff';
        $validTypes      = ['id','class','attr','other'];
        $validPositions  = ['before','prepend','append','after','replace'];
        $listingSelType  = Tools::getValue('listing_sel_type', 'class');
        $listingSelValue = trim(Tools::getValue('listing_sel_value', 'product-price-and-shipping'));
        $listingPosition = Tools::getValue('listing_position', 'prepend');
        $productSelType  = Tools::getValue('product_sel_type', 'class');
        $productSelValue = trim(Tools::getValue('product_sel_value', 'product-prices'));
        $productPosition = Tools::getValue('product_position', 'prepend');
        if (!in_array($listingSelType,  $validTypes))     $listingSelType  = 'class';
        if (!in_array($listingPosition, $validPositions)) $listingPosition = 'prepend';
        if (!in_array($productSelType,  $validTypes))     $productSelType  = 'class';
        if (!in_array($productPosition, $validPositions)) $productPosition = 'prepend';
        $now        = date('Y-m-d H:i:s');

        // Garantizar que las columnas existen antes de intentar guardar
        $this->scEnsureColumns();

        $this->scLog('scSave flag_text=[' . $flagText . '] POST=' . json_encode($_POST));

        $conditionTypes   = Tools::getValue('condition_type', []);
        $conditionValues  = Tools::getValue('condition_value', []);
        $conditionValues2 = Tools::getValue('condition_value2', []);

        if (!is_array($conditionTypes)) {
            $conditionTypes = [];
        }

        $filteredTypes = $filteredValues = $filteredValues2 = [];
        foreach ($conditionTypes as $i => $type) {
            if (!empty($type)) {
                $filteredTypes[]   = $type;
                $filteredValues[]  = isset($conditionValues[$i])  ? $conditionValues[$i]  : '';
                $filteredValues2[] = isset($conditionValues2[$i]) ? $conditionValues2[$i] : '';
            }
        }

        $this->scLog('scSave name=[' . $name . '] idCat=[' . $idCategory . '] types=' . json_encode($filteredTypes));

        $errors = [];
        if (empty($name))          $errors[] = $this->l('El nombre de la regla es obligatorio.');
        if (!$idCategory)          $errors[] = $this->l('Debes seleccionar una categoria.');
        if (empty($filteredTypes)) $errors[] = $this->l('Debes anadir al menos una condicion.');
        if ($startDateRaw !== '' && !$startDate) $errors[] = $this->l('La fecha de inicio no es válida.');
        if ($endDateRaw !== '' && !$endDate) $errors[] = $this->l('La fecha de fin no es válida.');
        if ($startDate && $endDate && strtotime($startDate) >= strtotime($endDate)) $errors[] = $this->l('La fecha de fin debe ser posterior a la fecha de inicio.');

        if (!empty($errors)) {
            foreach ($errors as $err) {
                $this->errors[] = $err;
            }
            $this->renderFormView();
            return;
        }

        $db = Db::getInstance();

        try {
            if ($idRule) {
                $db->update('smartcategory_rules', [
                    'name'        => pSQL($name),
                    'id_category' => (int) $idCategory,
                    'active'      => ($active === null ? (int) Db::getInstance()->getValue('SELECT active FROM `' . _DB_PREFIX_ . 'smartcategory_rules` WHERE id_rule = ' . (int) $idRule) : (int) $active),
                    'start_date'  => $startDate ? pSQL($startDate) : null,
                    'end_date'    => $endDate ? pSQL($endDate) : null,
                    'noindex'     => (int) $noindex,
                    'flag_text'   => pSQL($flagText),
                    'flag_bg'     => pSQL($flagBg),
                    'flag_color'        => pSQL($flagColor),
                    'listing_sel_type'  => pSQL($listingSelType),
                    'listing_sel_value' => pSQL($listingSelValue),
                    'listing_position'  => pSQL($listingPosition),
                    'product_sel_type'  => pSQL($productSelType),
                    'product_sel_value' => pSQL($productSelValue),
                    'product_position'  => pSQL($productPosition),
                    'date_upd'    => pSQL($now),
                ], 'id_rule = ' . (int) $idRule);
                $savedId = $idRule;
            } else {
                $db->insert('smartcategory_rules', [
                    'name'        => pSQL($name),
                    'id_category' => (int) $idCategory,
                    'active'      => ($active === null ? 1 : (int) $active),
                    'start_date'  => $startDate ? pSQL($startDate) : null,
                    'end_date'    => $endDate ? pSQL($endDate) : null,
                    'noindex'     => (int) $noindex,
                    'flag_text'   => pSQL($flagText),
                    'flag_bg'     => pSQL($flagBg),
                    'flag_color'        => pSQL($flagColor),
                    'listing_sel_type'  => pSQL($listingSelType),
                    'listing_sel_value' => pSQL($listingSelValue),
                    'listing_position'  => pSQL($listingPosition),
                    'product_sel_type'  => pSQL($productSelType),
                    'product_sel_value' => pSQL($productSelValue),
                    'product_position'  => pSQL($productPosition),
                    'date_add'    => pSQL($now),
                    'date_upd'    => pSQL($now),
                ]);
                $savedId = (int) $db->Insert_ID();
            }

            if (!$savedId) {
                throw new Exception('DB error: ' . $db->getMsgError());
            }

            SmartCategoryCondition::deleteByRule($savedId);

            foreach ($filteredTypes as $i => $type) {
                $db->insert('smartcategory_conditions', [
                    'id_rule'        => (int) $savedId,
                    'condition_type' => pSQL($type),
                    'operator'       => 'AND',
                    'value'          => pSQL($filteredValues[$i]),
                    'value2'         => pSQL($filteredValues2[$i]),
                    'sort_order'     => (int) $i,
                ]);
            }

            $this->scLog('scSave SUCCESS savedId=' . $savedId);

            try {
                $savedRule = new SmartCategoryRule((int) $savedId);
                $savedRule->execute();
            } catch (Exception $e) {
                $this->scLog('scSave execute-after-save ERROR: ' . $e->getMessage());
            }

            $adminLink = $this->context->link->getAdminLink('AdminSmartCategories')
                       . '&sc_msg=' . urlencode($this->l('Regla guardada correctamente.'));
            Tools::redirectAdmin($adminLink);

        } catch (Exception $e) {
            $this->scLog('scSave ERROR: ' . $e->getMessage());
            $this->errors[] = $this->l('Error al guardar: ') . $e->getMessage();
            $this->renderFormView();
        }
    }


    public function scDuplicate()
    {
        $idRule = (int) Tools::getValue('id_rule', 0);

        if (!$idRule) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSmartCategories') . '&sc_err=' . urlencode($this->l('Regla no válida.')));
        }

        $db = Db::getInstance();

        $source = $db->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'smartcategory_rules` WHERE id_rule = ' . (int) $idRule
        );

        if (!$source) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSmartCategories') . '&sc_err=' . urlencode($this->l('No se encontró la regla original.')));
        }

        unset($source['id_rule']);

        $source['name'] = $source['name'] . ' (Copia)';
        $source['active'] = 0;
        $source['date_add'] = date('Y-m-d H:i:s');
        $source['date_upd'] = date('Y-m-d H:i:s');

        if (isset($source['last_execution'])) {
            $source['last_execution'] = null;
        }

        foreach ($source as $key => $value) {
            if ($value !== null) {
                $source[$key] = pSQL($value);
            }
        }

        if (!$db->insert('smartcategory_rules', $source)) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSmartCategories') . '&sc_err=' . urlencode($this->l('No se pudo duplicar la regla.')));
        }

        $newIdRule = (int) $db->Insert_ID();

        $conditions = SmartCategoryCondition::getConditionsByRule($idRule);

        foreach ($conditions as $condition) {
            $db->insert('smartcategory_conditions', [
                'id_rule'        => (int) $newIdRule,
                'condition_type' => pSQL($condition['condition_type']),
                'operator'       => pSQL($condition['operator']),
                'value'          => pSQL($condition['value']),
                'value2'         => pSQL($condition['value2']),
                'sort_order'     => (int) $condition['sort_order'],
            ]);
        }

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminSmartCategories')
            . '&action=edit&id_rule=' . (int) $newIdRule
            . '&sc_msg=' . urlencode($this->l('Regla duplicada correctamente. La copia está inactiva.'))
        );
    }


    public function scDelete()
    {
        $idRule = (int) Tools::getValue('id_rule', 0);

        if ($idRule) {
            $db         = Db::getInstance();
            $idCategory = (int) $db->getValue(
                'SELECT id_category FROM `' . _DB_PREFIX_ . 'smartcategory_rules` WHERE id_rule = ' . $idRule
            );
            if ($idCategory) {
                $db->delete('category_product', 'id_category = ' . $idCategory);
            }
            SmartCategoryCondition::deleteByRule($idRule);
            $db->delete('smartcategory_badges', 'id_rule = ' . $idRule);
            $db->delete('smartcategory_rules', 'id_rule = ' . $idRule);
            $db->delete('smartcategory_logs',  'id_rule = ' . $idRule);
        }

        $adminLink = $this->context->link->getAdminLink('AdminSmartCategories')
                   . '&sc_msg=' . urlencode($this->l('Regla eliminada correctamente.'));
        Tools::redirectAdmin($adminLink);
    }

    public function scToggle()
    {
        $idRule = (int) Tools::getValue('id_rule', 0);
        $active = 0;

        if ($idRule) {
            $current = (int) Db::getInstance()->getValue(
                'SELECT active FROM `' . _DB_PREFIX_ . 'smartcategory_rules` WHERE id_rule = ' . $idRule
            );
            $active = $current ? 0 : 1;
            Db::getInstance()->update(
                'smartcategory_rules',
                ['active' => $active, 'date_upd' => date('Y-m-d H:i:s')],
                'id_rule = ' . $idRule
            );

            try {
                $rule = new SmartCategoryRule($idRule);
                $rule->execute();
            } catch (Exception $e) {
                $this->scLog('scToggle execute ERROR: ' . $e->getMessage());
            }
        }

        if (Tools::isSubmit('ajax')) {
            die(json_encode(['success' => true, 'active' => $active]));
        }

        $this->renderListView();
    }

    public function scRunRule()
    {
        $idRule = (int) Tools::getValue('id_rule', 0);
        $result = ['status' => 'error', 'message' => 'ID no valido', 'added' => 0, 'removed' => 0];

        if ($idRule) {
            $rule   = new SmartCategoryRule($idRule);
            $result = $rule->execute();
        }

        if (Tools::isSubmit('ajax')) {
            die(json_encode($result));
        }

        if ($result['status'] === 'success') {
            $this->confirmations[] = sprintf(
                $this->l('Ejecutada: +%d productos, -%d eliminados.'),
                $result['added'], $result['removed']
            );
        } else {
            $this->errors[] = $this->l('Error: ') . $result['message'];
        }

        $this->renderListView();
    }

    public function scRunAll()
    {
        $results = $this->module->runAllRules();
        $totalAdded = $totalRemoved = 0;

        foreach ($results as $r) {
            $totalAdded   += $r['added'];
            $totalRemoved += $r['removed'];
        }

        if (Tools::isSubmit('ajax')) {
            die(json_encode([
                'results'       => $results,
                'total_added'   => $totalAdded,
                'total_removed' => $totalRemoved,
            ]));
        }

        $this->confirmations[] = sprintf(
            $this->l('Todas ejecutadas: +%d, -%d.'),
            $totalAdded, $totalRemoved
        );

        $this->renderListView();
    }

    public function scToggleDebug()
    {
        $current = (int) Configuration::get('SC_DEBUG');
        Configuration::updateValue('SC_DEBUG', $current ? 0 : 1);
        $this->scLog($current ? 'Debug DISABLED' : 'Debug ENABLED');
        $msgText = $current
            ? $this->l('Debug desactivado.')
            : $this->l('Debug activado. Log: modules/smartcategories/sc_debug.log');
        $adminLink = $this->context->link->getAdminLink('AdminSmartCategories')
                   . '&sc_msg=' . urlencode($msgText);
        Tools::redirectAdmin($adminLink);
    }

    /**
     * Garantiza que todas las columnas necesarias existen en BD.
     * Se llama antes de cada guardado para instalaciones que vienen de versiones anteriores.
     */

    private function scGetTargetCategoryOptions()
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $rows = Db::getInstance()->executeS(
            'SELECT c.id_category, c.id_parent, c.active, c.nleft, c.level_depth, cl.name
             FROM `' . _DB_PREFIX_ . 'category` c
             INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (cl.id_category = c.id_category AND cl.id_lang = ' . $idLang . ' AND cl.id_shop = ' . $idShop . ')
             INNER JOIN `' . _DB_PREFIX_ . 'category_shop` cs
                ON (cs.id_category = c.id_category AND cs.id_shop = ' . $idShop . ')
             WHERE c.id_category > 1
             ORDER BY c.nleft ASC'
        ) ?: [];

        $names = [];
        foreach ($rows as $row) {
            $names[(int) $row['id_category']] = $row['name'];
        }

        $result = [];
        foreach ($rows as $row) {
            $idCategory = (int) $row['id_category'];
            $path = [];

            $current = $row;
            $guard = 0;

            while ($current && $guard < 20) {
                array_unshift($path, $current['name']);
                $parentId = (int) $current['id_parent'];

                if ($parentId <= 1 || !isset($names[$parentId])) {
                    break;
                }

                $current = null;
                foreach ($rows as $candidate) {
                    if ((int) $candidate['id_category'] === $parentId) {
                        $current = $candidate;
                        break;
                    }
                }

                $guard++;
            }

            $status = ((int) $row['active'] === 1) ? 'Activa' : 'Inactiva';
            $label = '[' . $idCategory . '] ' . implode(' > ', $path) . ' · ' . $status;

            $result[$idCategory] = $label;
        }

        return $result;
    }


    private function scEnsureColumns()
    {
        $db     = Db::getInstance();
        $prefix = _DB_PREFIX_;

        // Comprobar si active existe
        $cols = $db->executeS("SHOW COLUMNS FROM \`" . $prefix . "smartcategory_rules\` LIKE 'active'");
        if (empty($cols)) {
            $db->execute("ALTER TABLE \`" . $prefix . "smartcategory_rules\` ADD COLUMN \`active\` TINYINT(1) NOT NULL DEFAULT 1 AFTER id_category");
            $this->scLog("scEnsureColumns: active column created");
        }

        // Comprobar si flag_text existe
        $cols = $db->executeS('SHOW COLUMNS FROM `' . $prefix . 'smartcategory_rules` LIKE \'flag_text\'');
        if (empty($cols)) {
            $db->execute('ALTER TABLE `' . $prefix . 'smartcategory_rules` ADD COLUMN `flag_text` VARCHAR(100) DEFAULT \'\'');
            $this->scLog('scEnsureColumns: flag_text column created');
        }

        // Comprobar si noindex existe
        $cols = $db->executeS('SHOW COLUMNS FROM `' . $prefix . 'smartcategory_rules` LIKE \'noindex\'');
        if (empty($cols)) {
            $db->execute('ALTER TABLE `' . $prefix . 'smartcategory_rules` ADD COLUMN `noindex` TINYINT(1) NOT NULL DEFAULT 0');
            $this->scLog('scEnsureColumns: noindex column created');
        }

        $cols = $db->executeS('SHOW COLUMNS FROM `' . $prefix . 'smartcategory_rules` LIKE \'start_date\'');
        if (empty($cols)) {
            $db->execute('ALTER TABLE `' . $prefix . 'smartcategory_rules` ADD COLUMN `start_date` DATETIME DEFAULT NULL');
            $this->scLog('scEnsureColumns: start_date column created');
        }

        $cols = $db->executeS('SHOW COLUMNS FROM `' . $prefix . 'smartcategory_rules` LIKE \'end_date\'');
        if (empty($cols)) {
            $db->execute('ALTER TABLE `' . $prefix . 'smartcategory_rules` ADD COLUMN `end_date` DATETIME DEFAULT NULL');
            $this->scLog('scEnsureColumns: end_date column created');
        }

        // Comprobar si tabla badges existe
        $tables = $db->executeS('SHOW TABLES LIKE \'' . $prefix . 'smartcategory_badges\'');
        if (empty($tables)) {
            $db->execute(
                'CREATE TABLE `' . $prefix . 'smartcategory_badges` (
                    `id_product` INT(10) UNSIGNED NOT NULL,
                    `id_rule` INT(10) UNSIGNED NOT NULL,
                    `badge_text` VARCHAR(100) NOT NULL,
                    PRIMARY KEY (`id_product`, `id_rule`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );
            $this->scLog('scEnsureColumns: badges table created');
        }
    }


    private function scNormalizeDatetimeInput($value)
    {
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime(str_replace('T', ' ', $value));
        if ($timestamp === false) {
            return false;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    public function scPurgeBadges()
    {
        // Eliminar badges de reglas que ya no existen
        Db::getInstance()->execute(
            'DELETE b FROM `' . _DB_PREFIX_ . 'smartcategory_badges` b
             LEFT JOIN `' . _DB_PREFIX_ . 'smartcategory_rules` r ON r.id_rule = b.id_rule
             WHERE r.id_rule IS NULL'
        );

        $adminLink = $this->context->link->getAdminLink('AdminSmartCategories')
                   . '&sc_msg=' . urlencode($this->l('Badges huerfanos eliminados correctamente.'));
        Tools::redirectAdmin($adminLink);
    }

    public function scDiagBadges()
    {
        $db     = Db::getInstance();
        $prefix = _DB_PREFIX_;

        // Badges guardados
        $badges = $db->executeS('SELECT * FROM `' . $prefix . 'smartcategory_badges` ORDER BY id_rule, id_product') ?: [];

        // Hooks registrados
        $hooks = $db->executeS(
            'SELECT h.name, hm.id_module
             FROM `' . $prefix . 'hook_module` hm
             INNER JOIN `' . $prefix . 'hook` h ON h.id_hook = hm.id_hook
             INNER JOIN `' . $prefix . 'module` m ON m.id_module = hm.id_module
             WHERE m.name = \'smartcategories\'
             ORDER BY h.name'
        ) ?: [];

        $html = '<div class="sc-module"><div class="sc-header"><div class="sc-header-inner"><div class="sc-header-title">';
        $html .= '<a href="' . $this->context->link->getAdminLink('AdminSmartCategories') . '" class="sc-back">←</a>';
        $html .= '<div><h1>Diagnóstico de badges</h1></div></div></div></div>';

        $html .= '<div class="sc-card" style="margin:20px">';
        $html .= '<div class="sc-card-header"><h2>Hooks registrados (' . count($hooks) . ')</h2></div><div style="padding:16px">';
        if ($hooks) {
            foreach ($hooks as $h) {
                $html .= '<code style="display:block;margin:2px 0">' . $h['name'] . '</code>';
            }
        } else {
            $html .= '<p style="color:red">⚠ No hay hooks registrados. Ve al listado y entra de nuevo para auto-registrarlos.</p>';
        }
        $html .= '</div></div>';

        $html .= '<div class="sc-card" style="margin:20px">';
        $html .= '<div class="sc-card-header"><h2>Badges en BD (' . count($badges) . ')</h2></div><div style="padding:16px">';
        if ($badges) {
            $html .= '<table style="width:100%;border-collapse:collapse">';
            $html .= '<tr><th style="text-align:left;padding:4px 8px;border-bottom:1px solid #ddd">id_product</th><th style="text-align:left;padding:4px 8px;border-bottom:1px solid #ddd">id_rule</th><th style="text-align:left;padding:4px 8px;border-bottom:1px solid #ddd">badge_text</th></tr>';
            foreach ($badges as $b) {
                $html .= '<tr><td style="padding:4px 8px">' . (int)$b['id_product'] . '</td><td style="padding:4px 8px">' . (int)$b['id_rule'] . '</td><td style="padding:4px 8px">' . htmlspecialchars($b['badge_text']) . '</td></tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p style="color:orange">⚠ La tabla badges está vacía. Ejecuta la regla para que se poblen los badges.</p>';
        }
        $html .= '</div></div></div>';

        $this->content = $html;
        parent::initContent();
    }

    public function scRunMigrations()
    {
        $db = Db::getInstance();
        $migrations = [
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_conditions`
             MODIFY `value` TEXT NOT NULL,
             MODIFY `value2` TEXT DEFAULT NULL',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `noindex` TINYINT(1) NOT NULL DEFAULT 0',
            'ALTER TABLE `' . _DB_PREFIX_ . 'smartcategory_rules`
             ADD COLUMN IF NOT EXISTS `flag_text` VARCHAR(100) DEFAULT \'\'',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'smartcategory_badges` (
                `id_product` INT(10) UNSIGNED NOT NULL,
                `id_rule` INT(10) UNSIGNED NOT NULL,
                `badge_text` VARCHAR(100) NOT NULL,
                PRIMARY KEY (`id_product`, `id_rule`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        ];

        $errors = 0;
        foreach ($migrations as $mig) {
            if (!$db->execute($mig)) {
                $errors++;
            }
        }

        $msg = $errors === 0
            ? $this->l('Migraciones aplicadas correctamente.')
            : $this->l('Algunas migraciones fallaron (puede que ya estuvieran aplicadas).');

        $adminLink = $this->context->link->getAdminLink('AdminSmartCategories')
                   . '&sc_msg=' . urlencode($msg);
        Tools::redirectAdmin($adminLink);
    }



    private function scIsValidStoredDatetime($value)
    {
        if ($value === null) {
            return false;
        }

        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00 00:00:00' || $value === '0000-00-00') {
            return false;
        }

        $ts = strtotime($value);
        if ($ts === false || $ts <= 0) {
            return false;
        }

        return true;
    }

    private function scGetTemporalScheduleStatus(array $rule, $nowTs)
    {
        $startTs = $this->scIsValidStoredDatetime(isset($rule['start_date']) ? $rule['start_date'] : null)
            ? strtotime($rule['start_date'])
            : null;
        $endTs = $this->scIsValidStoredDatetime(isset($rule['end_date']) ? $rule['end_date'] : null)
            ? strtotime($rule['end_date'])
            : null;

        if ($endTs && $nowTs > $endTs) {
            return 'finished';
        }

        if ($startTs && $nowTs < $startTs) {
            return 'scheduled';
        }

        return 'running';
    }

    private function scDecorateRulesForList(array $rules)
    {
        $stats = [
            'active' => 0,
            'scheduled' => 0,
            'finished' => 0,
            'inactive' => 0,
        ];

        foreach ($rules as &$rule) {
            $nowTs = time();
            $activeFlag = isset($rule['active']) ? (int) $rule['active'] : 0;
            $scheduleStatus = $activeFlag ? $this->scGetTemporalScheduleStatus($rule, $nowTs) : 'inactive';

            if ($scheduleStatus === 'running') {
                $scheduleStatus = 'active';
            }

            if (!isset($stats[$scheduleStatus])) {
                $scheduleStatus = 'inactive';
            }

            $stats[$scheduleStatus]++;

            $startTs = $this->scIsValidStoredDatetime(isset($rule['start_date']) ? $rule['start_date'] : null)
                ? strtotime($rule['start_date'])
                : null;
            $endTs   = $this->scIsValidStoredDatetime(isset($rule['end_date']) ? $rule['end_date'] : null)
                ? strtotime($rule['end_date'])
                : null;

            $rule['schedule_status'] = $scheduleStatus;
            $rule['campaign_window'] = $this->scBuildCampaignWindow($startTs, $endTs);
            $rule['progress'] = $this->scCalculateCampaignProgress($scheduleStatus, $startTs, $endTs, $nowTs);

            switch ($scheduleStatus) {
                case 'scheduled':
                    $rule['status_label'] = $this->l('Programada');
                    $rule['status_hint'] = $startTs ? $this->l('Empieza en ') . $this->scHumanTimeDiff($nowTs, $startTs) : '';
                    break;
                case 'finished':
                    $rule['status_label'] = $this->l('Finalizada');
                    $rule['status_hint'] = $endTs ? $this->l('Terminó hace ') . $this->scHumanTimeDiff($endTs, $nowTs) : '';
                    break;
                case 'inactive':
                    $rule['status_label'] = $this->l('Inactiva');
                    $rule['status_hint'] = $this->l('No se ejecuta hasta activarla');
                    break;
                case 'active':
                default:
                    $rule['schedule_status'] = 'active';
                    $rule['status_label'] = $this->l('Activa');
                    $rule['status_hint'] = $endTs ? $this->l('Termina en ') . $this->scHumanTimeDiff($nowTs, $endTs) : $this->l('Sin fecha de fin');
                    break;
            }
        }
        unset($rule);

        return [$rules, $stats];
    }

    private function scBuildCampaignWindow($startTs, $endTs)
    {
        if (!$startTs && !$endTs) {
            return '';
        }

        if ($startTs && $endTs) {
            return date('d/m/Y H:i', $startTs) . ' → ' . date('d/m/Y H:i', $endTs);
        }

        if ($startTs) {
            return $this->l('Desde ') . date('d/m/Y H:i', $startTs);
        }

        return $this->l('Hasta ') . date('d/m/Y H:i', $endTs);
    }

    private function scCalculateCampaignProgress($scheduleStatus, $startTs, $endTs, $nowTs)
    {
        if (!$startTs || !$endTs || $endTs <= $startTs) {
            return $scheduleStatus === 'finished' ? 100 : 0;
        }

        if ($scheduleStatus === 'scheduled') {
            return 0;
        }

        if ($scheduleStatus === 'finished') {
            return 100;
        }

        if ($scheduleStatus === 'inactive') {
            if ($nowTs <= $startTs) {
                return 0;
            }
            if ($nowTs >= $endTs) {
                return 100;
            }
        }

        $progress = (($nowTs - $startTs) / ($endTs - $startTs)) * 100;
        return max(0, min(100, (int) round($progress)));
    }

    private function scHumanTimeDiff($fromTs, $toTs)
    {
        $diff = max(0, (int) abs($toTs - $fromTs));

        if ($diff < 3600) {
            $minutes = max(1, (int) floor($diff / 60));
            return $minutes . ' ' . ($minutes === 1 ? $this->l('minuto') : $this->l('minutos'));
        }

        if ($diff < 86400) {
            $hours = max(1, (int) floor($diff / 3600));
            return $hours . ' ' . ($hours === 1 ? $this->l('hora') : $this->l('horas'));
        }

        $days = (int) floor($diff / 86400);
        return $days . ' ' . ($days === 1 ? $this->l('día') : $this->l('días'));
    }

    public function scCreateCategory()
    {
        $name     = trim(Tools::getValue('cat_name', ''));
        $parentId = (int) Tools::getValue('cat_parent', 2);

        if (empty($name)) {
            die(json_encode(['success' => false, 'error' => 'El nombre es obligatorio.']));
        }

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $parent = new Category($parentId);
        if (!Validate::isLoadedObject($parent)) {
            $parentId = 2;
        }

        $category = new Category();
        $category->name            = [$idLang => $name];
        $category->link_rewrite    = [$idLang => Tools::link_rewrite($name)];
        $category->id_parent       = $parentId;
        $category->active          = 1;
        $category->id_shop_default = $idShop;

        if ($category->add()) {
            $category->addShop($idShop);
            die(json_encode(['success' => true, 'id_category' => (int) $category->id, 'name' => $name]));
        }

        die(json_encode(['success' => false, 'error' => 'No se pudo crear la categoria.']));
    }
}
