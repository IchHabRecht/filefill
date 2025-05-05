import $ from "jquery";
import DocumentService from "@typo3/core/document-service.js";
import FormEngine from '@typo3/backend/form-engine.js';
import Icons from "@typo3/backend/icons.js";
import RegularEvent from "@typo3/core/event/regular-event.js";

class SubmitInterceptor {
    constructor() {
        DocumentService.ready().then(document => {
            this.registerEvents();
        });
    }

    registerEvents() {
        new RegularEvent("click", (event, target) => {
            const $me = $(target);

            if (target instanceof HTMLInputElement || target instanceof HTMLButtonElement) {
                target.disabled = true;
            } else if (target instanceof HTMLAnchorElement) {
                target.classList.add("disabled");
            }

            Icons.getIcon("spinner-circle", Icons.sizes.small).then(markup => {
                target.replaceChild(document.createRange().createContextualFragment(markup), target.querySelector(".t3js-icon"))
            })

            const name = $me.data("name") || target.getAttribute("name");
            const value = $me.data("value") || target.getAttribute("value");
            const $hiddenField = $('<input />').attr("type", "hidden").attr("name", name).attr("value", value);
            const $form = $me.closest("form");
            $form.append($hiddenField);

            FormEngine.saveDocument();
        }).delegateTo(document, ".t3js-editform-submitButton")
    }
}

export default new SubmitInterceptor;
