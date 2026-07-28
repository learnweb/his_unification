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
 * The wizard shell: it owns the cache the steps fill up, which step is showing, and the Back/Next navigation.
 * It knows nothing about the individual steps beyond what the stepvmap in steps/config tells it.
 *
 * @module     lsf_unification/wizard/Wizard
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useState, useEffect} from "react";
import {STEPS, sequenceFor, canAdvance} from "./steps/config";
import type {WizardCache} from "./steps/config";
import {str} from "../lang";

export default function Wizard({onClose}: {onClose: () => void}) {
  const [step, setStep] = useState(0);
  const [showErrors, setShowErrors] = useState(false);
  const [cache, setCache] = useState<WizardCache>({branch: null, course: null, teacher: null, category: null, submitted: false});

  const patchCache = (partial: Partial<WizardCache>) => setCache((c) => ({...c, ...partial}));

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && onClose();
    document.addEventListener("keydown", onKey);

    return () => document.removeEventListener("keydown", onKey);
  }, [onClose]);

  const sequence = sequenceFor(cache.branch);
  const currentStepId = sequence[step];
  const {component: Step} = STEPS[currentStepId];
  const isFirst = step === 0;
  const isLast = step === sequence.length - 1;

  const title = currentStepId === "choose" || cache.branch === null
    ? str("wizard_shell_title")
    : str(cache.branch === "import" ? "course_import" : "course_request");

  const back = () => {
    setShowErrors(false);
    setStep((s) => s - 1);
  };

  const next = () => {
    if (!canAdvance(currentStepId, cache)) {
      setShowErrors(true);
      return;
    }
    setShowErrors(false);
    setStep((s) => s + 1);
  };

  return (
    <>
      <div className="modal fade show d-block" tabIndex={-1} role="dialog" aria-modal="true">
        <div className="modal-dialog modal-lg modal-dialog-centered">
          <div className="modal-content shadow">
            <div className="modal-header">
              <h5 className="modal-title">{title}</h5>
              <button type="button" className="btn-close" aria-label={str("close")} onClick={onClose}/>
            </div>
            <div className="modal-body">
              <Step cache={cache} patch={patchCache} showErrors={showErrors}/>
            </div>
            {!cache.submitted && (
              <div className="modal-footer justify-content-between">
                <button type="button" className="btn btn-outline-secondary" onClick={back} disabled={isFirst}>
                  {str("wizard_shell_back")}
                </button>
                {!isLast && (
                  <button type="button" className="btn btn-primary text-white" onClick={next}>
                    {str("wizard_shell_next")}
                  </button>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
      <div className="modal-backdrop fade show"/>
    </>
  );
}
