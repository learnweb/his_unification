import{str as s}from"../../lang";import{jsx as e,jsxs as o}from"react/jsx-runtime";/**
 * A reusable html component that lists cms courses to pick one from. Both import and request use it to show courses.
 *
 * @module     lsf_unification/wizard/components/CourseTable
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const i=r=>new Date(r*1e3).toLocaleDateString("de-DE",{timeZone:"Europe/Berlin",day:"2-digit",month:"2-digit",year:"numeric"});function m({courses:r,selected:l,onSelect:d}){return r.length===0?e("p",{className:"text-center text-muted py-4 mb-0",children:e("em",{children:s("nocoursesfound")})}):e("div",{className:"table-responsive",children:o("table",{className:"table table-hover table-borderless align-middle mb-0",children:[e("thead",{children:o("tr",{children:[e("th",{scope:"col",children:s("title")}),e("th",{scope:"col",children:s("semester")}),e("th",{scope:"col",children:s("created")})]})}),e("tbody",{children:r.map(t=>{const a=l?.id===t.id;return o("tr",{className:a?"table-primary":"",style:{cursor:"pointer"},onClick:()=>d(t),children:[o("td",{className:"fw-semibold",children:[a&&e("i",{className:"fa-solid fa-check text-primary me-2"}),t.title]}),e("td",{children:t.semester}),e("td",{children:i(t.created)})]},t.id)})})]})})}export{m as default};
