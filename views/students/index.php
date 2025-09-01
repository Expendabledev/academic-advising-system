<?php 
$pageTitle = "Student Management"; 
$activeTab = "students"; 
include __DIR__ . '/../includes/header.php'; 
?>

<!-- Students Tab Content -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Student Management</h3>
            <button class="btn btn-primary" onclick="showModal('addStudentModal')">
                <i class="fas fa-plus"></i> Add Student
            </button>
        </div>
        
        <div class="card-body">
            <div class="form-group">
                <input type="text" id="studentSearch" class="form-input" 
                       placeholder="Search students..." oninput="searchStudents(this.value)">
            </div>
            
            <div class="table-container">
                <?php include __DIR__ . '/partials/students_table.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Student</h3>
            <button class="modal-close" onclick="closeModal('addStudentModal')">&times;</button>
        </div>
        
        <div class="modal-body">
            <form id="addStudentForm" onsubmit="addStudent(event)">
                <div class="form-group">
                    <label class="form-label">Student ID</label>
                    <input type="text" name="student_id" class="form-input" 
                           placeholder="e.g., CS2025001" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-input" 
                           placeholder="Enter full name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" 
                           placeholder="student@university.edu" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Program</label>
                    <select name="program" class="form-select" required>
                        <option value="">Select Program</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Software Engineering">Software Engineering</option>
                        <option value="Cybersecurity">Cybersecurity</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year" class="form-select" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="Graduate">Graduate</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Student</button>
                    <button type="button" class="btn btn-secondary" 
                            onclick="closeModal('addStudentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
<!-- Enhanced Search and Filter Functions -->
<script>
// Global variables for filtering
let originalRows = [];

document.addEventListener('DOMContentLoaded', function() {
    // Store original table rows for filtering
    const tableRows = document.querySelectorAll('#studentsTable tbody tr');
    originalRows = Array.from(tableRows);
});

// Enhanced search functionality
function searchStudents(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    const tableRows = document.querySelectorAll('#studentsTable tbody tr');
    
    tableRows.forEach(row => {
        const studentId = row.cells[0].textContent.toLowerCase();
        const studentName = row.cells[1].textContent.toLowerCase();
        const program = row.cells[2].textContent.toLowerCase();
        const email = row.querySelector('.text-muted')?.textContent.toLowerCase() || '';
        
        const matches = studentId.includes(term) || 
                       studentName.includes(term) || 
                       program.includes(term) || 
                       email.includes(term);
        
        row.style.display = matches ? '' : 'none';
    });
    
    updateTableInfo();
}

// Filter students by multiple criteria
function filterStudents() {
    const programFilter = document.getElementById('programFilter').value;
    const yearFilter = document.getElementById('yearFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
    
    const tableRows = document.querySelectorAll('#studentsTable tbody tr');
    
    tableRows.forEach(row => {
        const program = row.cells[2].textContent;
        const year = row.cells[3].textContent;
        const status = row.querySelector('.status-badge').textContent;
        
        // Check search term
        const studentId = row.cells[0].textContent.toLowerCase();
        const studentName = row.cells[1].textContent.toLowerCase();
        const programText = program.toLowerCase();
        const email = row.querySelector('.text-muted')?.textContent.toLowerCase() || '';
        
        const matchesSearch = !searchTerm || 
                            studentId.includes(searchTerm) || 
                            studentName.includes(searchTerm) || 
                            programText.includes(searchTerm) || 
                            email.includes(searchTerm);
        
        // Check filters
        const matchesProgram = !programFilter || program === programFilter;
        const matchesYear = !yearFilter || year === yearFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        const shouldShow = matchesSearch && matchesProgram && matchesYear && matchesStatus;
        row.style.display = shouldShow ? '' : 'none';
    });
    
    updateTableInfo();
}

// Update table information
function updateTableInfo() {
    const visibleRows = document.querySelectorAll('#studentsTable tbody tr[style=""]').length;
    const totalRows = document.querySelectorAll('#studentsTable tbody tr').length;
    
    const tableInfo = document.querySelector('.table-info');
    if (tableInfo) {
        if (visibleRows === totalRows) {
            tableInfo.textContent = `Showing ${totalRows} students`;
        } else {
            tableInfo.textContent = `Showing ${visibleRows} of ${totalRows} students`;
        }
    }
}

// Add student functionality with better error handling
function addStudent(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    // Client-side validation
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    if (!isValid) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Student';
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    // Send data to backend
    fetch('/GROUP17/api/students/add.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Student added successfully!', 'success');
            closeModal('addStudentModal');
            setTimeout(() => {
                location.reload(); // Refresh to show new student
            }, 1000);
        } else {
            showNotification('Error adding student: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Network error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Student';
    });
}

// Enhanced modal functionality
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus on first input
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Reset form and remove error classes
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        }
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Escape key to close modals
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            activeModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
    
    // Ctrl + N to add new student
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        showModal('addStudentModal');
    }
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
});

// Clear filters function
function clearFilters() {
    document.getElementById('studentSearch').value = '';
    document.getElementById('programFilter').value = '';
    document.getElementById('yearFilter').value = '';
    document.getElementById('statusFilter').value = '';
    filterStudents();
}

// Export students data
function exportStudents(format = 'csv') {
    const visibleRows = Array.from(document.querySelectorAll('#studentsTable tbody tr'))
        .filter(row => row.style.display !== 'none');
    
    if (visibleRows.length === 0) {
        showNotification('No students to export', 'warning');
        return;
    }
    
    // Create download link
    const link = document.createElement('a');
    link.href = `/GROUP17/api/students/export.php?format=${format}`;
    link.download = `students_export_${new Date().toISOString().split('T')[0]}.${format}`;
    link.click();
    
    showNotification(`Exporting ${visibleRows.length} students as ${format.toUpperCase()}`, 'info');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?> 