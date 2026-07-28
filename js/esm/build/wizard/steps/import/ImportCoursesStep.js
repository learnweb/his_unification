import{useEffect as p,useState as l}from"react";import{fetchOwnCourses as m}from"../../../services/csm";import i from"../../components/CourseTable";import{str as r}from"../../../lang";import{jsx as o,jsxs as n}from"react/jsx-runtime";/**
 * Wizard step: the user picks one of their own cms courses to import into Moodle.
 *
 * @module     lsf_unification/wizard/steps/ImportCoursesStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const d=e=>e.course!==null;function f({cache:e,patch:s,showErrors:t}){const[c,a]=l([]);return p(()=>{m().then(a)},[]),n("div",{children:[o("h5",{className:"mb-1",children:r("courseselect")}),o("p",{className:"text-muted",children:r("courseselect_import")}),t&&!d(e)&&o("div",{className:"alert alert-danger py-2",children:r("courseselect_validate")}),o(i,{courses:c,selected:e.course,onSelect:u=>s({course:u})})]})}export{f as default,d as validate};
