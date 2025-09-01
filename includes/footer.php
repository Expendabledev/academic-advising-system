</main>
    </div>
    
    <!-- Modals -->
    <?php include __DIR__ . '/modals/add_student.php'; ?>
    <?php include __DIR__ . '/modals/schedule_meeting.php'; ?>
    
    <!-- JavaScript -->
    <script src="/swepgroup17/assets/js/main.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            const showTab = (tabId) => {
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.getElementById(tabId).classList.add('active');
            };
           
            // Initialize charts if they exist on page
            if (typeof initCharts === 'function') {
                initCharts();
            }
            
            // Handle form submissions with CSRF protection
            const forms = document.querySelectorAll('form[data-ajax]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    // Add your AJAX form handling here
                });
            });
        });
        
        // Global function to show notifications
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.innerHTML = `
                <i class="fas fa-info-circle"></i>
                ${message}
                <button type="button" class="close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>