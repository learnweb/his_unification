var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * Language strings of this plugin, the way get_string() works in PHP.
 *
 * This is synchronous, unlike core's own string API: dashboard.php calls
 * lsf_unification_cache_strings(), which hands every string of this component to the page
 * with strings_for_js(), so they are all in memory by the time anything renders. Any other
 * page that mounts these components has to do the same, otherwise every string shows up as
 * "[[key,local_lsf_unification]]".
 *
 * @module     lsf_unification/lang
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const str = /* @__PURE__ */ __name((key, fromCore, param) => {
  return fromCore ? M.util.get_string(key, "core", param) : M.util.get_string(key, "local_lsf_unification", param);
}, "str");
export {
  str
};
//# sourceMappingURL=lang.dev.js.map
