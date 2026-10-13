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
 * Wizard step: the user chooses whether to import an existing course or to request a new course in the name of a teacher.
 * This choice decides which branch of steps the wizard shows afterward.
 *
 * @module     lsf_unification/wizard/steps/ChooseActionStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import type {StepProps} from "./config";
import {str} from "../../lang";

export default function ChooseActionStep({cache, patch}: StepProps) {
    const selected = cache.branch;

    return (
        <div>
            <h5 className="mb-1">{str("choose_action")}</h5>

            <div className="row g-3">
                <div className="col-md-6">
                    <button type="button"
                            className={"card h-100 w-100 text-center p-4 border-2"
                                + (selected === "import" ? " border-primary" : "")}
                            onClick={() => patch({branch: "import"})}>
                        <i className="fa-solid fa-file-import fa-2x text-primary mb-3"/>
                        <span className="fw-semibold">{str("submit_import_label")}</span>
                        <span className="small text-muted mt-1">
                            {str("choose_action_import")}
                        </span>
                    </button>
                </div>

                <div className="col-md-6">
                    <button type="button"
                            className={"card h-100 w-100 text-center p-4 border-2"
                                + (selected === "request" ? " border-primary" : "")}
                            onClick={() => patch({branch: "request"})}>
                        <i className="fa-solid fa-user-tie fa-2x text-primary mb-3"/>
                        <span className="fw-semibold">{str("submit_request_label")}</span>
                        <span className="small text-muted mt-1">
                            {str("choose_action_request")}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    );
}
