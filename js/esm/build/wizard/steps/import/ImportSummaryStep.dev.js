var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * Wizard step: shows the course that is about to be imported and submits it. Last step
 * of the import branch; after submitting only the confirmation is shown.
 *
 * @module     lsf_unification/wizard/steps/ImportSummaryStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { submitCourseImport } from "../../../services/csm";
import SummaryPanel from "../../components/SummaryPanel";
import { str } from "../../../lang";
function ImportSummaryStep({ cache, patch }) {
  const rows = [
    { label: str("title"), value: cache.course?.title ?? "" },
    { label: str("shorttitle"), value: cache.course?.shorttitle ?? "" },
    { label: str("semester"), value: cache.course?.semester ?? "" },
    { label: str("description"), value: cache.course?.description ?? "" },
    { label: str("category"), value: cache.category?.name ?? "" }
  ];
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("h5", { className: "mb-1", children: str("summary_import_title") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportSummaryStep.tsx",
      lineNumber: 41,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: str("summary_import_text") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportSummaryStep.tsx",
      lineNumber: 42,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV(
      SummaryPanel,
      {
        rows,
        submitted: cache.submitted,
        onSubmitted: () => patch({ submitted: true }),
        submit: () => submitCourseImport(cache),
        submitLabel: str("submit_import_label"),
        successText: str("submit_import_success"),
        errorText: str("submit_import_error")
      },
      void 0,
      false,
      {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportSummaryStep.tsx",
        lineNumber: 44,
        columnNumber: 7
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportSummaryStep.tsx",
    lineNumber: 40,
    columnNumber: 5
  }, this);
}
__name(ImportSummaryStep, "ImportSummaryStep");
export {
  ImportSummaryStep as default
};
//# sourceMappingURL=ImportSummaryStep.dev.js.map
