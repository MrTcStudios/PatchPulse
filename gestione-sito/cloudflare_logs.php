<?php
declare(strict_types=1);

include("../config.php");

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}


final class Config {
    private const API_TOKEN = '';
    private const ZONE_ID = '';
    
    public static function getApiToken(): string {
        return self::API_TOKEN;
    }
    
    public static function getZoneId(): string {
        return self::ZONE_ID;
    }
}

final class CloudflareApi {
    private string $apiToken;
    private string $zoneId;
    
    public function __construct(string $apiToken, string $zoneId) {
        $this->apiToken = $apiToken;
        $this->zoneId = $zoneId;
    }
    
    public function getFirewallRules(): array {
        $url = sprintf(
            'https://api.cloudflare.com/client/v4/zones/%s/firewall/rules',
            $this->zoneId
        );

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->apiToken}",
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new RuntimeException("Errore API Cloudflare: $error");
        }
        
        $data = json_decode($response, true);
        return $data['result'] ?? [];
    }
}

try {
    $api = new CloudflareApi(Config::getApiToken(), Config::getZoneId());
    $rules = $api->getFirewallRules();
} catch (Exception $e) {
    $error = $e->getMessage();
    $rules = [];
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Regole Firewall Cloudflare</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl mr-3"></i>
                    <span class="text-xl font-semibold text-gray-800">Cloudflare Security</span>
                </div>
                <div class="text-gray-600">
                    <i class="fas fa-user-shield mr-2"></i>
		    <a href="https://mrtc.cc/PatchPulse/gestione-sito/dashboard.php" ">Admin Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Dashboard Regole Firewall</h1>
                    <p class="text-gray-600 mt-2">Gestione e monitoraggio delle regole firewall Cloudflare</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-blue-800">Regole Totali</div>
                    <div class="text-2xl font-bold text-blue-600"><?= count($rules) ?></div>
                </div>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-8" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <div>
                        <p class="font-bold">Errore</p>
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (empty($rules)): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-yellow-400 mr-3"></i>
                    <p class="text-yellow-700">Nessuna regola firewall trovata.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azione</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrizione</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espressione</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($rules as $rule): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-gray-900"><?= htmlspecialchars($rule['id']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($rule['description'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $actionClass = $rule['action'] === 'block' 
                                            ? 'bg-red-50 text-red-700 border-red-300'
                                            : 'bg-green-50 text-green-700 border-green-300';
                                        $actionIcon = $rule['action'] === 'block' 
                                            ? 'fa-ban' 
                                            : 'fa-check';
                                        ?>
                                        <span class="px-3 py-1 inline-flex items-center border rounded-full text-sm font-medium <?= $actionClass ?>">
                                            <i class="fas <?= $actionIcon ?> mr-1"></i>
                                            <?= htmlspecialchars($rule['action']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($rule['description'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <code class="text-sm bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                                            <?= htmlspecialchars($rule['filter']['expression']) ?>
                                        </code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-white mt-8 shadow-inner">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <p class="text-gray-600 text-sm">
                    Dashboard Firewall Cloudflare © <?= date('Y') ?>
                </p>
                <div class="flex items-center text-gray-500 text-sm">
                    <i class="fas fa-clock mr-2"></i>
                    Ultimo aggiornamento: <?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
