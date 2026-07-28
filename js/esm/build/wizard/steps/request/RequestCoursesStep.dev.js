var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * Wizard step: the user picks the course they want to request, out of the courses of the teacher chosen in the step before.
 *
 * @module     lsf_unification/wizard/steps/RequestCoursesStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { fetchTeacherCourses } from "../../../services/csm";
import CourseTable from "../../components/CourseTable";
import { str } from "../../../lang";
const validate = /* @__PURE__ */ __name((cache) => cache.course !== null, "validate");
function RequestCoursesStep({ cache, patch, showErrors }) {
  const [courses, setCourses] = useState([]);
  const username = cache.teacher?.username ?? null;
  useEffect(() => {
    if (username) {
      fetchTeacherCourses(username).then(setCourses);
    }
  }, [username]);
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("h5", { className: "mb-1", children: str("courseselect") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestCoursesStep.tsx",
      lineNumber: 44,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: str("courseselect_request") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestCoursesStep.tsx",
      lineNumber: 45,
      columnNumber: 7
    }, this),
    showErrors && !validate(cache) && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-danger py-2", children: str("courseselect_validate") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestCoursesStep.tsx",
      lineNumber: 48,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(CourseTable, { courses, selected: cache.course, onSelect: (course) => patch({ course }) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestCoursesStep.tsx",
      lineNumber: 51,
      columnNumber: 7
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestCoursesStep.tsx",
    lineNumber: 43,
    columnNumber: 5
  }, this);
}
__name(RequestCoursesStep, "RequestCoursesStep");
export {
  RequestCoursesStep as default,
  validate
};
//# sourceMappingURL=RequestCoursesStep.dev.js.map
