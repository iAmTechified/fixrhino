document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('device-search');
    const tagsContainer = document.getElementById('tags-container');
    const hiddenInput = document.getElementById('devices-hidden-input');

    input.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const text = input.value.trim();
            if (text !== "") {
                addTag(text);
                input.value = "";
            }
        }
    });

    window.addTag = function(text) {
        const tag = document.createElement('div');
        tag.className = 'tag';
        tag.innerHTML = `${text} <span class="remove-tag" onclick="removeTag(this)">×</span>`;
        tagsContainer.appendChild(tag);
        updateHiddenInput();
    }

    window.removeTag = function(element) {
        element.parentElement.remove();
        updateHiddenInput();
    }

    function updateHiddenInput() {
        const tags = Array.from(tagsContainer.children).map(tag => tag.textContent.replace('×', '').trim());
        hiddenInput.value = tags.join(',');
    }
});
