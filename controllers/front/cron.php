<?php
/**
 * SmartCategories - Controlador de tarea cron
 * URL: /module/smartcategories/cron?secure_key=XXXX
 */

class SmartCategoriesCronModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ssl = true;

    public function init()
    {
        parent::init();

        // Verificar clave de seguridad
        $secureKey = Configuration::get('SC_SECURE_KEY');
        $requestKey = Tools::getValue('secure_key', '');

        if (empty($secureKey) || $requestKey !== $secureKey) {
            http_response_code(403);
            die('Access denied. Invalid secure key.');
        }

        // Ejecutar reglas
        $startTime = microtime(true);
        $results = $this->module->runAllRules();
        $totalTime = round(microtime(true) - $startTime, 3);

        $totalAdded = 0;
        $totalRemoved = 0;
        $errors = 0;

        foreach ($results as $result) {
            $totalAdded += $result['added'];
            $totalRemoved += $result['removed'];
            if ($result['status'] === 'error') {
                $errors++;
            }
        }

        // Respuesta
        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'timestamp'     => date('Y-m-d H:i:s'),
            'rules_executed'=> count($results),
            'total_added'   => $totalAdded,
            'total_removed' => $totalRemoved,
            'errors'        => $errors,
            'execution_time'=> $totalTime . 's',
            'details'       => $results,
        ], JSON_PRETTY_PRINT);

        exit;
    }
}
