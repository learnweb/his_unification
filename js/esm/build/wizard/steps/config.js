import r from"./ChooseActionStep";import o,{validate as a}from"./import/ImportCoursesStep";import p,{validate as s}from"./import/ImportDetailsStep";import m from"./import/ImportSummaryStep";import i,{validate as c}from"./request/RequestTeacherStep";import u,{validate as n}from"./request/RequestCoursesStep";import l from"./request/RequestSummaryStep";/**
 * The wizard's data model and step map: what the wizard collects, which steps exist, in what order the steps are.
 * This file only wires things together, the steps themselves hold their own fetching, markup and rules.
 *
 * @module     lsf_unification/wizard/steps/config
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const d={choose:{component:r},importCourses:{component:o,validate:a},importDetails:{component:p,validate:s},importSummary:{component:m},requestTeacher:{component:i,validate:c},requestCourses:{component:u,validate:n},requestSummary:{component:l}},S={import:["importCourses","importDetails","importSummary"],request:["requestTeacher","requestCourses","requestSummary"]};function R(e){return e===null?["choose"]:["choose",...S[e]]}function T(e,t){return d[e].validate?.(t)??!0}export{d as STEPS,T as canAdvance,R as sequenceFor};
