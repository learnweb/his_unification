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
 * React component that shows the request manager.
 * @module     lsf_unification/RequestManager
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useState} from "react";
import {fetchRequests, Request, RequestAction, submitRequestAction} from "../services/csm";
import RequestActions from "./RequestActions";
import {str} from "../lang";

const formatDate = (date: number): string =>
    new Date(date * 1000).toLocaleDateString("de-DE",
        {timeZone: "Europe/Berlin", day: "2-digit", month: "2-digit", year: "numeric"});

export default function RequestManager({onClose} : {onClose: () => void}) {
  const [requests, setRequests] = useState<Request[]>([]);

  const handleDecide = async(action: RequestAction) => {
    await submitRequestAction(action);
    setRequests(await fetchRequests());
  };

  useEffect(() => {
    fetchRequests().then(setRequests);

    const onKey = (e: KeyboardEvent) => e.key === "Escape" && onClose();
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [onClose]);

  return (
    <>
      <div className="modal fade show d-block" tabIndex={-1} role="dialog" aria-modal="true">
        <div className="modal-dialog modal-lg modal-dialog-centered">
          <div className="modal-content shadow">
            <div className="modal-header">
              <h5 className="modal-title">{str("request_manager_title")}</h5>
              <button type="button" className="btn-close" aria-label={str("close")} onClick={onClose}/>
            </div>
            <div className="modal-body">
              <div>
                <p className="text-muted">{str("request_manager_text")}</p>
                <div className="table-responsive">
                  <table className="table table-hover table-borderless align-middle mb-0">
                    <thead>
                      <tr>
                        <th scope="col">{str("course")}</th>
                        <th scope="col">{str("user")}</th>
                        <th scope="col">{str("created")}</th>
                        <th scope="col">{str("action")}</th>
                      </tr>
                    </thead>
                    <tbody>
                    {requests.map((request) => {
                      return (
                        <tr key={request.id}>
                          <td className="fw-semibold">
                            {request.title}
                          </td>
                          <td>{request.requester}</td>
                          <td>{formatDate(request.created)}</td>
                          <td><RequestActions request={request} onDecide={handleDecide}/></td>
                        </tr>
                      );
                    })}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="modal-backdrop fade show"/>
    </>
  );
}
