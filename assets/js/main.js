document.addEventListener('DOMContentLoaded', function () {
    // Select the registration form
    const registerForm = document.querySelector('form[action=""]'); // Assuming the register form matches this or we can add an ID

    // Improved selection strategy: check if we are on register page
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const fullnameInput = document.getElementById('fullname'); // Available on register page
    // const fullnameInput = document.getElementById('fullname'); // Available if needed

    if (registerForm && emailInput && passwordInput && fullnameInput) {
        registerForm.addEventListener('submit', function (event) {
            let isValid = true;
            let messages = [];

            // Email Validation (Basic Regex)
            const emailValue = emailInput.value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(emailValue)) {
                isValid = false;
                messages.push("Please enter a valid email address.");
            }

            // Password Validation (Alphabet + Number + Special Char)
            const passwordValue = passwordInput.value;
            // (?=.*[a-zA-Z]) -> at least one alphabet
            // (?=.*\d) -> at least one digit
            // (?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]) -> at least one special char
            const passwordPattern = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).+$/;

            if (!passwordPattern.test(passwordValue)) {
                isValid = false;
                messages.push("Password must contain at least one alphabet, one number, and one special character.");
            }

            if (!isValid) {
                event.preventDefault(); // Stop form submission
                alert(messages.join("\n")); // Show errors
            }
        });
    }

    // Toast Notification Logic
    var toast = document.getElementById("toast-notification");
    var urlParams = new URLSearchParams(window.location.search);
    var msg = urlParams.get('msg');

    if (msg && toast) {
        var messageText = "";

        if (msg === 'login_success') {
            messageText = "Login Successful!";
        } else if (msg === 'logged_out') {
            messageText = "Logout Successful!";
        }

        if (messageText) {
            toast.textContent = messageText;
            toast.className = "toast-notification show";

            // Clean URL
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: newUrl }, '', newUrl);

            // Hide after 5 seconds
            setTimeout(function () {
                toast.className = toast.className.replace("show", "");
            }, 5000);
        }
    }
});
