/**
 * Simple Compact Client Form Integration
 * 
 * Handles the show/hide functionality and form submission
 */

/* eslint-env browser */
/* global window, document, console, setTimeout, fetch, FormData, MutationObserver */

document.addEventListener('DOMContentLoaded', function() {
    initCompactClientForm();
});

// Global event delegation for form submission (backup)
document.addEventListener('submit', function(event) {
    if (event.target && event.target.id === 'compact-client-form-actual') {
        console.log('Global form submission handler triggered');
        handleFormSubmission(event);
    }
});

// Global event delegation for cancel button (backup)
document.addEventListener('click', function(event) {
    if (event.target && event.target.id === 'cancel-compact-form') {
        console.log('Global cancel handler triggered');
        event.preventDefault();
        event.stopPropagation();
        hideCompactClientForm();
    }
});

// Additional backup - capture phase
document.addEventListener('click', function(event) {
    if (event.target && event.target.id === 'cancel-compact-form') {
        console.log('Global cancel handler (capture phase) triggered');
        event.preventDefault();
        event.stopPropagation();
        hideCompactClientForm();
    }
}, true);

function initCompactClientForm() {
    // Add event listeners to "Add New Client" buttons
    const addClientButtons = document.querySelectorAll('button[onclick*="toggleCompactClientForm"]');
    addClientButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            toggleCompactClientForm();
        });
        button.removeAttribute('onclick');
    });
    
    // Use a more aggressive approach to attach form handlers
    attachFormHandlers();
    
    // Re-attach handlers after any DOM changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                attachFormHandlers();
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

function attachFormHandlers() {
    // Handle form submission
    const form = document.getElementById('compact-client-form-actual');
    if (form && !form.hasAttribute('data-handler-attached')) {
        console.log('Attaching form submission handler');
        form.addEventListener('submit', handleFormSubmission);
        form.setAttribute('data-handler-attached', 'true');
    }
    
    // Handle cancel button - more aggressive approach
    const cancelButton = document.getElementById('cancel-compact-form');
    if (cancelButton && !cancelButton.hasAttribute('data-handler-attached')) {
        console.log('Attaching cancel button handler');
        
        // Add multiple event types to ensure it works
        cancelButton.addEventListener('click', function(event) {
            console.log('Cancel button clicked');
            event.preventDefault();
            event.stopPropagation();
            hideCompactClientForm();
        });
        
        cancelButton.addEventListener('mousedown', function(event) {
            console.log('Cancel button mousedown');
            event.preventDefault();
        });
        
        cancelButton.setAttribute('data-handler-attached', 'true');
    }
}

function toggleCompactClientForm() {
    console.log('Toggling compact client form');
    
    // Find the CompactClientForm component
    const compactForm = document.querySelector('[data-live-name-value="CompactClientForm"]');
    
    if (compactForm) {
        // Find the toggle button within the component
        const toggleButton = compactForm.querySelector('[data-live-action-param="toggle"]');
        
        if (toggleButton) {
            console.log('Found toggle button, clicking...');
            toggleButton.click();
        } else {
            console.log('Toggle button not found');
        }
    } else {
        console.log('CompactClientForm component not found');
    }
}

function hideCompactClientForm() {
    console.log('Hiding compact client form');
    
    const compactForm = document.querySelector('[data-live-name-value="CompactClientForm"]');
    
    if (compactForm) {
        const toggleButton = compactForm.querySelector('[data-live-action-param="toggle"]');
        if (toggleButton) {
            console.log('Found toggle button, clicking to hide form');
            toggleButton.click();
        } else {
            console.log('Toggle button not found in compact form');
        }
    } else {
        console.log('CompactClientForm component not found');
    }
}

function handleFormSubmission(event) {
    console.log('Form submission handler called');
    event.preventDefault();
    event.stopPropagation();
    
    const form = event.target;
    const formData = new FormData(form);
    
    console.log('Form data:', Object.fromEntries(formData));
    
    // Disable submit button
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = 'Creating...';
    
    console.log('Sending AJAX request to:', form.action);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Clear form
            form.reset();
            
            // Hide form
            hideCompactClientForm();
            
            
            // Show success message
            showSuccessMessage('Client created successfully');
        } else {
            showErrorMessage(data.error || 'Failed to create client');
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        showErrorMessage('Failed to create client. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}



function showSuccessMessage(message) {
    showMessage(message, 'success');
}

function showErrorMessage(message) {
    showMessage(message, 'danger');
}

function showMessage(message, type) {
    // Remove existing alerts
    const existingAlert = document.querySelector('.client-creation-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show client-creation-alert`;
    const icon = type === 'success' ? 'check' : 'exclamation-triangle';
    alert.innerHTML = `
        <i class="fa fa-${icon}"></i> ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    // Insert at the top of the main content
    const mainContent = document.querySelector('.card-body') || document.querySelector('.content');
    if (mainContent) {
        mainContent.insertBefore(alert, mainContent.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}





// Make function globally available
window.toggleCompactClientForm = toggleCompactClientForm;