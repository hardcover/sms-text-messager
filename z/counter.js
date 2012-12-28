function addCounter(inputID, counterID, maxLength) {
    'use strict';
    var input = document.getElementById(inputID);
    var counter = document.getElementById(counterID);
    if (maxLength) {
        input.oninput = function () {
            counter.textContent = input.value.length;
            if (input.value.length > maxLength) {
                input.style.backgroundColor = '#f00';
            } else {
                input.style.backgroundColor = '#f5f5f9';
            }
        };
    }
}

window.onload = function () {
    'use strict';
    document.getElementById('message').focus();
    addCounter('message', 'counterMessage', 600);
};