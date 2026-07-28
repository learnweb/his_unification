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
 * The wizard's data model and step map: what the wizard collects, which steps exist, in what order the steps are.
 * This file only wires things together, the steps themselves hold their own fetching, markup and rules.
 *
 * @module     lsf_unification/wizard/steps/config
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import type {ReactElement} from "react";
import type {Category, Course, Teacher} from "../../services/csm";
import ChooseActionStep from "./ChooseActionStep";
import ImportCoursesStep, {validate as validateImportCourses} from "./import/ImportCoursesStep";
import ImportDetailsStep, {validate as validateImportDetails} from "./import/ImportDetailsStep";
import ImportSummaryStep from "./import/ImportSummaryStep";
import RequestTeacherStep, {validate as validateRequestTeacher} from "./request/RequestTeacherStep";
import RequestCoursesStep, {validate as validateRequestCourses} from "./request/RequestCoursesStep";
import RequestSummaryStep from "./request/RequestSummaryStep";

export type Branch = "import" | "request";

/** Every step the wizard can show. */
export type StepId =
    | "choose"
    | "importCourses"
    | "importDetails"
    | "importSummary"
    | "requestTeacher"
    | "requestCourses"
    | "requestSummary";

/**
 * Everything the wizard collects, filled in step by step. This is what gets submitted:
 * branch - if it's an import or request
 * course - which course gets imported/requested. Can get edited in the import flow.
 * teacher - only used in requests. The teacher that is being request on behalf to.
 * category - only used in imports. Saves the category the course gets assigned to.
 * submitted - only used for rendering purposes
 */
export type WizardCache = {
    branch: Branch | null;
    course: Course | null;
    teacher: Teacher | null;
    category: Category | null;
    submitted: boolean;
};

/**
 * What every step gets: the cache to read, a way to add to it, and whether to show errors.
 * showError is set once the user pressed Next on an incomplete step, so it marks what is missing.
 */
export type StepProps = {
    cache: WizardCache;
    patch: (partial: Partial<WizardCache>) => void;
    showErrors: boolean;
};

type Step = {
    component: (props: StepProps) => ReactElement;
    /** Blocks Next while it returns false. Each step exports its own rule next to the
     * fields it marks; steps with nothing to fill in leave this out. */
    validate?: (cache: WizardCache) => boolean;
};

/** Every step: what renders it, and what it requires before the user may move on. */
export const STEPS: Record<StepId, Step> = {
    choose: {component: ChooseActionStep},
    importCourses: {component: ImportCoursesStep, validate: validateImportCourses},
    importDetails: {component: ImportDetailsStep, validate: validateImportDetails},
    importSummary: {component: ImportSummaryStep},
    requestTeacher: {component: RequestTeacherStep, validate: validateRequestTeacher},
    requestCourses: {component: RequestCoursesStep, validate: validateRequestCourses},
    requestSummary: {component: RequestSummaryStep},
};

/** The ordered steps that follow the "choose" step, per branch. */
const SEQUENCES: Record<Branch, StepId[]> = {
    "import": ["importCourses", "importDetails", "importSummary"],
    request: ["requestTeacher", "requestCourses", "requestSummary"],
};

/** Builds the full step sequence for the current branch. Before a branch is chosen the wizard only knows about the "choose" step.*/
export function sequenceFor(branch: Branch | null): StepId[] {
    return branch === null ? ["choose"] : ["choose", ...SEQUENCES[branch]];
}

/**
 * Whether the user may leave the given step with the cache as it currently is. The shell calls this when Next is pressed.
 * false keeps the wizard on the step and makes it mark what is missing.
 */
export function canAdvance(stepId: StepId, cache: WizardCache): boolean {
    return STEPS[stepId].validate?.(cache) ?? true;
}
