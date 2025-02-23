class ApiKeyVisibilityToggle {
    constructor() {
        this.toggleButton = document.querySelector('.toggle-visibility');
        this.apiKeyInput = document.getElementById('webnorth_openweather_api_key');
        
        if (this.toggleButton && this.apiKeyInput) {
            this.init();
        }
    }

    init() {
        this.toggleButton.addEventListener('click', () => this.toggleVisibility());
    }

    toggleVisibility() {
        const icon = this.toggleButton.querySelector('.dashicons');
        
        if (this.apiKeyInput.type === 'password') {
            this.apiKeyInput.type = 'text';
            icon.classList.remove('dashicons-visibility');
            icon.classList.add('dashicons-hidden');
        } else {
            this.apiKeyInput.type = 'password';
            icon.classList.remove('dashicons-hidden');
            icon.classList.add('dashicons-visibility');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ApiKeyVisibilityToggle();
});
