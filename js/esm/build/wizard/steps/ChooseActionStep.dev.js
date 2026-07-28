var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * Wizard step: the user chooses whether to import an existing course or to request a new course in the name of a teacher.
 * This choice decides which branch of steps the wizard shows afterward.
 *
 * @module     lsf_unification/wizard/steps/ChooseActionStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { str } from "../../lang";
function ChooseActionStep({ cache, patch }) {
  const selected = cache.branch;
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("h5", { className: "mb-1", children: str("choose_action") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
      lineNumber: 33,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "row g-3", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "col-md-6", children: /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "card h-100 w-100 text-center p-4 border-2" + (selected === "import" ? " border-primary" : ""),
          onClick: () => patch({ branch: "import" }),
          children: [
            /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-file-import fa-2x text-primary mb-3" }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
              lineNumber: 41,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("span", { className: "fw-semibold", children: str("submit_import_label") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
              lineNumber: 42,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("span", { className: "small text-muted mt-1", children: str("choose_action_import") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
              lineNumber: 43,
              columnNumber: 25
            }, this)
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
          lineNumber: 37,
          columnNumber: 21
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
        lineNumber: 36,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "col-md-6", children: /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "card h-100 w-100 text-center p-4 border-2" + (selected === "request" ? " border-primary" : ""),
          onClick: () => patch({ branch: "request" }),
          children: [
            /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-user-tie fa-2x text-primary mb-3" }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
              lineNumber: 54,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("span", { className: "fw-semibold", children: str("submit_request_label") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
              lineNumber: 55,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("span", { className: "small text-muted mt-1", children: str("choose_action_request") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
              lineNumber: 56,
              columnNumber: 25
            }, this)
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
          lineNumber: 50,
          columnNumber: 21
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
        lineNumber: 49,
        columnNumber: 17
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
      lineNumber: 35,
      columnNumber: 13
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/ChooseActionStep.tsx",
    lineNumber: 32,
    columnNumber: 9
  }, this);
}
__name(ChooseActionStep, "ChooseActionStep");
export {
  ChooseActionStep as default
};
//# sourceMappingURL=ChooseActionStep.dev.js.map
