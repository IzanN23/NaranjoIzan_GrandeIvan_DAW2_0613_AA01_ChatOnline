
const MAX_MENSAJE = 250;

function mostrarErrorMensaje(texto) {
    let input = document.getElementById("message-content");
    let error = document.getElementById("error-message");

    error.textContent = texto;
    error.hidden = false;
    input.style.border = "2px solid red";
    input.focus();
}

function limpiarErrorMensaje() {
    let input = document.getElementById("message-content");
    let error = document.getElementById("error-message");

    error.textContent = "";
    error.hidden = true;
    input.style.border = "";
}

function validaMensaje() {
    let input = document.getElementById("message-content");
    let mensaje = input.value.trim();

    if (mensaje === "") {
        mostrarErrorMensaje("No puedes enviar un mensaje vacío.");
        return false;
    }

    if (mensaje.length > MAX_MENSAJE) {
        mostrarErrorMensaje("El mensaje no puede superar los " + MAX_MENSAJE + " caracteres.");
        return false;
    }

    limpiarErrorMensaje();
    return true;
}

let formMensaje = document.getElementById("form_message");

if (formMensaje) {
    formMensaje.onsubmit = validaMensaje;

    document.getElementById("message-content").oninput = limpiarErrorMensaje;
}