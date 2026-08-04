// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * React component for the request manager actions
 * @module     lsf_unification/RequestActions
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useState} from "react";
import {str} from "../lang";
import type {Request, RequestAction, RequestActionType} from "../services/csm";


type Props = {
  request: Request;
  onDecide: (action: RequestAction) => Promise<void>;
};

export default function RequestActions({request, onDecide}: Props) {
  const [confirm, setConfirm] = useState<RequestActionType | null>(null);
  const [busy, setBusy] = useState(false);

  const decide = async (action: RequestActionType) => {
    setBusy(true);
    try {
      await onDecide({id: request.id, action});
    } finally {
      setBusy(false);
    }
  };

  if (busy) {
    return <span className="spinner-border spinner-border-sm text-muted" role="status"
                 aria-label={str("loading")}/>;
  }

  if (confirm) {
    return (
        <div className="d-flex gap-2 align-items-center">
        <span className="small text-muted">
          {str(confirm === "approve" ? "confirm_approve" : "confirm_reject")}
        </span>
          <button type="button" autoFocus
                  className={`btn btn-sm ${confirm === "approve" ? "btn-success" : "btn-danger"}`}
                  onClick={() => decide(confirm)}>
            {str("yes", true)}
          </button>
          <button type="button" className="btn btn-sm btn-outline-secondary"
                  onClick={() => setConfirm(null)}>
            {str("cancel", true)}
          </button>
        </div>
    );
  }

  return (
      <div className="d-flex gap-2">
        <button type="button" className="btn btn-sm btn-outline-success"
                onClick={() => setConfirm("approve")}>
          <i className="fa-solid fa-check me-1"/>{str("approve", true)}
        </button>
        <button type="button" className="btn btn-sm btn-outline-danger"
                onClick={() => setConfirm("reject")}>
          <i className="fa-solid fa-xmark me-1"/>{str("reject", true)}
        </button>
      </div>
  );
}