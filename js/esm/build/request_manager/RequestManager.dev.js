var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * React component that shows the request manager.
 * @module     lsf_unification/RequestManager
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { fetchRequests, submitRequestAction } from "../services/csm";
import RequestActions from "./RequestActions";
import { str } from "../lang";
const formatDate = /* @__PURE__ */ __name((date) => new Date(date * 1e3).toLocaleDateString(
  "de-DE",
  { timeZone: "Europe/Berlin", day: "2-digit", month: "2-digit", year: "numeric" }
), "formatDate");
function RequestManager({ onClose }) {
  const [requests, setRequests] = useState([]);
  const handleDecide = /* @__PURE__ */ __name(async (action) => {
    await submitRequestAction(action);
    setRequests(await fetchRequests());
  }, "handleDecide");
  useEffect(() => {
    fetchRequests().then(setRequests);
    const onKey = /* @__PURE__ */ __name((e) => e.key === "Escape" && onClose(), "onKey");
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [onClose]);
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("div", { className: "modal fade show d-block", tabIndex: -1, role: "dialog", "aria-modal": "true", children: /* @__PURE__ */ jsxDEV("div", { className: "modal-dialog modal-lg modal-dialog-centered", children: /* @__PURE__ */ jsxDEV("div", { className: "modal-content shadow", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "modal-header", children: [
        /* @__PURE__ */ jsxDEV("h5", { className: "modal-title", children: str("request_manager_title") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
          lineNumber: 54,
          columnNumber: 15
        }, this),
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn-close", "aria-label": str("close"), onClick: onClose }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
          lineNumber: 55,
          columnNumber: 15
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
        lineNumber: 53,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "modal-body", children: /* @__PURE__ */ jsxDEV("div", { children: [
        /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: str("request_manager_text") }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
          lineNumber: 59,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: "table-responsive", children: /* @__PURE__ */ jsxDEV("table", { className: "table table-hover table-borderless align-middle mb-0", children: [
          /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("course") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
              lineNumber: 64,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("user") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
              lineNumber: 65,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("created") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
              lineNumber: 66,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: str("action") }, void 0, false, {
              fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
              lineNumber: 67,
              columnNumber: 25
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
            lineNumber: 63,
            columnNumber: 23
          }, this) }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
            lineNumber: 62,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("tbody", { children: requests.map((request) => {
            return /* @__PURE__ */ jsxDEV("tr", { children: [
              /* @__PURE__ */ jsxDEV("td", { className: "fw-semibold", children: request.title }, void 0, false, {
                fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
                lineNumber: 74,
                columnNumber: 27
              }, this),
              /* @__PURE__ */ jsxDEV("td", { children: request.requester }, void 0, false, {
                fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
                lineNumber: 77,
                columnNumber: 27
              }, this),
              /* @__PURE__ */ jsxDEV("td", { children: formatDate(request.created) }, void 0, false, {
                fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
                lineNumber: 78,
                columnNumber: 27
              }, this),
              /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV(RequestActions, { request, onDecide: handleDecide }, void 0, false, {
                fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
                lineNumber: 79,
                columnNumber: 31
              }, this) }, void 0, false, {
                fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
                lineNumber: 79,
                columnNumber: 27
              }, this)
            ] }, request.id, true, {
              fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
              lineNumber: 73,
              columnNumber: 25
            }, this);
          }) }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
            lineNumber: 70,
            columnNumber: 21
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
          lineNumber: 61,
          columnNumber: 19
        }, this) }, void 0, false, {
          fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
          lineNumber: 60,
          columnNumber: 17
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
        lineNumber: 58,
        columnNumber: 15
      }, this) }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
        lineNumber: 57,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
      lineNumber: 52,
      columnNumber: 11
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
      lineNumber: 51,
      columnNumber: 9
    }, this) }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
      lineNumber: 50,
      columnNumber: 7
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "modal-backdrop fade show" }, void 0, false, {
      fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
      lineNumber: 91,
      columnNumber: 7
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestManager.tsx",
    lineNumber: 49,
    columnNumber: 5
  }, this);
}
__name(RequestManager, "RequestManager");
export {
  RequestManager as default
};
//# sourceMappingURL=RequestManager.dev.js.map
