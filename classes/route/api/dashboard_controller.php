<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * REST API controller for the LSF unification dashboard.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lsf_unification\route\api;

use core\exception\invalid_parameter_exception;
use core\param;
use core\router\route;
use core\router\schema\response\payload_response;
use core\router\schema\parameters\path_parameter;
use local_lsf_unification\local\dto\course_dto;
use local_lsf_unification\local\service\cms_service;
use local_lsf_unification\local\service\dashboard_service;
use local_lsf_unification\route\schema\requestbodies\submit_body;
use local_lsf_unification\route\schema\responses\dashboard_categories_response;
use local_lsf_unification\route\schema\responses\dashboard_courses_response;
use local_lsf_unification\route\schema\responses\dashboard_teachers_response;
use local_lsf_unification\route\schema\responses\submit_response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Endpoint: GET /api/rest/v2/local_lsf_unification/dashboard/courses
 */
class dashboard_controller {
    /**
     * Return all dashboard (imported and requested) courses of the current user.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return payload_response
     */
    #[route(
        title: 'Get Dashboard courses',
        description: "Returns the current users imported and requested courses",
        path: '/dashboard/dashboardcourses',
        method: ['GET'],
        responses: [new dashboard_courses_response()],
    )]
    public function get_dashboard_courses(ServerRequestInterface $request, ResponseInterface $response): payload_response {
        $payload = array_map(
            fn(course_dto $dto) => $dto->to_array(),
            dashboard_service::get_dashboard_courses()
        );
        return new payload_response($payload, $request, $response);
    }

    /**
     * Return all cms courses of the current user.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return payload_response
     */
    #[route(
        title: 'Get cms courses',
        description: "Returns the current user's cms courses.",
        path: '/dashboard/courses',
        method: ['GET'],
        responses: [new dashboard_courses_response()],
    )]
    public function get_courses(ServerRequestInterface $request, ResponseInterface $response): payload_response {
        $payload = array_map(
            fn(course_dto $dto) => $dto->to_array(),
            cms_service::get_courses_from_teacher()
        );
        return new payload_response($payload, $request, $response);
    }

    /**
     * Return all cms courses of a specific user.
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $username
     * @return payload_response
     */
    #[route(
        title: 'Get cms courses for a user',
        description: "Returns a given user's cms courses.",
        path: '/dashboard/courses/{username}',
        method: ['GET'],
        pathtypes: [new path_parameter(name: 'username', type: param::ALPHANUM)],
        responses: [new dashboard_courses_response()],
    )]
    public function get_courses_for_user(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $username
    ): payload_response {
        $payload = array_map(
            fn(course_dto $dto) => $dto->to_array(),
            cms_service::get_courses_from_teacher((object) ['username' => $username], true)
        );
        return new payload_response($payload, $request, $response);
    }

    /**
     * Return all teachers with current courses in the cms.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return payload_response
     */
    #[route(
        title: 'Get teachers',
        description: "Returns a list of teachers that have courses.",
        path: '/dashboard/teachers',
        method: ['GET'],
        responses: [new dashboard_teachers_response()],
    )]
    public function get_teachers(ServerRequestInterface $request, ResponseInterface $response): payload_response {
        return new payload_response(cms_service::get_teachers(), $request, $response);
    }

    /**
     * Return all existing Moodle course categories.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return payload_response
     */
    #[route(
        title: 'Get categories',
        description: "Returns all existing Moodle course categories the teacher can import into.",
        path: '/dashboard/categories',
        method: ['GET'],
        responses: [new dashboard_categories_response()],
    )]
    public function get_categories(ServerRequestInterface $request, ResponseInterface $response): payload_response {
        return new payload_response(dashboard_service::get_categories(), $request, $response);
    }

    /**
     * Save a course request that was built in the wizard.
     *
     * The body is the wizard cache: the branch plus the chosen teacher and course.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return payload_response
     * @throws invalid_parameter_exception If the body is missing the branch, teacher or course.
     */
    #[route(
        title: 'Save a course request',
        description: 'Stores a course request that was built in the wizard.',
        path: '/dashboard/request',
        method: ['POST'],
        requestbody: new submit_body(),
        responses: [new submit_response()],
    )]
    public function post_request(ServerRequestInterface $request, ResponseInterface $response): payload_response {
        $cache = $request->getParsedBody();

        // The schema hands a slot the other branch owns through as null, so check here that the
        // keys this branch actually needs were filled in.
        foreach (['branch', 'teacher', 'course'] as $key) {
            if (empty($cache[$key])) {
                throw new invalid_parameter_exception("Missing '{$key}' in the request body.");
            }
        }
        $status = cms_service::add_request((object) $cache['course']);
        return new payload_response(['status' => $status], $request, $response);
    }

    /**
     * Save a course import.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return payload_response
     * @throws invalid_parameter_exception If the body is missing the branch, teacher or course.
     */
    #[route(
        title: 'Save a course import',
        description: 'Stores a course import that was built in the wizard.',
        path: '/dashboard/import',
        method: ['POST'],
        requestbody: new submit_body(),
        responses: [new submit_response()],
    )]
    public function post_import(ServerRequestInterface $request, ResponseInterface $response): payload_response {
        $cache = $request->getParsedBody();

        // The schema hands a slot the other branch owns through as null, so check here that the
        // keys this branch actually needs were filled in.
        foreach (['branch', 'course', 'category'] as $key) {
            if (empty($cache[$key])) {
                throw new invalid_parameter_exception("Missing '{$key}' in the request body.");
            }
        }
        $status = cms_service::import_course((object) $cache['course'], (object) $cache['category']);
        return new payload_response(['status' => $status], $request, $response);
    }
}
