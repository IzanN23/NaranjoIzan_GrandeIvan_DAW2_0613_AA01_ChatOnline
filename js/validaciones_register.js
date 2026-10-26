document.getElementById("register-real-name").oninput = validaNombre;
document.getElementById("register-username").oninput = validaUsername;
document.getElementById("register-email").oninput = validaEmail;
document.getElementById("register-password").oninput = validaPassword;
document.getElementById("register-password-confirmation").oninput = validaPasswordConf;




function validaNombre(){
    let input = document.getElementById("register-real-name");
    let nombre = input.value.trim();
    let error = document.getElementById("error-nombre");

    if(nombre === ""){
        input.style.border = "2px solid red";
        error.textContent = "Debes escribir tu nombre antes de continuar";
        error.style.color="red";
    }else if(/\d/.test(nombre)){
        input.style.border = "2px solid red";
        error.textContent = "Tu nombre no tiene números, a no ser que seas un bot";
        error.style.color="red";
    }else if(nombre.length < 3) {
        input.style.border = "2px solid red";
        error.textContent = "Tu nombre debe tener al menos tres letas";
        error.style.color="red";
    }else{
        input.style.border = "2px solid green";
        error.textContent = "";
    }

    
}



function validaUsername(){
    let input = document.getElementById("register-username");
    let username = input.value.trim();
    let error = document.getElementById("error-username");


    if(username === ""){
        input.style.border = "2px solid red";
        error.textContent = "Debes escribir tu nombre de usuario antes de continuar";
        error.style.color="red";
    }else if(username.length < 3) {
        input.style.border = "2px solid red";
        error.textContent = "Tu nombre de usuario debe tener al menos tres letas";
        error.style.color="red";
    }else if (!/^[A-Za-z0-9._]+$/.test(username)) {
        input.style.border = "2px solid red";
        error.textContent = "Tu nombre de usuario solo puede tener caracteres de la a a la Z, numeros, . y _";
        error.style.color="red";
    }else{
        input.style.border = "2px solid green";
        error.textContent = "";
    }


}

function validaEmail(){
    let input = document.getElementById("register-email");
    let email = input.value.trim();
    let error = document.getElementById("error-email");

    if(email === ""){
        input.style.border = "2px solid red";
        error.textContent = "El correo no puede estar vacío";
        error.style.color="red";
    }else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
        input.style.border = "2px solid red";
        error.textContent = "Ese no es el formato correcto de un coreo electrónico";
        error.style.color="red";
    }else{
        input.style.border = "2px solid green";
        error.textContent = "";
    }

}

function validaPassword(){
    let input = document.getElementById("register-password");
    let pwd = input.value.trim();
    let error = document.getElementById("error-pwd");


    if(pwd === ""){
        input.style.border = "2px solid red";
        error.textContent = "La contraseña no puede estar vacía";
        error.style.color="red";
    }else if(pwd.length < 8){
        input.style.border = "2px solid red";
        error.textContent = "La contraseña es poco segura, debe tener almenos ocho caracteres";
        error.style.color="red";
    }else if(!/[a-z]/.test(pwd)){
        input.style.border = "2px solid red";
        error.textContent = "La contraseña es poco segura, debe tener almenos una minúscula";
        error.style.color="red";
    }else if(!/[A-Z]/.test(pwd)){
        input.style.border = "2px solid red";
        error.textContent = "La contraseña es poco segura, debe tener almenos una mayúscula";
        error.style.color="red";
    }else if(!/\d/.test(pwd)){
        input.style.border = "2px solid red";
        error.textContent = "La contraseña es poco segura, debe tener almenos un número";
        error.style.color="red";
    }else if(pwd.length > 100){
        input.style.border = "2px solid red";
        error.textContent = "La contraseña es demasiado larga";
        error.style.color="red";
    }else{
        input.style.border = "2px solid green";
        error.textContent = "";
    }
}

function validaPasswordConf() {
    let input = document.getElementById("register-password-confirmation");
    let pwdconf = input.value;
    let pwd = document.getElementById("register-password").value;
    let error = document.getElementById("error-pwdconf");

    if (pwdconf === "") {
        input.style.border = "2px solid red";
        error.textContent = "Has de repetir la contraseña";
        error.style.color = "red";
    } else if (pwdconf !== pwd) {
        input.style.border = "2px solid red";
        error.textContent = "Las contraseñas no coinciden";
        error.style.color = "red";
    } else {
        input.style.border = "2px solid green";
        error.textContent = "";
    }
}

