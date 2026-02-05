document.addEventListener('DOMContentLoaded', function () {
    // 🌓 Theme Selection Logic
    const themeBtn = document.getElementById('theme-switch');
    const htmlEl = document.documentElement;
    const themeIcon = themeBtn ? themeBtn.querySelector('i') : null;

    function updateThemeIcon(theme) {
        if (!themeIcon) return;
        if (theme === 'dark') {
            themeIcon.className = 'fas fa-sun';
        } else {
            themeIcon.className = 'fas fa-moon';
        }
    }

    // Initialize Icon from current attribute
    updateThemeIcon(htmlEl.getAttribute('data-theme'));

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const currentTheme = htmlEl.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            htmlEl.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme-preference', newTheme);
            updateThemeIcon(newTheme);

            // Re-trigger layout paints if needed
            window.dispatchEvent(new Event('resize'));
        });
    }

    // 🚀 Scroll Animations using Intersection Observer
    const animateItems = document.querySelectorAll('.animate-up');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animateItems.forEach(item => {
            item.style.animationPlayState = 'paused';
            observer.observe(item);
        });
    }

    // 📝 Registration Form Validation
    const registerForm = document.querySelector('form[action=""]');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const fullnameInput = document.getElementById('fullname');

    if (registerForm && emailInput && passwordInput && fullnameInput) {
        registerForm.addEventListener('submit', function (event) {
            let isValid = true;
            let messages = [];

            const emailValue = emailInput.value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(emailValue)) {
                isValid = false;
                messages.push("Please enter a valid email address.");
            }

            const passwordValue = passwordInput.value;
            const passwordPattern = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).+$/;

            if (!passwordPattern.test(passwordValue)) {
                isValid = false;
                messages.push("Password must contain at least one alphabet, one number, and one special character.");
            }

            if (!isValid) {
                event.preventDefault();
                alert(messages.join("\n"));
            }
        });
    }

    // 🔔 Toast Notification Logic
    const toast = document.getElementById("toast-notification");
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');

    if (msg && toast) {
        let messageText = "";
        if (msg === 'login_success') messageText = "Welcome to TeleMed!";
        else if (msg === 'logged_out') messageText = "Successfully Logged Out!";
        else if (msg === 'prescription_saved') messageText = "Prescription Uploaded!";

        if (messageText) {
            toast.innerHTML = `<i class="fas fa-check-circle"></i> ${messageText}`;
            toast.classList.add("show");

            // Cleanup URL
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: newUrl }, '', newUrl);

            setTimeout(() => {
                toast.classList.remove("show");
            }, 4000);
        }
    }
});
