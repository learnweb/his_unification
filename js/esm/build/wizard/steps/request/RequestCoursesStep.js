import{useEffect as m,useState as p}from"react";import{fetchTeacherCourses as i}from"../../../services/csm";import n from"../../components/CourseTable";import{str as t}from"../../../lang";import{jsx as s,jsxs as C}from"react/jsx-runtime";/**
 * Wizard step: the user picks the course they want to request, out of the courses of the teacher chosen in the step before.
 *
 * @module     lsf_unification/wizard/steps/RequestCoursesStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const d=e=>e.course!==null;function f({cache:e,patch:o,showErrors:u}){const[a,c]=p([]),r=e.teacher?.username??null;return m(()=>{r&&i(r).then(c)},[r]),C("div",{children:[s("h5",{className:"mb-1",children:t("courseselect")}),s("p",{className:"text-muted",children:t("courseselect_request")}),u&&!d(e)&&s("div",{className:"alert alert-danger py-2",children:t("courseselect_validate")}),s(n,{courses:a,selected:e.course,onSelect:l=>o({course:l})})]})}export{f as default,d as validate};
