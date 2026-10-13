import{submitCourseImport as o}from"../../../services/csm";import i from"../../components/SummaryPanel";import{str as t}from"../../../lang";import{jsx as r,jsxs as l}from"react/jsx-runtime";/**
 * Wizard step: shows the course that is about to be imported and submits it. Last step
 * of the import branch; after submitting only the confirmation is shown.
 *
 * @module     lsf_unification/wizard/steps/ImportSummaryStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function u({cache:e,patch:m}){const s=[{label:t("title"),value:e.course?.title??""},{label:t("shorttitle"),value:e.course?.shorttitle??""},{label:t("semester"),value:e.course?.semester??""},{label:t("description"),value:e.course?.description??""},{label:t("category"),value:e.category?.name??""}];return l("div",{children:[r("h5",{className:"mb-1",children:t("summary_import_title")}),r("p",{className:"text-muted",children:t("summary_import_text")}),r(i,{rows:s,submitted:e.submitted,onSubmitted:()=>m({submitted:!0}),submit:()=>o(e),submitLabel:t("submit_import_label"),successText:t("submit_import_success"),errorText:t("submit_import_error")})]})}export{u as default};
