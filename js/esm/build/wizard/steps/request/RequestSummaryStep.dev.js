var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * Wizard step: shows the request that is about to be sent and submits it. Last step of the request branch
 *
 * @module     lsf_unification/wizard/steps/RequestSummaryStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { submitCourseRequest } from "../../../services/csm";
import SummaryPanel from "../../components/SummaryPanel";
import { str } from "../../../lang";
function RequestSummaryStep({ cache, patch }) {
  const teacher = cache.teacher;
  const rows = [
    { label: str("teacher"), value: teacher ? `${teacher.firstname} ${teacher.lastname}` : "" },
    { label: str("course"), value: cache.course?.title ?? "" },
    { label: str("semester"), value: cache.course?.semester ?? "" }
  ];
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("h5", { className: "mb-1", children: str("summary_request_title") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestSummaryStep.tsx",
      lineNumber: 39,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: str("summary_request_text") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestSummaryStep.tsx",
      lineNumber: 40,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV(
      SummaryPanel,
      {
        rows,
        submitted: cache.submitted,
        onSubmitted: () => patch({ submitted: true }),
        submit: () => submitCourseRequest(cache),
        submitLabel: str("submit_request_label"),
        successText: str("submit_request_success"),
        errorText: str("submit_request_error")
      },
      void 0,
      false,
      {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestSummaryStep.tsx",
        lineNumber: 42,
        columnNumber: 7
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestSummaryStep.tsx",
    lineNumber: 38,
    columnNumber: 5
  }, this);
}
__name(RequestSummaryStep, "RequestSummaryStep");
export {
  RequestSummaryStep as default
};
//# sourceMappingURL=RequestSummaryStep.dev.js.map
