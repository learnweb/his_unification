var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * Wizard step: the user picks one of their own cms courses to import into Moodle.
 *
 * @module     lsf_unification/wizard/steps/ImportCoursesStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { fetchOwnCourses } from "../../../services/csm";
import CourseTable from "../../components/CourseTable";
import { str } from "../../../lang";
const validate = /* @__PURE__ */ __name((cache) => cache.course !== null, "validate");
function ImportCoursesStep({ cache, patch, showErrors }) {
  const [courses, setCourses] = useState([]);
  useEffect(() => {
    fetchOwnCourses().then(setCourses);
  }, []);
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("h5", { className: "mb-1", children: str("courseselect") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportCoursesStep.tsx",
      lineNumber: 41,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: str("courseselect_import") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportCoursesStep.tsx",
      lineNumber: 42,
      columnNumber: 7
    }, this),
    showErrors && !validate(cache) && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-danger py-2", children: str("courseselect_validate") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportCoursesStep.tsx",
      lineNumber: 45,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(CourseTable, { courses, selected: cache.course, onSelect: (course) => patch({ course }) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportCoursesStep.tsx",
      lineNumber: 48,
      columnNumber: 7
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportCoursesStep.tsx",
    lineNumber: 40,
    columnNumber: 5
  }, this);
}
__name(ImportCoursesStep, "ImportCoursesStep");
export {
  ImportCoursesStep as default,
  validate
};
//# sourceMappingURL=ImportCoursesStep.dev.js.map
