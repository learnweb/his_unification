import Fetch from "@moodle/lms/core/fetch";
import type {WizardCache} from "../wizard/steps/config";

export type Course = {
    id: number;
    cms: string;
    cmsinstance: number;
    cmsurl: string;
    title: string;
    shorttitle: string;
    description: string;
    teacher: string;
    semester: string;
    created: number;
    courseid: number;
    moodleid: number;
    requeststate: number;
    moodleurl: string;
};

export type Request = {
    id: number;
    title: string;
    requester: string;
    requeststate: number;
    created: number;
};

export type RequestActionType = "approve" | "reject";

export type RequestAction = {
    id: number;
    action: RequestActionType;
}

export type Teacher = {
    username: string;
    firstname: string;
    lastname: string;
};

export type Category = {
    id: number;
    name: string;
};

export const fetchDashboardCourses = async(): Promise<Course[]> => {
    const response = await Fetch.performGet("local_lsf_unification", "dashboard/dashboardcourses");
    return await response.json() as Promise<Course[]>;
};

export const fetchOwnCourses = async(): Promise<Course[]> => {
    const response = await Fetch.performGet("local_lsf_unification", "dashboard/courses");
    return await response.json() as Promise<Course[]>;
};

export const fetchTeacherCourses = async(username: string): Promise<Course[]> => {
    const response = await Fetch.performGet("local_lsf_unification", `dashboard/courses/${username}`);
    return await response.json() as Promise<Course[]>;
};

export const fetchRequests = async(): Promise<Request[]> => {
    const response = await Fetch.performGet("local_lsf_unification", "dashboard/dashboardrequests");
    return await response.json() as Promise<Request[]>;
};

/**
 * Submits a course request built from the whole wizard cache (branch, teacher,
 * course, ...). Resolves with the status the controller reports back.
 */
export const submitCourseRequest = async(cache: WizardCache): Promise<boolean> => {
    const response = await Fetch.performPost("local_lsf_unification", "dashboard/request", {body: cache});
    const payload = await response.json() as {status: boolean};
    return payload.status;
};

/**
 * Submits a course import built from the whole wizard cache (course details, category).
 */
export const submitCourseImport = async(cache: WizardCache): Promise<boolean> => {
    const response = await Fetch.performPost("local_lsf_unification", "dashboard/import", {body: cache});
    const payload = await response.json() as {status: boolean};
    return payload.status;
};

export const submitRequestAction = async(action: RequestAction): Promise<boolean> => {
    const response = await Fetch.performPost("local_lsf_unification", "dashboard/requestaction", {body: action});
    const payload = await response.json() as {status: boolean};
    return payload.status;
};

export const fetchTeachers = async(): Promise<Teacher[]> => {
    const response = await Fetch.performGet("local_lsf_unification", "dashboard/teachers");
    return await response.json() as Promise<Teacher[]>;
};

/** Loads all Moodle course categories the user may pick from for the import. */
export const fetchCategories = async(): Promise<Category[]> => {
    const response = await Fetch.performGet("local_lsf_unification", "dashboard/categories");
    return await response.json() as Promise<Category[]>;
};
