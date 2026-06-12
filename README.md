# SmartCategories - Módulo PrestaShop

**Versión:** 1.0.3  
**Autor:** Luis de Yrissarri  
**Compatible con:** PrestaShop 1.7.5 → 9.x  

## ¿Qué hace este módulo?

SmartCategories permite crear **reglas automáticas** que añaden o quitan productos de categorías según condiciones configurables:

- **Precio** (entre X e Y, mayor que X, menor que X)
- **Fecha de alta** (hace más/menos de X días)
- **Stock** (con/sin stock, mayor/menor/igual a X unidades)

Las reglas se ejecutan automáticamente mediante una tarea **cron diaria** o manualmente desde el panel.

---

## Instalación

1. Sube la carpeta `smartcategories/` a `/modules/` de tu PrestaShop
2. Ve a **Módulos → Gestor de módulos**
3. Busca "Smart Categories" e instala

El módulo aparecerá en **Catálogo → Smart Categories** en el menú de administración.

---

## Uso

### 1. Crear una regla

1. Ve a **Catálogo → Smart Categories**
2. Clic en **"Nueva regla"**
3. Asigna un nombre descriptivo (ej: "Día del Padre 2025")
4. Selecciona la **categoría destino**
5. Añade una o más **condiciones**
6. Guarda

### 2. Tipos de condiciones disponibles

| Tipo | Descripción |
|------|-------------|
| Precio entre X e Y | El precio de venta está en ese rango |
| Precio mayor que X | El precio supera ese valor |
| Precio menor que X | El precio está por debajo |
| Alta hace más de X días | El producto lleva en tienda más de X días |
| Alta hace menos de X días | El producto lleva en tienda menos de X días |
| Con stock | stock_available > 0 |
| Sin stock | stock_available = 0 |
| Stock mayor que X | stock > X unidades |
| Stock menor que X | stock < X unidades |
| Stock igual a X | stock = X unidades exactas |

> Todas las condiciones se combinan con **AND** (se deben cumplir TODAS).

### 3. Ejecutar reglas

**Manual:** Desde el listado, usa el botón ▶ junto a cada regla, o "Ejecutar todas".

**Automático (cron):** La URL del cron aparece en el panel. Configúrala en tu servidor:

```bash
# Ejecutar cada día a las 02:00
0 2 * * * wget -q -O /dev/null "https://tutienda.com/module/smartcategories/cron?secure_key=XXXX"
```

O con curl:
```bash
0 2 * * * curl -s "https://tutienda.com/module/smartcategories/cron?secure_key=XXXX" > /dev/null
```

---

## Estructura de archivos

```
smartcategories/
├── smartcategories.php              # Módulo principal
├── config.xml
├── classes/
│   ├── SmartCategoryRule.php        # Modelo de regla + ejecución
│   └── SmartCategoryCondition.php   # Modelo de condición
├── controllers/
│   ├── admin/
│   │   └── AdminSmartCategoriesController.php
│   └── front/
│       └── cron.php                 # Endpoint cron
├── views/
│   ├── css/admin.css
│   ├── js/admin.js
│   └── templates/admin/
│       ├── list.tpl                 # Listado de reglas
│       ├── form.tpl                 # Formulario de creación/edición
│       └── logs.tpl                 # Historial de ejecuciones
└── sql/
    └── install.sql
```

---

## Tablas de base de datos

| Tabla | Descripción |
|-------|-------------|
| `ps_smartcategory_rules` | Reglas configuradas |
| `ps_smartcategory_conditions` | Condiciones de cada regla |
| `ps_smartcategory_logs` | Historial de ejecuciones |

---

## Notas técnicas

- El módulo **no elimina** la categoría principal del producto; solo gestiona la asignación a la categoría destino de la regla.
- Los productos se identifican como activos (`p.active = 1`).
- El stock se lee de `ps_stock_available` por producto sin atributo (`id_product_attribute = 0`), sumando el stock del shop actual.
- Compatible con multitienda (filtra por `id_shop` actual).
- La clave segura del cron se genera automáticamente en la primera instalación.
