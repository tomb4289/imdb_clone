<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\ActivityLog;
use App\Providers\Auth;

class ActivityLogController extends BaseController
{
    protected ActivityLog $activityLogModel;

    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
        $this->activityLogModel = new ActivityLog($pdo);
    }

    public function index()
    {
        Auth::session();
        Auth::privilege(1);

        $logs = $this->activityLogModel->getAllActivityLogsWithUsernames();

        echo $this->twig->render('logs/index.html.twig', [
            'activity_logs' => $logs
        ]);
    }
}