import{submitCourseRequest as o}from"../../../services/csm";import i from"../../components/SummaryPanel";import{str as e}from"../../../lang";import{jsx as r,jsxs as l}from"react/jsx-runtime";/**
 * Wizard step: shows the request that is about to be sent and submits it. Last step of the request branch
 *
 * @module     lsf_unification/wizard/steps/RequestSummaryStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function a({cache:t,patch:u}){const s=t.teacher,m=[{label:e("teacher"),value:s?`${s.firstname} ${s.lastname}`:""},{label:e("course"),value:t.course?.title??""},{label:e("semester"),value:t.course?.semester??""}];return l("div",{children:[r("h5",{className:"mb-1",children:e("summary_request_title")}),r("p",{className:"text-muted",children:e("summary_request_text")}),r(i,{rows:m,submitted:t.submitted,onSubmitted:()=>u({submitted:!0}),submit:()=>o(t),submitLabel:e("submit_request_label"),successText:e("submit_request_success"),errorText:e("submit_request_error")})]})}export{a as default};
