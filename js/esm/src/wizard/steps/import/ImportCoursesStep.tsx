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
 * Wizard step: the user picks one of their own cms courses to import into Moodle.
 *
 * @module     lsf_unification/wizard/steps/ImportCoursesStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useState} from "react";
import {fetchOwnCourses, Course} from "../../../services/csm";
import CourseTable from "../../components/CourseTable";
import type {StepProps, WizardCache} from "../config";
import {str} from "../../../lang";

export const validate = (cache: WizardCache): boolean => cache.course !== null;

export default function ImportCoursesStep({cache, patch, showErrors}: StepProps) {
  const [courses, setCourses] = useState<Course[]>([]);

  useEffect(() => {
    fetchOwnCourses().then(setCourses);
  }, []);

  return (
    <div>
      <h5 className="mb-1">{str("courseselect")}</h5>
      <p className="text-muted">{str("courseselect_import")}</p>

      {showErrors && !validate(cache) && (
        <div className="alert alert-danger py-2">{str("courseselect_validate")}</div>
      )}

      <CourseTable courses={courses} selected={cache.course} onSelect={(course) => patch({course})}/>
    </div>
  );
}
