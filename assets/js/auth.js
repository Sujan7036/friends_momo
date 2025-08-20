// Authentication JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    if (confirmPasswordField) {
        confirmPasswordField.addEventListener('input', function() {
            if (this.value !== passwordField.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        passwordField.addEventListener('input', function() {
            if (confirmPasswordField.value !== this.value) {
                confirmPasswordField.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordField.setCustomValidity('');
            }
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Demo account quick fill
    document.querySelectorAll('.demo-account').forEach(account => {
        account.addEventListener('click', function() {
            const text = this.textContent;
            const emailMatch = text.match(/Email: ([\w@.]+)/);
            const passwordMatch = text.match(/Password: ([\w]+)/);
            
            if (emailMatch && passwordMatch) {
                const emailField = document.getElementById('email');
                const passwordField = document.getElementById('password');
                
                if (emailField && passwordField) {
                    emailField.value = emailMatch[1];
                    passwordField.value = passwordMatch[1];
                }
            }
        });
    });
});
