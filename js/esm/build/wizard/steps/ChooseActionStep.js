import{str as e}from"../../lang";import{jsx as t,jsxs as a}from"react/jsx-runtime";/**
 * Wizard step: the user chooses whether to import an existing course or to request a new course in the name of a teacher.
 * This choice decides which branch of steps the wizard shows afterward.
 *
 * @module     lsf_unification/wizard/steps/ChooseActionStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function m({cache:r,patch:s}){const o=r.branch;return a("div",{children:[t("h5",{className:"mb-1",children:e("choose_action")}),a("div",{className:"row g-3",children:[t("div",{className:"col-md-6",children:a("button",{type:"button",className:"card h-100 w-100 text-center p-4 border-2"+(o==="import"?" border-primary":""),onClick:()=>s({branch:"import"}),children:[t("i",{className:"fa-solid fa-file-import fa-2x text-primary mb-3"}),t("span",{className:"fw-semibold",children:e("submit_import_label")}),t("span",{className:"small text-muted mt-1",children:e("choose_action_import")})]})}),t("div",{className:"col-md-6",children:a("button",{type:"button",className:"card h-100 w-100 text-center p-4 border-2"+(o==="request"?" border-primary":""),onClick:()=>s({branch:"request"}),children:[t("i",{className:"fa-solid fa-user-tie fa-2x text-primary mb-3"}),t("span",{className:"fw-semibold",children:e("submit_request_label")}),t("span",{className:"small text-muted mt-1",children:e("choose_action_request")})]})})]})]})}export{m as default};
