function openpassword() {
    const password=document.getElementById("typepass");
    let icon=document.getElementById("eyeicon");
    if (!password) return;
    if (password.type === "password") {
        password.type="text";
        icon.classList.replace("bi-eye-slash-fill","bi-eye-fill");
    }
    else {
        password.type="password";
        icon.classList.replace("bi-eye-fill","bi-eye-slash-fill");
    }
}