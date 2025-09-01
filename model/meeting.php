<?php
require_once __DIR__ . '/BaseModel.php';

class Meeting extends BaseModel {
    protected $table = 'meetings';

    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function create($data) {
        try {
            $meetingData = [
                'student_id' => $data['student_id'],
                'supervisor_id' => $data['supervisor_id'],
                'meeting_date' => $data['meeting_date'],
                'meeting_time' => $data['meeting_time'],
                'duration' => $data['duration'],
                'type' => $data['type'],
                'purpose' => $data['purpose'] ?? null,
                'status' => $data['status'] ?? 'scheduled'
            ];

            return parent::create($meetingData);
        } catch (Exception $e) {
            error_log("Error creating meeting: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $meetingData = [
                'student_id' => $data['student_id'],
                'supervisor_id' => $data['supervisor_id'],
                'meeting_date' => $data['meeting_date'],
                'meeting_time' => $data['meeting_time'],
                'duration' => $data['duration'],
                'type' => $data['type'],
                'purpose' => $data['purpose'] ?? null,
                'status' => $data['status'] ?? 'scheduled',
                'notes' => $data['notes'] ?? null
            ];

            return parent::update($id, $meetingData);
        } catch (Exception $e) {
            error_log("Error updating meeting: " . $e->getMessage());
            return false;
        }
    }

    public function getUpcoming($userId, $limit = null) {
        try {
            // Check if user is supervisor or student based on user role
            $userRole = $_SESSION['role'] ?? 'student';
            
            if ($userRole === 'supervisor') {
                $query = "SELECT m.*, s.full_name as student_name, s.student_id
                         FROM meetings m
                         JOIN students st ON m.student_id = st.id
                         JOIN supervisors sup ON m.supervisor_id = sup.id
                         WHERE sup.user_id = :user_id
                         AND m.meeting_date >= CURDATE()
                         AND m.status = 'scheduled'
                         ORDER BY m.meeting_date, m.meeting_time";
            } else {
                $query = "SELECT m.*, sup.full_name as supervisor_name
                         FROM meetings m
                         JOIN students st ON m.student_id = st.id
                         JOIN supervisors sup ON m.supervisor_id = sup.id
                         WHERE st.user_id = :user_id
                         AND m.meeting_date >= CURDATE()
                         AND m.status = 'scheduled'
                         ORDER BY m.meeting_date, m.meeting_time";
            }

            $params = ['user_id' => $userId];
            if ($limit) {
                $query .= " LIMIT :limit";
                $params['limit'] = $limit;
            }

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error getting upcoming meetings: " . $e->getMessage());
            return [];
        }
    }

    public function countUpcoming($userId) {
        try {
            $userRole = $_SESSION['role'] ?? 'student';
            
            if ($userRole === 'supervisor') {
                $query = "SELECT COUNT(*) as count FROM meetings m
                         JOIN supervisors sup ON m.supervisor_id = sup.id
                         WHERE sup.user_id = :user_id
                         AND m.meeting_date >= CURDATE()
                         AND m.status = 'scheduled'";
            } else {
                $query = "SELECT COUNT(*) as count FROM meetings m
                         JOIN students st ON m.student_id = st.id
                         WHERE st.user_id = :user_id
                         AND m.meeting_date >= CURDATE()
                         AND m.status = 'scheduled'";
            }

            $result = $this->fetchOne($query, ['user_id' => $userId]);
            return $result ? (int) $result['count'] : 0;
        } catch (Exception $e) {
            error_log("Error counting upcoming meetings: " . $e->getMessage());
            return 0;
        }
    }

    public function checkAvailability($supervisorId, $date, $time, $duration, $excludeMeetingId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM meetings m
                     JOIN supervisors sup ON m.supervisor_id = sup.id
                     WHERE sup.id = :supervisor_id
                     AND m.meeting_date = :meeting_date
                     AND m.status = 'scheduled'
                     AND (
                         (m.meeting_time <= :start_time AND ADDTIME(m.meeting_time, SEC_TO_TIME(m.duration * 60)) > :start_time)
                         OR (m.meeting_time < :end_time AND ADDTIME(m.meeting_time, SEC_TO_TIME(m.duration * 60)) >= :end_time)
                         OR (m.meeting_time >= :start_time AND m.meeting_time < :end_time)
                     )";

            $params = [
                'supervisor_id' => $supervisorId,
                'meeting_date' => $date,
                'start_time' => $time,
                'end_time' => date('H:i:s', strtotime("+$duration minutes", strtotime($time)))
            ];

            if ($excludeMeetingId) {
                $query .= " AND m.id != :exclude_id";
                $params['exclude_id'] = $excludeMeetingId;
            }

            $result = $this->fetchOne($query, $params);
            return $result ? ((int) $result['count'] === 0) : true;
        } catch (Exception $e) {
            error_log("Error checking availability: " . $e->getMessage());
            return false;
        }
    }

    public function getByDateRange($startDate, $endDate, $supervisorId = null) {
        try {
            $query = "SELECT m.*, s.full_name as student_name, sup.full_name as supervisor_name
                     FROM meetings m
                     JOIN students s ON m.student_id = s.id
                     JOIN supervisors sup ON m.supervisor_id = sup.id
                     WHERE m.meeting_date BETWEEN :start_date AND :end_date";
            
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate
            ];

            if ($supervisorId) {
                $query .= " AND sup.user_id = :supervisor_id";
                $params['supervisor_id'] = $supervisorId;
            }

            $query .= " ORDER BY m.meeting_date, m.meeting_time";

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error getting meetings by date range: " . $e->getMessage());
            return [];
        }
    }

    public function updateStatus($id, $status, $notes = null) {
        try {
            $data = ['status' => $status];
            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            return parent::update($id, $data);
        } catch (Exception $e) {
            error_log("Error updating meeting status: " . $e->getMessage());
            return false;
        }
    }

    public function getByStudent($studentId) {
        try {
            $query = "SELECT m.*, sup.full_name as supervisor_name
                     FROM meetings m
                     JOIN supervisors sup ON m.supervisor_id = sup.id
                     WHERE m.student_id = :student_id
                     ORDER BY m.meeting_date DESC, m.meeting_time DESC";

            return $this->fetchAll($query, ['student_id' => $studentId]);
        } catch (Exception $e) {
            error_log("Error getting meetings by student: " . $e->getMessage());
            return [];
        }
    }

    public function getBySupervisor($supervisorId) {
        try {
            $query = "SELECT m.*, s.full_name as student_name, s.student_id
                     FROM meetings m
                     JOIN students s ON m.student_id = s.id
                     WHERE m.supervisor_id = :supervisor_id
                     ORDER BY m.meeting_date DESC, m.meeting_time DESC";

            return $this->fetchAll($query, ['supervisor_id' => $supervisorId]);
        } catch (Exception $e) {
            error_log("Error getting meetings by supervisor: " . $e->getMessage());
            return [];
        }
    }

    public function getTodaysMeetings($userId) {
        try {
            $userRole = $_SESSION['role'] ?? 'student';
            
            if ($userRole === 'supervisor') {
                $query = "SELECT m.*, s.full_name as student_name, s.student_id
                         FROM meetings m
                         JOIN students s ON m.student_id = s.id
                         JOIN supervisors sup ON m.supervisor_id = sup.id
                         WHERE sup.user_id = :user_id
                         AND m.meeting_date = CURDATE()
                         AND m.status = 'scheduled'
                         ORDER BY m.meeting_time";
            } else {
                $query = "SELECT m.*, sup.full_name as supervisor_name
                         FROM meetings m
                         JOIN students st ON m.student_id = st.id
                         JOIN supervisors sup ON m.supervisor_id = sup.id
                         WHERE st.user_id = :user_id
                         AND m.meeting_date = CURDATE()
                         AND m.status = 'scheduled'
                         ORDER BY m.meeting_time";
            }

            return $this->fetchAll($query, ['user_id' => $userId]);
        } catch (Exception $e) {
            error_log("Error getting today's meetings: " . $e->getMessage());
            return [];
        }
    }

    public function getCompletedMeetings($userId, $limit = null) {
        try {
            $userRole = $_SESSION['role'] ?? 'student';
            
            if ($userRole === 'supervisor') {
                $query = "SELECT m.*, s.full_name as student_name
                         FROM meetings m
                         JOIN students s ON m.student_id = s.id
                         JOIN supervisors sup ON m.supervisor_id = sup.id
                         WHERE sup.user_id = :user_id
                         AND m.status = 'completed'
                         ORDER BY m.meeting_date DESC, m.meeting_time DESC";
            } else {
                $query = "SELECT m.*, sup.full_name as supervisor_name
                         FROM meetings m
                         JOIN students st ON m.student_id = st.id
                         JOIN supervisors sup ON m.supervisor_id = sup.id
                         WHERE st.user_id = :user_id
                         AND m.status = 'completed'
                         ORDER BY m.meeting_date DESC, m.meeting_time DESC";
            }

            $params = ['user_id' => $userId];
            if ($limit) {
                $query .= " LIMIT :limit";
                $params['limit'] = $limit;
            }

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error getting completed meetings: " . $e->getMessage());
            return [];
        }
    }
}
?>