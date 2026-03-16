function openpassword() {
    const password=document.getElementById("birthday");
    if (!password) return;
    if (password.type === "password") {
        password.type="text"
    }
    else {
        password.type="password"
    }
}