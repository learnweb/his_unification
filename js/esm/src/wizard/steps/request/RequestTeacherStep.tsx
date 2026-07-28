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
 * Wizard step: the user picks the teacher whose course should be requested. A
 * search bar filters the list by username, first name or last name; clicking a
 * row selects that teacher.
 *
 * @module     lsf_unification/wizard/steps/RequestTeacherStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useState} from "react";
import {fetchTeachers, Teacher} from "../../../services/csm";
import type {StepProps, WizardCache} from "../config";
import {str} from "../../../lang";

/** The next step loads this teacher's courses, so one has to be picked. */
export const validate = (cache: WizardCache): boolean => cache.teacher !== null;

export default function RequestTeacherStep({cache, patch, showErrors}: StepProps) {
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [query, setQuery] = useState("");
  const selected = cache.teacher;

  useEffect(() => {
    fetchTeachers().then(setTeachers);
  }, []);

  const filtered = teachers.filter(({username, firstname, lastname}) =>
    [username, firstname, lastname].some((field) => field.toLowerCase().includes(query.trim().toLowerCase())));

  return (
    <div>
      <h5 className="mb-1">{str("teachersearch")}</h5>
      <p className="text-muted">{str("teachersearch_text")}</p>

      {showErrors && selected === null && (
        <div className="alert alert-danger py-2">{str("teacherselect")}</div>
      )}

      <div className="input-group mb-3">
        <span className="input-group-text"><i className="fa-solid fa-magnifying-glass"/></span>
        <input type="search" className="form-control" placeholder={str("teachersearch_placeholder")}
            value={query} onChange={(e) => setQuery(e.target.value)}/>
      </div>

      <div className="table-responsive">
        <table className="table table-hover table-borderless align-middle mb-0">
          <thead>
              <tr>
                  <th scope="col">{str("name")}</th>
                  <th scope="col">{str("username")}</th>
              </tr>
          </thead>
          <tbody>
            {filtered.map((teacher) => {
              const isSelected = selected?.username === teacher.username;
              return (
                <tr key={teacher.username}
                  className={isSelected ? "table-primary" : ""}
                  style={{cursor: "pointer"}}
                  onClick={() => patch({teacher})}>

                  <td className="fw-semibold">
                    {isSelected && <i className="fa-solid fa-check text-primary me-2"/>}
                    {teacher.firstname} {teacher.lastname}
                  </td>
                  <td>{teacher.username}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>

      {filtered.length === 0 && (
        <p className="text-center text-muted py-4 mb-0">
          <em>{str("teachernotfound")}</em>
        </p>
      )}
    </div>
  );
}
