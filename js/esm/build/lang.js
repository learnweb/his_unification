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
 */const i=(t,n,r)=>n?M.util.get_string(t,"core",r):M.util.get_string(t,"local_lsf_unification",r);export{i as str};
