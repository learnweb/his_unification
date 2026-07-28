var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * Wizard step: the user picks the teacher whose course should be requested. A
 * search bar filters the list by username, first name or last name; clicking a
 * row selects that teacher.
 *
 * @module     lsf_unification/wizard/steps/RequestTeacherStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { fetchTeachers } from "../../../services/csm";
import { str } from "../../../lang";
const validate = /* @__PURE__ */ __name((cache) => cache.teacher !== null, "validate");
function RequestTeacherStep({ cache, patch, showErrors }) {
  const [teachers, setTeachers] = useState([]);
  const [query, setQuery] = useState("");
  const selected = cache.teacher;
  useEffect(() => {
    fetchTeachers().then(setTeachers);
  }, []);
  const filtered = teachers.filter(({ username, firstname, lastname }) => [username, firstname, lastname].some((field) => field.toLowerCase().includes(query.trim().toLowerCase())));
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("h5", { className: "mb-1", children: str("teachersearch") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 48,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: str("teachersearch_text") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 49,
      columnNumber: 7
    }, this),
    showErrors && selected === null && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-danger py-2", children: str("teacherselect") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 52,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "input-group mb-3", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "input-group-text", children: /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-magnifying-glass" }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
        lineNumber: 56,
        columnNumber: 44
      }, this) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
        lineNumber: 56,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "search",
          className: "form-control",
          placeholder: str("teachersearch_placeholder"),
          value: query,
          onChange: (e) => setQuery(e.target.value)
        },
        void 0,
        false,
        {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
          lineNumber: 57,
          columnNumber: 9
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 55,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "table-responsive", children: /* @__PURE__ */ jsxDEV("table", { className: "table table-hover table-borderless align-middle mb-0", children: [
      /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
        /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("name") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
          lineNumber: 65,
          columnNumber: 19
        }, this),
        /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("username") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
          lineNumber: 66,
          columnNumber: 19
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
        lineNumber: 64,
        columnNumber: 15
      }, this) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
        lineNumber: 63,
        columnNumber: 11
      }, this),
      /* @__PURE__ */ jsxDEV("tbody", { children: filtered.map((teacher) => {
        const isSelected = selected?.username === teacher.username;
        return /* @__PURE__ */ jsxDEV(
          "tr",
          {
            className: isSelected ? "table-primary" : "",
            style: { cursor: "pointer" },
            onClick: () => patch({ teacher }),
            children: [
              /* @__PURE__ */ jsxDEV("td", { className: "fw-semibold", children: [
                isSelected && /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-check text-primary me-2" }, void 0, false, {
                  fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
                  lineNumber: 79,
                  columnNumber: 36
                }, this),
                teacher.firstname,
                " ",
                teacher.lastname
              ] }, void 0, true, {
                fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
                lineNumber: 78,
                columnNumber: 19
              }, this),
              /* @__PURE__ */ jsxDEV("td", { children: teacher.username }, void 0, false, {
                fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
                lineNumber: 82,
                columnNumber: 19
              }, this)
            ]
          },
          teacher.username,
          true,
          {
            fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
            lineNumber: 73,
            columnNumber: 17
          },
          this
        );
      }) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
        lineNumber: 69,
        columnNumber: 11
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 62,
      columnNumber: 9
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 61,
      columnNumber: 7
    }, this),
    filtered.length === 0 && /* @__PURE__ */ jsxDEV("p", { className: "text-center text-muted py-4 mb-0", children: /* @__PURE__ */ jsxDEV("em", { children: str("teachernotfound") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 92,
      columnNumber: 11
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
      lineNumber: 91,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/request/RequestTeacherStep.tsx",
    lineNumber: 47,
    columnNumber: 5
  }, this);
}
__name(RequestTeacherStep, "RequestTeacherStep");
export {
  RequestTeacherStep as default,
  validate
};
//# sourceMappingURL=RequestTeacherStep.dev.js.map
