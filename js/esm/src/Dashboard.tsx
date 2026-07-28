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
 * React component that shows the lsf unification dashboard
 * @module     lsf_unification/Dashboard
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useState} from "react";
import {Course, fetchDashboardCourses} from "./services/csm";
import Wizard from "./wizard/Wizard";
import {str} from "./lang";

/** Request states, mirroring the constants of local\models\course_request. */
const REQUEST_PENDING = 1;
const REQUEST_ACCEPTED = 2;
const REQUEST_DECLINED = 3;
const REQUEST_IMPORTED = 4;

/** How a request state is presented in the badge of a requested course. */
const REQUEST_STATE_BADGES: Record<number, {css: string, icon: string, key: string}> = {
    [REQUEST_PENDING]: {
        css: "text-bg-warning",
        icon: "fa-regular fa-hourglass-half",
        key: "dashboard_request_state_pending",
    },
    [REQUEST_ACCEPTED]: {
        css: "text-bg-success",
        icon: "fa-regular fa-circle-check",
        key: "dashboard_request_state_accepted",
    },
    [REQUEST_DECLINED]: {
        css: "text-bg-danger",
        icon: "fa-regular fa-circle-xmark",
        key: "dashboard_request_state_declined",
    },
    [REQUEST_IMPORTED]: {
        css: "text-bg-primary",
        icon: "fa-solid fa-file-import",
        key: "dashboard_request_state_imported",
    },
};

export default function Dashboard() {
    const [courses, setCourses] = useState<Course[]>([]);
    const [wizardOpen, setWizardOpen] = useState(false);
    const reloadCourses = () => fetchDashboardCourses().then(setCourses);

    useEffect(() => {
      reloadCourses();
    }, []);

    const requested = courses.filter((c) => c.requeststate !== 0);
    const created = courses.filter((c) => c.moodleid !== 0);

    return (
    <>
      <div className="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
          <h3 className="mb-1">{str("dashboard_title")}</h3>
          <p className="text-muted mb-0">{str("dashboard_title_text")}</p>
        </div>
        <button type="button" className="btn btn-primary btn-md text-white" onClick={() => setWizardOpen(true)}>
          <i className="fa-solid fa-plus me-2"/>{str("dashboard_wizard_button")}
        </button>
      </div>
      {/* Current courses. */}
      <div className="card mb-4 shadow-sm">
        <div className="card-header bg-white d-flex align-items-center">
          <i className="fa-solid fa-graduation-cap text-primary me-2"/>
          <h5 className="mb-0">{str("dashboard_imported_courses")}</h5>
          <span className="badge text-bg-secondary rounded-pill ms-2">{created.length}</span>
        </div>
        <div className="list-group list-group-flush">
          {created.map((course) => (
            <div key={course.id} className="list-group-item d-flex flex-wrap justify-content-between align-items-center py-3">
              <div className="me-3">
                <div className="fw-semibold">{course.title}</div>
                <div className="small text-muted">
                  <i className="fa-regular fa-calendar me-1"/>{course.semester}
                </div>
              </div>
              <a href={course.moodleurl} className="btn btn-primary btn-sm text-white">
                <i className="fa-solid fa-arrow-up-right-from-square me-1"/>{str("dashboard_to_course")}
              </a>
            </div>
          ))}
        </div>
      </div>
      <hr className="hr"/>
      {/* Pending courses*/}
      <div className="card mb-4 shadow-sm">
        <div className="card-header bg-white d-flex align-items-center">
          <i className="fa-regular fa-clock text-primary me-2"/>
          <h5 className="mb-0">{str("dashboard_requested_courses")}</h5>
          <span className="badge text-bg-secondary rounded-pill ms-2">{requested.length}</span>
        </div>
        <div className="list-group list-group-flush">
          {requested.map((course) => {
            const badge = REQUEST_STATE_BADGES[course.requeststate];
            return (
            <div key={course.id}
                 className="list-group-item border-start-accent d-flex flex-wrap justify-content-between align-items-center py-3">
              <div className="me-3">
                <div className="fw-semibold">{course.title}</div>
                <div className="small text-muted">
                  <i className="fa-solid fa-user-tie me-1"/>{str("dashboard_request_teacher")} {course.teacher}
                </div>
              </div>
              {badge && (
                <span className={`badge ${badge.css} rounded-pill text-white`}>
                    <i className={`${badge.icon} me-1`}/>{str(badge.key)}
                </span>
              )}
            </div>
            );
          })}
        </div>
      </div>
      {/* Import wizard modal*/}
      {wizardOpen && <Wizard onClose={() => {
        setWizardOpen(false);
        reloadCourses();
      }}/>}
    </>
  );
}