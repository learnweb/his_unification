var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * Wizard step: the user refines the details of the course to import. Title, short title, semester and description are pre-filled
 * from the selected course and can be edited.
 *
 * @module     lsf_unification/wizard/steps/ImportDetailsStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { fetchCategories } from "../../../services/csm";
import { str } from "../../../lang";
function missingFields(cache) {
  const blank = /* @__PURE__ */ __name((value) => (value ?? "").trim() === "", "blank");
  return {
    title: blank(cache.course?.title),
    shorttitle: blank(cache.course?.shorttitle),
    semester: blank(cache.course?.semester),
    category: cache.category === null
  };
}
__name(missingFields, "missingFields");
const validate = /* @__PURE__ */ __name((cache) => !Object.values(missingFields(cache)).some(Boolean), "validate");
function ImportDetailsStep({ cache, patch, showErrors }) {
  const [categories, setCategories] = useState([]);
  const course = cache.course;
  const missing = missingFields(cache);
  const invalid = /* @__PURE__ */ __name((isMissing) => showErrors && isMissing ? " is-invalid" : "", "invalid");
  useEffect(() => {
    fetchCategories().then((categories2) => setCategories(categories2));
  }, []);
  const patchCourse = /* @__PURE__ */ __name((partial) => course && patch({ course: { ...course, ...partial } }), "patchCourse");
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("h5", { className: "mb-1", children: str("coursedetails_title") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
      lineNumber: 62,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: str("coursedetails_text") }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
      lineNumber: 63,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "mb-3", children: [
      /* @__PURE__ */ jsxDEV("label", { className: "form-label", children: [
        str("title"),
        /* @__PURE__ */ jsxDEV("span", { className: "text-danger", children: "*" }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 66,
          columnNumber: 53
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
        lineNumber: 66,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "text",
          className: `form-control${invalid(missing.title)}`,
          value: course?.title ?? "",
          onChange: (e) => patchCourse({ title: e.target.value })
        },
        void 0,
        false,
        {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 67,
          columnNumber: 9
        },
        this
      ),
      /* @__PURE__ */ jsxDEV("div", { className: "invalid-feedback", children: str("title_validate") }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
        lineNumber: 69,
        columnNumber: 9
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
      lineNumber: 65,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "row", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "col-md-6 mb-3", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "form-label", children: [
          str("shorttitle"),
          /* @__PURE__ */ jsxDEV("span", { className: "text-danger", children: "*" }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
            lineNumber: 74,
            columnNumber: 60
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 74,
          columnNumber: 11
        }, this),
        /* @__PURE__ */ jsxDEV(
          "input",
          {
            type: "text",
            className: `form-control${invalid(missing.shorttitle)}`,
            value: course?.shorttitle ?? "",
            onChange: (e) => patchCourse({ shorttitle: e.target.value })
          },
          void 0,
          false,
          {
            fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
            lineNumber: 75,
            columnNumber: 11
          },
          this
        ),
        /* @__PURE__ */ jsxDEV("div", { className: "invalid-feedback", children: str("shorttitle_validate") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 77,
          columnNumber: 11
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
        lineNumber: 73,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "col-md-6 mb-3", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "form-label", children: [
          str("semester"),
          /* @__PURE__ */ jsxDEV("span", { className: "text-danger", children: "*" }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
            lineNumber: 80,
            columnNumber: 58
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 80,
          columnNumber: 11
        }, this),
        /* @__PURE__ */ jsxDEV(
          "input",
          {
            type: "text",
            className: `form-control${invalid(missing.semester)}`,
            value: course?.semester ?? "",
            onChange: (e) => patchCourse({ semester: e.target.value })
          },
          void 0,
          false,
          {
            fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
            lineNumber: 81,
            columnNumber: 11
          },
          this
        ),
        /* @__PURE__ */ jsxDEV("div", { className: "invalid-feedback", children: str("semester_validate") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 83,
          columnNumber: 11
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
        lineNumber: 79,
        columnNumber: 9
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
      lineNumber: 72,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "mb-3", children: [
      /* @__PURE__ */ jsxDEV("label", { className: "form-label", children: str("description") }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
        lineNumber: 88,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV(
        "textarea",
        {
          className: "form-control",
          rows: 4,
          value: course?.description ?? "",
          onChange: (e) => patchCourse({ description: e.target.value })
        },
        void 0,
        false,
        {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 89,
          columnNumber: 9
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
      lineNumber: 87,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "mb-3", children: [
      /* @__PURE__ */ jsxDEV("label", { className: "form-label", children: [
        str("category"),
        /* @__PURE__ */ jsxDEV("span", { className: "text-danger", children: "*" }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 94,
          columnNumber: 56
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
        lineNumber: 94,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV(
        "select",
        {
          className: `form-select${invalid(missing.category)}`,
          value: cache.category?.id ?? "",
          onChange: (e) => patch({ category: categories.find((c) => c.id === Number(e.target.value)) ?? null }),
          children: [
            /* @__PURE__ */ jsxDEV("option", { value: "", disabled: true, children: str("category_choose") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
              lineNumber: 97,
              columnNumber: 11
            }, this),
            categories.map((category) => /* @__PURE__ */ jsxDEV("option", { value: category.id, children: category.name }, category.id, false, {
              fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
              lineNumber: 99,
              columnNumber: 13
            }, this))
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
          lineNumber: 95,
          columnNumber: 9
        },
        this
      ),
      /* @__PURE__ */ jsxDEV("div", { className: "invalid-feedback", children: str("category_validate") }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
        lineNumber: 102,
        columnNumber: 9
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
      lineNumber: 93,
      columnNumber: 7
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/wizard/steps/import/ImportDetailsStep.tsx",
    lineNumber: 61,
    columnNumber: 5
  }, this);
}
__name(ImportDetailsStep, "ImportDetailsStep");
export {
  ImportDetailsStep as default,
  validate
};
//# sourceMappingURL=ImportDetailsStep.dev.js.map
