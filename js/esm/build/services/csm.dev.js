var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import Fetch from "@moodle/lms/core/fetch";
const fetchDashboardCourses = /* @__PURE__ */ __name(async () => {
  const response = await Fetch.performGet("local_lsf_unification", "dashboard/dashboardcourses");
  return await response.json();
}, "fetchDashboardCourses");
const fetchOwnCourses = /* @__PURE__ */ __name(async () => {
  const response = await Fetch.performGet("local_lsf_unification", "dashboard/courses");
  return await response.json();
}, "fetchOwnCourses");
const fetchTeacherCourses = /* @__PURE__ */ __name(async (username) => {
  const response = await Fetch.performGet("local_lsf_unification", `dashboard/courses/${username}`);
  return await response.json();
}, "fetchTeacherCourses");
const fetchRequests = /* @__PURE__ */ __name(async () => {
  const response = await Fetch.performGet("local_lsf_unification", "dashboard/dashboardrequests");
  return await response.json();
}, "fetchRequests");
const submitCourseRequest = /* @__PURE__ */ __name(async (cache) => {
  const response = await Fetch.performPost("local_lsf_unification", "dashboard/request", { body: cache });
  const payload = await response.json();
  return payload.status;
}, "submitCourseRequest");
const submitCourseImport = /* @__PURE__ */ __name(async (cache) => {
  const response = await Fetch.performPost("local_lsf_unification", "dashboard/import", { body: cache });
  const payload = await response.json();
  return payload.status;
}, "submitCourseImport");
const submitRequestAction = /* @__PURE__ */ __name(async (action) => {
  const response = await Fetch.performPost("local_lsf_unification", "dashboard/requestaction", { body: action });
  const payload = await response.json();
  return payload.status;
}, "submitRequestAction");
const fetchTeachers = /* @__PURE__ */ __name(async () => {
  const response = await Fetch.performGet("local_lsf_unification", "dashboard/teachers");
  return await response.json();
}, "fetchTeachers");
const fetchCategories = /* @__PURE__ */ __name(async () => {
  const response = await Fetch.performGet("local_lsf_unification", "dashboard/categories");
  return await response.json();
}, "fetchCategories");
export {
  fetchCategories,
  fetchDashboardCourses,
  fetchOwnCourses,
  fetchRequests,
  fetchTeacherCourses,
  fetchTeachers,
  submitCourseImport,
  submitCourseRequest,
  submitRequestAction
};
//# sourceMappingURL=csm.dev.js.map
