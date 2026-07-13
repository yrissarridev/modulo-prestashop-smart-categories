<?php
/**
 * SmartCategoryCondition - Clase que representa una condición de una regla
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class SmartCategoryCondition extends ObjectModel
{
    public $id_condition;
    public $id_rule;
    public $condition_type;
    public $operator;
    public $value;
    public $value2;
    public $sort_order;

    public static $definition = [
        'table'   => 'smartcategory_conditions',
        'primary' => 'id_condition',
        'fields'  => [
            'id_rule'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'condition_type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 50],
            'operator'       => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 20],
            'value'          => ['type' => self::TYPE_HTML, 'size' => 65535],
            'value2'         => ['type' => self::TYPE_HTML, 'size' => 65535],
            'sort_order'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
        ],
    ];

    /**
     * Tipos de condiciones disponibles
     */
    public static function getConditionTypes()
    {
        return [
            'price' => [
                'label' => 'Precio',
                'types' => [
                    'price_between' => 'Entre X e Y',
                    'price_greater' => 'Mayor que X',
                    'price_less'    => 'Menor que X',
                ],
            ],
            'date' => [
                'label' => 'Fecha de alta',
                'types' => [
                    'date_added_before' => 'Dado de alta hace más de X días',
                    'date_added_after'  => 'Dado de alta hace menos de X días',
                ],
            ],
            'stock' => [
                'label' => 'Stock',
                'types' => [
                    'stock_with'    => 'Con stock (cantidad > 0)',
                    'stock_without' => 'Sin stock (cantidad = 0)',
                    'stock_greater' => 'Stock mayor que X',
                    'stock_less'    => 'Stock menor que X',
                    'stock_equal'   => 'Stock igual a X',
                ],
            ],
            'sales' => [
                'label' => 'Ventas',
                'types' => [
                    'no_sales_since_days' => 'Sin ventas en los últimos X días',
                    'no_sales_ever'       => 'Nunca vendido',
                ],
            ],
            'catalog' => [
                'label' => 'Catálogo',
                'types' => [
                    'in_categories'      => 'Pertenece a categorías (cualquiera)',
                    'not_in_categories'  => 'Excluir categorías (ninguna de estas)',
                    'in_feature_values'  => 'Tiene característica con valor',
                    'in_attributes'      => 'Tiene atributo/variante con valor (ej: Color=Rojo)',
                    'not_in_attributes'  => 'Excluir atributo/variante con valor',
            'no_sales_since_days'  => 'Sin ventas en X días',
            'no_sales_ever'        => 'Nunca vendido',
                ],
            ],
        ];
    }

    /**
     * Obtener condiciones por regla
     */
    public static function getConditionsByRule($idRule)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'smartcategory_conditions`
             WHERE id_rule = ' . (int) $idRule . '
             ORDER BY sort_order ASC, id_condition ASC'
        ) ?: [];
    }

    /**
     * Eliminar todas las condiciones de una regla
     */
    public static function deleteByRule($idRule)
    {
        return Db::getInstance()->delete('smartcategory_conditions', 'id_rule = ' . (int) $idRule);
    }

    /**
     * Obtener etiqueta legible de un tipo de condición
     */
    public static function getConditionLabel($type)
    {
        $labels = [
            'price_between'      => 'Precio entre X e Y',
            'price_greater'      => 'Precio mayor que X',
            'price_less'         => 'Precio menor que X',
            'date_added_before'  => 'Dado de alta hace más de X días',
            'date_added_after'   => 'Dado de alta hace menos de X días',
            'stock_with'         => 'Con stock',
            'stock_without'      => 'Sin stock',
            'stock_greater'      => 'Stock mayor que X',
            'stock_less'         => 'Stock menor que X',
            'stock_equal'        => 'Stock igual a X',
            'in_categories'      => 'Pertenece a categorías',
            'not_in_categories'  => 'Excluir categorías',
            'in_feature_values'  => 'Tiene característica con valor',
            'in_attributes'      => 'Tiene atributo con valor',
            'not_in_attributes'  => 'Excluir atributo con valor',
            'no_sales_since_days'  => 'Sin ventas en X días',
            'no_sales_ever'        => 'Nunca vendido',
        ];

        return isset($labels[$type]) ? $labels[$type] : $type;
    }

    /**
     * Obtener descripción legible de una condición con sus valores
     */
    public static function getConditionDescription($condition)
    {
        switch ($condition['condition_type']) {
            case 'price_between':
                return 'Precio entre ' . $condition['value'] . '€ y ' . $condition['value2'] . '€';
            case 'price_greater':
                return 'Precio > ' . $condition['value'] . '€';
            case 'price_less':
                return 'Precio < ' . $condition['value'] . '€';
            case 'date_added_before':
                return 'Alta hace más de ' . $condition['value'] . ' días';
            case 'date_added_after':
                return 'Alta hace menos de ' . $condition['value'] . ' días';
            case 'stock_with':
                return 'Con stock (> 0 uds.)';
            case 'stock_without':
                return 'Sin stock (0 uds.)';
            case 'stock_greater':
                return 'Stock > ' . $condition['value'] . ' uds.';
            case 'stock_less':
                return 'Stock < ' . $condition['value'] . ' uds.';
            case 'stock_equal':
                return 'Stock = ' . $condition['value'] . ' uds.';
            case 'in_categories':
                $ids = array_filter(explode(',', $condition['value']));
                return 'Pertenece a ' . count($ids) . ' categoría(s) seleccionada(s)';
            case 'not_in_categories':
                $ids = array_filter(explode(',', $condition['value']));
                return 'Excluye ' . count($ids) . ' categoría(s)';
            case 'in_feature_values':
                $ids = array_filter(explode(',', $condition['value']));
                return 'Tiene ' . count($ids) . ' valor(es) de característica seleccionado(s)';
            case 'in_attributes':
                $ids = array_filter(explode(',', $condition['value']));
                return 'Tiene ' . count($ids) . ' valor(es) de atributo seleccionado(s)';
            case 'not_in_attributes':
                $ids = array_filter(explode(',', $condition['value']));
                return 'Excluye ' . count($ids) . ' valor(es) de atributo';
            case 'no_sales_since_days':
                return 'Sin ventas en los últimos ' . $condition['value'] . ' días';
            case 'no_sales_ever':
                return 'Nunca ha tenido ventas';
            default:
                return $condition['condition_type'];
        }
    }
}
