var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * A reusable html component that lists cms courses to pick one from. Both import and request use it to show courses.
 *
 * @module     lsf_unification/wizard/components/CourseTable
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { str } from "../../lang";
const formatCreated = /* @__PURE__ */ __name((created) => new Date(created * 1e3).toLocaleDateString(
  "de-DE",
  { timeZone: "Europe/Berlin", day: "2-digit", month: "2-digit", year: "numeric" }
), "formatCreated");
function CourseTable({ courses, selected, onSelect }) {
  if (courses.length === 0) {
    return /* @__PURE__ */ jsxDEV("p", { className: "text-center text-muted py-4 mb-0", children: /* @__PURE__ */ jsxDEV("em", { children: str("nocoursesfound") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
      lineNumber: 45,
      columnNumber: 60
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
      lineNumber: 45,
      columnNumber: 12
    }, this);
  }
  return /* @__PURE__ */ jsxDEV("div", { className: "table-responsive", children: /* @__PURE__ */ jsxDEV("table", { className: "table table-hover table-borderless align-middle mb-0", children: [
    /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
      /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("title") }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
        lineNumber: 53,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("semester") }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
        lineNumber: 54,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("created") }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
        lineNumber: 55,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
      lineNumber: 52,
      columnNumber: 11
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
      lineNumber: 51,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("tbody", { children: courses.map((course) => {
      const isSelected = selected?.id === course.id;
      return /* @__PURE__ */ jsxDEV(
        "tr",
        {
          className: isSelected ? "table-primary" : "",
          style: { cursor: "pointer" },
          onClick: () => onSelect(course),
          children: [
            /* @__PURE__ */ jsxDEV("td", { className: "fw-semibold", children: [
              isSelected && /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-check text-primary me-2" }, void 0, false, {
                fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
                lineNumber: 65,
                columnNumber: 34
              }, this),
              course.title
            ] }, void 0, true, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
              lineNumber: 64,
              columnNumber: 17
            }, this),
            /* @__PURE__ */ jsxDEV("td", { children: course.semester }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
              lineNumber: 68,
              columnNumber: 17
            }, this),
            /* @__PURE__ */ jsxDEV("td", { children: formatCreated(course.created) }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
              lineNumber: 69,
              columnNumber: 17
            }, this)
          ]
        },
        course.id,
        true,
        {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
          lineNumber: 62,
          columnNumber: 15
        },
        this
      );
    }) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
      lineNumber: 58,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
    lineNumber: 50,
    columnNumber: 7
  }, this) }, void 0, false, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/components/CourseTable.tsx",
    lineNumber: 49,
    columnNumber: 5
  }, this);
}
__name(CourseTable, "CourseTable");
export {
  CourseTable as default
};
//# sourceMappingURL=CourseTable.dev.js.map
