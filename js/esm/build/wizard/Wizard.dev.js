var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * The wizard shell: it owns the cache the steps fill up, which step is showing, and the Back/Next navigation.
 * It knows nothing about the individual steps beyond what the stepvmap in steps/config tells it.
 *
 * @module     lsf_unification/wizard/Wizard
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useState, useEffect } from "react";
import { STEPS, sequenceFor, canAdvance } from "./steps/config";
import { str } from "../lang";
function Wizard({ onClose }) {
  const [step, setStep] = useState(0);
  const [showErrors, setShowErrors] = useState(false);
  const [cache, setCache] = useState({ branch: null, course: null, teacher: null, category: null, submitted: false });
  const patchCache = /* @__PURE__ */ __name((partial) => setCache((c) => ({ ...c, ...partial })), "patchCache");
  useEffect(() => {
    const onKey = /* @__PURE__ */ __name((e) => e.key === "Escape" && onClose(), "onKey");
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [onClose]);
  const sequence = sequenceFor(cache.branch);
  const currentStepId = sequence[step];
  const { component: Step } = STEPS[currentStepId];
  const isFirst = step === 0;
  const isLast = step === sequence.length - 1;
  const title = currentStepId === "choose" || cache.branch === null ? str("wizard_shell_title") : str(cache.branch === "import" ? "course_import" : "course_request");
  const back = /* @__PURE__ */ __name(() => {
    setShowErrors(false);
    setStep((s) => s - 1);
  }, "back");
  const next = /* @__PURE__ */ __name(() => {
    if (!canAdvance(currentStepId, cache)) {
      setShowErrors(true);
      return;
    }
    setShowErrors(false);
    setStep((s) => s + 1);
  }, "next");
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("div", { className: "modal fade show d-block", tabIndex: -1, role: "dialog", "aria-modal": "true", children: /* @__PURE__ */ jsxDEV("div", { className: "modal-dialog modal-lg modal-dialog-centered", children: /* @__PURE__ */ jsxDEV("div", { className: "modal-content shadow", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "modal-header", children: [
        /* @__PURE__ */ jsxDEV("h5", { className: "modal-title", children: title }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
          lineNumber: 74,
          columnNumber: 15
        }, this),
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn-close", "aria-label": str("close"), onClick: onClose }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
          lineNumber: 75,
          columnNumber: 15
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
        lineNumber: 73,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "modal-body", children: /* @__PURE__ */ jsxDEV(Step, { cache, patch: patchCache, showErrors }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
        lineNumber: 78,
        columnNumber: 15
      }, this) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
        lineNumber: 77,
        columnNumber: 13
      }, this),
      !cache.submitted && /* @__PURE__ */ jsxDEV("div", { className: "modal-footer justify-content-between", children: [
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary", onClick: back, disabled: isFirst, children: str("wizard_shell_back") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
          lineNumber: 82,
          columnNumber: 17
        }, this),
        !isLast && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary text-white", onClick: next, children: str("wizard_shell_next") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
          lineNumber: 86,
          columnNumber: 19
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
        lineNumber: 81,
        columnNumber: 15
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
      lineNumber: 72,
      columnNumber: 11
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
      lineNumber: 71,
      columnNumber: 9
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
      lineNumber: 70,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "modal-backdrop fade show" }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
      lineNumber: 95,
      columnNumber: 7
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/Wizard.tsx",
    lineNumber: 69,
    columnNumber: 5
  }, this);
}
__name(Wizard, "Wizard");
export {
  Wizard as default
};
//# sourceMappingURL=Wizard.dev.js.map
