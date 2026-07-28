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
 * Wizard step: shows the request that is about to be sent and submits it. Last step of the request branch
 *
 * @module     lsf_unification/wizard/steps/RequestSummaryStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {submitCourseRequest} from "../../../services/csm";
import SummaryPanel from "../../components/SummaryPanel";
import type {StepProps} from "../config";
import {str} from "../../../lang";

export default function RequestSummaryStep({cache, patch}: StepProps) {
  const teacher = cache.teacher;
  const rows = [
    {label: str("teacher"), value: teacher ? `${teacher.firstname} ${teacher.lastname}` : ""},
    {label: str("course"), value: cache.course?.title ?? ""},
    {label: str("semester"), value: cache.course?.semester ?? ""},
  ];

  return (
    <div>
      <h5 className="mb-1">{str("summary_request_title")}</h5>
      <p className="text-muted">{str("summary_request_text")}</p>

      <SummaryPanel
        rows={rows}
        submitted={cache.submitted}
        onSubmitted={() => patch({submitted: true})}
        submit={() => submitCourseRequest(cache)}
        submitLabel={str("submit_request_label")}
        successText={str("submit_request_success")}
        errorText={str("submit_request_error")}/>
    </div>
  );
}
