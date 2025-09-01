<?php
// This should be saved as views/dashboard.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Advising & Monitoring System</title>
    <link rel="stylesheet" href="">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="/swepgroup17/style.css">
    <style>
        /* Additional styles for the new components */
        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            background: var(--bg-secondary);
            font-weight: 600;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-in-progress {
            background: #f59e0b;
            color: white;
        }
        
        .status-completed {
            background: #10b981;
            color: white;
        }
        
        .progress-bar {
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            margin: 8px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 300px;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            background: var(--bg-secondary);
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .chat-message {
            padding: 8px 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            max-width: 80%;
        }
        
        .chat-message.received {
            background: white;
            align-self: flex-start;
            border: 1px solid var(--border-color);
        }
        
        .chat-message.sent {
            background: var(--primary-color);
            color: white;
            align-self: flex-end;
            margin-left: auto;
        }
        
        .chat-input-container {
            display: flex;
            gap: 8px;
        }
        
        .chat-input-group {
            display: flex;
            flex: 1;
            gap: 8px;
        }
        
        .chat-input {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 1rem;
            font-family: inherit;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .card-icon {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1>
                    
                    <svg width="90" height="90" viewBox="0 0 100 100" style="background: white; padding: 10px; border-radius: 8px;">
                        <circle cx="50" cy="50" r="30" fill="var(--primary-color)"/>
                        <text x="50" y="55" text-anchor="middle" fill="white" font-size="20" font-weight="bold">LAU</text>
                    </svg>
                    Academic Advising & Monitoring System
                </h1>
                <p style="color: rgba(255,255,255,0.9); font-weight: 600; margin-top: 0.5rem;">
                    LADOKE AKINTOLA UNIVERSITY OF TECHNOLOGY, OGBOMOSO
                </p>
            </div>
            <div class="supervisor-info">
                <h3>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h3>
                <p style="font-weight: 600; margin-bottom: 0.5rem;">
                    <?= ucfirst($_SESSION['role']) ?>
                </p>
                <small>Department of Information System Science</small>
                <br>
                <a href="<?= BASE_URL ?>/logout" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showTab('dashboard')">📊 Dashboard</button>
            <button class="nav-tab" onclick="showTab('students')">👥 Students</button>
            <button class="nav-tab" onclick="showTab('courses')">📖 Courses</button>
            <button class="nav-tab" onclick="showTab('advising')">💬 Advising</button>
            <button class="nav-tab" onclick="showTab('reports')">📈 Reports</button>
            <button class="nav-tab" onclick="showTab('profile')">👤 Profile</button>
        </div>

        <!-- Dashboard Tab -->
        <div id="dashboard" class="tab-content active">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_students'] ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['active_courses'] ?></div>
                    <div class="stat-label">Active Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['success_rate'], 1) ?>%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['upcoming_meetings'] ?></div>
                    <div class="stat-label">Upcoming Meetings</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Student Progress Overview</h3>
                        <div class="card-icon">📊</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="progressChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activities</h3>
                        <div class="card-icon">🔔</div>
                    </div>
                    <?php foreach ($recentActivities as $activity): ?>
                    <div class="notification">
                        📝 <?= htmlspecialchars($activity['action']) ?> - <?= htmlspecialchars($activity['time']) ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Students</h3>
                        <div class="card-icon">👥</div>
                    </div>
                    <?php if (!empty($recentStudents)): ?>
                        <?php foreach ($recentStudents as $student): ?>
                        <div class="notification">
                            👤 <?= htmlspecialchars($student['full_name'] ?? $student['username']) ?> 
                            <small style="color: var(--text-secondary);">
                                - <?= htmlspecialchars($student['program'] ?? 'No program set') ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notification">No students found</div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Upcoming Meetings</h3>
                        <div class="card-icon">📅</div>
                    </div>
                    <?php if (!empty($upcomingMeetings)): ?>
                        <?php foreach ($upcomingMeetings as $meeting): ?>
                        <div class="notification">
                            <strong><?= htmlspecialchars($meeting['student_name']) ?></strong><br>
                            <small>
                                📅 <?= date('M d, Y', strtotime($meeting['meeting_date'])) ?> 
                                ⏰ <?= date('g:i A', strtotime($meeting['meeting_time'])) ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notification">No upcoming meetings</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Students Tab -->
        <div id="students" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Student Management</h3>
                    <button class="btn btn-primary" onclick="showModal('addStudentModal')">Add Student</button>
                </div>
                
                <div class="form-group">
                    <input type="text" class="form-input" placeholder="Search students..." id="studentSearch">
                </div>

                <div class="table-container">
                    <table class="table">
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
                        <tbody id="studentsTable">
                            <tr>
                                <td>CS2021001</td>
                                <td>John Smith</td>
                                <td>Computer Science</td>
                                <td>3rd Year</td>
                                <td>3.7</td>
                                <td><span class="status-badge status-in-progress">In Progress</span></td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">View</button>
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>CS2020015</td>
                                <td>Sarah Johnson</td>
                                <td>Computer Science</td>
                                <td>4th Year</td>
                                <td>3.9</td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">View</button>
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>CS2022008</td>
                                <td>Michael Brown</td>
                                <td>Computer Science</td>
                                <td>2nd Year</td>
                                <td>3.2</td>
                                <td><span class="status-badge status-in-progress">In Progress</span></td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">View</button>
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Courses Tab -->
        <div id="courses" class="tab-content">
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Course Management</h3>
                        <button class="btn btn-primary">Add Course</button>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" class="form-input" placeholder="Search courses...">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong>CS301 - Data Structures</strong>
                                <span class="status-badge status-in-progress">Active</span>
                            </div>
                            <p style="color: var(--text-secondary); margin-bottom: 8px;">Spring 2025 • 3 Credits</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 65%;"></div>
                            </div>
                            <small>25 students enrolled</small>
                        </div>

                        <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong>CS401 - Software Engineering</strong>
                                <span class="status-badge status-in-progress">Active</span>
                            </div>
                            <p style="color: var(--text-secondary); margin-bottom: 8px;">Spring 2025 • 4 Credits</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 80%;"></div>
                            </div>
                            <small>18 students enrolled</small>
                        </div>

                        <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong>CS501 - Advanced Algorithms</strong>
                                <span class="status-badge status-completed">Completed</span>
                            </div>
                            <p style="color: var(--text-secondary); margin-bottom: 8px;">Fall 2024 • 3 Credits</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%;"></div>
                            </div>
                            <small>12 students completed</small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Course Statistics</h3>
                        <div class="card-icon">📈</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="courseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advising Tab -->
        <div id="advising" class="tab-content">
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Advising Sessions</h3>
                        <button class="btn btn-primary" onclick="showModal('scheduleModal')">Schedule Meeting</button>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="padding: 16px; border-left: 4px solid var(--primary-color); background: var(--bg-secondary); border-radius: 0 8px 8px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong>John Smith</strong>
                                <span style="color: var(--text-secondary);">Tomorrow 2:00 PM</span>
                            </div>
                            <p style="color: var(--text-secondary);">Career planning and course selection</p>
                            <div style="margin-top: 8px;">
                                <button class="btn btn-success" style="padding: 4px 12px; font-size: 0.8rem; margin-right: 8px;">Join Meeting</button>
                                <button class="btn btn-secondary" style="padding: 4px 12px; font-size: 0.8rem;">Reschedule</button>
                            </div>
                        </div>

                        <div style="padding: 16px; border-left: 4px solid var(--warning-color); background: var(--bg-secondary); border-radius: 0 8px 8px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong>Sarah Johnson</strong>
                                <span style="color: var(--text-secondary);">Friday 10:00 AM</span>
                            </div>
                            <p style="color: var(--text-secondary);">Thesis defense preparation</p>
                            <div style="margin-top: 8px;">
                                <button class="btn btn-success" style="padding: 4px 12px; font-size: 0.8rem; margin-right: 8px;">Join Meeting</button>
                                <button class="btn btn-secondary" style="padding: 4px 12px; font-size: 0.8rem;">Reschedule</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Communication Center</h3>
                        <div class="card-icon">💬</div>
                    </div>
                    
                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <div class="chat-message received">
                                <strong>John Smith</strong><br>
                                Hi Dr. Oyelakun, I need help choosing electives for next semester.
                            </div>
                            <div class="chat-message sent">
                                Hello John! I'd be happy to help. What are your career interests?
                            </div>
                            <div class="chat-message received">
                                <strong>John Smith</strong><br>
                                I'm interested in machine learning and data science.
                            </div>
                        </div>
                        <div class="chat-input-container">
                            <div class="chat-input-group">
                                <input type="text" class="chat-input" placeholder="Type your message..." id="chatInput">
                                <button class="btn btn-primary" onclick="sendMessage()">Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Generate Reports</h3>
                        <div class="card-icon">📊</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Report Type</label>
                        <select class="form-select">
                            <option>Student Progress Report</option>
                            <option>Course Performance Report</option>
                            <option>Graduation Tracking Report</option>
                            <option>Advising Summary Report</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Time Period</label>
                        <select class="form-select">
                            <option>Current Semester</option>
                            <option>Academic Year</option>
                            <option>Last 6 Months</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Format</label>
                        <select class="form-select">
                            <option>PDF</option>
                            <option>Excel</option>
                            <option>CSV</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Analytics Dashboard</h3>
                        <div class="card-icon">📈</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="analyticsChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Reports</h3>
                        <div class="card-icon">📋</div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <div>
                                <strong>Spring 2025 Progress Report</strong>
                                <p style="color: var(--text-secondary); margin: 4px 0 0 0; font-size: 0.9rem;">Generated on March 15, 2025</p>
                            </div>
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Download</button>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <div>
                                <strong>Course Performance Analysis</strong>
                                <p style="color: var(--text-secondary); margin: 4px 0 0 0; font-size: 0.9rem;">Generated on March 10, 2025</p>
                            </div>
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Download</button>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <div>
                                <strong>Graduation Tracking Summary</strong>
                                <p style="color: var(--text-secondary); margin: 4px 0 0 0; font-size: 0.9rem;">Generated on March 5, 2025</p>
                            </div>
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Download</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Tab -->
        <div id="profile" class="tab-content">
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Profile Settings</h3>
                        <div class="card-icon">👤</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" value="Dr. Oyelakun T.A.">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" value="t.oyelakun@university.edu">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-input" value="Computer Science">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Office Hours</label>
                        <input type="text" class="form-input" value="Monday-Friday, 2:00 PM - 4:00 PM">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-input" value="+1 (555) 123-4567">
                    </div>
                    
                    <button class="btn btn-primary">Update Profile</button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Preferences</h3>
                        <div class="card-icon">⚙️</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Notifications</label>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                            <input type="checkbox" id="emailNotifications" checked>
                            <label for="emailNotifications">Receive email notifications for new messages</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Meeting Reminders</label>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                            <input type="checkbox" id="meetingReminders" checked>
                            <label for="meetingReminders">Send meeting reminders 24 hours in advance</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Auto-save Frequency</label>
                        <select class="form-select">
                            <option>Every 5 minutes</option>
                            <option>Every 10 minutes</option>
                            <option>Every 15 minutes</option>
                            <option>Manual save only</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary">Save Preferences</button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Information</h3>
                        <div class="card-icon">ℹ️</div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>System Version:</span>
                            <strong>v2.1.5</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Last Login:</span>
                            <strong>March 15, 2025 9:30 AM</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Total Students Advised:</span>
                            <strong>127</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Active Sessions:</span>
                            <strong>3</strong>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                        <button class="btn btn-secondary" style="margin-right: 12px;">Export Data</button>
                        <button class="btn btn-warning">Clear Cache</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Student</h3>
                <button class="modal-close" onclick="closeModal('addStudentModal')">&times;</button>
            </div>
            
            <div class="form-group">
                <label class="form-label">Student ID</label>
                <input type="text" class="form-input" placeholder="e.g., CS2025001">
            </div>
            
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-input" placeholder="Enter full name">
            </div>
            
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" placeholder="student@university.edu">
            </div>
            
            <div class="form-group">
                <label class="form-label">Program</label>
                <select class="form-select">
                    <option>Computer Science</option>
                    <option>Information Technology</option>
                    <option>Software Engineering</option>
                    <option>Cybersecurity</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Academic Year</label>
                <select class="form-select">
                    <option>1st Year</option>
                    <option>2nd Year</option>
                    <option>3rd Year</option>
                    <option>4th Year</option>
                    <option>Graduate</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" onclick="addStudent()">Add Student</button>
                <button class="btn btn-secondary" onclick="closeModal('addStudentModal')">Cancel</button>
            </div>
        </div>
    </div>

    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule Meeting</h3>
                <button class="modal-close" onclick="closeModal('scheduleModal')">&times;</button>
            </div>
            
            <div class="form-group">
                <label class="form-label">Student</label>
                <select class="form-select">
                    <option>John Smith</option>
                    <option>Sarah Johnson</option>
                    <option>Michael Brown</option>
                    <option>Emily Davis</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" class="form-input">
            </div>
            
            <div class="form-group">
                <label class="form-label">Time</label>
                <input type="time" class="form-input">
            </div>
            
            <div class="form-group">
                <label class="form-label">Duration</label>
                <select class="form-select">
                    <option>30 minutes</option>
                    <option>45 minutes</option>
                    <option>1 hour</option>
                    <option>1.5 hours</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Meeting Type</label>
                <select class="form-select">
                    <option>In-Person</option>
                    <option>Video Call</option>
                    <option>Phone Call</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Purpose</label>
                <textarea class="form-input" rows="3" placeholder="Brief description of meeting purpose..."></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" onclick="scheduleMeeting()">Schedule Meeting</button>
                <button class="btn btn-secondary" onclick="closeModal('scheduleModal')">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.nav-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Modal functionality
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Chat functionality
        function sendMessage() {
            const chatInput = document.getElementById('chatInput');
            const chatMessages = document.getElementById('chatMessages');
            
            if (chatInput.value.trim()) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'chat-message sent';
                messageDiv.textContent = chatInput.value;
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                chatInput.value = '';
            }
        }

        // Add student functionality
        function addStudent() {
            alert('Student added successfully!');
            closeModal('addStudentModal');
            // Here you would typically send data to backend
        }

        // Schedule meeting functionality
        function scheduleMeeting() {
            alert('Meeting scheduled successfully!');
            closeModal('scheduleModal');
            // Here you would typically send data to backend
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Progress Chart
            const progressCtx = document.getElementById('progressChart');
            if (progressCtx) {
                new Chart(progressCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress', 'Not Started'],
                        datasets: [{
                            data: [65, 25, 10],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Course Chart
            const courseCtx = document.getElementById('courseChart');
            if (courseCtx) {
                new Chart(courseCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['CS301', 'CS401', 'CS501', 'CS302', 'CS402'],
                        datasets: [{
                            label: 'Enrollment',
                            data: [25, 18, 12, 22, 16],
                            backgroundColor: '#B8860B',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Analytics Chart
            const analyticsCtx = document.getElementById('analyticsChart');
            if (analyticsCtx) {
                new Chart(analyticsCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Student Progress',
                            data: [65, 72, 68, 85, 89, 92],
                            borderColor: '#B8860B',
                            backgroundColor: 'rgba(184, 134, 11, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        });

        // Search functionality
        document.getElementById('studentSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('#studentsTable tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Chat input enter key
        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });

        // Simulate real-time updates
        setInterval(function() {
            // Update timestamp or other dynamic content
            const now = new Date();
            // You could update last activity, notifications, etc.
        }, 60000); // Update every minute
    </script>
</body>
</html>