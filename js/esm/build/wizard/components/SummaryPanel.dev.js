var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * The shared body of both summary steps: it lists what is about to be sent, submits
 * it, and reports how that went. Both branches do exactly this and differ only in the
 * rows they list, the wording and which service call they make, so those are props.
 *
 * @module     lsf_unification/wizard/components/SummaryPanel
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { Fragment as Fragment2, useState } from "react";
function SummaryPanel({ rows, submitted, onSubmitted, submit, submitLabel, successText, errorText }) {
  const [submitting, setSubmitting] = useState(false);
  const [failed, setFailed] = useState(false);
  const handleSubmit = /* @__PURE__ */ __name(() => {
    setSubmitting(true);
    setFailed(false);
    submit().then((status) => status ? onSubmitted() : setFailed(true)).catch(() => setFailed(true)).finally(() => setSubmitting(false));
  }, "handleSubmit");
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("dl", { className: "row mb-4", children: rows.map(({ label, value }) => /* @__PURE__ */ jsxDEV(Fragment2, { children: [
      /* @__PURE__ */ jsxDEV("dt", { className: "col-sm-4 text-muted fw-normal", children: label }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
        lineNumber: 59,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("dd", { className: "col-sm-8 fw-semibold", children: value || "\u2013" }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
        lineNumber: 60,
        columnNumber: 13
      }, this)
    ] }, label, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
      lineNumber: 58,
      columnNumber: 11
    }, this)) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
      lineNumber: 56,
      columnNumber: 7
    }, this),
    submitted ? /* @__PURE__ */ jsxDEV("div", { className: "alert alert-success d-flex align-items-center mb-0", role: "alert", children: [
      /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-circle-check me-2" }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
        lineNumber: 67,
        columnNumber: 11
      }, this),
      /* @__PURE__ */ jsxDEV("div", { children: successText }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
        lineNumber: 68,
        columnNumber: 11
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
      lineNumber: 66,
      columnNumber: 9
    }, this) : /* @__PURE__ */ jsxDEV(Fragment, { children: [
      failed && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-danger d-flex align-items-center", role: "alert", children: [
        /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-triangle-exclamation me-2" }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
          lineNumber: 74,
          columnNumber: 15
        }, this),
        /* @__PURE__ */ jsxDEV("div", { children: errorText }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
          lineNumber: 75,
          columnNumber: 15
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
        lineNumber: 73,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "d-flex justify-content-end", children: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary text-white", onClick: handleSubmit, disabled: submitting, children: [
        submitting && /* @__PURE__ */ jsxDEV("span", { className: "spinner-border spinner-border-sm me-2", role: "status" }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
          lineNumber: 80,
          columnNumber: 30
        }, this),
        submitLabel
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
        lineNumber: 79,
        columnNumber: 13
      }, this) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
        lineNumber: 78,
        columnNumber: 11
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
      lineNumber: 71,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/components/SummaryPanel.tsx",
    lineNumber: 52,
    columnNumber: 5
  }, this);
}
__name(SummaryPanel, "SummaryPanel");
export {
  SummaryPanel as default
};
//# sourceMappingURL=SummaryPanel.dev.js.map
