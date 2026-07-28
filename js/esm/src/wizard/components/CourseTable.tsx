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
 * A reusable html component that lists cms courses to pick one from. Both import and request use it to show courses.
 *
 * @module     lsf_unification/wizard/components/CourseTable
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import type {Course} from "../../services/csm";
import {str} from "../../lang";

/**
 * The data that gets shown: the courses to list, the selected course and a function that updates the course in the cache.
 */
type Props = {
  courses: Course[];
  selected: Course | null;
  onSelect: (course: Course) => void;
};

/**
 * Formats a unix timestamp into a readable date.
 */
const formatCreated = (created: number): string =>
  new Date(created * 1000).toLocaleDateString("de-DE",
    {timeZone: "Europe/Berlin", day: "2-digit", month: "2-digit", year: "numeric"});

export default function CourseTable({courses, selected, onSelect}: Props) {
  if (courses.length === 0) {
    return <p className="text-center text-muted py-4 mb-0"><em>{str("nocoursesfound")}</em></p>;
  }

  return (
    <div className="table-responsive">
      <table className="table table-hover table-borderless align-middle mb-0">
        <thead>
          <tr>
            <th scope="col">{str("title")}</th>
            <th scope="col">{str("semester")}</th>
            <th scope="col">{str("created")}</th>
          </tr>
        </thead>
        <tbody>
          {courses.map((course) => {
            const isSelected = selected?.id === course.id;
            return (
              <tr key={course.id} className={isSelected ? "table-primary" : ""}
                  style={{cursor: "pointer"}} onClick={() => onSelect(course)}>
                <td className="fw-semibold">
                  {isSelected && <i className="fa-solid fa-check text-primary me-2"/>}
                  {course.title}
                </td>
                <td>{course.semester}</td>
                <td>{formatCreated(course.created)}</td>
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}
