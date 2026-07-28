var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * The wizard's data model and step map: what the wizard collects, which steps exist, in what order the steps are.
 * This file only wires things together, the steps themselves hold their own fetching, markup and rules.
 *
 * @module     lsf_unification/wizard/steps/config
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import ChooseActionStep from "./ChooseActionStep";
import ImportCoursesStep, { validate as validateImportCourses } from "./import/ImportCoursesStep";
import ImportDetailsStep, { validate as validateImportDetails } from "./import/ImportDetailsStep";
import ImportSummaryStep from "./import/ImportSummaryStep";
import RequestTeacherStep, { validate as validateRequestTeacher } from "./request/RequestTeacherStep";
import RequestCoursesStep, { validate as validateRequestCourses } from "./request/RequestCoursesStep";
import RequestSummaryStep from "./request/RequestSummaryStep";
const STEPS = {
  choose: { component: ChooseActionStep },
  importCourses: { component: ImportCoursesStep, validate: validateImportCourses },
  importDetails: { component: ImportDetailsStep, validate: validateImportDetails },
  importSummary: { component: ImportSummaryStep },
  requestTeacher: { component: RequestTeacherStep, validate: validateRequestTeacher },
  requestCourses: { component: RequestCoursesStep, validate: validateRequestCourses },
  requestSummary: { component: RequestSummaryStep }
};
const SEQUENCES = {
  "import": ["importCourses", "importDetails", "importSummary"],
  request: ["requestTeacher", "requestCourses", "requestSummary"]
};
function sequenceFor(branch) {
  return branch === null ? ["choose"] : ["choose", ...SEQUENCES[branch]];
}
__name(sequenceFor, "sequenceFor");
function canAdvance(stepId, cache) {
  return STEPS[stepId].validate?.(cache) ?? true;
}
__name(canAdvance, "canAdvance");
export {
  STEPS,
  canAdvance,
  sequenceFor
};
//# sourceMappingURL=config.dev.js.map
