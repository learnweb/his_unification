var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * React component for the request manager actions
 * @module     lsf_unification/RequestActions
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useState } from "react";
import { str } from "../lang";
function RequestActions({ request, onDecide }) {
  const [confirm, setConfirm] = useState(null);
  const [busy, setBusy] = useState(false);
  const decide = /* @__PURE__ */ __name(async (action) => {
    setBusy(true);
    try {
      await onDecide({ id: request.id, action });
    } finally {
      setBusy(false);
    }
  }, "decide");
  if (busy) {
    return /* @__PURE__ */ jsxDEV(
      "span",
      {
        className: "spinner-border spinner-border-sm text-muted",
        role: "status",
        "aria-label": str("loading")
      },
      void 0,
      false,
      {
        fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
        lineNumber: 47,
        columnNumber: 12
      },
      this
    );
  }
  if (confirm) {
    return /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2 align-items-center", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "small text-muted", children: str(confirm === "approve" ? "confirm_approve" : "confirm_reject") }, void 0, false, {
        fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
        lineNumber: 54,
        columnNumber: 9
      }, this),
      /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          autoFocus: true,
          className: `btn btn-sm ${confirm === "approve" ? "btn-success" : "btn-danger"}`,
          onClick: () => decide(confirm),
          children: str("yes", true)
        },
        void 0,
        false,
        {
          fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
          lineNumber: 57,
          columnNumber: 11
        },
        this
      ),
      /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "btn btn-sm btn-outline-secondary",
          onClick: () => setConfirm(null),
          children: str("cancel", true)
        },
        void 0,
        false,
        {
          fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
          lineNumber: 62,
          columnNumber: 11
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
      lineNumber: 53,
      columnNumber: 9
    }, this);
  }
  return /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2", children: [
    /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "btn btn-sm btn-outline-success",
        onClick: () => setConfirm("approve"),
        children: [
          /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-check me-1" }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
            lineNumber: 74,
            columnNumber: 11
          }, this),
          str("approve", true)
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
        lineNumber: 72,
        columnNumber: 9
      },
      this
    ),
    /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "btn btn-sm btn-outline-danger",
        onClick: () => setConfirm("reject"),
        children: [
          /* @__PURE__ */ jsxDEV("i", { className: "fa-solid fa-xmark me-1" }, void 0, false, {
            fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
            lineNumber: 78,
            columnNumber: 11
          }, this),
          str("reject", true)
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
        lineNumber: 76,
        columnNumber: 9
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/lsf_unification/js/esm/src/request_manager/RequestActions.tsx",
    lineNumber: 71,
    columnNumber: 7
  }, this);
}
__name(RequestActions, "RequestActions");
export {
  RequestActions as default
};
//# sourceMappingURL=RequestActions.dev.js.map
