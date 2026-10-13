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
 * Wizard step: shows the course that is about to be imported and submits it. Last step
 * of the import branch; after submitting only the confirmation is shown.
 *
 * @module     lsf_unification/wizard/steps/ImportSummaryStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {submitCourseImport} from "../../../services/csm";
import SummaryPanel from "../../components/SummaryPanel";
import type {StepProps} from "../config";
import {str} from "../../../lang";

export default function ImportSummaryStep({cache, patch}: StepProps) {
  const rows = [
    {label: str("title"), value: cache.course?.title ?? ""},
    {label: str("shorttitle"), value: cache.course?.shorttitle ?? ""},
    {label: str("semester"), value: cache.course?.semester ?? ""},
    {label: str("description"), value: cache.course?.description ?? ""},
    {label: str("category"), value: cache.category?.name ?? ""},
  ];

  return (
    <div>
      <h5 className="mb-1">{str("summary_import_title")}</h5>
      <p className="text-muted">{str("summary_import_text")}</p>

      <SummaryPanel
        rows={rows}
        submitted={cache.submitted}
        onSubmitted={() => patch({submitted: true})}
        submit={() => submitCourseImport(cache)}
        submitLabel={str("submit_import_label")}
        successText={str("submit_import_success")}
        errorText={str("submit_import_error")}/>
    </div>
  );
}
