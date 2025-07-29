<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../admin/helpers/auth.php';

requireAdmin();

$reportType = $_GET['type'] ?? 'sales';
$format = $_GET['format'] ?? 'view';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

function generateCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

function getReportData($type, $dateFrom = '', $dateTo = '') {
    $sql = '';
    $params = [];
    
    switch ($type) {
        case 'sales':
            $sql = "SELECT * FROM v_reports_sales WHERE 1=1";
            break;
        case 'shipments':
            $sql = "SELECT * FROM v_reports_shipments WHERE 1=1";
            break;
        case 'detailed':
            $sql = "SELECT * FROM v_reports_detailed WHERE 1=1";
            break;
    }
    
    if ($dateFrom) {
        $sql .= " AND order_date >= ?";
        $params[] = $dateFrom;
    }
    
    if ($dateTo) {
        $sql .= " AND order_date <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    $sql .= " ORDER BY order_date DESC";
    
    $stmt = Database::conn()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

$data = getReportData($reportType, $dateFrom, $dateTo);

if ($format === 'csv') {
    $filename = "report_{$reportType}_" . date('Y-m-d') . ".csv";
    generateCSV($data, $filename);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include __DIR__ . '/../views/nav.php'; ?>

<div class="container-fluid">
    <h1 class="mb-4">Reports</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select class="form-select" name="type">
                        <option value="sales" <?= $reportType === 'sales' ? 'selected' : '' ?>>Sales Report</option>
                        <option value="shipments" <?= $reportType === 'shipments' ? 'selected' : '' ?>>Shipments Report</option>
                        <option value="detailed" <?= $reportType === 'detailed' ? 'selected' : '' ?>>Detailed Report</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Generate
                    </button>
                    <button type="submit" name="format" value="csv" class="btn btn-success">
                        <i class="fas fa-download"></i> CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($data): ?>
        <div class="mb-3">
            <span class="badge bg-info"><?= count($data) ?> records found</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead class="table-dark">
                    <tr>
                        <?php foreach (array_keys($data[0]) as $header): ?>
                            <th><?= ucwords(str_replace('_', ' ', $header)) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars((string)($value ?? '')) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
            <h3>No data found</h3>
            <p class="text-muted">Try adjusting your filters</p>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>
</body>
</html>