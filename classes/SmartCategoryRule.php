<?php
/**
 * SmartCategoryRule - Clase que representa una regla de categorización
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class SmartCategoryRule extends ObjectModel
{
    public $id_rule;
    public $name;
    public $id_category;
    public $active;
    public $start_date;
    public $end_date;
    public $noindex = 0;
    public $flag_text = '';
    public $flag_bg = '#e84444';
    public $flag_color = '#ffffff';
    public $listing_sel_type = 'class';
    public $listing_sel_value = 'product-price-and-shipping';
    public $listing_position = 'prepend';
    public $product_sel_type = 'class';
    public $product_sel_value = 'product-prices';
    public $product_position = 'prepend';
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table'   => 'smartcategory_rules',
        'primary' => 'id_rule',
        'fields'  => [
            'name'        => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'id_category' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'active'      => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'start_date'  => ['type' => self::TYPE_DATE],
            'end_date'    => ['type' => self::TYPE_DATE],
            'noindex'     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'flag_text'   => ['type' => self::TYPE_STRING, 'size' => 100],
            'flag_bg'     => ['type' => self::TYPE_STRING, 'size' => 7],
            'flag_color'        => ['type' => self::TYPE_STRING, 'size' => 7],
            'listing_sel_type'  => ['type' => self::TYPE_STRING, 'size' => 10],
            'listing_sel_value' => ['type' => self::TYPE_STRING, 'size' => 255],
            'listing_position'  => ['type' => self::TYPE_STRING, 'size' => 10],
            'product_sel_type'  => ['type' => self::TYPE_STRING, 'size' => 10],
            'product_sel_value' => ['type' => self::TYPE_STRING, 'size' => 255],
            'product_position'  => ['type' => self::TYPE_STRING, 'size' => 10],
            'date_add'    => ['type' => self::TYPE_DATE],
            'date_upd'    => ['type' => self::TYPE_DATE],
        ],
    ];

    /**
     * Obtener todas las reglas activas
     */
    public static function getActiveRules()
    {
        $rules = [];
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'smartcategory_rules` WHERE active = 1 ORDER BY id_rule ASC'
        );

        if ($rows) {
            foreach ($rows as $row) {
                $rule = new SmartCategoryRule((int) $row['id_rule']);
                // Forzar hidratación de columnas añadidas por migración
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
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Obtener todas las reglas (activas e inactivas)
     */
    public static function getAllRules()
    {
        $rules = [];
        $idShop = (int) Context::getContext()->shop->id;
        $idLang = (int) Context::getContext()->language->id;

        $rows = Db::getInstance()->executeS(
            'SELECT r.id_rule, r.name, r.id_category, r.active, r.start_date, r.end_date, r.date_add, r.date_upd,
                    c.name as category_name,
                    (SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'smartcategory_conditions` WHERE id_rule = r.id_rule) as conditions_count,
                    CASE
                        WHEN r.active = 0 THEN "inactive"
                        WHEN r.end_date IS NOT NULL AND r.end_date <> "0000-00-00 00:00:00" AND NOW() > r.end_date THEN "finished"
                        WHEN r.start_date IS NOT NULL AND r.start_date <> "0000-00-00 00:00:00" AND NOW() < r.start_date THEN "scheduled"
                        ELSE "running"
                    END as schedule_status
             FROM `' . _DB_PREFIX_ . 'smartcategory_rules` r
             LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` c
                ON (c.id_category = r.id_category AND c.id_lang = ' . $idLang . ' AND c.id_shop = ' . $idShop . ')
             ORDER BY r.id_rule ASC'
        );

        return $rows ?: [];
    }

    /**
     * Obtener las condiciones de esta regla
     */
    public function getConditions()
    {
        return SmartCategoryCondition::getConditionsByRule($this->id_rule);
    }

    public function hasValidStartDate()
    {
        return !empty($this->start_date)
            && $this->start_date !== '0000-00-00 00:00:00'
            && strtotime($this->start_date) !== false
            && strtotime($this->start_date) > 0;
    }

    public function hasValidEndDate()
    {
        return !empty($this->end_date)
            && $this->end_date !== '0000-00-00 00:00:00'
            && strtotime($this->end_date) !== false
            && strtotime($this->end_date) > 0;
    }

    public function isScheduledForFuture()
    {
        return $this->hasValidStartDate() && strtotime($this->start_date) > time();
    }

    public function isFinishedBySchedule()
    {
        return $this->hasValidEndDate() && strtotime($this->end_date) < time();
    }

    public function isWithinSchedule()
    {
        if (!(int) $this->active) {
            return false;
        }
        if ($this->isScheduledForFuture()) {
            return false;
        }
        if ($this->isFinishedBySchedule()) {
            return false;
        }
        return true;
    }

    public function clearCategoryBecauseFinished()
    {
        return $this->clearCategoryAndDisable('Campaña finalizada: categoría vaciada y desactivada automáticamente.');
    }

    public function clearCategoryBecauseScheduled()
    {
        return $this->clearCategoryAndDisable('Campaña programada: categoría vaciada y desactivada hasta la fecha de inicio.');
    }

    public function clearCategoryBecauseInactive()
    {
        return $this->clearCategoryAndDisable('Regla inactiva: categoría vaciada y desactivada.');
    }

    private function clearCategoryAndDisable($message)
    {
        $startTime = microtime(true);
        $removed = 0;

        try {
            $managedProducts = $this->getManagedProductsByRule();
            foreach ($managedProducts as $idProduct => $managedCategoryId) {
                if ($this->productExistsInCategory((int) $idProduct, (int) $managedCategoryId)) {
                    $this->removeProductFromCategory((int) $idProduct, (int) $managedCategoryId);
                    $removed++;
                }
            }

            $currentCategoryProducts = array_map('intval', $this->getProductsInCategory());
            foreach ($currentCategoryProducts as $idProduct) {
                if (!isset($managedProducts[(int) $idProduct]) || (int) $managedProducts[(int) $idProduct] !== (int) $this->id_category) {
                    $this->removeProductFromCategory((int) $idProduct, (int) $this->id_category);
                    $removed++;
                }
            }

            Db::getInstance()->delete('smartcategory_rule_products', 'id_rule = ' . (int) $this->id_rule);
            Db::getInstance()->delete('smartcategory_badges', 'id_rule = ' . (int) $this->id_rule);
            $this->setCategoryActive(false);
            $this->applyCategoryIndexation();

            return $this->logResult(0, $removed, microtime(true) - $startTime, 'success', $message);
        } catch (Exception $e) {
            return $this->logResult(0, 0, microtime(true) - $startTime, 'error', $e->getMessage());
        }
    }

    /**
     * Comprueba si otra regla que comparte la misma categoria destino esta vigente ahora mismo
     * (activa y dentro de su rango de fechas, o sin fechas definidas).
     */
    private function hasVigenteSiblingSharingCategory()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT active, start_date, end_date FROM `' . _DB_PREFIX_ . 'smartcategory_rules` '
            . 'WHERE id_category = ' . (int) $this->id_category . ' AND id_rule != ' . (int) $this->id_rule
        );

        if (!$rows) {
            return false;
        }

        foreach ($rows as $row) {
            if (!(int) $row['active']) {
                continue;
            }

            $hasStart = !empty($row['start_date']) && $row['start_date'] !== '0000-00-00 00:00:00' && strtotime($row['start_date']) > 0;
            $hasEnd   = !empty($row['end_date'])   && $row['end_date']   !== '0000-00-00 00:00:00' && strtotime($row['end_date'])   > 0;

            if ($hasStart && strtotime($row['start_date']) > time()) {
                continue;
            }
            if ($hasEnd && strtotime($row['end_date']) < time()) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Vacia y desactiva la categoria solo si ninguna otra regla vigente la comparte.
     * Si otra regla vigente la comparte, no se toca nada (esa regla ya la gestiona).
     */
    private function clearCategoryUnlessSiblingActive($message)
    {
        if ($this->hasVigenteSiblingSharingCategory()) {
            return $this->logResult(0, 0, 0, 'success', $message . ' (Categoria compartida con otra regla vigente: no se toca.)');
        }
        return $this->clearCategoryAndDisable($message);
    }

    /**
     * Ejecutar la regla: encontrar productos coincidentes y sincronizar la categoría
     */
    public function execute()
    {
        $startTime = microtime(true);
        $added = 0;
        $removed = 0;

        try {
            if (!(int) $this->active) {
                return $this->clearCategoryUnlessSiblingActive('Regla inactiva: categoria vaciada y desactivada.');
            }

            if ($this->isScheduledForFuture()) {
                return $this->logResult(0, 0, microtime(true) - $startTime, 'success', 'Campana programada: todavia no ha empezado, no se realiza ninguna accion.');
            }

            if ($this->isFinishedBySchedule()) {
                return $this->clearCategoryUnlessSiblingActive('Campana finalizada: categoria vaciada y desactivada automaticamente.');
            }

            $this->setCategoryActive(true);

            $conditions = $this->getConditions();

            if (empty($conditions)) {
                return $this->logResult(0, 0, 0, 'error', 'La regla no tiene condiciones definidas.');
            }

            // Productos que cumplen las condiciones actualmente
            $matchingProductIds = array_map('intval', $this->getMatchingProducts($conditions));
            $matchingProductIds = array_values(array_unique($matchingProductIds));

            // Productos actualmente presentes en la categoría destino
            $currentCategoryProducts = array_map('intval', $this->getProductsInCategory());

            // Historial de productos gestionados por esta regla (para poder limpiar categorías antiguas si cambió la categoría destino)
            $managedProducts = $this->getManagedProductsByRule(); // [id_product => id_category]

            // Si la regla cambió de categoría destino, quitamos de la categoría anterior cualquier producto que esta regla hubiera gestionado allí.
            foreach ($managedProducts as $idProduct => $managedCategoryId) {
                if ((int) $managedCategoryId !== (int) $this->id_category) {
                    $this->removeManagedProduct((int) $idProduct, (int) $managedCategoryId);
                    $removed++;
                }
            }

            // La categoría destino queda 100% controlada por el módulo: todo lo que no cumpla, sale.
            $toRemove = array_values(array_diff($currentCategoryProducts, $matchingProductIds));
            foreach ($toRemove as $idProduct) {
                $this->removeProductFromCategory((int) $idProduct, (int) $this->id_category);
                Db::getInstance()->delete(
                    'smartcategory_rule_products',
                    'id_rule = ' . (int) $this->id_rule . ' AND id_product = ' . (int) $idProduct
                );
                $removed++;
            }

            // Todo lo que cumpla y no esté ya, entra.
            $toAdd = array_values(array_diff($matchingProductIds, $currentCategoryProducts));
            foreach ($toAdd as $idProduct) {
                $this->addProductToCategory((int) $idProduct);
                $added++;
            }

            // Sincronizar tabla interna con el estado final real de la regla.
            Db::getInstance()->delete('smartcategory_rule_products', 'id_rule = ' . (int) $this->id_rule);
            foreach ($matchingProductIds as $idProduct) {
                Db::getInstance()->insert('smartcategory_rule_products', [
                    'id_rule'     => (int) $this->id_rule,
                    'id_product'  => (int) $idProduct,
                    'id_category' => (int) $this->id_category,
                    'date_add'    => date('Y-m-d H:i:s'),
                ]);
            }

            // Gestionar badges para todos los productos que cumplen la regla.
            if (!empty($this->flag_text)) {
                $this->applyBadge($matchingProductIds, $this->flag_text, $this->flag_bg, $this->flag_color);
            }
            $this->removeBadge($toRemove);

            // Aplicar configuración de indexación a la categoría destino
            $this->applyCategoryIndexation();

            $executionTime = microtime(true) - $startTime;
            return $this->logResult($added, $removed, $executionTime, 'success', null);
        } catch (Exception $e) {
            $executionTime = microtime(true) - $startTime;
            return $this->logResult(0, 0, $executionTime, 'error', $e->getMessage());
        }
    }

    /**
     * Construir y ejecutar la query para productos que cumplen las condiciones
     */
    private function getMatchingProducts(array $conditions)
    {
        $joins = [];
        $conditionWheres = [];
        $idShop = (int) Context::getContext()->shop->id;

        $joins['ps_base'] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps_base ON (ps_base.id_product = p.id_product AND ps_base.id_shop = ' . $idShop . ')';
        $baseWheres = ['ps_base.active = 1'];

        foreach ($conditions as $condition) {
            switch ($condition['condition_type']) {
                case 'price_between':
                    $joins['ps'] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (ps.id_product = p.id_product AND ps.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'ps.price >= ' . (float) $condition['value'] . ' AND ps.price <= ' . (float) $condition['value2'];
                    break;

                case 'price_greater':
                    $joins['ps'] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (ps.id_product = p.id_product AND ps.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'ps.price > ' . (float) $condition['value'];
                    break;

                case 'price_less':
                    $joins['ps'] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (ps.id_product = p.id_product AND ps.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'ps.price < ' . (float) $condition['value'];
                    break;

                case 'date_added_before':
                    $days = (int) $condition['value'];
                    $conditionWheres[] = 'p.date_add < DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)';
                    break;

                case 'date_added_after':
                    $days = (int) $condition['value'];
                    $conditionWheres[] = 'p.date_add > DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)';
                    break;

                case 'stock_with':
                    $joins['sa'] = 'INNER JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (sa.id_product = p.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'sa.quantity > 0';
                    break;

                case 'stock_without':
                    $joins['sa'] = 'INNER JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (sa.id_product = p.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'sa.quantity <= 0';
                    break;

                case 'stock_greater':
                    $joins['sa'] = 'INNER JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (sa.id_product = p.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'sa.quantity > ' . (int) $condition['value'];
                    break;

                case 'stock_less':
                    $joins['sa'] = 'INNER JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (sa.id_product = p.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'sa.quantity < ' . (int) $condition['value'];
                    break;

                case 'stock_equal':
                    $joins['sa'] = 'INNER JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (sa.id_product = p.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = ' . $idShop . ')';
                    $conditionWheres[] = 'sa.quantity = ' . (int) $condition['value'];
                    break;

                case 'in_categories':
                    $ids = array_map('intval', array_filter(explode(',', $condition['value'])));
                    if (!empty($ids)) {
                        $inList = implode(',', $ids);
                        $alias  = 'cp_' . count($joins);
                        $joins[$alias] = 'INNER JOIN `' . _DB_PREFIX_ . 'category_product` ' . $alias
                            . ' ON (' . $alias . '.id_product = p.id_product AND ' . $alias . '.id_category IN (' . $inList . '))';
                    }
                    break;

                case 'not_in_categories':
                    $ids = array_map('intval', array_filter(explode(',', $condition['value'])));
                    if (!empty($ids)) {
                        $inList = implode(',', $ids);
                        $conditionWheres[] = 'p.id_product NOT IN ('
                            . 'SELECT id_product FROM `' . _DB_PREFIX_ . 'category_product`'
                            . ' WHERE id_category IN (' . $inList . ')'
                            . ')'  ;
                    }
                    break;

                case 'in_feature_values':
                    $ids = array_map('intval', array_filter(explode(',', $condition['value'])));
                    if (!empty($ids)) {
                        $inList = implode(',', $ids);
                        $alias  = 'pfv_' . count($joins);
                        $joins[$alias] = 'INNER JOIN `' . _DB_PREFIX_ . 'feature_product` ' . $alias
                            . ' ON (' . $alias . '.id_product = p.id_product AND ' . $alias . '.id_feature_value IN (' . $inList . '))';
                    }
                    break;

                case 'in_attributes':
                    $ids = array_map('intval', array_filter(explode(',', $condition['value'])));
                    if (!empty($ids)) {
                        $inList = implode(',', $ids);
                        $conditionWheres[] = 'p.id_product IN ('
                            . 'SELECT pa.id_product FROM `' . _DB_PREFIX_ . 'product_attribute` pa'
                            . ' INNER JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (pac.id_product_attribute = pa.id_product_attribute)'
                            . ' WHERE pac.id_attribute IN (' . $inList . ')'
                            . ')';
                    }
                    break;

                case 'not_in_attributes':
                    $ids = array_map('intval', array_filter(explode(',', $condition['value'])));
                    if (!empty($ids)) {
                        $inList = implode(',', $ids);
                        $conditionWheres[] = 'p.id_product NOT IN ('
                            . 'SELECT pa.id_product FROM `' . _DB_PREFIX_ . 'product_attribute` pa'
                            . ' INNER JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (pac.id_product_attribute = pa.id_product_attribute)'
                            . ' WHERE pac.id_attribute IN (' . $inList . ')'
                            . ')';
                    }
                    break;

                case 'in_manufacturers':
                    $ids = array_map('intval', array_filter(explode(',', $condition['value'])));
                    if (!empty($ids)) {
                        $inList = implode(',', $ids);
                        $conditionWheres[] = 'p.id_manufacturer IN (' . $inList . ')';
                    }
                    break;

                case 'not_in_manufacturers':
                    $ids = array_map('intval', array_filter(explode(',', $condition['value'])));
                    if (!empty($ids)) {
                        $inList = implode(',', $ids);
                        $conditionWheres[] = '(p.id_manufacturer IS NULL OR p.id_manufacturer NOT IN (' . $inList . '))';
                    }
                    break;

                case 'no_sales_since_days':
                    $days = (int) $condition['value'];
                    $conditionWheres[] = 'p.id_product NOT IN (
                        SELECT od.product_id
                        FROM `' . _DB_PREFIX_ . 'order_detail` od
                        INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_order = od.id_order)
                        WHERE o.date_add >= DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)
                    )';
                    break;

                case 'no_sales_ever':
                    $conditionWheres[] = 'p.id_product NOT IN (
                        SELECT od.product_id
                        FROM `' . _DB_PREFIX_ . 'order_detail` od
                        INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_order = od.id_order)
                    )';
                    break;
            }
        }

        $sql = 'SELECT DISTINCT p.id_product FROM `' . _DB_PREFIX_ . 'product` p ';
        $sql .= implode(' ', $joins);
        $allWheres = array_merge($baseWheres, $conditionWheres);
        $sql .= ' WHERE ' . implode(' AND ', $allWheres);

        $rows = Db::getInstance()->executeS($sql);
        if (!$rows) {
            return [];
        }

        return array_column($rows, 'id_product');
    }

    /**
     * Guardar badge personalizado para una lista de productos.
     * No toca ningún flag nativo de PrestaShop.
     */
    private function applyBadge(array $productIds, $text, $bg = '#e84444', $color = '#ffffff')
    {
        if (empty($productIds)) {
            return;
        }
        $db     = Db::getInstance();
        $idRule = (int) $this->id_rule;
        $text   = pSQL($text);
        $bg     = pSQL($bg ?: '#e84444');
        $color  = pSQL($color ?: '#ffffff');

        foreach ($productIds as $idProduct) {
            $db->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'smartcategory_badges`
                 (`id_product`, `id_rule`, `badge_text`, `badge_bg`, `badge_color`)
                 VALUES (' . (int) $idProduct . ', ' . $idRule . ', \'' . $text . '\', \'' . $bg . '\', \'' . $color . '\')
                 ON DUPLICATE KEY UPDATE
                   `badge_text`  = \'' . $text . '\',
                   `badge_bg`    = \'' . $bg . '\',
                   `badge_color` = \'' . $color . '\''
            );
        }
    }

    /**
     * Eliminar badge de una lista de productos para esta regla.
     */
    private function removeBadge(array $productIds)
    {
        if (empty($productIds)) {
            return;
        }
        $db     = Db::getInstance();
        $idRule = (int) $this->id_rule;
        $idList = implode(',', array_map('intval', $productIds));

        $db->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'smartcategory_badges`
             WHERE `id_rule` = ' . $idRule . ' AND `id_product` IN (' . $idList . ')'
        );
    }

    /**
     * Aplicar la configuración de indexación (noindex) a la categoría destino
     */
    private function applyCategoryIndexation()
    {
        $indexation = $this->noindex ? 0 : 1;
        Db::getInstance()->update(
            'category',
            ['indexation' => (int) $indexation],
            'id_category = ' . (int) $this->id_category
        );
    }

    private function setCategoryActive($active)
    {
        $active = (int) (bool) $active;
        Db::getInstance()->update(
            'category',
            ['active' => $active],
            'id_category = ' . (int) $this->id_category
        );

        Db::getInstance()->update(
            'category_shop',
            ['active' => $active],
            'id_category = ' . (int) $this->id_category
        );
    }

    /**
     * Obtener IDs de productos actualmente en la categoría
     */
    private function getProductsInCategory()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT id_product FROM `' . _DB_PREFIX_ . 'category_product` WHERE id_category = ' . (int) $this->id_category
        );

        return $rows ? array_column($rows, 'id_product') : [];
    }

    /**
     * Añadir producto a la categoría
     */
    private function addProductToCategory($idProduct)
    {
        // Verificar si ya existe
        $exists = Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'category_product`
             WHERE id_category = ' . (int) $this->id_category . ' AND id_product = ' . (int) $idProduct
        );

        if (!$exists) {
            $position = (int) Db::getInstance()->getValue(
                'SELECT MAX(position) FROM `' . _DB_PREFIX_ . 'category_product` WHERE id_category = ' . (int) $this->id_category
            ) + 1;

            Db::getInstance()->insert('category_product', [
                'id_category' => (int) $this->id_category,
                'id_product'  => (int) $idProduct,
                'position'    => $position,
            ]);
        }
    }

    /**
     * Eliminar producto de la categoría
     */
    private function removeProductFromCategory($idProduct, $idCategory = null)
    {
        $targetCategoryId = $idCategory !== null ? (int) $idCategory : (int) $this->id_category;
        Db::getInstance()->delete('category_product', 'id_category = ' . $targetCategoryId . ' AND id_product = ' . (int) $idProduct);
    }

    /**
     * Obtener productos gestionados por esta regla y la categoría donde fueron insertados.
     */
    private function getManagedProductsByRule()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT id_product, id_category
             FROM `' . _DB_PREFIX_ . 'smartcategory_rule_products`
             WHERE id_rule = ' . (int) $this->id_rule
        );

        if (!$rows) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['id_product']] = (int) $row['id_category'];
        }
        return $result;
    }

    /**
     * Comprueba si el producto ya está en una categoría concreta.
     */
    private function productExistsInCategory($idProduct, $idCategory)
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT COUNT(*)
             FROM `' . _DB_PREFIX_ . 'category_product`
             WHERE id_product = ' . (int) $idProduct . '
               AND id_category = ' . (int) $idCategory
        );
    }

    /**
     * Añadir producto a la categoría y registrarlo como gestionado por esta regla.
     */
    private function addManagedProduct($idProduct)
    {
        $this->addProductToCategory($idProduct);

        Db::getInstance()->insert('smartcategory_rule_products', [
            'id_rule'     => (int) $this->id_rule,
            'id_product'  => (int) $idProduct,
            'id_category' => (int) $this->id_category,
            'date_add'    => date('Y-m-d H:i:s'),
        ], false, true, Db::REPLACE);
    }

    /**
     * Eliminar producto de la categoría que gestiona esta regla y borrar su rastro.
     */
    private function removeManagedProduct($idProduct, $idCategory)
    {
        $this->removeProductFromCategory($idProduct, $idCategory);
        Db::getInstance()->delete(
            'smartcategory_rule_products',
            'id_rule = ' . (int) $this->id_rule . ' AND id_product = ' . (int) $idProduct
        );
    }

    /**
     * Registrar el resultado de la ejecución
     */
    private function logResult($added, $removed, $executionTime, $status, $message)
    {
        Db::getInstance()->insert('smartcategory_logs', [
            'id_rule'        => (int) $this->id_rule,
            'products_added' => (int) $added,
            'products_removed' => (int) $removed,
            'execution_time' => (float) $executionTime,
            'status'         => pSQL($status),
            'message'        => $message ? pSQL($message) : null,
            'date_add'       => date('Y-m-d H:i:s'),
        ]);

        return [
            'id_rule'   => $this->id_rule,
            'name'      => $this->name,
            'added'     => $added,
            'removed'   => $removed,
            'status'    => $status,
            'message'   => $message,
        ];
    }

    /**
     * Obtener logs de esta regla
     */
    public function getLogs($limit = 10)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'smartcategory_logs`
             WHERE id_rule = ' . (int) $this->id_rule . '
             ORDER BY date_add DESC
             LIMIT ' . (int) $limit
        );
    }

    /**
     * Obtener todos los logs recientes
     */
    public static function getAllRecentLogs($limit = 50)
    {
        return Db::getInstance()->executeS(
            'SELECT l.*, r.name as rule_name
             FROM `' . _DB_PREFIX_ . 'smartcategory_logs` l
             LEFT JOIN `' . _DB_PREFIX_ . 'smartcategory_rules` r ON r.id_rule = l.id_rule
             ORDER BY l.date_add DESC
             LIMIT ' . (int) $limit
        ) ?: [];
    }

    /**
     * Obtener lista plana de categorías para el selector de condiciones
     * Devuelve: [ ['id' => X, 'name' => '— Nombre', 'level' => N], ... ]
     */
    public static function getCategoriesForCondition()
    {
        $idLang = (int) Context::getContext()->language->id;
        $idShop = (int) Context::getContext()->shop->id;

        $rows = Db::getInstance()->executeS(
            'SELECT c.id_category, cl.name, c.level_depth
             FROM `' . _DB_PREFIX_ . 'category` c
             INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (cl.id_category = c.id_category AND cl.id_lang = ' . $idLang . ' AND cl.id_shop = ' . $idShop . ')
             INNER JOIN `' . _DB_PREFIX_ . 'category_shop` cs
                ON (cs.id_category = c.id_category AND cs.id_shop = ' . $idShop . ')
             WHERE c.active = 1 AND c.id_category > 1
             ORDER BY c.nleft ASC'
        ) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $indent = str_repeat('— ', max(0, (int) $row['level_depth'] - 1));
            $result[] = [
                'id'    => (int) $row['id_category'],
                'name'  => $indent . $row['name'],
                'level' => (int) $row['level_depth'],
            ];
        }
        return $result;
    }

    /**
     * Obtener características y sus valores agrupados para el selector de condiciones
     * Devuelve: [ ['id_feature' => X, 'feature_name' => '...', 'values' => [ ['id' => Y, 'name' => '...'], ... ] ], ... ]
     */
    public static function getFeaturesForCondition()
    {
        $idLang = (int) Context::getContext()->language->id;

        $rows = Db::getInstance()->executeS(
            'SELECT f.id_feature, fl.name as feature_name,
                    fv.id_feature_value, fvl.value as value_name
             FROM `' . _DB_PREFIX_ . 'feature` f
             INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON (fl.id_feature = f.id_feature AND fl.id_lang = ' . $idLang . ')
             INNER JOIN `' . _DB_PREFIX_ . 'feature_value` fv
                ON (fv.id_feature = f.id_feature AND fv.custom = 0)
             INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                ON (fvl.id_feature_value = fv.id_feature_value AND fvl.id_lang = ' . $idLang . ')
             ORDER BY fl.name ASC, fvl.value ASC'
        ) ?: [];

        $grouped = [];
        foreach ($rows as $row) {
            $fid = (int) $row['id_feature'];
            if (!isset($grouped[$fid])) {
                $grouped[$fid] = [
                    'id_feature'   => $fid,
                    'feature_name' => $row['feature_name'],
                    'values'       => [],
                ];
            }
            $grouped[$fid]['values'][] = [
                'id'   => (int) $row['id_feature_value'],
                'name' => $row['value_name'],
            ];
        }
        return array_values($grouped);
    }

    /**
     * Obtener marcas/fabricantes para el selector de condiciones.
     * Devuelve: [ ['id' => X, 'name' => 'Marca'], ... ]
     */
    public static function getManufacturersForCondition()
    {
        $idLang = (int) Context::getContext()->language->id;

        $rows = Db::getInstance()->executeS(
            'SELECT m.id_manufacturer, m.name
             FROM `' . _DB_PREFIX_ . 'manufacturer` m
             WHERE m.active = 1
             ORDER BY m.name ASC'
        ) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => (int) $row['id_manufacturer'],
                'name' => $row['name'],
            ];
        }

        return $result;
    }

    /**
     * Obtener atributos (variantes: Color, Talla, etc.) y sus valores para el selector de condiciones.
     * Devuelve: [ ['id_attribute_group' => X, 'group_name' => 'Color', 'values' => [ ['id' => Y, 'name' => 'Rojo'], ... ] ], ... ]
     */
    public static function getAttributesForCondition()
    {
        $idLang = (int) Context::getContext()->language->id;

        $rows = Db::getInstance()->executeS(
            'SELECT ag.id_attribute_group, agl.name as group_name,
                    a.id_attribute, al.name as attribute_name
             FROM `' . _DB_PREFIX_ . 'attribute_group` ag
             INNER JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
                ON (agl.id_attribute_group = ag.id_attribute_group AND agl.id_lang = ' . $idLang . ')
             INNER JOIN `' . _DB_PREFIX_ . 'attribute` a
                ON (a.id_attribute_group = ag.id_attribute_group)
             INNER JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON (al.id_attribute = a.id_attribute AND al.id_lang = ' . $idLang . ')
             ORDER BY agl.name ASC, al.name ASC'
        ) ?: [];

        $grouped = [];
        foreach ($rows as $row) {
            $gid = (int) $row['id_attribute_group'];
            if (!isset($grouped[$gid])) {
                $grouped[$gid] = [
                    'id_attribute_group' => $gid,
                    'group_name'         => $row['group_name'],
                    'values'             => [],
                ];
            }
            $grouped[$gid]['values'][] = [
                'id'   => (int) $row['id_attribute'],
                'name' => $row['attribute_name'],
            ];
        }
        return array_values($grouped);
    }

    public function getScheduleStatusLabel()
    {
        if (!(int) $this->active) {
            return 'Inactiva';
        }
        if ($this->isScheduledForFuture()) {
            return 'Programada';
        }
        if ($this->isFinishedBySchedule()) {
            return 'Finalizada';
        }
        return 'Activa';
    }

    public static function formatDatetimeForInput($value)
    {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return '';
        }
        return date('Y-m-d\TH:i', strtotime($value));
    }
}
