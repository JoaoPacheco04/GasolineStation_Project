const premioButtons = document.querySelectorAll('.premio-button');
const premioInput = document.getElementById('premio_id');

premioButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Toggle selection
        button.classList.toggle('selected');

        // Get all selected IDs
        const selectedIds = Array.from(document.querySelectorAll('.premio-button.selected'))
            .map(btn => btn.getAttribute('data-id'));

        // Set hidden input value
        premioInput.value = selectedIds.join(',');
    });

    // Accessibility: allow keyboard selection (Enter or Space key)
    button.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            button.click();
        }
    });
});

// Validate prize selected on form submit
document.getElementById('premioForm').addEventListener('submit', function(e) {
    if (!premioInput.value) {
        alert("Por favor, selecione pelo menos um prémio.");
        e.preventDefault();
    }
});
