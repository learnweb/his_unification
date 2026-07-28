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
 * The shared body of both summary steps: it lists what is about to be sent, submits
 * it, and reports how that went. Both branches do exactly this and differ only in the
 * rows they list, the wording and which service call they make, so those are props.
 *
 * @module     lsf_unification/wizard/components/SummaryPanel
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Fragment, useState} from "react";

type Props = {
  rows: {label: string; value: string}[]; /** The cache as label/value pairs to show. */
  submitted: boolean; /** Whether the submit already went through. */
  onSubmitted: () => void; /** Tells the shell the wizard is done. */
  submit: () => Promise<boolean>; /** Sends the cache; resolves with whether it worked. */
  submitLabel: string; /** Caption of the submit button. */
  successText: string; /** Shown in place of the button once it went through. */
  errorText: string; /** Shown above the button when it did not. */
};

export default function SummaryPanel({rows, submitted, onSubmitted, submit, submitLabel, successText, errorText}: Props) {
  const [submitting, setSubmitting] = useState(false);
  const [failed, setFailed] = useState(false);

  const handleSubmit = () => {
    setSubmitting(true);
    setFailed(false);
    submit()
      .then((status) => (status ? onSubmitted() : setFailed(true)))
      .catch(() => setFailed(true))
      .finally(() => setSubmitting(false));
  };

  return (
    <>
      {/* The dt/dd pairs are the columns of this row, so they must sit in it directly:
          wrapping each pair in a row of its own would nest the gutters and pull the rows
          out to the left of the heading. */}
      <dl className="row mb-4">
        {rows.map(({label, value}) => (
          <Fragment key={label}>
            <dt className="col-sm-4 text-muted fw-normal">{label}</dt>
            <dd className="col-sm-8 fw-semibold">{value || "–"}</dd>
          </Fragment>
        ))}
      </dl>

      {submitted ? (
        <div className="alert alert-success d-flex align-items-center mb-0" role="alert">
          <i className="fa-solid fa-circle-check me-2"/>
          <div>{successText}</div>
        </div>
      ) : (
        <>
          {failed && (
            <div className="alert alert-danger d-flex align-items-center" role="alert">
              <i className="fa-solid fa-triangle-exclamation me-2"/>
              <div>{errorText}</div>
            </div>
          )}
          <div className="d-flex justify-content-end">
            <button type="button" className="btn btn-primary text-white" onClick={handleSubmit} disabled={submitting}>
              {submitting && <span className="spinner-border spinner-border-sm me-2" role="status"/>}
              {submitLabel}
            </button>
          </div>
        </>
      )}
    </>
  );
}
