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
 * Wizard step: the user refines the details of the course to import. Title, short title, semester and description are pre-filled
 * from the selected course and can be edited.
 *
 * @module     lsf_unification/wizard/steps/ImportDetailsStep
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useState} from "react";
import {fetchCategories, Category, Course} from "../../../services/csm";
import type {StepProps, WizardCache} from "../config";
import {str} from "../../../lang";

/**
 * Which of the required fields are still empty. The description is optional, everything else is needed to import the course.
 */
function missingFields(cache: WizardCache) {
  const blank = (value?: string) => (value ?? "").trim() === "";
  return {
    title: blank(cache.course?.title),
    shorttitle: blank(cache.course?.shorttitle),
    semester: blank(cache.course?.semester),
    category: cache.category === null,
  };
}

export const validate = (cache: WizardCache): boolean => !Object.values(missingFields(cache)).some(Boolean);

export default function ImportDetailsStep({cache, patch, showErrors}: StepProps) {
  const [categories, setCategories] = useState<Category[]>([]);
  const course = cache.course;
  const missing = missingFields(cache);

  /** A field turns red only after the user tried to move on, and goes back to normal as soon as they fill it in. */
  const invalid = (isMissing: boolean) => (showErrors && isMissing ? " is-invalid" : "");

  useEffect(() => {
    fetchCategories().then((categories) => setCategories(categories));
  }, []);

  /** Patches a single field of the course and writes the updated course back to the cache. */
  const patchCourse = (partial: Partial<Course>) => course && patch({course: {...course, ...partial}});

  return (
    <div>
      <h5 className="mb-1">{str("coursedetails_title")}</h5>
      <p className="text-muted">{str("coursedetails_text")}</p>

      <div className="mb-3">
        <label className="form-label">{str("title")}<span className="text-danger">*</span></label>
        <input type="text" className={`form-control${invalid(missing.title)}`}
            value={course?.title ?? ""} onChange={(e) => patchCourse({title: e.target.value})}/>
        <div className="invalid-feedback">{str("title_validate")}</div>
      </div>

      <div className="row">
        <div className="col-md-6 mb-3">
          <label className="form-label">{str("shorttitle")}<span className="text-danger">*</span></label>
          <input type="text" className={`form-control${invalid(missing.shorttitle)}`}
              value={course?.shorttitle ?? ""} onChange={(e) => patchCourse({shorttitle: e.target.value})}/>
          <div className="invalid-feedback">{str("shorttitle_validate")}</div>
        </div>
        <div className="col-md-6 mb-3">
          <label className="form-label">{str("semester")}<span className="text-danger">*</span></label>
          <input type="text" className={`form-control${invalid(missing.semester)}`}
              value={course?.semester ?? ""} onChange={(e) => patchCourse({semester: e.target.value})}/>
          <div className="invalid-feedback">{str("semester_validate")}</div>
        </div>
      </div>

      <div className="mb-3">
        <label className="form-label">{str("description")}</label>
        <textarea className="form-control" rows={4}
            value={course?.description ?? ""} onChange={(e) => patchCourse({description: e.target.value})}/>
      </div>

      <div className="mb-3">
        <label className="form-label">{str("category")}<span className="text-danger">*</span></label>
        <select className={`form-select${invalid(missing.category)}`} value={cache.category?.id ?? ""}
            onChange={(e) => patch({category: categories.find((c) => c.id === Number(e.target.value)) ?? null})}>
          <option value="" disabled>{str("category_choose")}</option>
          {categories.map((category) => (
            <option key={category.id} value={category.id}>{category.name}</option>
          ))}
        </select>
        <div className="invalid-feedback">{str("category_validate")}</div>
      </div>
    </div>
  );
}