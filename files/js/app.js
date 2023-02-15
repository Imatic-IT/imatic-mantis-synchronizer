"use strict";
selectMultipleCheckboxex();
function selectMultipleCheckboxex() {
    // let startCheckbox: HTMLInputElement;
    let endCheckbox;
    let checkboxes;
    checkboxes = document.querySelectorAll("td input[type='checkbox']");
    let startCheckbox = null;
    let lastChecked = null;
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].addEventListener("click", function (event) {
            if (event.shiftKey) {
                let start = Array.from(checkboxes).indexOf(this);
                let end = Array.from(checkboxes).indexOf(startCheckbox);
                // Ak start > end, tak výmena hodnôt
                if (start > end) {
                    [start, end] = [end, start];
                }
                for (let j = start; j <= end; j++) {
                    checkboxes[j].checked = lastChecked.checked;
                }
            }
            else {
                // Inak nastaviť aktuálny checkbox ako startCheckbox
                startCheckbox = this;
            }
            lastChecked = this;
        });
    }
}
