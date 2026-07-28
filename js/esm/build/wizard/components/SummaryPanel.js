import{Fragment as N,useState as r}from"react";import{Fragment as n,jsx as e,jsxs as t}from"react/jsx-runtime";/**
 * The shared body of both summary steps: it lists what is about to be sent, submits
 * it, and reports how that went. Both branches do exactly this and differ only in the
 * rows they list, the wording and which service call they make, so those are props.
 *
 * @module     lsf_unification/wizard/components/SummaryPanel
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function x({rows:m,submitted:d,onSubmitted:o,submit:c,submitLabel:u,successText:b,errorText:f}){const[l,i]=r(!1),[g,a]=r(!1),v=()=>{i(!0),a(!1),c().then(s=>s?o():a(!0)).catch(()=>a(!0)).finally(()=>i(!1))};return t(n,{children:[e("dl",{className:"row mb-4",children:m.map(({label:s,value:p})=>t(N,{children:[e("dt",{className:"col-sm-4 text-muted fw-normal",children:s}),e("dd",{className:"col-sm-8 fw-semibold",children:p||"\u2013"})]},s))}),d?t("div",{className:"alert alert-success d-flex align-items-center mb-0",role:"alert",children:[e("i",{className:"fa-solid fa-circle-check me-2"}),e("div",{children:b})]}):t(n,{children:[g&&t("div",{className:"alert alert-danger d-flex align-items-center",role:"alert",children:[e("i",{className:"fa-solid fa-triangle-exclamation me-2"}),e("div",{children:f})]}),e("div",{className:"d-flex justify-content-end",children:t("button",{type:"button",className:"btn btn-primary text-white",onClick:v,disabled:l,children:[l&&e("span",{className:"spinner-border spinner-border-sm me-2",role:"status"}),u]})})]})]})}export{x as default};
