document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('device-search');
    const tagsContainer = document.getElementById('tags-container');
    const hiddenInput = document.getElementById('devices-hidden-input');
    const addBtn = document.getElementById('add-tag-btn');
    const form = document.getElementById('waitlistForm');

    // Tag functionality helper
    function handleInputTag() {
        const text = input.value.trim();
        if (text !== "") {
            addTag(text);
            input.value = "";
            input.focus();
        }
    }

    // Enter key support
    input.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleInputTag();
        }
    });

    // Add button support
    if (addBtn) {
        addBtn.addEventListener('click', function (e) {
            e.preventDefault();
            handleInputTag();
        });
    }

    window.addTag = function (text) {
        const tag = document.createElement('div');
        tag.className = 'tag';
        tag.innerHTML = `${text} <span class="remove-tag" onclick="removeTag(this)">×</span>`;
        tagsContainer.appendChild(tag);
        updateHiddenInput();
    }

    window.removeTag = function (element) {
        element.parentElement.remove();
        updateHiddenInput();
    }

    function updateHiddenInput() {
        const tags = Array.from(tagsContainer.children).map(tag => tag.textContent.replace('×', '').trim());
        hiddenInput.value = tags.join(',');
    }

    function clearTags() {
        tagsContainer.innerHTML = '';
        updateHiddenInput();
    }

    // Modal helpers
    window.closeModal = function (modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Form Submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // 1. Show Loading
        document.getElementById('loading-overlay').classList.remove('hidden');

        const formData = new FormData(form);

        fetch('./api/submit.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                // 2. Hide Loading
                document.getElementById('loading-overlay').classList.add('hidden');

                if (data.success) {
                    // 3. Show Success
                    document.getElementById('success-modal').classList.remove('hidden');
                    form.reset();
                    clearTags();
                } else {
                    // 4. Show Error (logic error)
                    console.error('Server reported error:', data.message);
                    document.getElementById('error-modal').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                document.getElementById('loading-overlay').classList.add('hidden');
                document.getElementById('error-modal').classList.remove('hidden');
            });
    });
});
