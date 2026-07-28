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
 * Wizard step: the user picks the course they want to request, out of the courses of the teacher chosen in the step before.
 *
 * @module     lsf_unification/wizard/steps/RequestCoursesStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useState} from "react";
import {fetchTeacherCourses, Course} from "../../../services/csm";
import CourseTable from "../../components/CourseTable";
import type {StepProps, WizardCache} from "../config";
import {str} from "../../../lang";

export const validate = (cache: WizardCache): boolean => cache.course !== null;

export default function RequestCoursesStep({cache, patch, showErrors}: StepProps) {
  const [courses, setCourses] = useState<Course[]>([]);
  const username = cache.teacher?.username ?? null;

  useEffect(() => {
    if (username) {
      fetchTeacherCourses(username).then(setCourses);
    }
  }, [username]);

  return (
    <div>
      <h5 className="mb-1">{str("courseselect")}</h5>
      <p className="text-muted">{str("courseselect_request")}</p>

      {showErrors && !validate(cache) && (
        <div className="alert alert-danger py-2">{str("courseselect_validate")}</div>
      )}

      <CourseTable courses={courses} selected={cache.course} onSelect={(course) => patch({course})}/>
    </div>
  );
}
