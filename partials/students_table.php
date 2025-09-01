// Enhanced student action functions
function viewStudent(studentId) {
    // Show loading state
    showNotification('Loading student details...', 'info');
    
    // Redirect to student detail page
    window.location.href = `/GROUP17/index.php?page=student_details&id=${studentId}`;
}

function editStudent(studentId) {
    // Open edit modal or redirect to edit page
    window.location.href = `/GROUP17/index.php?page=student_edit&id=${studentId}`;
}

function messageStudent(studentId) {
    // Open messaging interface
    window.location.href = `/GROUP17/index.php?page=messages&student=${studentId}`;
}

function scheduleMeeting(studentId) {
    // Open meeting scheduler modal
    showModal('scheduleMeetingModal');
    
    // Pre-fill student in the form
    const studentSelect = document.querySelector('#schedul<?php
// This would typically fetch data from database
// For now, using sample data that matches your system structure
$students = [
    [
        'id' => 1,
        'student_id' => 'CS2021001',
        'full_name' => 'John Smith',
        'program' => 'Computer Science',
        'academic_year' => '3rd Year',
        'gpa' => '3.7',
        'status' => 'In Progress',
        'email' => 'john.smith@lautech.edu.ng',
        'phone' => '+234 803 123 4567',
        'supervisor_id' => $_SESSION['user_id'] ?? 1
    ],
    [
        'id' => 2,
        'student_id' => 'CS2020015',
        'full_name' => 'Sarah Johnson',
        'program' => 'Computer Science',
        'academic_year' => '4th Year',
        'gpa' => '3.9',
        'status' => 'Completed',
        'email' => 'sarah.johnson@lautech.edu.ng',
        'phone' => '+234 803 234 5678',
        'supervisor_id' => $_SESSION['user_id'] ?? 1
    ],
    [
        'id' => 3,
        'student_id' => 'CS2022008',
        'full_name' => 'Michael Brown',
        'program' => 'Computer Science',
        'academic_year' => '2nd Year',
        'gpa' => '3.2',
        'status' => 'In Progress',
        'email' => 'michael.brown@lautech.edu.ng',
        'phone' => '+234 803 345 6789',
        'supervisor_id' => $_SESSION['user_id'] ?? 1
    ],
    [
        'id' => 4,
        'student_id' => 'IT2021003',
        'full_name' => 'Emily Davis',
        'program' => 'Information Technology',
        'academic_year' => '3rd Year',
        'gpa' => '3.5',
        'status' => 'In Progress',
        'email' => 'emily.davis@lautech.edu.ng',
        'phone' => '+234 803 456 7890',
        'supervisor_id' => $_SESSION['user_id'] ?? 1
    ],
    [
        'id' => 5,
        'student_id' => 'SE2020012',
        'full_name' => 'David Wilson',
        'program' => 'Software Engineering',
        'academic_year' => '4th Year',
        'gpa' => '3.8',
        'status' => 'In Progress',
        'email' => 'david.wilson@lautech.edu.ng',
        'phone' => '+234 803 567 8901',
        'supervisor_id' => $_SESSION['user_id'] ?? 1
    ],
    [
        'id' => 6,
        'student_id' => 'CY2021007',
        'full_name' => 'Jessica Martinez',
        'program' => 'Cybersecurity',
        'academic_year' => '2nd Year',
        'gpa' => '3.6',
        'status' => 'In Progress',
        'email' => 'jessica.martinez@lautech.edu.ng',
        'phone' => '+234 803 678 9012',
        'supervisor_id' => $_SESSION['user_id'] ?? 1
    ]
];

// Count statistics
$totalStudents = count($students);
$activeStudents = count(array_filter($students, function($s) { return $s['status'] === 'In Progress'; }));
$completedStudents = count(array_filter($students, function($s) { return $s['status'] === 'Completed'; }));
?>

<!-- Table Statistics -->
<div class="table-stats">
    <div class="stat-item">
        <i class="fas fa-users"></i>
        <span class="stat-number"><?= $totalStudents ?></span>
        <span class="stat-label">Total Students</span>
    </div>
    <div class="stat-item">
        <i class="fas fa-user-check"></i>
        <span class="stat-number"><?= $activeStudents ?></span>
        <span class="stat-label">Active</span>
    </div>
    <div class="stat-item">
        <i class="fas fa-user-graduate"></i>
        <span class="stat-number"><?= $completedStudents ?></span>
        <span class="stat-label">Completed</span>
    </div>
    <div class="stat-item actions">
        <button class="btn btn-sm btn-secondary" onclick="clearFilters()">
            <i class="fas fa-filter"></i> Clear Filters
        </button>
        <button class="btn btn-sm btn-info" onclick="exportStudents('csv')">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>
</div>

<table class="table" id="studentsTable">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Program</th>
            <th>Year</th>
            <th>GPA</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $student): ?>
        <tr data-student-id="<?= htmlspecialchars($student['student_id']) ?>" 
            data-student-db-id="<?= htmlspecialchars($student['id']) ?>">
            <td>
                <div class="student-id">
                    <strong><?= htmlspecialchars($student['student_id']) ?></strong>
                    <small class="registration-info">
                        ID: <?= htmlspecialchars($student['id']) ?>
                    </small>
                </div>
            </td>
            <td>
                <div class="student-info">
                    <strong><?= htmlspecialchars($student['full_name']) ?></strong>
                    <div class="contact-info">
                        <small class="text-muted">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($student['email']) ?>
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($student['phone']) ?>
                        </small>
                    </div>
                </div>
            </td>
            <td>
                <span class="program-badge program-<?= strtolower(str_replace(' ', '-', $student['program'])) ?>">
                    <?= htmlspecialchars($student['program']) ?>
                </span>
            </td>
            <td>
                <span class="year-badge year-<?= substr($student['academic_year'], 0, 1) ?>">
                    <?= htmlspecialchars($student['academic_year']) ?>
                </span>
            </td>
            <td>
                <span class="gpa-badge <?= $student['gpa'] >= 3.5 ? 'gpa-high' : ($student['gpa'] >= 3.0 ? 'gpa-medium' : 'gpa-low') ?>">
                    <i class="fas fa-star"></i> <?= htmlspecialchars($student['gpa']) ?>
                </span>
            </td>
            <td>
                <span class="status-badge <?= $student['status'] == 'Completed' ? 'status-completed' : ($student['status'] == 'In Progress' ? 'status-in-progress' : 'status-on-hold') ?>">
                    <i class="fas fa-<?= $student['status'] == 'Completed' ? 'check-circle' : ($student['status'] == 'In Progress' ? 'clock' : 'pause-circle') ?>"></i>
                    <?= htmlspecialchars($student['status']) ?>
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" 
                                onclick="viewStudent(<?= htmlspecialchars($student['id']) ?>)"
                                title="View Student Details" data-tooltip="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" 
                                onclick="editStudent(<?= htmlspecialchars($student['id']) ?>)"
                                title="Edit Student Information" data-tooltip="Edit Student">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-info" 
                                onclick="messageStudent(<?= htmlspecialchars($student['id']) ?>)"
                                title="Send Message" data-tooltip="Send Message">
                            <i class="fas fa-comment"></i>
                        </button>
                        <button class="btn btn-sm btn-success" 
                                onclick="scheduleMeeting(<?= htmlspecialchars($student['id']) ?>)"
                                title="Schedule Meeting" data-tooltip="Schedule Meeting">
                            <i class="fas fa-calendar-plus"></i>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline dropdown-toggle" 
                                    onclick="toggleDropdown(this)" title="More Actions">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="#" onclick="viewProgress(<?= htmlspecialchars($student['id']) ?>)">
                                    <i class="fas fa-chart-line"></i> View Progress
                                </a>
                                <a href="#" onclick="exportStudent(<?= htmlspecialchars($student['id']) ?>)">
                                    <i class="fas fa-download"></i> Export Data
                                </a>
                                <a href="#" onclick="archiveStudent(<?= htmlspecialchars($student['id']) ?>)">
                                    <i class="fas fa-archive"></i> Archive
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="table-footer">
    <div class="table-info">
        Showing <?= count($students) ?> students
    </div>
    <div class="table-pagination">
        <!-- Pagination controls would go here -->
        <button class="btn btn-sm btn-secondary" disabled>Previous</button>
        <span class="pagination-info">Page 1 of 1</span>
        <button class="btn btn-sm btn-secondary" disabled>Next</button>
    </div>
</div>

<script>
// Enhanced student action functions
function viewStudent(studentId) {
    // Show loading state
    showNotification('Loading student details...', 'info');
    
    // Redirect to student detail page
    window.location.href = `/GROUP17/index.php?page=student_details&id=${studentId}`;
}

function editStudent(studentId) {
    // Open edit modal or redirect to edit page
    window.location.href = `/GROUP17/index.php?page=student_edit&id=${studentId}`;
}

function messageStudent(studentId) {
    // Open messaging interface
    window.location.href = `/GROUP17/index.php?page=messages&student=${studentId}`;
}

function scheduleMeeting(studentId) {
    // Open meeting scheduler modal
    showModal('scheduleMeetingModal');
    
    // Pre-fill student in the form
    const studentSelect = document.querySelector('#scheduleMeetingModal select[name="student_id"]');
    if (studentSelect) {
        studentSelect.value = studentId;
    }
}

function viewProgress(studentId) {
    // Open progress tracking page
    window.location.href = `/GROUP17/index.php?page=student_progress&id=${studentId}`;
}

function exportStudent(studentId) {
    // Export individual student data
    const link = document.createElement('a');
    link.href = `/GROUP17/api/students/export_single.php?id=${studentId}`;
    link.download = `student_${studentId}_data.pdf`;
    link.click();
    
    showNotification('Exporting student data...', 'info');
}

function archiveStudent(studentId) {
    if (confirm('Are you sure you want to archive this student? They will be moved to archived students.')) {
        fetch(`/GROUP17/api/students/archive.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ student_id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Student archived successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error archiving student: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error archiving student', 'error');
        });
    }
}

// Dropdown toggle function
function toggleDropdown(button) {
    const dropdown = button.parentElement;
    const isOpen = dropdown.classList.contains('open');
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown.open').forEach(d => d.classList.remove('open'));
    
    // Toggle current dropdown
    if (!isOpen) {
        dropdown.classList.add('open');
        
        // Close dropdown when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeDropdown(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('open');
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }, 0);
    }
}
</script>

<style>
/* Enhanced styles for the students table */
.search-filter-section {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.input-group {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    z-index: 2;
}

.input-group .form-input {
    padding-left: 40px;
}

.filter-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-group .form-select {
    min-width: 150px;
}

.table-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    align-items: center;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 8px;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-item.actions {
    margin-left: auto;
    background: transparent;
    box-shadow: none;
    gap: 0.5rem;
}

.stat-item i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.stat-number {
    font-weight: bold;
    font-size: 1.1rem;
    color: var(--primary-color);
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.student-id {
    display: flex;
    flex-direction: column;
}

.registration-info {
    color: var(--text-secondary);
    font-size: 0.75rem;
}

.student-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.contact-info small {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.program-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.program-computer-science {
    background: #dbeafe;
    color: #1e40af;
}

.program-information-technology {
    background: #f3e8ff;
    color: #7c3aed;
}

.program-software-engineering {
    background: #ecfdf5;
    color: #059669;
}

.program-cybersecurity {
    background: #fef2f2;
    color: #dc2626;
}

.year-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.year-1 { background: #fef3c7; color: #92400e; }
.year-2 { background: #ddd6fe; color: #6d28d9; }
.year-3 { background: #bfdbfe; color: #1d4ed8; }
.year-4 { background: #d1fae5; color: #065f46; }
.year-g { background: #f3f4f6; color: #374151; }

.gpa-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 6px 10px;
    border-radius: 16px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-in-progress {
    background: #fbbf24;
    color: white;
}

.status-completed {
    background: #10b981;
    color: white;
}

.status-on-hold {
    background: #ef4444;
    color: white;
}

.btn-group {
    display: flex;
    gap: 4px;
    align-items: center;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-toggle {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    transition: all 0.2s;
}

.dropdown-toggle:hover {
    background: var(--bg-secondary);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 160px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    z-index: 1000;
    padding: 0.5rem 0;
}

.dropdown.open .dropdown-menu {
    display: block;
}

.dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.875rem;
    transition: background-color 0.2s;
}

.dropdown-menu a:hover {
    background-color: var(--bg-secondary);
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.table-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-pagination {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 0 8px;
}

/* Form error states */
.form-input.error,
.form-select.error {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Responsive design */
@media (max-width: 768px) {
    .search-filter-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .input-group {
        min-width: auto;
    }
    
    .filter-group {
        justify-content: stretch;
    }
    
    .filter-group .form-select {
        min-width: auto;
        flex: 1;
    }
    
    .table-stats {
        justify-content: center;
    }
    
    .stat-item.actions {
        margin-left: 0;
        margin-top: 0.5rem;
        justify-content: center;
    }
    
    .btn-group {
        flex-wrap: wrap;
    }
    
    .table-footer {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
    
    .contact-info {
        font-size: 0.8rem;
    }
}

/* Tooltip styles */
[data-tooltip] {
    position: relative;
}

[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 4px;
}

/* Loading states */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.fa-spin {
    animation: fa-spin 1s infinite linear;
}

/* Animation for table rows */
.table tbody tr {
    transition: opacity 0.3s ease;
}

.table tbody tr[style*="none"] {
    opacity: 0;
}
</style>