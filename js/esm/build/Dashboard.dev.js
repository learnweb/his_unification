var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * React component that shows the lsf unification dashboard
 * @module     lsf_unification/Dashboard
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { fetchDashboardCourses } from "./services/csm";
import Wizard from "./wizard/Wizard";
import { str } from "./lang";
const REQUEST_PENDING = 1;
const REQUEST_ACCEPTED = 2;
const REQUEST_DECLINED = 3;
const REQUEST_IMPORTED = 4;
const REQUEST_STATE_BADGES = {
  [REQUEST_PENDING]: {
    css: "text-bg-warning",
    icon: "fa-regular fa-hourglass-half",
    key: "dashboard_request_state_pending"
  },
  [REQUEST_ACCEPTED]: {
    css: "text-bg-success",
    icon: "fa-regular fa-circle-check",
    key: "dashboard_request_state_accepted"
  },
  [REQUEST_DECLINED]: {
    css: "text-bg-danger",
    icon: "fa-regular fa-circle-xmark",
    key: "dashboard_request_state_declined"
  },
  [REQUEST_IMPORTED]: {
    css: "text-bg-primary",
    icon: "fa-solid fa-file-import",
    key: "dashboard_request_state_imported"
  }
};
function Dashboard() {
  const [courses, setCourses] = useState([]);
  const [wizardOpen, setWizardOpen] = useState(false);
  const reloadCourses = /* @__PURE__ */ __name(() => fetchDashboardCourses().then(setCourses), "reloadCourses");
  useEffect(() => {
    reloadCourses();
  }, []);
  const requested = courses.filter((c) => c.requeststate !== 0);
  const created = courses.filter((c) => c.moodleid !== 0);
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2", children: [
      /* @__PURE__ */ jsxDEV("div", { children: [
        /* @__PURE__ */ jsxDEV("h3", { className: "mb-1", children: str("dashboard_title") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 74,
          columnNumber: 11
        }, this),
        /* @__PURE__ */ jsxDEV("p", { className: "text-muted mb-0", children: str("dashboard_title_text") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 75,
          columnNumber: 11
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
        lineNumber: 73,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary btn-md text-white", onClick: () => setWizardOpen(true), children: [
        /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-plus me-2" }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 78,
          columnNumber: 11
        }, this),
        str("dashboard_wizard_button")
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
        lineNumber: 77,
        columnNumber: 9
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
      lineNumber: 72,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "card mb-4 shadow-sm", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "card-header bg-white d-flex align-items-center", children: [
        /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-graduation-cap text-primary me-2" }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 84,
          columnNumber: 11
        }, this),
        /* @__PURE__ */ jsxDEV("h5", { className: "mb-0", children: str("dashboard_imported_courses") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 85,
          columnNumber: 11
        }, this),
        /* @__PURE__ */ jsxDEV("span", { className: "badge text-bg-secondary rounded-pill ms-2", children: created.length }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 86,
          columnNumber: 11
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
        lineNumber: 83,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "list-group list-group-flush", children: created.map((course) => /* @__PURE__ */ jsxDEV("div", { className: "list-group-item d-flex flex-wrap justify-content-between align-items-center py-3", children: [
        /* @__PURE__ */ jsxDEV("div", { className: "me-3", children: [
          /* @__PURE__ */ jsxDEV("div", { className: "fw-semibold", children: course.title }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
            lineNumber: 92,
            columnNumber: 17
          }, this),
          /* @__PURE__ */ jsxDEV("div", { className: "small text-muted", children: [
            /* @__PURE__ */ jsxDEV("i", { className: "fa-regular fa-calendar me-1" }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
              lineNumber: 94,
              columnNumber: 19
            }, this),
            course.semester
          ] }, void 0, true, {
            fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
            lineNumber: 93,
            columnNumber: 17
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 91,
          columnNumber: 15
        }, this),
        /* @__PURE__ */ jsxDEV("a", { href: course.moodleurl, className: "btn btn-primary btn-sm text-white", children: [
          /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-arrow-up-right-from-square me-1" }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
            lineNumber: 98,
            columnNumber: 17
          }, this),
          str("dashboard_to_course")
        ] }, void 0, true, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 97,
          columnNumber: 15
        }, this)
      ] }, course.id, true, {
        fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
        lineNumber: 90,
        columnNumber: 13
      }, this)) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
        lineNumber: 88,
        columnNumber: 9
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
      lineNumber: 82,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("hr", { className: "hr" }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
      lineNumber: 104,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "card mb-4 shadow-sm", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "card-header bg-white d-flex align-items-center", children: [
        /* @__PURE__ */ jsxDEV("i", { className: "fa-regular fa-clock text-primary me-2" }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 108,
          columnNumber: 11
        }, this),
        /* @__PURE__ */ jsxDEV("h5", { className: "mb-0", children: str("dashboard_requested_courses") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 109,
          columnNumber: 11
        }, this),
        /* @__PURE__ */ jsxDEV("span", { className: "badge text-bg-secondary rounded-pill ms-2", children: requested.length }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
          lineNumber: 110,
          columnNumber: 11
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
        lineNumber: 107,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "list-group list-group-flush", children: requested.map((course) => {
        const badge = REQUEST_STATE_BADGES[course.requeststate];
        return /* @__PURE__ */ jsxDEV(
          "div",
          {
            className: "list-group-item border-start-accent d-flex flex-wrap justify-content-between align-items-center py-3",
            children: [
              /* @__PURE__ */ jsxDEV("div", { className: "me-3", children: [
                /* @__PURE__ */ jsxDEV("div", { className: "fw-semibold", children: course.title }, void 0, false, {
                  fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
                  lineNumber: 119,
                  columnNumber: 17
                }, this),
                /* @__PURE__ */ jsxDEV("div", { className: "small text-muted", children: [
                  /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-user-tie me-1" }, void 0, false, {
                    fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
                    lineNumber: 121,
                    columnNumber: 19
                  }, this),
                  str("dashboard_request_teacher"),
                  " ",
                  course.teacher
                ] }, void 0, true, {
                  fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
                  lineNumber: 120,
                  columnNumber: 17
                }, this)
              ] }, void 0, true, {
                fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
                lineNumber: 118,
                columnNumber: 15
              }, this),
              badge && /* @__PURE__ */ jsxDEV("span", { className: `badge ${badge.css} rounded-pill text-white`, children: [
                /* @__PURE__ */ jsxDEV("i", { className: `${badge.icon} me-1` }, void 0, false, {
                  fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
                  lineNumber: 126,
                  columnNumber: 21
                }, this),
                str(badge.key)
              ] }, void 0, true, {
                fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
                lineNumber: 125,
                columnNumber: 17
              }, this)
            ]
          },
          course.id,
          true,
          {
            fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
            lineNumber: 116,
            columnNumber: 13
          },
          this
        );
      }) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
        lineNumber: 112,
        columnNumber: 9
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
      lineNumber: 106,
      columnNumber: 7
    }, this),
    wizardOpen && /* @__PURE__ */ jsxDEV(Wizard, { onClose: () => {
      setWizardOpen(false);
      reloadCourses();
    } }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
      lineNumber: 135,
      columnNumber: 22
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/Dashboard.tsx",
    lineNumber: 71,
    columnNumber: 5
  }, this);
}
__name(Dashboard, "Dashboard");
export {
  Dashboard as default
};
//# sourceMappingURL=Dashboard.dev.js.map
