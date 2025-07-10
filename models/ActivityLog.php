<?php
namespace App\Models;

use PDO;

class ActivityLog extends BaseModel
{
    protected string $table = 'activity_logs';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function getAllActivityLogsWithUsernames()
    {
        try {
            $sql = "SELECT
                        al.id,
                        al.user_id,
                        al.username,
                        al.ip_address,
                        al.page_visited,
                        al.request_method,
                        al.timestamp,
                        al.user_agent,
                        COALESCE(al.username, u.username) AS display_username
                    FROM
                        activity_logs al
                    LEFT JOIN
                        users u ON al.user_id = u.id
                    ORDER BY
                        al.timestamp DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all activity logs: " . $e->getMessage());
            return [];
        }
    }

    public function logActivity(
        ?int $userId,
        ?string $username,
        string $ipAddress,
        string $pageVisited,
        string $requestMethod,
        ?string $userAgent
    ): bool {
        try {
            $sql = "INSERT INTO activity_logs (user_id, username, ip_address, page_visited, request_method, user_agent)
                    VALUES (:user_id, :username, :ip_address, :page_visited, :request_method, :user_agent)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':user_id' => $userId,
                ':username' => $username,
                ':ip_address' => $ipAddress,
                ':page_visited' => $pageVisited,
                ':request_method' => $requestMethod,
                ':user_agent' => $userAgent
            ]);
        } catch (PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
}